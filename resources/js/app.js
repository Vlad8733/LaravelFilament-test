import './bootstrap';
import Alpine from 'alpinejs';

// ВАЖНО: Регистрируем компоненты ДО запуска Alpine
document.addEventListener('DOMContentLoaded', () => {
    // Ждём пока все скрипты загрузятся
    setTimeout(() => {
        if (!window.Alpine) {
            window.Alpine = Alpine;
            Alpine.start();
        }
    }, 100);
});
