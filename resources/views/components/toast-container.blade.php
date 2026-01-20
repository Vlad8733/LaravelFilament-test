{{-- Universal Toast Notifications Container --}}
{{-- Usage: <x-toast-container /> --}}
<div class="toast-container">
    <template x-for="(notification, index) in notifications.slice().reverse()" :key="notification.id">
        <div x-show="notification.show"
             x-transition:enter="toast-enter"
             x-transition:enter-start="toast-enter-start"
             x-transition:enter-end="toast-enter-end"
             x-transition:leave="toast-leave"
             x-transition:leave-start="toast-leave-start"
             x-transition:leave-end="toast-leave-end"
             :class="'toast-notification toast--' + notification.type"
             role="alert">
            <div class="toast-icon">
                {{-- Success Icon --}}
                <svg x-show="notification.type === 'success'" class="toast-icon-svg" viewBox="0 -960 960 960" fill="currentColor">
                    <path d="m424-296 282-282-56-56-226 226-114-114-56 56 170 170Zm56 216q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                </svg>
                {{-- Error Icon --}}
                <svg x-show="notification.type === 'error'" class="toast-icon-svg" viewBox="0 -960 960 960" fill="currentColor">
                    <path d="m336-280 144-144 144 144 56-56-144-144 144-144-56-56-144 144-144-144-56 56 144 144-144 144 56 56ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                </svg>
                {{-- Warning Icon --}}
                <svg x-show="notification.type === 'warning'" class="toast-icon-svg" viewBox="0 -960 960 960" fill="currentColor">
                    <path d="M480-280q17 0 28.5-11.5T520-320q0-17-11.5-28.5T480-360q-17 0-28.5 11.5T440-320q0 17 11.5 28.5T480-280Zm-40-160h80v-240h-80v240ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                </svg>
                {{-- Info Icon --}}
                <svg x-show="notification.type === 'info'" class="toast-icon-svg" viewBox="0 -960 960 960" fill="currentColor">
                    <path d="M440-280h80v-240h-80v240Zm40-320q17 0 28.5-11.5T520-640q0-17-11.5-28.5T480-680q-17 0-28.5 11.5T440-640q0 17 11.5 28.5T480-600Zm0 520q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Z"/>
                </svg>
            </div>
            <div class="toast-content">
                <div class="toast-title" x-text="notification.productName || notification.title || ''"></div>
                <div class="toast-message" x-text="notification.message"></div>
            </div>
            <button @click="removeNotification(notification.id)" class="toast-close" aria-label="Close">
                <svg viewBox="0 -960 960 960" fill="currentColor">
                    <path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/>
                </svg>
            </button>
            <div class="toast-progress"></div>
        </div>
    </template>
</div>
