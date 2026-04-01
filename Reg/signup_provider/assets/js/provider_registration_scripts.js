// Initialize AOS
if (typeof AOS !== 'undefined') {
  AOS.init({
    duration: 800,
    once: true
  });
}

// Global Variables
let currentStep = 1;
let map, marker, autocomplete;
let selectedServices = [];
let selectedSkills = [];

// Restore saved selections from session
function restoreSavedSelections() {
    // Restore services from hidden input
    const savedServicesInput = document.getElementById('selectedServices');
    if (savedServicesInput && savedServicesInput.value) {
        selectedServices = savedServicesInput.value.split(',').filter(s => s !== '');
        selectedServices.forEach(service => {
            const serviceCard = document.querySelector(`.service-card[data-service="${service}"]`);
            if (serviceCard) {
                serviceCard.classList.add('selected');
            }
        });
    }
    
    // Restore skills from hidden input
    const savedSkillsInput = document.getElementById('selectedSkills');
    if (savedSkillsInput && savedSkillsInput.value) {
        selectedSkills = savedSkillsInput.value.split(',').filter(s => s !== '');
        selectedSkills.forEach(skill => {
            const skillTag = document.querySelector(`.skill-tag[data-skill="${skill}"]`);
            if (skillTag) {
                skillTag.classList.add('selected');
            }
        });
    }

    
   
}

// Initialize Registration
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded');
  updateStepIndicator();
  setupEventListeners();
  restoreSavedSelections(); // Restore saved data
  
  // Get the current step from URL parameter or PHP session
  const urlParams = new URLSearchParams(window.location.search);
  const stepFromUrl = urlParams.get('step');
  
  if (stepFromUrl) {
    const step = parseInt(stepFromUrl);
    if (step >= 1 && step <= 5) {
      showStep(step);
    } else {
      showStep(1);
    }
  } else {
    // Check if we have a verified email (step 5)
    const verifiedEmail = document.getElementById('verified_email');
    if (verifiedEmail && verifiedEmail.value) {
      showStep(5);
    } else {
      showStep(1);
    }
  }
});

// Show specific step
function showStep(step) {
  // Hide all steps
  document.querySelectorAll('.form-section').forEach(section => {
    section.classList.remove('active');
  });
  
  // Update step indicators
  document.querySelectorAll('.step').forEach(stepEl => {
    stepEl.classList.remove('active');
  });
  
  // Show current step
  const stepElement = document.getElementById('step' + step);
  const stepIndicator = document.querySelector('.step[data-step="' + step + '"]');
  
  if (stepElement) {
    stepElement.classList.add('active');
  }
  
  if (stepIndicator) {
    stepIndicator.classList.add('active');
  }
  
  currentStep = step;
  updateStepIndicator();
  
  // Initialize map on step 3
  if (currentStep === 3) {
    setTimeout(initMap, 100);
  }
  
  // Update review summary on step 5
  if (currentStep === 5) {
    updateReviewSummary();
  }
  
  window.scrollTo(0, 0);
}

