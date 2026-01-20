document.addEventListener('DOMContentLoaded', () => {
    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', e => {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            
            // Validate file type for security
            if (!file.type.startsWith('image/')) {
                console.warn('Invalid file type selected');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = ev => {
                // Use DOM API instead of innerHTML to prevent XSS
                const img = document.createElement('img');
                img.src = ev.target.result;
                img.alt = 'avatar';
                img.className = 'w-full h-full object-cover rounded-full';
                avatarPreview.replaceChildren(img);
            };
            reader.readAsDataURL(file);
        });
    }

    const themeToggle = document.getElementById('themeToggle');

    function normalizeAndApplyTheme(t) {
        var chosen = t;
        if (!chosen) chosen = 'dark';
        if (chosen === 'auto') {
            chosen = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }

        // remove any existing theme-* classes
        document.documentElement.classList.remove('theme-light', 'theme-dark');
        document.body.classList.remove('theme-light', 'theme-dark');

        document.documentElement.classList.add(chosen === 'dark' ? 'theme-dark' : 'theme-light');
        document.body.classList.add(chosen === 'dark' ? 'theme-dark' : 'theme-light');

        // normalize storage keys so other scripts see same value
        try {
            localStorage.setItem('site_theme', chosen);
            localStorage.setItem('settings_theme', chosen);
            localStorage.setItem('theme', chosen);
        } catch (e) {}

        if (themeToggle) themeToggle.textContent = 'Theme: ' + chosen;
    }

    // Prefer canonical keys if present
    const stored = localStorage.getItem('settings_theme') || localStorage.getItem('theme') || localStorage.getItem('site_theme');
    if (stored) normalizeAndApplyTheme(stored);

    if (themeToggle) {
        const cur = localStorage.getItem('settings_theme') || localStorage.getItem('site_theme') || 'light';
        themeToggle.textContent = 'Theme: ' + cur;
        themeToggle.addEventListener('click', () => {
            const current = localStorage.getItem('settings_theme') || localStorage.getItem('site_theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            normalizeAndApplyTheme(next);
        });
    }
});

