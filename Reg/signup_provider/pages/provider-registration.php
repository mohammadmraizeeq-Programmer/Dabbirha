<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../../../includes/config.php";

// Debug: Check what's in session
error_log("SESSION data at start: " . print_r($_SESSION, true));

// Check if user is coming from verification with a verified email
$verified_email = $_SESSION['verified_email'] ?? '';
$is_verified_user = false;

// Check if user exists and is verified in database
if (!empty($verified_email)) {
  $check_sql = "SELECT user_id, verified FROM users WHERE email = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("s", $verified_email);
  $check_stmt->execute();
  $result = $check_stmt->get_result();

  if ($result->num_rows > 0) {
    $is_verified_user = true;
    $user_data = $result->fetch_assoc();
    $_SESSION['verified_user_id'] = $user_data['user_id'];
  }
}

// Load form data from session - IMPORTANT: Don't overwrite if we already have it
if (!isset($_SESSION['signup_data'])) {
  $_SESSION['signup_data'] = [];
}

$form_data = $_SESSION['signup_data'];

// Get current step
$step = isset($_GET['step']) ? (int) $_GET['step'] : 1;

// Handle step data saving when POSTed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $step = (int) ($_POST['step'] ?? $step);

  // Save all POST data except the step and save_step_data flag
  foreach ($_POST as $key => $value) {
    if ($key !== 'step' && $key !== 'save_step_data' && !empty($value)) {
      $_SESSION['signup_data'][$key] = $value;
    }
  }

  // Handle file uploads
  if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    // Save file info temporarily
    $_SESSION['signup_data']['profile_picture_tmp'] = $_FILES['profile_picture']['tmp_name'];
    $_SESSION['signup_data']['profile_picture_name'] = $_FILES['profile_picture']['name'];
    $_SESSION['signup_data']['profile_picture_size'] = $_FILES['profile_picture']['size'];
    $_SESSION['signup_data']['profile_picture_type'] = $_FILES['profile_picture']['type'];
  }

  // If this is just a step save (not form submission), redirect back to same step
  if (isset($_POST['save_step_data'])) {
    // Also save any verified email info
    if (!empty($verified_email)) {
      $_SESSION['signup_data']['email'] = $verified_email;
    }

    header("Location: ?step=" . $step);
    exit();
  }
}

// Update current form data
$form_data = $_SESSION['signup_data'];
$error = $_SESSION['signup_error'] ?? null;
unset($_SESSION['signup_error']);

// Debug: Log what we have before rendering
error_log("Form data before render: " . print_r($form_data, true));
error_log("Verified email: " . $verified_email);
error_log("Is verified user: " . ($is_verified_user ? 'yes' : 'no'));
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Become a Service Provider</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="../assets/css/provider_registration_style.css?v=3">
  <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>