// Setup Event Listeners
function setupEventListeners() {
  // Profile Picture Upload
  const profilePictureInput = document.getElementById('profilePicture');
  if (profilePictureInput) {
    profilePictureInput.addEventListener('change', function(e) {
      if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const preview = document.getElementById('profilePreview');
          if (preview) {
            preview.src = e.target.result;
          }
        };
        reader.readAsDataURL(e.target.files[0]);
      }
    });
  }
  
  // Password Toggle
  const togglePassword = document.getElementById('togglePassword');
  if (togglePassword) {
    togglePassword.addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');
      if (passwordInput) {
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          passwordInput.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      }
    });
  }
  
  const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
  if (toggleConfirmPassword) {
    toggleConfirmPassword.addEventListener('click', function() {
      const confirmInput = document.getElementById('confirm_password');
      const icon = this.querySelector('i');
      if (confirmInput) {
        if (confirmInput.type === 'password') {
          confirmInput.type = 'text';
          icon.classList.remove('fa-eye');
          icon.classList.add('fa-eye-slash');
        } else {
          confirmInput.type = 'password';
          icon.classList.remove('fa-eye-slash');
          icon.classList.add('fa-eye');
        }
      }
    });
  }
  
  // Password Strength Check
  const passwordInput = document.getElementById('password');
  if (passwordInput) {
    passwordInput.addEventListener('input', checkPasswordStrength);
    // Trigger initial check if there's a value
    if (passwordInput.value) {
      checkPasswordStrength.call(passwordInput);
    }
  }
  
  const confirmPasswordInput = document.getElementById('confirm_password');
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    // Trigger initial check if there's a value
    if (confirmPasswordInput.value) {
      checkPasswordMatch.call(confirmPasswordInput);
    }
  }
  

  
  // Terms Checkbox for Step 4
  const termsCheckbox = document.getElementById('terms');
  if (termsCheckbox) {
    termsCheckbox.addEventListener('change', function() {
      const submitBtn = document.getElementById('submitStep4Btn');
      if (submitBtn) {
        submitBtn.disabled = !this.checked;
      }
      saveStepData(currentStep);
    });
    
    // Initialize button state
    const submitBtn = document.getElementById('submitStep4Btn');
    if (submitBtn) {
      submitBtn.disabled = !termsCheckbox.checked;
    }
  }
  
  // Service Selection
  document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', function() {
      const service = this.getAttribute('data-service');
      if (selectedServices.includes(service)) {
        selectedServices = selectedServices.filter(s => s !== service);
        this.classList.remove('selected');
      } else {
        selectedServices.push(service);
        this.classList.add('selected');
      }
      const selectedServicesInput = document.getElementById('selectedServices');
      if (selectedServicesInput) {
        selectedServicesInput.value = selectedServices.join(',');
        saveStepData(currentStep);
      }
    });
  });
  
  // Skill Selection
  document.querySelectorAll('.skill-tag').forEach(tag => {
    tag.addEventListener('click', function() {
      const skill = this.getAttribute('data-skill');
      if (selectedSkills.includes(skill)) {
        selectedSkills = selectedSkills.filter(s => s !== skill);
        this.classList.remove('selected');
      } else {
        selectedSkills.push(skill);
        this.classList.add('selected');
      }
      const selectedSkillsInput = document.getElementById('selectedSkills');
      if (selectedSkillsInput) {
        selectedSkillsInput.value = selectedSkills.join(',');
        saveStepData(currentStep);
      }
    });
  });
  
  // Custom Skill
  const customSkillInput = document.getElementById('customSkill');
  if (customSkillInput) {
    customSkillInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        addCustomSkill();
      }
    });
  }
  
  // Input change listeners for auto-saving
  const autoSaveInputs = ['full_name', 'email', 'phone', 'primary_expertise', 'hourly_rate'];
  autoSaveInputs.forEach(inputId => {
    const input = document.getElementById(inputId);
    if (input) {
      input.addEventListener('change', function() {
        saveStepData(currentStep);
      });
    }
  });
}

// Navigation Functions
function nextStep(step) {
  if (validateCurrentStep()) {
    saveStepData(currentStep);
    showStep(step);
  }
}

function prevStep(step) {
  saveStepData(currentStep);
  showStep(step);
}

// Update the updateStepIndicator function for 5 steps
function updateStepIndicator() {
  const progressElement = document.getElementById('stepProgress');
  if (progressElement) {
    // Adjust calculation for 5 steps
    const progress = ((currentStep - 1) / 4) * 80 + 10;
    progressElement.style.width = progress + '%';
  }
  
  // Mark previous steps as completed
  document.querySelectorAll('.step').forEach((stepEl) => {
    const stepNum = parseInt(stepEl.getAttribute('data-step'));
    if (stepNum < currentStep) {
      stepEl.classList.add('completed');
    } else if (stepNum > currentStep) {
      stepEl.classList.remove('completed');
    }
  });
}

