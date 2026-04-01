<?php
// PHP SCRIPT START: Session Management and Configuration Loading.
session_start();

// Load essential system configuration (Database connection: $pdo, etc.)
require_once __DIR__ . '/../../includes/config.php';

// --- CRITICAL: AUTHENTICATION AND REDIRECTION ---
// If the user is not logged in, capture the current URL and redirect to login page.
if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: ../reg.php?redirect=$redirect_url");
    exit;
}
$user_display_name = $logged_in_user['full_name'] ?? 'User';
// User ID is safely secured from the session for subsequent database operations.
$user_id = $_SESSION['user_id'];
$page_title = "Intelligent Service Request | AI Diagnosis";

// --- FETCH LOGGED-IN USER DATA FOR FORM PRE-FILLING ---
try {
    // Select name, phone, and role. Adjust query to fetch address/location if available.
    // NOTE: If you add 'address' or 'location_pin' columns to the 'users' table, update this SELECT statement.
    $sql = "SELECT full_name, phone, role FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $logged_in_user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Security Check: Handle case where session ID doesn't correspond to a user.
    if (!$logged_in_user) {
        session_unset();
        session_destroy();
        header('Location: ../reg.php?error=relogin_required');
        exit;
    }

    // --- ROLE-BASED ACCESS CONTROL ---
    // Redirect 'provider' or 'admin' roles to their respective dashboards.
    if ($logged_in_user['role'] === 'provider') {
        header('Location: ../pages/provider_dashboard.php');
        exit;
    }
    if ($logged_in_user['role'] === 'admin') {
        header('Location: ../pages/admin_dashboard.php');
        exit;
    }

    // Set variables used to pre-fill the form (escaped for security).
    $user_phone = htmlspecialchars($logged_in_user['phone']) ?? '';
    // Default values for other fields if they are not stored in the session/DB.
    // These should be updated if you fetch more data in the SQL query above.
    $user_address = '';
    $user_property_type = 'apartment';
    $user_location_pin = '';
} catch (PDOException $e) {
    // Critical: Log the error and ensure fallback variables prevent script failure.
    error_log("DB Error fetching user data (ID: $user_id): " . $e->getMessage());
    $user_phone = $user_address = $user_location_pin = '';
    $user_property_type = 'apartment';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/upload_problem.css" />
    
    <style>
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body>
    
    <form action="../actions/upload_job.php" method="POST" enctype="multipart/form-data" id="submissionForm">
    <?php include '../includes/navbar.php'; ?>

<header class="hero-modern">
    <div class="container">
        <h1 class="display-4">Describe Your Issue</h1>
        <p class="lead">Our AI-powered system will analyze your problem and find the best provider for you.</p>
    </div>
</header>

        <section class="py-5">
            <div class="container">
                <div class="modern-card mx-auto" style="max-width: 1100px;">
                    <div class="row">
                        <div class="col-lg-3">
                            <ul class="step-indicators pt-4">
                                <li class="active" id="step-1-indicator" data-aos="fade-right">
                                    <div class="icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                    <h6 class="ms-4 fw-bold mb-0">AI Media Upload</h6>
                                    <p class="small text-muted ms-4 mb-0">File selection & instant analysis.</p>
                                </li>
                                <li id="step-2-indicator" data-aos="fade-right" data-aos-delay="100">
                                    <div class="icon"><i class="bi bi-geo-alt-fill"></i></div>
                                    <h6 class="ms-4 fw-bold mb-0">Contact & Location</h6>
                                    <p class="small text-muted ms-4 mb-0">Address and appointment details.</p>
                                </li>
                            </ul>
                        </div>

                        <div class="col-lg-9">
                            <h2 class="fw-bolder mb-5 text-secondary">Initiate New Service</h2>
                            <div class="form-steps-container">

                                <div class="form-step visible" id="step-1-content">

                                    <div id="uploadArea" role="button" class="upload-zone p-5 mb-4 rounded-4">
                                        <i class="bi bi-cloud-arrow-up display-3 text-primary mb-3"></i>
                                        <p class="fw-bold mb-1 text-secondary">Drag & Drop or Click to Select File</p>
                                        <p class="small text-muted mb-0">Image or Video (max 20MB)</p>
                                        <input type="file" class="form-control d-none" id="mediaUpload" name="problem_media" accept=".png,.jpg,.jpeg,.mp4" required>
                                    </div>

                                    <div id="aiResult" class="mt-4 p-4 rounded-3 d-none ai-result">
                                        <h5 class="mb-3 fw-bold text-primary">AI Diagnosis Complete</h5>
                                        <p class="mb-2 fw-medium">Suggested Service: <span class="text-success fw-bolder fs-6" id="aiCategory">Processing...</span></p>

                                        <input type="hidden" name="ai_category_suggestion" id="aiCategoryInput">
                                        <input type="hidden" id="problemTitle" name="problem_title">

                                        <div class="row g-3">

                                            <div class="col-md-5">
                                                <label for="urgencyLevel" class="form-label fw-medium small text-muted">Urgency *</label>
                                                <select class="form-select" id="urgencyLevel" name="urgency_level" required>
                                                    <option value="" selected disabled>Select urgency...</option>
                                                    <option value="low">Low (Next Week)</option>
                                                    <option value="medium">Medium (3-5 Days)</option>
                                                    <option value="high">High (24 Hours)</option>
                                                    <option value="critical">Critical (Emergency - ASAP)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="form-floating">
                                                <textarea class="form-control" id="problemDescription" name="problem_description" rows="3" placeholder="Describe what happened"></textarea>
                                                <label for="problemDescription">Brief Description of Problem (Optional)</label>
                                            </div>
                                        </div>

                                        <div class="d-grid mt-4">
                                            <button type="button" class="btn btn-primary btn-lg" id="nextStepBtn" disabled>
                                                Proceed to Step 2 <i class="bi bi-arrow-right-circle-fill ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-step hidden" id="step-2-content">
                                    <h4 class="fw-semibold mb-4 text-secondary">Step 2: Location and Contact</h4>

                                    <div class="p-4 border rounded-4 bg-light shadow-sm">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input type="tel" class="form-control" id="contactPhone" name="contact_phone" value="<?php echo htmlspecialchars($user_phone); ?>" required inputmode="tel">
                                                    <label for="contactPhone">Primary Contact Phone *</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <select class="form-select" id="propertyType" name="property_type" required>
                                                        <option value="apartment" <?php if ($user_property_type == 'apartment') echo 'selected'; ?>>Apartment</option>
                                                        <option value="house" <?php if ($user_property_type == 'house') echo 'selected'; ?>>House/Villa</option>
                                                        <option value="office" <?php if ($user_property_type == 'office') echo 'selected'; ?>>Office/Commercial</option>
                                                        <option value="other" <?php if ($user_property_type == 'other') echo 'selected'; ?>>Other</option>
                                                    </select>
                                                    <label for="propertyType">Property Type *</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <div class="form-floating">
                                                <input type="text" class="form-control" id="serviceAddress" name="service_address" value="<?php echo htmlspecialchars($user_address); ?>" required autocomplete="street-address">
                                                <label for="serviceAddress">Service Address *</label>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <label for="locationPin" class="form-label fw-medium small text-muted"><i class="bi bi-geo-alt m-2"></i>Location</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="locationPin" name="location_pin" value="<?php echo htmlspecialchars($user_location_pin); ?>" placeholder="Click the button to pin your location" required readonly>
                                                <button class="btn btn-outline-primary" type="button" id="getLocationBtn"><i class="bi bi-crosshair me-1"></i> Use Current Location</button>
                                            </div>
                                            <div id="locationError" class="text-danger small mt-2 d-none"></div>
                                        </div>
                                    </div>

                                    <div class="d-grid mt-5">
                                        <button type="submit" class="btn btn-success btn-lg" id="submitBtn">
                                            <i class="bi bi-send-check-fill me-2"></i> Confirm & Submit Request
                                        </button>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <button type="button" class="btn btn-link btn-sm text-secondary" id="prevStepBtn">
                                            <i class="bi bi-arrow-left"></i> Review AI Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <input type="hidden" name="image_base64" id="image_base64">

        <?php include '../includes/footer.php'; ?>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
document.addEventListener('DOMContentLoaded', () => {

    AOS.init({
        duration: 600,
        once: true
    });

    // --- DOM ELEMENT CACHING ---
    const submissionForm = document.getElementById('submissionForm'); // For required pin validation
    const uploadArea = document.getElementById('uploadArea');
    const mediaInput = document.getElementById('mediaUpload');
    const aiResultDiv = document.getElementById('aiResult');
    const aiCategorySpan = document.getElementById('aiCategory');
    const aiCategoryInput = document.getElementById('aiCategoryInput');
    const problemTitleInput = document.getElementById('problemTitle');

    const nextStepBtn = document.getElementById('nextStepBtn');
    const prevStepBtn = document.getElementById('prevStepBtn');
    const step1Content = document.getElementById('step-1-content');
    const step2Content = document.getElementById('step-2-content');
    const step1Indicator = document.getElementById('step-1-indicator');
    const step2Indicator = document.getElementById('step-2-indicator');
    const stepsContainer = document.querySelector('.form-steps-container');
    const MAX_SIZE_BYTES = 20 * 1024 * 1024; // Maximum allowed file size (20MB)
    const getLocationBtn = document.getElementById('getLocationBtn');
    const locationPinInput = document.getElementById('locationPin');
    const locationErrorDiv = document.getElementById('locationError');

    // --- REQUIRED PIN VALIDATION ON SUBMIT ---
    submissionForm.addEventListener('submit', (e) => {
        if (!locationPinInput.value || locationPinInput.value.trim() === "") {
            e.preventDefault(); // Stop form submission
            alert("Please use the 'Use Current Location' button to pin your address before submitting.");
            locationPinInput.classList.add('is-invalid');
            
            // Scroll to the error so they see it
            locationPinInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // --- STEP NAVIGATION LOGIC (Unchanged) ---
    const showStep = (stepNumber) => {
        const currentStep = stepNumber === 1 ? step1Content : step2Content;
        const prevStep = stepNumber === 1 ? step2Content : step1Content;

        prevStep.classList.remove('visible');
        prevStep.classList.add('hidden');

        setTimeout(() => {
            currentStep.classList.remove('hidden');
            currentStep.classList.add('visible');
            stepsContainer.style.minHeight = currentStep.offsetHeight + 'px';
        }, 10);

        step1Indicator.classList.toggle('active', stepNumber === 1);
        step2Indicator.classList.toggle('active', stepNumber === 2);
    };

    // Initial setup and button handlers
    stepsContainer.style.minHeight = step1Content.offsetHeight + 'px';
    showStep(1);

    nextStepBtn.addEventListener('click', () => {
        const urgencyLevel = document.getElementById('urgencyLevel');
        if (urgencyLevel.value === '') {
            alert('Please select an urgency level before proceeding.');
            urgencyLevel.classList.add('is-invalid');
            return;
        }
        urgencyLevel.classList.remove('is-invalid');
        showStep(2);
    });
    prevStepBtn.addEventListener('click', () => showStep(1));


            // --- MEDIA UPLOAD AND FILE HANDLING ---

            // Utility function to update the file upload UI
            const resetUploadAreaUI = (message = 'Drag & Drop or Click to Select File', iconClass = 'bi bi-cloud-arrow-up', textColor = 'text-primary') => {
                uploadArea.innerHTML = `<i class="${iconClass} display-3 ${textColor} mb-3"></i><p class="fw-bold mb-1 text-secondary">${message}</p><p class="small text-muted mb-0">Image or Video (max 20MB)</p>`;
                // ADDED: Remove drag-over class on reset
                uploadArea.classList.remove('drag-over');
            };

            // Handles file selection, validation, and initiates AI analysis
            const handleFileSelect = () => {
                const file = mediaInput.files[0];

                aiResultDiv.classList.add('d-none');
                aiResultDiv.classList.remove('ai-analyzing', 'ai-error'); // Reset classes
                nextStepBtn.disabled = true;

                if (file) {
                    if (file.size > MAX_SIZE_BYTES) {
                        alert('File too large (max 20MB).');
                        mediaInput.value = '';
                        resetUploadAreaUI('File Rejected: Too Large', 'bi bi-exclamation-octagon', 'text-danger');
                        return;
                    }

                    // Update UI for file selected state
                    uploadArea.innerHTML = `<i class="bi bi-file-earmark-arrow-up display-3 text-primary mb-3"></i><p class="fw-bold">File selected: ${file.name}</p><p class="small text-muted mb-0">Starting AI analysis...</p>`;

                    analyzeWithAI(file);
                    showStep(1); // Remain on Step 1 while analysis runs
                } else {
                    resetUploadAreaUI();
                }
            };

            // Event listeners for file input and drag/drop functionality
            if (uploadArea && mediaInput) {
                uploadArea.addEventListener('click', () => mediaInput.click());

                // ADDED: Drag/Drop visual feedback logic
                uploadArea.addEventListener('dragenter', e => {
                    e.preventDefault();
                    e.stopPropagation();
                    uploadArea.classList.add('drag-over');
                });
                uploadArea.addEventListener('dragover', e => {
                    e.preventDefault();
                    e.stopPropagation();
                    uploadArea.classList.add('drag-over');
                });
                uploadArea.addEventListener('dragleave', e => {
                    e.preventDefault();
                    e.stopPropagation();
                    // Conditional removal ensures the class is kept when dragging over children
                    if (e.relatedTarget && !uploadArea.contains(e.relatedTarget)) {
                        uploadArea.classList.remove('drag-over');
                    } else if (!e.relatedTarget) {
                        uploadArea.classList.remove('drag-over');
                    }
                });

                uploadArea.addEventListener('drop', e => {
                    e.preventDefault();
                    e.stopPropagation();
                    uploadArea.classList.remove('drag-over'); // Remove class on drop
                    if (e.dataTransfer.files.length) {
                        mediaInput.files = e.dataTransfer.files;
                        handleFileSelect();
                    }
                });
                mediaInput.addEventListener('change', handleFileSelect);
            }

            // --- CORE AJAX: AI ANALYSIS FUNCTION ---
            async function analyzeWithAI(file) {
                const endpoint = '../actions/ai_analyze_google.php';
                const formData = new FormData();
                formData.append('problem_media', file);

                // Set loading state UI
                uploadArea.innerHTML = `<span class="spinner-border text-primary me-2" role="status"></span><p>AI is Analyzing...</p>`;

                // ADDED: Set analyzing class
                aiResultDiv.classList.add('ai-analyzing');
                aiResultDiv.classList.remove('d-none', 'ai-error');
                aiCategorySpan.textContent = 'Processing...';

                try {
                    const response = await fetch(endpoint, {
                        method: 'POST',
                        body: formData
                    });
                    let data = await response.json();

                    // REMOVED: analyzing class after response
                    aiResultDiv.classList.remove('ai-analyzing');

                    if (data.error || !response.ok) {
                        const errorMessage = data.error || `HTTP Error! Status: ${response.status}`;
                        aiCategorySpan.textContent = 'Analysis Failed.';
                        resetUploadAreaUI(`Error: ${errorMessage}. Please retry.`, 'bi bi-x-octagon', 'text-danger');
                        nextStepBtn.disabled = true;
                        // ADDED: error class
                        aiResultDiv.classList.add('ai-error');
                    } else {
                        // SUCCESS:
                        const suggestedCategory = data.category
                            .replace(/_/g, ' ')
                            .replace(/\b\w/g, l => l.toUpperCase());

                        aiCategorySpan.textContent = suggestedCategory;
                        aiCategoryInput.value = data.category;
                        problemTitleInput.value = `Service Request: ${suggestedCategory}`;

                        uploadArea.innerHTML = `<i class="bi bi-check-circle-fill display-3 text-success mb-3"></i><p class="fw-bold">AI Success! (${suggestedCategory})</p>`;
                        nextStepBtn.disabled = false;
                        // REMOVED: error class if successful
                        aiResultDiv.classList.remove('ai-error');
                    }

                } catch (error) {
                    console.error('Fetch Error:', error);
                    aiCategorySpan.textContent = 'Network/Fatal Error.';
                    resetUploadAreaUI('Connection Error. Try again.', 'bi bi-wifi-off', 'text-danger');
                    nextStepBtn.disabled = true;
                    // ADDED: error class
                    aiResultDiv.classList.remove('ai-analyzing');
                    aiResultDiv.classList.add('ai-error');
                }

                stepsContainer.style.minHeight = step1Content.offsetHeight + 'px';
            }


            // --- GEOLOCATION LOGIC ---

            getLocationBtn.addEventListener('click', () => {
                if (navigator.geolocation) {
                    locationErrorDiv.classList.add('d-none');
                    getLocationBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Getting...';
                    getLocationBtn.disabled = true;

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            locationPinInput.value = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                            // UPDATED: Success text with icon
                            getLocationBtn.innerHTML = '<i class="bi bi-geo-alt-fill"></i> Location Pinned';
                            getLocationBtn.disabled = false;
                        },
                        (error) => {
                            let errorMsg = 'Unable to retrieve location. ';
                            if (error.code === error.PERMISSION_DENIED) {
                                errorMsg = 'Permission denied. Allow location access in settings.';
                            }
                            locationErrorDiv.textContent = errorMsg;
                            locationErrorDiv.classList.remove('d-none');
                            // UPDATED: Error text with icon
                            getLocationBtn.innerHTML = '<i class="bi bi-x-circle-fill me-1"></i> Retry Geolocation';
                            getLocationBtn.disabled = false;
                        }, {
                            enableHighAccuracy: true,
                            timeout: 5000,
                            maximumAge: 0
                        }
                    );
                } else {
                    locationErrorDiv.textContent = 'Geolocation is not supported by this browser.';
                    locationErrorDiv.classList.remove('d-none');
                }
            });
        });
        document.getElementById('mediaUpload').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // This converts the image to a long text string
                    document.getElementById('image_base64').value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>