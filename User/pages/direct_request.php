<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../reg.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$user_display_name = $user_data['full_name'] ?? 'User';
$stmt->close();

$provider_id = $_GET['provider_id'] ?? null;
$provider_name = $_GET['name'] ?? 'Provider';
$service_type = $_GET['service'] ?? 'General';

if (!$provider_id) {
    header("Location: browse_providers.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Direct Request - <?php echo htmlspecialchars($provider_name); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body class="bg-light">
    <?php include "../includes/navbar.php"; ?>
    <div class="container py-5 mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="fw-bold">Direct Request</h3>
                        <p class="text-muted">You are requesting a quote specifically from <strong><?php echo htmlspecialchars($provider_name); ?></strong> for <strong><?php echo htmlspecialchars($service_type); ?></strong> services.</p>
                        <hr>
                        <form action="../actions/upload_job.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="redirect_to" value="location_step">
                            <input type="hidden" name="target_provider_id" value="<?php echo htmlspecialchars($provider_id); ?>">
                            <input type="hidden" name="ai_category_suggestion" value="<?php echo htmlspecialchars($service_type); ?>">
                            <input type="hidden" name="problem_title" value="Direct Request for <?php echo htmlspecialchars($service_type); ?>">
                            <input type="hidden" name="latitude" id="lat_input" value="0">
                            <input type="hidden" name="longitude" id="lng_input" value="0">
                            <input type="hidden" name="location_pin" id="location_pin" value="0,0">
                           

                            <div class="mb-3">
                                <label class="form-label fw-bold">Describe your problem</label>
                                <textarea name="problem_description" class="form-control" rows="4" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Contact Phone Number</label>
                                <input type="tel" name="contact_phone" class="form-control" placeholder="07XXXXXXXX" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Service Address / Location</label>
                                <div class="input-group">
                                    <input type="text" id="service_address" name="service_address" class="form-control" placeholder="Click the button to pin your location" required>
                                    <button class="btn btn-outline-primary" type="button" id="getLocationBtn">
                                        <i class="bi bi-geo-alt-fill"></i> Get Location
                                    </button>
                                </div>
                                <div id="locationError" class="text-danger small d-none mt-1"></div>

                                <input type="hidden" name="latitude" id="lat_input">
                                <input type="hidden" name="longitude" id="lng_input">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Upload a Photo (Optional)</label>
                                <input type="file" name="problem_media" class="form-control">
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Urgency</label>
                                <select name="urgency_level" class="form-select">
                                    <option value="low">Not Urgent</option>
                                    <option value="medium" selected>Standard</option>
                                    <option value="high">Emergency</option>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg">Send Request</button>
                                <a href="browse_providers.php" class="btn btn-link text-muted mt-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    document.getElementById('getLocationBtn').addEventListener('click', function() {
        const btn = this;
        const errorDiv = document.getElementById('locationError');
        const addressInput = document.getElementById('service_address');
        const latInput = document.getElementById('lat_input');
        const lngInput = document.getElementById('lng_input');

        // UI Reset
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Locating...';
        errorDiv.classList.add('d-none');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    // 1. Store raw coordinates in hidden inputs
                    latInput.value = lat;
                    lngInput.value = lng;
                    document.getElementById('location_pin').value = lat + ", " + lng;
                    // 2. Perform Reverse Geocoding using Google Maps API
                    // Note: This requires the Google Maps JS script to be loaded on the page
                    if (typeof google !== 'undefined' && google.maps) {
                        const geocoder = new google.maps.Geocoder();
                        const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };

                        geocoder.geocode({ location: latlng }, (results, status) => {
                            if (status === "OK") {
                                if (results[0]) {
                                    // Fill the visible input with the actual address
                                    addressInput.value = results[0].formatted_address;
                                    btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Location Found';
                                } else {
                                    addressInput.value = `Location Captured (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                                }
                            } else {
                                console.error("Geocoder failed due to: " + status);
                                addressInput.value = `Location Captured (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                            }
                            
                            btn.classList.replace('btn-outline-primary', 'btn-success');
                            btn.disabled = false;
                        });
                    } else {
                        // Fallback if Google Maps is not loaded
                        addressInput.value = `GPS: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Saved';
                        btn.classList.replace('btn-outline-primary', 'btn-success');
                        btn.disabled = false;
                    }
                },
                (error) => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Retry Location';
                    
                    let errorMsg = "Unable to retrieve location.";
                    if (error.code === 1) errorMsg = "Location access denied. Please type address manually.";
                    if (error.code === 3) errorMsg = "Request timed out. Please try again.";
                    
                    errorDiv.textContent = errorMsg;
                    errorDiv.classList.remove('d-none');
                }, 
                {
                    enableHighAccuracy: true,
                    timeout: 8000, // Increased timeout slightly for better accuracy
                    maximumAge: 0
                }
            );
        } else {
            errorDiv.textContent = "Geolocation is not supported by your browser.";
            errorDiv.classList.remove('d-none');
            btn.disabled = false;
        }
    });
</script>
</body>

</html>