// Step Validation
function validateCurrentStep() {
  switch(currentStep) {
    case 1:
      return validateStep1();
    case 2:
      return validateStep2();
    case 3:
      return validateStep3();
    case 4:
      return validateStep4();
    case 5:
      return validateStep5();
    default:
      return true;
  }
}

function validateStep1() {
  const requiredFields = ['full_name', 'email', 'phone', 'password', 'confirm_password', 'primary_expertise', 'hourly_rate'];
  let isValid = true;
  
  requiredFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    
    if (!field || !field.value.trim()) {
      showFieldError(field, 'This field is required');
      isValid = false;
    } else if (fieldId === 'email' && !isValidEmail(field.value)) {
      showFieldError(field, 'Please enter a valid email address');
      isValid = false;
    } else if (fieldId === 'phone' && !isValidPhone(field.value)) {
      showFieldError(field, 'Please enter a valid 9-digit Jordanian phone number');
      isValid = false;
    } else if (fieldId === 'password' && field.value.length < 8) {
      showFieldError(field, 'Password must be at least 8 characters long');
      isValid = false;
    } else if (fieldId === 'confirm_password' && field.value !== document.getElementById('password').value) {
      showFieldError(field, 'Passwords do not match');
      isValid = false;
    } else {
      clearFieldError(field);
    }
  });
  
  return isValid;
}

function validateStep2() {
  // Check services selected
  if (selectedServices.length === 0) {
    showNotification('warning', 'Services Required', 'Please select at least one service you offer');
    return false;
  }
  
  // Check skills selected
  if (selectedSkills.length === 0) {
    showNotification('warning', 'Skills Required', 'Please select at least one professional skill');
    return false;
  }
  

 const aboutMe = document.getElementById('about_me');
 if (aboutMe && !aboutMe.value.trim()) {
   showNotification('warning', 'About Section Required', 'Please tell clients about yourself');
   return false;
 }
  return true;
}

function validateStep3() {
  const address = document.getElementById('address');
  const latitude = document.getElementById('latitude');
  
  if (!address || !address.value || !latitude || !latitude.value) {
    showNotification('warning', 'Location Required', 'Please select your service location on the map');
    return false;
  }
  
  return true;
}

function validateStep4() {
  const termsCheckbox = document.getElementById('terms');
  if (!termsCheckbox || !termsCheckbox.checked) {
    showNotification('warning', 'Terms Required', 'You must agree to the terms and conditions to continue');
    return false;
  }
  return true;
}

function validateStep5() {
  const finalTermsCheckbox = document.getElementById('finalTerms');
  if (finalTermsCheckbox && !finalTermsCheckbox.checked) {
    showNotification('warning', 'Confirmation Required', 'Please confirm that all information is accurate and agree to the terms');
    return false;
  }
  
  return true;
}

function showNotification(icon, title, text) {
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: icon,
      title: title,
      text: text
    });
  } else {
    alert(text);
  }
}

function showFieldError(field, message) {
  if (!field) return;
  
  field.classList.add('is-invalid');
  let errorElement = field.nextElementSibling;
  if (!errorElement || !errorElement.classList.contains('invalid-feedback')) {
    errorElement = document.createElement('div');
    errorElement.className = 'invalid-feedback';
    field.parentNode.appendChild(errorElement);
  }
  errorElement.textContent = message;
}

function clearFieldError(field) {
  if (!field) return;
  
  field.classList.remove('is-invalid');
  const errorElement = field.nextElementSibling;
  if (errorElement && errorElement.classList.contains('invalid-feedback')) {
    errorElement.textContent = '';
  }
}

