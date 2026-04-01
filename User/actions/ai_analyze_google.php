<?php
// PHP Script: Intelligent Service Request Analyzer (Weighted Scoring Version)

header('Content-Type: application/json');

// Helper function for keyword checks
if (!function_exists('str_contains_any')) {
    function str_contains_any($haystack, array $needles)
    {
        $haystack = strtolower($haystack);
        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}

// --- DIAGNOSTIC BLOCK (File Upload Check) ---
if (!isset($_FILES['problem_media'])) {
    echo json_encode(['error' => 'No file data found in POST request.', 'code' => 400]);
    exit;
}

$file = $_FILES['problem_media'];
$upload_error_code = $file['error'];

if ($upload_error_code !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'File Upload Failed', 'code' => $upload_error_code]);
    exit;
}

$allowed_types = ['image/png', 'image/jpeg', 'video/mp4'];
$allowed_extensions = ['png', 'jpg', 'jpeg', 'mp4'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($file['type'], $allowed_types) || !in_array($ext, $allowed_extensions)) {
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

// --- GOOGLE API AUTHENTICATION ---
$key_file_path = __DIR__ . '/../config/service-account.json';
if (!file_exists($key_file_path)) {
    echo json_encode(['error' => 'Service account key file not found.']);
    exit;
}

$key = json_decode(file_get_contents($key_file_path), true);
$private_key_fixed = str_replace('\n', "\n", $key['private_key']);
$private_key_resource = openssl_pkey_get_private($private_key_fixed);

// JWT Generation
$header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
$now = time();
$claim = json_encode([
    'iss' => $key['client_email'],
    'scope' => 'https://www.googleapis.com/auth/cloud-platform',
    'aud' => $key['token_uri'],
    'iat' => $now,
    'exp' => $now + 3600
]);

$base64url = fn($data) => str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
$jwt = $base64url($header) . '.' . $base64url($claim);
openssl_sign($jwt, $sig, $private_key_resource, 'sha256');
$jwt .= '.' . $base64url($sig);

// Get access token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $key['token_uri']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($res, true);
$token = $tokenData['access_token'] ?? null;

if (!$token) {
    echo json_encode(['error' => 'Token Request Failed']);
    exit;
}

// --- VISION API REQUEST ---
$imageContent = base64_encode(file_get_contents($file['tmp_name']));
$requestBody = [
    'requests' => [[
        'image' => ['content' => $imageContent],
        'features' => [
            ['type' => 'LABEL_DETECTION', 'maxResults' => 15],
            ['type' => 'OBJECT_LOCALIZATION', 'maxResults' => 10]
        ]
    ]]
];

$ch = curl_init('https://vision.googleapis.com/v1/images:annotate');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$response_data = $result['responses'][0] ?? [];

$labels = $response_data['labelAnnotations'] ?? [];
$objects = $response_data['localizedObjectAnnotations'] ?? [];

// --- WEIGHTED SCORING CATEGORIZATION ---

// Initialize scores for your exact base_service names
$scores = [
    'appliance_repair' => 0,
    'ac_repair_cooling' => 0,
    'plumbing_service' => 0,
    'electrical_repair' => 0,
    'roofing_waterproofing' => 0,
    'pest_control' => 0,
    'locksmith_handyman' => 0,
    'carpentry_furniture' => 0,
    'painting_damage_repair' => 0,
    'cleaning_service' => 0
];

// Mapping keywords to categories
$keyword_map = [
    'appliance_repair' => ['refrigerator', 'washing machine', 'oven', 'stove', 'microwave', 'dishwasher', 'appliance'],
    'ac_repair_cooling' => ['air conditioning', 'vent', 'cooling', 'hvac', 'fan', 'compressor'],
    'plumbing_service' => ['pipe', 'water', 'faucet', 'leak', 'plumbing', 'sink', 'toilet', 'shower', 'urinal', 'drain', 'plumbing fixture'],
    'electrical_repair' => ['wire', 'electric', 'circuit', 'socket', 'outlet', 'light switch', 'lamp', 'breaker'],
    'roofing_waterproofing' => ['roof', 'shingle', 'ceiling leak', 'gutter'],
    'pest_control' => ['insect', 'cockroach', 'ant', 'termite', 'pest', 'bug'],
    'locksmith_handyman' => ['lock', 'key', 'handle', 'security', 'hinge', 'gate', 'door'],
    'carpentry_furniture' => ['wood', 'furniture', 'table', 'chair', 'shelf', 'cabinet'],
    'painting_damage_repair' => ['wall', 'paint', 'plaster', 'crack', 'damage', 'ceiling', 'spackle', 'brick', 'tiles', 'flooring'],
    'cleaning_service' => ['mess', 'clean', 'dirt', 'grime', 'stain', 'trash', 'rubbish', 'mold', 'graffiti']
];

// Process Labels and Objects into scores
$all_findings = [];

// Combine labels and objects for the results display
foreach($labels as $l) { $all_findings[] = strtolower($l['description']); }
foreach($objects as $o) { $all_findings[] = strtolower($o['name']); }

// Calculate weights based on confidence
foreach ($labels as $l) {
    $desc = strtolower($l['description']);
    $confidence = $l['score'] ?? 0;
    
    foreach ($keyword_map as $category_key => $keywords) {
        if (str_contains_any($desc, $keywords)) {
            // General architectural words like "Wall" or "Room" get low weight (1)
            // Specific keywords get higher weight (5)
            $weight = (in_array($desc, ['wall', 'room', 'flooring', 'ceiling', 'brick'])) ? 1 : 5;
            $scores[$category_key] += ($weight * $confidence);
        }
    }
}

// Objects usually indicate high intent (Localization)
foreach ($objects as $o) {
    $name = strtolower($o['name']);
    $confidence = $o['score'] ?? 0;
    foreach ($keyword_map as $category_key => $keywords) {
        if (str_contains_any($name, $keywords)) {
            $scores[$category_key] += (10 * $confidence); // Objects get the highest weight
        }
    }
}

// Find the winner
arsort($scores);
$category = array_key_first($scores);

if ($scores[$category] == 0) {
    $category = 'general_maintenance';
}

// --- FILE PERMANENT SAVE ---
$upload_dir = __DIR__ . '/../uploads/jobs/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$unique_name = uniqid('job_media_', true) . '.' . $ext;
$target = $upload_dir . $unique_name;
$permanent_web_path = null;

if (move_uploaded_file($file['tmp_name'], $target)) {
    $permanent_web_path = '/User/uploads/jobs/' . $unique_name;
}

// Final output
echo json_encode([
    'category' => $category,
    'labels' => array_unique($all_findings),
    'file_path' => $permanent_web_path,
    'scores' => $scores // Included for debugging so you can see the "winner" math
]);
exit;
// Update the job category in the database
$updateStmt = $pdo->prepare("UPDATE jobs SET service_type = ? WHERE job_id = ?");
$updateStmt->execute([$category, $_GET['job_id']]);

echo json_encode(['success' => true, 'category' => $category]);