<body>
  <div class="registration-container">
    <div class="registration-card" data-aos="fade-up" data-aos-duration="1000">
      <!-- Header -->
      <div class="registration-header">
        <h1>Become a Service Provider</h1>
        <p>Join our platform and start offering your professional services to customers in Jordan</p>
      </div>

      <!-- Step Indicator -->
      <div class="step-indicator">
        <div class="step-line"></div>
        <div class="step-progress" id="stepProgress"></div>

        <div class="step active" data-step="1">
          <div class="step-number">1</div>
          <div class="step-label">Basic Info</div>
        </div>
        <div class="step" data-step="2">
          <div class="step-number">2</div>
          <div class="step-label">Services</div>
        </div>
        <div class="step" data-step="3">
          <div class="step-number">3</div>
          <div class="step-label">Location</div>
        </div>
        <div class="step" data-step="4">
          <div class="step-number">4</div>
          <div class="step-label">Verification</div>
        </div>
        <div class="step" data-step="5">
          <div class="step-number">5</div>
          <div class="step-label">Complete</div>
        </div>
      </div>

      <!-- Registration Form -->
      <form id="providerRegistrationForm" action="../actions/process-signup.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="googleSignupFlag" name="google_signup" value="0">
        <input type="hidden" id="googleId" name="google_id" value="">

        <!-- Step 1: Basic Information -->
        <div class="form-section active" id="step1">
          <h3 class="form-title">
            <i class="fas fa-user-circle me-2"></i>
            Basic Information
          </h3>

          <div class="row">
            <!-- Google Signup Option -->
            <div class="col-12">
              <div class="google-signup-container">
                <p class="mb-3">Sign up quickly with Google</p>
                <div id="g_id_onload"
                  data-client_id="84776159395-u2bg538ej9gb816uuvvsesc9r12o82jh.apps.googleusercontent.com"
                  data-context="signup" data-ux_mode="popup" data-callback="handleGoogleSignIn"
                  data-auto_prompt="false">
                </div>
                <div class="g_id_signin" data-type="standard" data-shape="rectangular" data-theme="outline"
                  data-text="signup_with" data-size="large" data-logo_alignment="left" data-width="300">
                </div>
              </div>

              <div class="text-center my-4">
                <span class="text-muted">OR</span>
              </div>
            </div>

            <!-- Profile Picture -->
            <div class="col-12">
              <div class="profile-picture-container">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Profile Preview"
                  class="profile-picture-preview" id="profilePreview">
                <input type="file" id="profilePicture" name="profile_picture" accept="image/*" class="d-none">
                <label for="profilePicture" class="profile-upload-label">
                  <i class="fas fa-camera me-2"></i>
                  Upload Profile Photo
                </label>
                <small class="d-block text-muted mt-2">JPG, PNG up to 2MB</small>
              </div>
            </div>

            <!-- Full Name -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="full_name" class="form-label required">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required
                  placeholder="Enter your full name"
                  value="<?php echo htmlspecialchars($form_data['full_name'] ?? ''); ?>">
              </div>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="email" class="form-label required">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required 
                  placeholder="name@example.com"
                  value="<?php echo htmlspecialchars($form_data['email'] ?? $verified_email ?? ''); ?>">
              </div>
            </div>

            <!-- Phone -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="phone" class="form-label required">Phone Number</label>
                <div class="input-group">
                  <span class="input-group-text">+962</span>
                  <input type="tel" id="phone" name="phone" class="form-control" required 
                    placeholder="79XXXXXXX"
                    pattern="[0-9]{9}" maxlength="9" value="<?php echo htmlspecialchars($form_data['phone'] ?? ''); ?>">
                </div>
              </div>
            </div>

            <!-- Primary Expertise -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="primary_expertise" class="form-label required">Primary Expertise</label>
                <select id="primary_expertise" name="primary_expertise" class="form-select" required>
                  <option value="">Select your main service</option>
                  <option value="ac_repair_cooling" <?php echo (($form_data['primary_expertise'] ?? '') == 'ac_repair_cooling') ? 'selected' : ''; ?>>AC & Cooling (HVAC)</option>
                  <option value="appliance_repair" <?php echo (($form_data['primary_expertise'] ?? '') == 'appliance_repair') ? 'selected' : ''; ?>>Appliance Repair (Fridge/Wash)</option>
                  <option value="plumbing_service" <?php echo (($form_data['primary_expertise'] ?? '') == 'plumbing_service') ? 'selected' : ''; ?>>Plumbing & Pipes</option>
                  <option value="electrical_repair" <?php echo (($form_data['primary_expertise'] ?? '') == 'electrical_repair') ? 'selected' : ''; ?>>Electrical Repair</option>
                  <option value="roofing_waterproofing" <?php echo (($form_data['primary_expertise'] ?? '') == 'roofing_waterproofing') ? 'selected' : ''; ?>>Roofing & Waterproofing</option>
                  <option value="pest_control" <?php echo (($form_data['primary_expertise'] ?? '') == 'pest_control') ? 'selected' : ''; ?>>Pest Control</option>
                  <option value="locksmith_handyman" <?php echo (($form_data['primary_expertise'] ?? '') == 'locksmith_handyman') ? 'selected' : ''; ?>>Locksmith & Doors</option>
                  <option value="carpentry_furniture" <?php echo (($form_data['primary_expertise'] ?? '') == 'carpentry_furniture') ? 'selected' : ''; ?>>Carpentry & Furniture</option>
                  <option value="painting_damage_repair" <?php echo (($form_data['primary_expertise'] ?? '') == 'painting_damage_repair') ? 'selected' : ''; ?>>Painting & Wall Repair</option>
                  <option value="cleaning_service" <?php echo (($form_data['primary_expertise'] ?? '') == 'cleaning_service') ? 'selected' : ''; ?>>Professional Cleaning</option>
                  <option value="general_maintenance" <?php echo (($form_data['primary_expertise'] ?? '') == 'general_maintenance') ? 'selected' : ''; ?>>General Maintenance</option>
                </select>
              </div>
            </div>

            <!-- Hourly Rate -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="hourly_rate" class="form-label required">Hourly Rate (JOD)</label>
                <div class="input-group">
                  <input type="number" id="hourly_rate" name="hourly_rate" class="form-control" min="5" max="100"
                    step="0.5" value="<?php echo htmlspecialchars($form_data['hourly_rate'] ?? '15'); ?>" required>
                  <span class="input-group-text">JOD/hr</span>
                </div>
              </div>
            </div>

            <!-- Password -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="password" class="form-label required">Password</label>
                <div class="position-relative">
                  <input type="password" id="password" name="password" class="form-control" required minlength="8"
                    placeholder="Minimum 8 characters"
                    value="<?php echo htmlspecialchars($form_data['password'] ?? ''); ?>">
                  <button type="button" class="password-toggle" id="togglePassword">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
                <div class="password-strength-meter mt-2">
                  <div class="strength-bar" id="passwordStrength"></div>
                  <small id="passwordFeedback" class="text-muted"></small>
                </div>
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="col-md-6">
              <div class="form-group">
                <label for="confirm_password" class="form-label required">Confirm Password</label>
                <div class="position-relative">
                  <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                    placeholder="Re-enter your password"
                    value="<?php echo htmlspecialchars($form_data['confirm_password'] ?? ''); ?>">
                  <button type="button" class="password-toggle" id="toggleConfirmPassword">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
                <small id="passwordMatch" class="text-muted"></small>
              </div>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="btn-navigation">
            <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='/Dabbirha/index.php'">
              <i class="fas fa-home me-2"></i> 
              Back to Home
            </button>
            <button type="button" class="btn btn-next" onclick="nextStep(2)">
              Next 
              <i class="fas fa-arrow-right ms-3"></i>
            </button>
          </div>
        </div>

        <!-- Step 2: Services & Skills -->
        <div class="form-section" id="step2">
          <h3 class="form-title">
            <i class="fas fa-concierge-bell me-2"></i>
            Services & Skills
          </h3>

          <!-- Services List -->
          <div class="form-group">
            <label class="form-label required">Select Services You Offer</label>
            <p class="text-muted mb-3">Select all services that apply to your expertise</p>

            <div class="services-grid">
              <!-- AC & Cooling (HVAC) -->
              <div class="service-card" data-service="ac_repair_cooling">
                <div class="service-icon"><i class="fas fa-wind"></i></div>
                <div class="service-name">AC & Cooling (HVAC)</div>
                <div class="service-description">Air conditioning, cooling systems, HVAC repair</div>
              </div>

              <!-- Appliance Repair (Fridge/Wash) -->
              <div class="service-card" data-service="appliance_repair">
                <div class="service-icon"><i class="fas fa-tv"></i></div>
                <div class="service-name">Appliance Repair</div>
                <div class="service-description">Refrigerator, washing machine, appliance repair</div>
              </div>

              <!-- Plumbing & Pipes -->
              <div class="service-card" data-service="plumbing_service">
                <div class="service-icon"><i class="fas fa-faucet"></i></div>
                <div class="service-name">Plumbing & Pipes</div>
                <div class="service-description">Pipe repairs, installations, drainage systems</div>
              </div>

              <!-- Electrical Repair -->
              <div class="service-card" data-service="electrical_repair">
                <div class="service-icon"><i class="fas fa-bolt"></i></div>
                <div class="service-name">Electrical Repair</div>
                <div class="service-description">Wiring, lighting, electrical installations</div>
              </div>

              <!-- Roofing & Waterproofing -->
              <div class="service-card" data-service="roofing_waterproofing">
                <div class="service-icon"><i class="fas fa-home"></i></div>
                <div class="service-name">Roofing & Waterproofing</div>
                <div class="service-description">Roof repair, waterproofing, installation</div>
              </div>

              <!-- Pest Control -->
              <div class="service-card" data-service="pest_control">
                <div class="service-icon"><i class="fas fa-bug"></i></div>
                <div class="service-name">Pest Control</div>
                <div class="service-description">Pest removal, prevention, extermination</div>
              </div>

              <!-- Locksmith & Doors -->
              <div class="service-card" data-service="locksmith_handyman">
                <div class="service-icon"><i class="fas fa-key"></i></div>
                <div class="service-name">Locksmith & Doors</div>
                <div class="service-description">Lock installation, door repair, security</div>
              </div>

              <!-- Carpentry & Furniture -->
              <div class="service-card" data-service="carpentry_furniture">
                <div class="service-icon"><i class="fas fa-hammer"></i></div>
                <div class="service-name">Carpentry & Furniture</div>
                <div class="service-description">Woodwork, furniture repair, custom carpentry</div>
              </div>

              <!-- Painting & Wall Repair -->
              <div class="service-card" data-service="painting_damage_repair">
                <div class="service-icon"><i class="fas fa-paint-roller"></i></div>
                <div class="service-name">Painting & Wall Repair</div>
                <div class="service-description">Interior/exterior painting, wall repair</div>
              </div>

              <!-- Professional Cleaning -->
              <div class="service-card" data-service="cleaning_service">
                <div class="service-icon"><i class="fas fa-broom"></i></div>
                <div class="service-name">Professional Cleaning</div>
                <div class="service-description">Deep cleaning, maintenance cleaning</div>
              </div>

              <!-- General Maintenance -->
              <div class="service-card" data-service="general_maintenance">
                <div class="service-icon"><i class="fas fa-tools"></i></div>
                <div class="service-name">General Maintenance</div>
                <div class="service-description">Various maintenance and repair services</div>
              </div>
            </div>
            <input type="hidden" id="selectedServices" name="services"
              value="<?php echo htmlspecialchars($form_data['services'] ?? ''); ?>">
          </div>

          <!-- Professional Skills -->
          <div class="form-group">
            <label class="form-label required">Professional Skills</label>
            <p class="text-muted mb-3">Add your professional skills (click to select)</p>

            <div id="skillsContainer">
              <?php
              $skill_categories = [
                'AC & Cooling (HVAC)' => ['AC Installation', 'AC Maintenance', 'AC Repair'],
                'Appliance Repair (Fridge/Wash)' => ['Refrigerator Repair', 'Washing Machine Repair', 'Oven/Stove Repair'],
                'Plumbing & Pipes' => ['Pipe Installation', 'Leak Detection', 'Drain Cleaning'],
                'Electrical Repair' => ['Electrical Wiring', 'Circuit Breaker Installation', 'Lighting Installation'],
                'Roofing & Waterproofing' => ['Roof Repair', 'Waterproofing', 'Leak Prevention'],
                'Pest Control' => ['Pest Inspection', 'Rodent Control', 'Insect Extermination'],
                'Locksmith & Doors' => ['Lock Installation', 'Lock Repair', 'Emergency Lockout Service'],
                'Carpentry & Furniture' => ['Cabinet Making', 'Furniture Repair', 'Custom Woodwork'],
                'Painting & Wall Repair' => ['Interior Painting', 'Exterior Painting', 'Drywall Repair'],
                'Professional Cleaning' => ['Deep Cleaning', 'Window Cleaning', 'Carpet Cleaning'],
                'General Maintenance' => ['Emergency Repair', 'Preventive Maintenance', 'Handyman Services']
              ];

              $all_skills = [];
              foreach ($skill_categories as $skills) {
                $all_skills = array_merge($all_skills, $skills);
              }

              $all_skills = array_unique($all_skills);
              sort($all_skills);

              foreach ($all_skills as $skill) {
                echo '<span class="skill-tag" data-skill="' . htmlspecialchars($skill) . '">' . $skill . '</span>';
              }
              ?>
            </div>

            <!-- Custom Skill Input -->
            <div class="mt-3">
              <div class="input-group">
                <input type="text" id="customSkill" class="form-control" placeholder="Add a custom skill...">
                <button type="button" class="btn btn-outline-primary" onclick="addCustomSkill()">
                  <i class="fas fa-plus"></i> Add
                </button>
              </div>
            </div>
            <input type="hidden" id="selectedSkills" name="skills"
              value="<?php echo htmlspecialchars($form_data['skills'] ?? ''); ?>">
          </div>

          <!-- About You -->
          <div class="form-group">
            <label for="about_me" class="form-label required">About You</label>
            <textarea id="about_me" name="about_me" class="form-control" rows="5"
              placeholder="Tell clients about your experience, qualifications, and approach to work..."
              required><?php echo htmlspecialchars($form_data['about_me'] ?? ''); ?></textarea>

           
          </div>

          <!-- Navigation Buttons -->
          <div class="btn-navigation">
            <button type="button" class="btn btn-prev" onclick="prevStep(1)">
              <i class="fas fa-arrow-left me-3"></i>
              Previous
            </button>
            <button type="button" class="btn btn-next" onclick="nextStep(3)">
              Next 
              <i class="fas fa-arrow-right ms-3"></i>
            </button>
          </div>
        </div>

        <!-- Step 3: Location -->
        <div class="form-section" id="step3">
          <h3 class="form-title">
            <i class="fas fa-map-marker-alt me-2"></i>
            Service Location
          </h3>

          <div class="row">
            <!-- Address -->
            <div class="col-12">
              <div class="form-group">
                <label class="form-label required">Service Address</label>
                <p class="text-muted">This address will be shown to clients in your area</p>

                <div class="location-search">
                  <input type="text" id="location-search-input" class="form-control"
                    placeholder="Search for your address..."
                    value="<?php echo htmlspecialchars($form_data['address'] ?? ''); ?>">
                  <i class="fas fa-search search-icon"></i>
                </div>

                <div class="map-container">
                  <div id="map"></div>
                </div>

                <!-- Hidden Fields for Location Data -->
                <input type="hidden" id="address" name="address" required
                  value="<?php echo htmlspecialchars($form_data['address'] ?? ''); ?>">
                <input type="hidden" id="latitude" name="latitude" required
                  value="<?php echo htmlspecialchars($form_data['latitude'] ?? ''); ?>">
                <input type="hidden" id="longitude" name="longitude" required
                  value="<?php echo htmlspecialchars($form_data['longitude'] ?? ''); ?>">
              </div>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="btn-navigation">
            <button type="button" class="btn btn-prev" onclick="prevStep(2)">
              <i class="fas fa-arrow-left me-3"></i>
              Previous
            </button>
            <button type="button" class="btn btn-next" onclick="nextStep(4)">
              Next 
              <i class="fas fa-arrow-right ms-3"></i>
            </button>
          </div>
        </div>

        <!-- Step 4: Verification & Terms -->
        <div class="form-section" id="step4">
          <h3 class="form-title">
            <i class="fas fa-shield-alt me-2"></i>
            Verification & Terms
          </h3>

          <!-- Email Verification Notice -->
          <div class="verification-notice">
            <h5>
              <i class="fas fa-envelope me-2"></i>
              Email Verification Required
            </h5>
            <p>After registration, you will receive a verification email. You must verify your email before you can log in and start accepting jobs.</p>
            <p class="mb-0"><strong>Please check your spam folder if you don't see the email.</strong></p>
          </div>

          <!-- Terms & Conditions -->
          <div class="form-group">
            <div class="terms-checkbox d-flex align-items-center">
              <input type="checkbox" id="terms" name="terms" class="me-2" required <?php echo (isset($form_data['terms']) && $form_data['terms']) ? 'checked' : ''; ?>>
              <label for="terms" class="terms-label mb-0">
                I agree to the&nbsp;
                <a href="/Dabbirha/policy.php" target="_blank" class="policy-link">
                  Dabbirha Privacy Policy
                </a>
              </label>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="btn-navigation">
            <button type="button" class="btn btn-prev" onclick="prevStep(3)">
              <i class="fas fa-arrow-left me-3"></i>
              Previous
            </button>
            <button type="button" class="btn btn-submit" id="submitStep4Btn" onclick="submitStep4()">
              <i class="fas fa-user-plus me-3"></i>
              Complete Registration
            </button>
          </div>
        </div>

        <!-- Step 5: Complete Registration -->
        <div class="form-section" id="step5">
          <div class="onboarding-body">
            <!-- Success Screen -->
            <div class="success-screen">
              <div class="success-icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <h2>Welcome to Dabbirha!</h2>
              <p>Your profile is now ready. Start finding work and connecting with clients.</p>

              <div class="feature-list">
                <div class="feature-item">
                  <i class="fas fa-edit"></i>
                  <div>
                    <strong>Complete Your Profile</strong>
                    <div class="text-muted small">Add more details anytime</div>
                  </div>
                </div>
                <div class="feature-item">
                  <i class="fas fa-shield-alt"></i>
                  <div>
                    <strong>Get Verified</strong>
                    <div class="text-muted small">Build trust with clients</div>
                  </div>
                </div>
                <div class="feature-item">
                  <i class="fas fa-briefcase"></i>
                  <div>
                    <strong>Browse Available Jobs</strong>
                    <div class="text-muted small">Find work that matches your skills</div>
                  </div>
                </div>
              </div>

              <!-- Go to Dashboard Button -->
              <a href="/Dabbirha/provider-dashboard.php" class="btn btn-primary btn-lg">
                <i class="fas fa-tachometer-alt me-2"></i>
                Go to Dashboard
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- Google Maps API -->
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCAGpwXPsdqJhaPfFxPapoMJ9W7ckh8SjQ&callback=initMap&libraries=places"
      async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="../assets/js/provider_registration_scripts.js"></script>
</body>
</html>