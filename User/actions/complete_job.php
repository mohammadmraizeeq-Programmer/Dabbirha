<?php
session_start();
require_once '../../includes/config.php';
require_once '../config/PayPal.php';
header('Content-Type: application/json');

function getPayPalAccessToken($clientId, $secret) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $secret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    $result = curl_exec($ch);
    $json = json_decode($result);
    return $json->access_token;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job_id = $_POST['job_id'] ?? null;
    $rating = $_POST['rating'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$job_id || !$rating || !$user_id) {
        echo json_encode(['success' => false, 'message' => 'Missing information.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Verify Job and Ownership
        $stmt = $pdo->prepare("SELECT payment_method, status FROM jobs WHERE job_id = ? AND user_id = ?");
        $stmt->execute([$job_id, $user_id]);
        $job_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$job_data || $job_data['status'] === 'closed') {
            throw new Exception("Job not found or already closed.");
        }

        // 2. Fetch Provider and Current Debt
        $stmt = $pdo->prepare("
            SELECT ja.provider_id, ja.quote_amount, p.paypal_email, p.pending_commission 
            FROM job_applications ja
            JOIN providers p ON ja.provider_id = p.provider_id
            WHERE ja.job_id = ? AND ja.application_status = 'assigned'
        ");
        $stmt->execute([$job_id]);
        $app_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$app_data) throw new Exception("No active provider found.");

        $provider_id = $app_data['provider_id'];
        $total_quote_jod = $app_data['quote_amount'];
        $target_email = $app_data['paypal_email'];
        $old_debt = $app_data['pending_commission']; //
        $current_job_commission = $total_quote_jod * 0.10; //

        $payout_jod = 0;
        $payout_data = null;

        // 3. Automated Payout & Debt Logic
        if ($job_data['payment_method'] === 'visa' || $job_data['payment_method'] === 'paypal') {
            // Total Platform Cut = (Current 10%) + (All Old Debt)
            $total_platform_cut = $current_job_commission + $old_debt;
            $payout_jod = $total_quote_jod - $total_platform_cut;

            // Handle case where debt is higher than current job value
            if ($payout_jod < 0) {
                $remaining_debt = abs($payout_jod);
                $payout_jod = 0;
            } else {
                $remaining_debt = 0;
            }

            // Convert and Payout via PayPal
            $conversion_rate = 1.41;
            $payout_usd = $payout_jod * $conversion_rate;

            if ($payout_usd > 0) {
                $token = getPayPalAccessToken(PAYPAL_CLIENT_ID, PAYPAL_SECRET);
                $payout_payload = [
                    "sender_batch_header" => [
                        "sender_batch_id" => "Job_" . $job_id . "_" . uniqid(),
                        "email_subject" => "Payment for Job #$job_id"
                    ],
                    "items" => [[
                        "recipient_type" => "EMAIL",
                        "amount" => ["value" => number_format($payout_usd, 2, '.', ''), "currency" => "USD"],
                        "receiver" => $target_email,
                        "note" => "Payout minus 10% commission and previous debt."
                    ]]
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, PAYPAL_BASE_URL . "/v1/payments/payouts");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json", "Authorization: Bearer $token"]);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payout_payload));
                $payout_response = curl_exec($ch);
                $payout_data = json_decode($payout_response, true);
                curl_close($ch);
            }

            // CLEAR THE DEBT (or update with remaining if job was too small to cover it)
            $updateDebt = $pdo->prepare("UPDATE providers SET pending_commission = ? WHERE provider_id = ?");
            $updateDebt->execute([$remaining_debt, $provider_id]);

        } else {
            // Cash job: Debt already updated in process_hire.php
            $total_platform_cut = $current_job_commission; 
        }

        // 4. Update Statuses
        $pdo->prepare("UPDATE jobs SET status = 'closed', is_paid = 1, updated_at = NOW() WHERE job_id = ?")->execute([$job_id]);
        $pdo->prepare("UPDATE job_applications SET application_status = 'accepted' WHERE job_id = ? AND application_status = 'assigned'")->execute([$job_id]);

        // 5. Log Transaction
        $logTransaction = $pdo->prepare("
            INSERT INTO transactions (
                job_id, provider_id, payment_method, 
                total_quote, admin_commission, debt_collected, 
                provider_payout, paypal_batch_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $logTransaction->execute([
            $job_id, $provider_id, $job_data['payment_method'],
            $total_quote_jod, $current_job_commission, 
            ($job_data['payment_method'] !== 'cash' ? $old_debt : 0),
            $payout_jod, $payout_data['sender_batch_header']['sender_batch_id'] ?? null
        ]);

        // 6. Finalize Review and Stats
        $pdo->prepare("INSERT INTO reviews (provider_id, user_id, job_id, rating, review_text, created_at) VALUES (?, ?, ?, ?, 'Completed.', NOW())")
            ->execute([$provider_id, $user_id, $job_id, $rating]);
        $pdo->prepare("UPDATE providers SET jobs_completed = jobs_completed + 1 WHERE provider_id = ?")->execute([$provider_id]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Job closed and debt settled!']);

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}