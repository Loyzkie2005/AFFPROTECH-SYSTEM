const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('login-password');
togglePassword.addEventListener('click', function (e) {
    const type = passwordField.type === 'password' ? 'text' : 'password';
    passwordField.type = type;


    this.classList.toggle('fa-eye-slash');
});

function handleLogin() {
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const loader = document.getElementById('loader');
    const form = document.getElementById('loginForm');


    loginBtn.disabled = true;
    btnText.classList.add('d-none');
    loader.classList.remove('d-none');

    
    setTimeout(() => {
        form.submit();
    }, 1000);
}