<!-- Système de notifications Toast -->
<div x-data="notificationManager()" x-show="notifications.length > 0" class="fixed top-4 right-4 z-50 space-y-2">
    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.visible" 
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <!-- Icône selon le type -->
                    <div class="flex-shrink-0">
                        <svg x-show="notification.type === 'success'" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="notification.type === 'error'" class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="notification.type === 'warning'" class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <svg x-show="notification.type === 'info'" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    
                    <!-- Contenu -->
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                        <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
                    </div>
                    
                    <!-- Bouton fermer -->
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="removeNotification(notification.id)" 
                                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Barre de progression -->
            <div x-show="notification.showProgress" class="h-1 bg-gray-200">
                <div class="h-full transition-all duration-100 ease-linear"
                     :class="{
                         'bg-green-500': notification.type === 'success',
                         'bg-red-500': notification.type === 'error',
                         'bg-yellow-500': notification.type === 'warning',
                         'bg-blue-500': notification.type === 'info'
                     }"
                     :style="`width: ${notification.progress}%`">
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function notificationManager() {
    return {
        notifications: [],
        nextId: 1,

        addNotification(type, title, message, duration = 5000) {
            const notification = {
                id: this.nextId++,
                type: type,
                title: title,
                message: message,
                visible: true,
                showProgress: duration > 0,
                progress: 100
            };

            this.notifications.push(notification);

            if (duration > 0) {
                this.startProgressTimer(notification, duration);
                setTimeout(() => {
                    this.removeNotification(notification.id);
                }, duration);
            }

            return notification.id;
        },

        removeNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                notification.visible = false;
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        },

        startProgressTimer(notification, duration) {
            const interval = 50;
            const step = (interval / duration) * 100;
            
            const timer = setInterval(() => {
                notification.progress -= step;
                if (notification.progress <= 0) {
                    clearInterval(timer);
                }
            }, interval);
        },

        success(title, message, duration = 5000) {
            return this.addNotification('success', title, message, duration);
        },

        error(title, message, duration = 8000) {
            return this.addNotification('error', title, message, duration);
        },

        warning(title, message, duration = 6000) {
            return this.addNotification('warning', title, message, duration);
        },

        info(title, message, duration = 5000) {
            return this.addNotification('info', title, message, duration);
        },

        clearAll() {
            this.notifications.forEach(notification => {
                notification.visible = false;
            });
            setTimeout(() => {
                this.notifications = [];
            }, 300);
        }
    }
}

// Fonction globale pour faciliter l'utilisation
window.showNotification = function(type, title, message, duration) {
    // Trouve le premier gestionnaire de notifications disponible
    const notificationManager = document.querySelector('[x-data*="notificationManager"]')?.__x?.$data;
    if (notificationManager) {
        return notificationManager[type](title, message, duration);
    }
    
    // Fallback pour les navigateurs sans Alpine.js
    console.log(`${type.toUpperCase()}: ${title} - ${message}`);
};

// Raccourcis globaux
window.showSuccess = (title, message, duration) => showNotification('success', title, message, duration);
window.showError = (title, message, duration) => showNotification('error', title, message, duration);
window.showWarning = (title, message, duration) => showNotification('warning', title, message, duration);
window.showInfo = (title, message, duration) => showNotification('info', title, message, duration);
</script>