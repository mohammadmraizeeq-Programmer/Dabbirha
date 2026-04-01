<?php
include "../../includes/config.php";

$query = isset($_GET['q']) ? $_GET['q'] : '';
$location = isset($_GET['loc']) ? $_GET['loc'] : '';

// Prepare SQL with basic filtering
// We search in full_name and services
$sql = "SELECT u.full_name, p.provider_id, p.services, p.image, p.latitude, p.longitude, p.address
        FROM users u
        INNER JOIN providers p ON u.user_id = p.user_id
        WHERE u.role='provider'";

if (!empty($query)) {
    $safe_query = $conn->real_escape_string($query);
    $sql .= " AND (u.full_name LIKE '%$safe_query%' OR p.services LIKE '%$safe_query%')";
}

if (!empty($location)) {
    $safe_loc = $conn->real_escape_string($location);
    $sql .= " AND p.address LIKE '%$safe_loc%'";
}

$result = $conn->query($sql);
$providers = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['image'] = $row['image'] ? $row['image'] : 'https://via.placeholder.com/60x60?text=P';
        $providers[] = $row;
    }
}

// Return data as JSON for the JavaScript to read
header('Content-Type: application/json');
echo json_encode($providers);
$conn->close();
?>