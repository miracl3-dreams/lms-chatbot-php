document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('#admin-burger');
    const navMenu = document.querySelector('#nav-menu');

    if (burger && navMenu) {
        burger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            burger.classList.toggle('is-active');
        });

        document.querySelectorAll('#nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                burger.classList.remove('is-active');
            });
        });
    }
});