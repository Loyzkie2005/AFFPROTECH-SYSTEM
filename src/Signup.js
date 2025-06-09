const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('signup-password');
const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
const confirmPasswordField = document.getElementById('confirm-password');
const signupForm = document.getElementById('signupForm');
const signupBtn = document.getElementById('signupBtn');
const capsLockWarning = document.getElementById('caps-lock-warning');
const capsLockWarningConfirm = document.getElementById('caps-lock-warning-confirm');

// Password toggle for password field
togglePassword.addEventListener('click', function () {
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    
    const eyeIcon = this.querySelector('i');
    if (type === 'password') {
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    } else {
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    }
});

// Password toggle for confirm password field
toggleConfirmPassword.addEventListener('click', function () {
    const type = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmPasswordField.setAttribute('type', type);
    
    const eyeIcon = this.querySelector('i');
    if (type === 'password') {
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    } else {
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    }
});

// Caps Lock warning for password field
passwordField.addEventListener('keyup', function (e) {
    if (e.getModifierState && e.getModifierState('CapsLock')) {
        capsLockWarning.style.display = 'block';
    } else {
        capsLockWarning.style.display = 'none';
    }
});
passwordField.addEventListener('blur', function () {
    capsLockWarning.style.display = 'none';
});

// Caps Lock warning for confirm password field
confirmPasswordField.addEventListener('keyup', function (e) {
    if (e.getModifierState && e.getModifierState('CapsLock')) {
        capsLockWarningConfirm.style.display = 'block';
    } else {
        capsLockWarningConfirm.style.display = 'none';
    }
});
confirmPasswordField.addEventListener('blur', function () {
    capsLockWarningConfirm.style.display = 'none';
});

// Signup form submission
signupForm.addEventListener('submit', function (e) {
    signupBtn.classList.add('loading');
    signupBtn.disabled = true;
});

// Hide alert after 2 seconds
document.addEventListener('DOMContentLoaded', function () {
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.remove();
        }, 2000);
    }
});