document.addEventListener('DOMContentLoaded', () => {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', e => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = ev => {
                avatarPreview.innerHTML = '<img src="' + ev.target.result + '" alt="avatar">';
            };
            reader.readAsDataURL(file);
        });
    }

    const themeToggle = document.getElementById('themeToggle');

    function applyTheme(t) {
        document.documentElement.classList.remove('theme-light', 'theme-dark');
        document.documentElement.classList.add(t === 'dark' ? 'theme-dark' : 'theme-light');
        localStorage.setItem('site_theme', t);
        if (themeToggle) themeToggle.textContent = 'Theme: ' + t;
    }

    const stored = localStorage.getItem('site_theme');
    if (stored) applyTheme(stored);

    if (themeToggle) {
        const cur = localStorage.getItem('site_theme') || 'light';
        themeToggle.textContent = 'Theme: ' + cur;
        themeToggle.addEventListener('click', () => {
            const current = localStorage.getItem('site_theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            applyTheme(next);
        });
    }
});