// Password Functions
function checkPasswordStrength() {
  const password = this.value;
  const strengthBar = document.getElementById('passwordStrength');
  const feedback = document.getElementById('passwordFeedback');
  
  if (!strengthBar || !feedback) return;
  
  let strength = 0;
  
  // Length check
  if (password.length >= 8) strength += 25;
  if (password.length >= 12) strength += 10;
  
  // Complexity checks
  if (/[A-Z]/.test(password)) strength += 20;
  if (/[a-z]/.test(password)) strength += 20;
  if (/[0-9]/.test(password)) strength += 20;
  if (/[^A-Za-z0-9]/.test(password)) strength += 15;
  
  // Update strength bar
  strengthBar.style.width = Math.min(strength, 100) + '%';
  
  // Set color and message
  if (strength < 40) {
    strengthBar.style.backgroundColor = '#dc3545';
    feedback.textContent = 'Weak password';
    feedback.style.color = '#dc3545';
  } else if (strength < 70) {
    strengthBar.style.backgroundColor = '#ffc107';
    feedback.textContent = 'Medium strength';
    feedback.style.color = '#ffc107';
  } else {
    strengthBar.style.backgroundColor = '#28a745';
    feedback.textContent = 'Strong password';
    feedback.style.color = '#28a745';
  }
}

function checkPasswordMatch() {
  const passwordInput = document.getElementById('password');
  const confirmInput = document.getElementById('confirm_password');
  const matchElement = document.getElementById('passwordMatch');
  
  if (!passwordInput || !confirmInput || !matchElement) return;
  
  const password = passwordInput.value;
  const confirm = confirmInput.value;
  
  if (!confirm) {
    matchElement.textContent = '';
  } else if (password === confirm) {
    matchElement.textContent = 'Passwords match';
    matchElement.style.color = '#28a745';
  } else {
    matchElement.textContent = 'Passwords do not match';
    matchElement.style.color = '#dc3545';
  }
}

// Skills Functions
function addCustomSkill() {
  const input = document.getElementById('customSkill');
  const skillsContainer = document.getElementById('skillsContainer');
  
  if (!input || !skillsContainer) return;
  
  const skill = input.value.trim();
  
  if (skill && !selectedSkills.includes(skill)) {
    const skillTag = document.createElement('span');
    skillTag.className = 'skill-tag';
    skillTag.textContent = skill;
    skillTag.setAttribute('data-skill', skill);
    skillTag.addEventListener('click', function() {
      if (selectedSkills.includes(skill)) {
        selectedSkills = selectedSkills.filter(s => s !== skill);
        this.classList.remove('selected');
      } else {
        selectedSkills.push(skill);
        this.classList.add('selected');
      }
      const selectedSkillsInput = document.getElementById('selectedSkills');
      if (selectedSkillsInput) {
        selectedSkillsInput.value = selectedSkills.join(',');
        saveStepData(currentStep);
      }
    });
    
    skillsContainer.appendChild(skillTag);
    selectedSkills.push(skill);
    const selectedSkillsInput = document.getElementById('selectedSkills');
    if (selectedSkillsInput) {
      selectedSkillsInput.value = selectedSkills.join(',');
      saveStepData(currentStep);
    }
    input.value = '';
  }
}

