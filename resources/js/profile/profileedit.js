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

// Simple switchAccount helper used on profile page and accounts page
function switchAccount(id) {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
    fetch('/profile/accounts/switch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ account_id: id })
    })
    .then(async resp => {
        const json = await resp.json().catch(() => ({}));
        if (!resp.ok) {
            console.error('switchAccount failed', resp.status, json);
            alert(json.message || 'Switch failed (see console)');
            return;
        }
        if (json.success) {
            location.reload();
        } else {
            alert(json.message || 'Switch failed');
            console.log(json);
        }
    })
    .catch(err => {
        console.error('switchAccount error', err);
        alert('Network error');
    });
}

// make global so inline onclick="switchAccount(...)" works
window.switchAccount = switchAccount;