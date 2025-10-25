// assets/script.js

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mainNav = document.querySelector('.main-nav');

    // --- Dark Mode Logic ---
    const currentTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', currentTheme);
    if (themeToggle) {
        themeToggle.textContent = currentTheme === 'dark' ? '☀️' : '🌙';
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            let theme = document.documentElement.getAttribute('data-theme');
            if (theme === 'dark') {
                theme = 'light';
                themeToggle.textContent = '🌙';
            } else {
                theme = 'dark';
                themeToggle.textContent = '☀️';
            }
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        });
    }

    // --- Mobile Menu Logic ---
    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', () => {
            mainNav.classList.toggle('show');
        });
    }

    // --- Modal Logic ---
    // This can be expanded if needed. For now, a simple example for one modal type.
    const modal = document.querySelector('.modal');
    if(modal){
        const openModalBtn = document.querySelector('.open-modal-btn'); // Example button
        const closeModalBtn = modal.querySelector('.close-btn');

        const showModal = () => modal.classList.add('show');
        const hideModal = () => modal.classList.remove('show');

        if(openModalBtn) openModalBtn.addEventListener('click', showModal);
        if(closeModalBtn) closeModalBtn.addEventListener('click', hideModal);

        modal.addEventListener('click', (e) => {
            if(e.target === modal) hideModal();
        });
    }

});