// Google Maps Functions
function initMap() {
  if (typeof google === 'undefined') {
    console.log('Google Maps API not loaded yet');
    return;
  }
  
  // Get saved location or default to Amman, Jordan
  const savedLat = document.getElementById('latitude')?.value;
  const savedLng = document.getElementById('longitude')?.value;
  const savedAddress = document.getElementById('address')?.value;
  
  let defaultLocation;
  if (savedLat && savedLng) {
    defaultLocation = { lat: parseFloat(savedLat), lng: parseFloat(savedLng) };
  } else {
    defaultLocation = { lat: 31.963158, lng: 35.930359 };
  }
  
  const mapElement = document.getElementById('map');
  
  if (!mapElement) {
    console.log('Map element not found');
    return;
  }
  
  map = new google.maps.Map(mapElement, {
    center: defaultLocation,
    zoom: 12,
    mapTypeControl: true,
    streetViewControl: false,
    fullscreenControl: true,
    styles: [
      {
        featureType: "poi",
        elementType: "labels",
        stylers: [{ visibility: "off" }]
      }
    ]
  });
  
  // Create marker
  marker = new google.maps.Marker({
    position: defaultLocation,
    map: map,
    draggable: true,
    title: 'Your service location'
  });
  
  // Initialize autocomplete
  const searchInput = document.getElementById('location-search-input');
  if (searchInput) {
    autocomplete = new google.maps.places.Autocomplete(searchInput, {
      types: ['address'],
      componentRestrictions: { country: 'jo' }
    });
    
    // Bind autocomplete to map
    autocomplete.bindTo('bounds', map);
    
    // When a place is selected, update map and marker
    autocomplete.addListener('place_changed', function() {
      const place = autocomplete.getPlace();
      if (!place.geometry) {
        console.log("No details available for input: '" + place.name + "'");
        return;
      }
      
      // Update map
      if (place.geometry.viewport) {
        map.fitBounds(place.geometry.viewport);
      } else {
        map.setCenter(place.geometry.location);
        map.setZoom(17);
      }
      
      // Update marker
      marker.setPosition(place.geometry.location);
      marker.setVisible(true);
      
      // Update hidden fields
      updateLocationFields(place);
      saveStepData(currentStep);
    });
  }
  
  // Update location when marker is dragged
  google.maps.event.addListener(marker, 'dragend', function() {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      location: marker.getPosition()
    }, function(results, status) {
      if (status === 'OK' && results[0]) {
        updateLocationFields(results[0]);
        if (searchInput) {
          searchInput.value = results[0].formatted_address;
        }
        saveStepData(currentStep);
      }
    });
  });
  
  // Update location when map is clicked
  map.addListener('click', function(event) {
    marker.setPosition(event.latLng);
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({
      location: event.latLng
    }, function(results, status) {
      if (status === 'OK' && results[0]) {
        updateLocationFields(results[0]);
        if (searchInput) {
          searchInput.value = results[0].formatted_address;
        }
        saveStepData(currentStep);
      }
    });
  });
  
  // If we have saved address but no lat/lng, geocode it
  if (savedAddress && (!savedLat || !savedLng)) {
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: savedAddress }, function(results, status) {
      if (status === 'OK' && results[0]) {
        marker.setPosition(results[0].geometry.location);
        map.setCenter(results[0].geometry.location);
        updateLocationFields(results[0]);
      }
    });
  }
}

function updateLocationFields(place) {
  const addressInput = document.getElementById('address');
  const latitudeInput = document.getElementById('latitude');
  const longitudeInput = document.getElementById('longitude');
  
  if (addressInput) addressInput.value = place.formatted_address || '';
  if (latitudeInput) latitudeInput.value = place.geometry.location.lat() || '';
  if (longitudeInput) longitudeInput.value = place.geometry.location.lng() || '';
}

function updateLocationFieldsFromPosition(position) {
  const geocoder = new google.maps.Geocoder();
  geocoder.geocode({ location: position }, function(results, status) {
    if (status === 'OK' && results[0]) {
      updateLocationFields(results[0]);
      const searchInput = document.getElementById('location-search-input');
      if (searchInput) {
        searchInput.value = results[0].formatted_address || '';
      }
      saveStepData(currentStep);
    }
  });
}



