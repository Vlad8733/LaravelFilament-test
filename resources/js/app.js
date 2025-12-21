import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Global Toast Notifications System
 * Persists notifications across page navigations
 */
window.ToastNotifications = {
    storageKey: 'pending_notifications',
    
    add(message, type = 'success', productName = '') {
        const notification = {
            id: Date.now() + Math.random(),
            message,
            type,
            productName,
            createdAt: Date.now()
        };
        
        const pending = this.getPending();
        pending.push(notification);
        sessionStorage.setItem(this.storageKey, JSON.stringify(pending));
        
        if (window.Alpine && this.getAlpineComponent()) {
            this.showPending();
        }
        
        return notification;
    },
    
    getPending() {
        try {
            const stored = sessionStorage.getItem(this.storageKey);
            if (!stored) return [];
            const notifications = JSON.parse(stored);
            const now = Date.now();
            return notifications.filter(n => now - n.createdAt < 10000);
        } catch (e) {
            return [];
        }
    },
    
    clearPending() {
        sessionStorage.removeItem(this.storageKey);
    },
    
    getAlpineComponent() {
        const elements = document.querySelectorAll('[x-data]');
        for (const el of elements) {
            if (el._x_dataStack) {
                for (const data of el._x_dataStack) {
                    if (Array.isArray(data.notifications)) {
                        return data;
                    }
                }
            }
        }
        return null;
    },
    
    showPending() {
        const pending = this.getPending();
        if (pending.length === 0) return;
        
        const component = this.getAlpineComponent();
        if (!component) return;
        
        this.clearPending();
        
        pending.forEach((notification, index) => {
            setTimeout(() => {
                if (typeof component.showNotification === 'function') {
                    component.showNotification(
                        notification.message,
                        notification.type,
                        notification.productName
                    );
                } else {
                    component.notifications.push({
                        ...notification,
                        show: true
                    });
                    
                    setTimeout(() => {
                        const idx = component.notifications.findIndex(n => n.id === notification.id);
                        if (idx !== -1) {
                            component.notifications[idx].show = false;
                            setTimeout(() => {
                                component.notifications = component.notifications.filter(n => n.id !== notification.id);
                            }, 500);
                        }
                    }, 4000);
                }
            }, index * 200);
        });
    },
    
    init() {
        setTimeout(() => this.showPending(), 150);
    }
};

// Start Alpine
Alpine.start();

// Init notifications after Alpine is ready
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => window.ToastNotifications.init(), 200);
});
