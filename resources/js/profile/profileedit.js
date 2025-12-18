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