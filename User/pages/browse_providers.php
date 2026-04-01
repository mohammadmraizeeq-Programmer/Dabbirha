<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../includes/config.php";

$sql = "SELECT u.user_id, u.full_name, u.email, u.phone,
                p.provider_id, p.services, p.image, p.latitude, p.longitude, p.address
        FROM users u
        INNER JOIN providers p ON u.user_id = p.user_id
        WHERE u.role='provider'";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error);
}

$providers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['image'] = $row['image'] ? htmlspecialchars($row['image']) : 'https://via.placeholder.com/60x60?text=P';
        $row['full_name_html'] = htmlspecialchars($row['full_name']);
        $row['services_html'] = htmlspecialchars($row['services']);
        $row['address_html'] = htmlspecialchars($row['address']);
        $providers[] = $row;
    }
}
$conn->close();
$provider_json = json_encode($providers);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dabbirha Providers - Map View</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/browse_providers.css">
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold ms-3" href="dashboard.php">
                <i class="bi bi-tools me-2"></i>Dabbirha دبّرها
            </a>


        </div>
    </nav>

    <div id="app-container">
        <div id="sidebar">
            <div class="sidebar-header">
                <h4 class="fw-bold mb-0">Find Your Expert</h4>
                <p class="text-muted small"><?php echo count($providers); ?> providers found</p>
            </div>

            <div class="search-filters">
                <div class="mb-3">
                    <input id="search-input" class="form-control" type="search" placeholder="Search by name or service (e.g. Plumber)...">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-12"> <select id="location-select" class="form-select">
                            <option value="" selected>All Locations</option>
                            <option value="Amman">Amman</option>
                            <option value="Irbid">Irbid</option>
                            <option value="Zarqa">Zarqa</option>
                            <option value="Madaba">Madaba</option>
                            <option value="Salt">Salt</option>
                        </select>
                    </div>
                </div>
                <button id="filter-btn" class="btn btn-sm w-100 mb-3" style="background-color: var(--accent-color); color: white;">
                    Refresh Results
                </button>
            </div>

            <div id="provider-list">
                <?php if (!empty($providers)) : ?>
                    <?php foreach ($providers as $row) : ?>
                        <div class="provider-card-v4"
                            data-lat="<?php echo $row['latitude']; ?>"
                            data-lng="<?php echo $row['longitude']; ?>">

                            <img class="card-image-v4" src="<?php echo $row['image']; ?>" alt="Provider Image">

                            <div class="card-details-v4">
                                <h5><?php echo $row['full_name_html']; ?></h5>
                                <p class="text-secondary mb-1"><?php echo $row['services_html']; ?></p>
                                <p class="mb-2"><i class="bi bi-geo-alt me-1"></i><?php echo $row['address_html']; ?></p>

                                <div class="d-flex gap-2 mt-2">
                                    <a href="provider_profile_page.php?id=<?php echo $row['provider_id']; ?>"
                                        class="btn btn-sm btn-outline-primary flex-grow-1">
                                        View Profile
                                    </a>
                                    <a href="direct_request.php?provider_id=<?php echo $row['provider_id']; ?>&name=<?php echo urlencode($row['full_name']); ?>&service=<?php echo urlencode($row['services']); ?>"
                                        class="btn btn-sm btn-success flex-grow-1">
                                        Request
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p class="text-center mt-5">No providers found matching your criteria.</p>
                <?php endif; ?>
            </div>
        </div>

        <div id="map-container">
            <div id="map"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const providersData = <?php echo $provider_json; ?>;
    </script>

    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=YOUR KEY PASSWORD&callback=initMap">
    </script>

    <script src="../assets/js/browse_providers.js"></script>
</body>

</html>