// Google Sign-In Callback
window.handleGoogleSignIn = function(response) {
  const user = parseJwt(response.credential);
  
  // Fill form fields with Google data
  const fullNameInput = document.getElementById('full_name');
  const emailInput = document.getElementById('email');
  
  if (fullNameInput) fullNameInput.value = user.name || '';
  if (emailInput) emailInput.value = user.email || '';
  
  // Generate random password for Google signup
  const randomPass = Math.random().toString(36).slice(-10) + 'A1!';
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  
  if (passwordInput) passwordInput.value = randomPass;
  if (confirmPasswordInput) confirmPasswordInput.value = randomPass;
  
  // Set Google signup flags
  const googleSignupFlag = document.getElementById('googleSignupFlag');
  const googleIdInput = document.getElementById('googleId');
  
  if (googleSignupFlag) googleSignupFlag.value = "1";
  if (googleIdInput) googleIdInput.value = user.sub || '';
  
  // Trigger password validation
  if (passwordInput) {
    checkPasswordStrength.call(passwordInput);
  }
  
  if (confirmPasswordInput) {
    checkPasswordMatch.call(confirmPasswordInput);
  }
  
  // Save the data
  saveStepData(currentStep);
  
  // Show success message
  if (typeof Swal !== 'undefined') {
    Swal.fire({
      icon: 'success',
      title: 'Google Account Linked',
      text: 'Your details have been filled from Google. Please complete the remaining fields.'
    });
  } else {
    alert('Google account linked successfully!');
  }
};

function parseJwt(token) {
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    
    return JSON.parse(jsonPayload);
  } catch (e) {
    console.error('Error parsing JWT:', e);
    return {};
  }
}

// Utility Functions
function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function isValidPhone(phone) {
  const re = /^[0-9]{9}$/;
  return re.test(phone);
}

// Function to save step data
function saveStepData(step) {
  const form = document.getElementById('providerRegistrationForm');
  const formData = new FormData(form);
  formData.append('step', step);
  formData.append('save_step_data', '1');
  
  // Send data to current page (self-submit)
  fetch(window.location.href, {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json'
    }
  })
  .then(response => {
    console.log('Step data saved for step', step);
  })
  .catch(error => {
    console.error('Error saving step data:', error);
  });
}

