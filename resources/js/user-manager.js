/**
 * User Manager - Gestion avancée des utilisateurs
 * Interface moderne avec Alpine.js pour la gestion des utilisateurs
 */

function userManager() {
    const initialData = window.userManagerData || {};
    
    // Debug: Afficher les données reçues
    console.log('=== USER MANAGER INIT ===');
    console.log('Initial data:', initialData);
    console.log('Users count:', initialData.users ? initialData.users.length : 'undefined');
    console.log('Pagination total:', initialData.pagination ? initialData.pagination.total : 'undefined');
    console.log('=== END DEBUG ===');
    
    return {
        users: initialData.users || [],
        pagination: initialData.pagination || {},
        stats: initialData.stats || {},
        loading: false,
        filters: initialData.filters || {},

        async search() {
            this.loading = true;
            try {
                const params = new URLSearchParams(this.filters);
                params.set('page', 1);
                
                const response = await fetch(`${window.userManagerRoutes.index}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                this.users = data.users;
                this.pagination = data.pagination;
                this.stats = data.stats;
                
                // Mettre à jour l'URL sans recharger la page
                window.history.pushState({}, '', `${window.userManagerRoutes.index}?${params}`);
            } catch (error) {
                console.error('Erreur lors de la recherche:', error);
                this.showNotification('Erreur lors de la recherche des utilisateurs', 'error');
            } finally {
                this.loading = false;
            }
        },

        sort(field) {
            if (this.filters.sort === field) {
                this.filters.direction = this.filters.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sort = field;
                this.filters.direction = 'asc';
            }
            this.search();
        },

        getSortIcon(field) {
            if (this.filters.sort !== field) return 'text-gray-400';
            return this.filters.direction === 'asc' ? 'text-indigo-600 transform rotate-180' : 'text-indigo-600';
        },

        async goToPage(page) {
            const params = new URLSearchParams(this.filters);
            params.set('page', page);
            
            this.loading = true;
            try {
                const response = await fetch(`${window.userManagerRoutes.index}?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                this.users = data.users;
                this.pagination = data.pagination;
                
                window.history.pushState({}, '', `${window.userManagerRoutes.index}?${params}`);
            } catch (error) {
                console.error('Erreur lors du changement de page:', error);
                this.showNotification('Erreur lors du changement de page', 'error');
            } finally {
                this.loading = false;
            }
        },

        previousPage() {
            if (this.pagination.current_page > 1) {
                this.goToPage(this.pagination.current_page - 1);
            }
        },

        nextPage() {
            if (this.pagination.current_page < this.pagination.last_page) {
                this.goToPage(this.pagination.current_page + 1);
            }
        },

        getVisiblePages() {
            const current = this.pagination.current_page;
            const last = this.pagination.last_page;
            const delta = 2;
            
            let pages = [];
            for (let i = Math.max(1, current - delta); i <= Math.min(last, current + delta); i++) {
                pages.push(i);
            }
            
            return pages;
        },

        async deleteUser(user) {
            if (!confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.name} ?`)) return;
            
            try {
                const response = await fetch(`${window.userManagerRoutes.destroy.replace(':user', user.id)}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                if (response.ok) {
                    this.search(); // Recharger la liste
                    this.showNotification('Utilisateur supprimé avec succès', 'success');
                } else {
                    const errorData = await response.json();
                    this.showNotification(errorData.message || 'Erreur lors de la suppression', 'error');
                }
            } catch (error) {
                console.error('Erreur lors de la suppression:', error);
                this.showNotification('Erreur lors de la suppression', 'error');
            }
        },

        async exportUsers() {
            try {
                const params = new URLSearchParams(this.filters);
                const url = `${window.userManagerRoutes.export}?${params}`;
                window.open(url, '_blank');
                this.showNotification('Export en cours...', 'info');
            } catch (error) {
                console.error('Erreur lors de l\'export:', error);
                this.showNotification('Erreur lors de l\'export', 'error');
            }
        },

        showNotification(message, type = 'info') {
            // Créer une notification toast moderne
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
            
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            notification.className += ` ${colors[type] || colors.info}`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animation d'entrée
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Suppression automatique après 5 secondes
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 300);
            }, 5000);
        }
    }
}

// Initialisation globale
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si Alpine.js est disponible
    if (typeof Alpine === 'undefined') {
        console.error('Alpine.js n\'est pas chargé. Veuillez inclure Alpine.js dans votre page.');
        return;
    }
    
    // Enregistrer la fonction globalement
    window.userManager = userManager;
});
