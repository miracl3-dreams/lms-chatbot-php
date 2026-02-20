const menu = document.querySelector('#mobile-menu');
const navLinks = document.querySelector('#nav-list');

menu.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    menu.classList.toggle('is-active');
});