// Step 4 Submission (for regular users - not verified yet)
function submitStep4() {
  const btn = document.getElementById('submitStep4Btn');
  if (!btn) {
    console.error('submitStep4Btn not found');
    alert('Button not found. Please refresh the page.');
    return;
  }
  
  const originalText = btn.innerHTML;
  
  console.log('submitStep4 called');
  
  // Validate Step 4
  if (!validateStep4()) {
    console.log('Step 4 validation failed');
    return;
  }
  
  // Also validate previous steps
  if (!validateStep1() || !validateStep2() || !validateStep3()) {
    console.log('Previous steps validation failed');
    showNotification('error', 'Validation Error', 'Please complete all previous steps correctly');
    return;
  }
  
  console.log('All validations passed');
  
  // Show loading
  btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
  btn.disabled = true;
  
  // Submit the form via AJAX
  const form = document.getElementById('providerRegistrationForm');
  if (!form) {
    console.error('Form not found');
    btn.innerHTML = originalText;
    btn.disabled = false;
    showNotification('error', 'Form Error', 'Form not found. Please refresh the page.');
    return;
  }
  
  const formData = new FormData(form);
  
  // Debug: Log form data entries
  console.log('Form data entries:');
  for (let pair of formData.entries()) {
    console.log(pair[0] + ': ' + pair[1]);
  }
  
  // CORRECTED PATH: Should be '../actions/process-signup.php' 
  const correctPath = '../actions/process-signup.php';
  console.log('Submitting to correct path:', correctPath);
  
  fetch(correctPath, {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json'
    }
  })
  .then(response => {
    console.log('Response status:', response.status);
    
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    
    return response.text().then(text => {
      console.log('Raw response length:', text.length);
      console.log('Raw response first 500 chars:', text.substring(0, 500));
      
      // Try to parse as JSON
      try {
        return JSON.parse(text);
      } catch (e) {
        console.error('JSON parse error:', e);
        
        // Check if this is a PHP error page
        if (text.includes('PHP Error') || text.includes('Fatal error') || text.includes('Parse error') || text.includes('Warning') || text.includes('Notice')) {
          console.error('PHP error detected in response');
          // Try to extract error message
          const errorPattern = /<b>(.*?)<\/b>:\s*(.*?)\s*in\s*<b>(.*?)<\/b>\s*on\s*line\s*<b>(\d+)<\/b>/i;
          const match = text.match(errorPattern);
          if (match) {
            throw new Error(`PHP Error: ${match[2]} in ${match[3]} on line ${match[4]}`);
          }
          throw new Error('PHP Error detected in server response');
        }
        
        // Check if this is a redirect
        if (text.includes('Location:') || text.includes('window.location') || text.includes('redirect')) {
          return { success: true, redirect: 'pages/verify_notice_2.php' };
        }
        
        // Check if it's HTML (not JSON)
        if (text.includes('<!DOCTYPE') || text.includes('<html') || text.includes('<head>')) {
          throw new Error('Server returned HTML instead of JSON. Check PHP errors.');
        }
        
        throw new Error('Server returned unexpected response format');
      }
    });
  })
  .then(data => {
    console.log('Parsed response data:', data);
    
    if (data.success) {
      if (data.redirect) {
        console.log('Redirecting to:', data.redirect);
        // Make sure the redirect path is correct
        const redirectPath = data.redirect.startsWith('http') ? 
          data.redirect : 
          '../' + data.redirect;
        console.log('Full redirect path:', redirectPath);
        window.location.href = redirectPath;
      } else {
        console.log('No redirect URL, going to index');
        window.location.href = '../../index.php';
      }
    } else {
      const errorMsg = data.message || 'Registration failed';
      console.error('Server error:', errorMsg);
      
      // Better error messages for specific cases
      let displayMessage = errorMsg;
      if (errorMsg.includes('Permission denied')) {
        displayMessage = 'Profile picture upload failed. You can continue without a profile picture or try a different image.';
      } else if (errorMsg.includes('Email already registered')) {
        displayMessage = 'This email is already registered. Please use a different email or try logging in.';
      }
      
      showNotification('error', 'Registration Failed', displayMessage);
      btn.innerHTML = originalText;
      btn.disabled = false;
    }
  })
  .catch(error => {
    console.error('Fetch error:', error);
    
    let errorMessage = 'Server error. Please try again.';
    
    if (error.message.includes('PHP Error')) {
      if (error.message.includes('Permission denied')) {
        errorMessage = 'File upload permission error. You can continue without uploading a profile picture.';
      } else {
        errorMessage = 'Server configuration error. Please contact support.';
      }
    } else if (error.message.includes('HTTP error')) {
      if (error.message.includes('404')) {
        errorMessage = 'Server file not found (404). Please check the path.';
      } else if (error.message.includes('500')) {
        errorMessage = 'Internal server error (500). Please try again later.';
      } else {
        errorMessage = 'Server error (' + error.message + ').';
      }
    } else if (error.message.includes('Network error')) {
      errorMessage = 'Cannot connect to server. Please check your internet connection.';
    }
    
    showNotification('error', 'Error', errorMessage);
    btn.innerHTML = originalText;
    btn.disabled = false;
  });
}


// Step 5 Success Screen JavaScript
document.addEventListener('DOMContentLoaded', function() {
  // Initialize Step 5 when it's active
  initStep5();
});

function initStep5() {
  // Check if we're on step 5
  const step5Section = document.getElementById('step5');
  if (!step5Section || !step5Section.classList.contains('active')) {
      return;
  }
  
  // Animate the success icon
  animateSuccessIcon();
  
  // Animate feature items one by one
  animateFeatureItems();
  
  // Add click handler for dashboard button
  setupDashboardButton();
  
  // Update step indicator
  updateStepIndicatorForStep5();
}

