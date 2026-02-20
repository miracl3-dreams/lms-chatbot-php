const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#passwordField');

togglePassword.addEventListener('click', function () {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.textContent = type === 'password' ? '👁️' : '🙈';
});

const loginForm = document.querySelector('#loginForm');
const loginBtn = document.querySelector('#loginBtn');
const loader = document.querySelector('#loader');
const btnText = document.querySelector('.btn-text');

loginForm.addEventListener('submit', function () {
    loginBtn.classList.add('loading');
    loginBtn.style.pointerEvents = 'none'; 
});