function animateSuccessIcon() {
  const successIcon = document.querySelector('.success-screen .success-icon i');
  if (successIcon) {
      // Reset animation
      successIcon.style.animation = 'none';
      
      // Trigger reflow
      void successIcon.offsetWidth;
      
      // Apply animation
      successIcon.style.animation = 'bounceIn 0.8s ease-out forwards';
      
      // Add glow effect
      setTimeout(() => {
          successIcon.classList.add('success-icon-glow');
      }, 800);
  }
}

function animateFeatureItems() {
  const featureItems = document.querySelectorAll('.feature-item');
  featureItems.forEach((item, index) => {
      // Hide initially
      item.style.opacity = '0';
      item.style.transform = 'translateY(20px)';
      
      // Animate with delay
      setTimeout(() => {
          item.style.transition = 'all 0.5s ease-out';
          item.style.opacity = '1';
          item.style.transform = 'translateY(0)';
      }, 300 + (index * 200));
  });
}

function setupDashboardButton() {
  const dashboardBtn = document.querySelector('.success-screen .btn-primary');
  if (dashboardBtn) {
      dashboardBtn.addEventListener('click', function(e) {
          // Add loading animation
          const icon = this.querySelector('i');
          const originalIconClass = icon.className;
          
          // Change to spinner
          icon.className = 'fas fa-spinner fa-spin me-2';
          this.disabled = true;
          this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Redirecting...';
          
          // Optional: Add a delay to show the spinner
          setTimeout(() => {
              // Allow the redirect to happen
              // The spinner will show for 1 second before redirect
          }, 1000);
      });
  }
}

function updateStepIndicatorForStep5() {
  // Mark step 5 as active in the step indicator
  const stepIndicators = document.querySelectorAll('.step');
  stepIndicators.forEach(step => {
      const stepNumber = parseInt(step.getAttribute('data-step'));
      step.classList.remove('active');
      step.classList.add('completed');
      
      if (stepNumber === 5) {
          step.classList.add('active');
      }
  });
  
  // Update progress bar to 100%
  const stepProgress = document.getElementById('stepProgress');
  if (stepProgress) {
      stepProgress.style.width = '100%';
  }
}

// Add CSS for animations (you can also put this in your CSS file)
const style = document.createElement('style');
style.textContent = `
  @keyframes bounceIn {
      0% {
          opacity: 0;
          transform: scale(0.3);
      }
      50% {
          opacity: 0.9;
          transform: scale(1.05);
      }
      80% {
          opacity: 1;
          transform: scale(0.95);
      }
      100% {
          opacity: 1;
          transform: scale(1);
      }
  }
  
  .success-icon-glow {
      animation: pulse 2s infinite;
  }
  
  @keyframes pulse {
      0% {
          box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
      }
      70% {
          box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
      }
      100% {
          box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
      }
  }
  
  .feature-item {
      transition: all 0.5s ease-out;
  }
`;
document.head.appendChild(style);

// Helper function to check if step 5 is active
function isStep5Active() {
  const step5Section = document.getElementById('step5');
  return step5Section && step5Section.classList.contains('active');
}

// If you have step navigation, add this to your existing navigation code
function goToStep5() {
  // Hide all steps
  document.querySelectorAll('.form-section').forEach(section => {
      section.classList.remove('active');
  });
  
  // Show step 5
  const step5Section = document.getElementById('step5');
  if (step5Section) {
      step5Section.classList.add('active');
      initStep5(); // Initialize step 5 animations
  }
  
  // Update URL if needed
  window.history.pushState({}, '', '?step=5');
  
  // Scroll to top
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Example: If you have a "Complete Registration" button in step 4
// document.getElementById('completeRegistrationBtn')?.addEventListener('click', function() {
//     // Save form data first, then go to step 5
//     goToStep5();
// });


// Make functions available globally
window.nextStep = nextStep;
window.prevStep = prevStep;
window.addCustomSkill = addCustomSkill;

window.submitStep4 = submitStep4;
window.submitCompleteRegistration = submitCompleteRegistration;
window.submitRegularRegistration = submitRegularRegistration;