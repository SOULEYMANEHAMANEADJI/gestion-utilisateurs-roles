@extends('layouts.auth')

@section('title', 'Connexion')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo et titre -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Bienvenue !
            </h2>
            <p class="text-gray-600">
                Connectez-vous à votre compte
            </p>
        </div>

        <!-- Formulaire de connexion -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
        <form method="POST" action="{{ route('login') }}"
              class="space-y-6">
            @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Adresse email
                    </label>
                    <div x-data="fieldValidation('email', { required: '⚠️ Veuillez saisir votre adresse email', email: '📧 Format d\'email invalide - Exemple: nom@domaine.com' })">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                    <input id="email"
                           name="email"
                           type="email"
                           autocomplete="email"
                               x-model="value"
                               @input="validate()"
                               @blur="validate()"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                               :class="{ 'border-red-500 ring-red-500': error }"
                               placeholder="votre@email.com"
                           value="{{ old('email') }}">
                        </div>
                        <div x-show="error"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span x-text="error"></span>
                        </div>
                    </div>
                </div>

                <!-- Mot de passe -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe
                    </label>
                    <div x-data="fieldValidation('password', { required: '🔒 Veuillez saisir votre mot de passe', minLength: '🔐 Le mot de passe doit contenir au moins 6 caractères' })">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                    <input id="password"
                           name="password"
                           type="password"
                           autocomplete="current-password"
                               x-model="value"
                               @input="validate()"
                               @blur="validate()"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-200"
                               :class="{ 'border-red-500 ring-red-500': error }"
                               placeholder="••••••••">
                        </div>
                        <div x-show="error"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span x-text="error"></span>
                        </div>
                    </div>
            </div>

                <!-- Se souvenir de moi -->
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember"
                           name="remember"
                           type="checkbox"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-sm text-gray-700">
                        Se souvenir de moi
                    </label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500 transition duration-200">
                            Mot de passe oublié ?
                        </a>
                    @endif
            </div>

                <!-- Boutons d'action -->
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Bouton principal - Se connecter -->
                <button type="submit"
                            style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 16px 24px; font-size: 18px; font-weight: bold; color: white; background: linear-gradient(135deg, #3b82f6, #8b5cf6); border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); cursor: pointer; transition: all 0.3s ease; transform: scale(1);"
                            onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 15px rgba(0, 0, 0, 0.2)'"
                            onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'">
                        <svg style="width: 24px; height: 24px; margin-right: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                    Se connecter
                </button>

                    <!-- Bouton secondaire - Vider les champs -->
                    <button type="button"
                            @click="clearFields()"
                            style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 12px 24px; font-size: 16px; font-weight: 500; color: #374151; background: #f3f4f6; border: 2px solid #d1d5db; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; transform: scale(1);"
                            onmouseover="this.style.transform='scale(1.02)'; this.style.backgroundColor='#e5e7eb'; this.style.borderColor='#9ca3af'"
                            onmouseout="this.style.transform='scale(1)'; this.style.backgroundColor='#f3f4f6'; this.style.borderColor='#d1d5db'">
                        <svg style="width: 20px; height: 20px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Vider les champs
                    </button>
            </div>
        </form>

            <!-- Lien vers l'inscription -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-200">
                        Créer un compte
                    </a>
                </p>
            </div>
        </div>

        <!-- Comptes de test -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Comptes de test disponibles
            </h3>
            <div class="grid grid-cols-1 gap-3 text-xs">
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <div>
                        <span class="font-medium text-red-800">Super Admin</span>
                        <p class="text-red-600">admin@example.com</p>
                    </div>
                    <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">Niveau 100</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                    <div>
                        <span class="font-medium text-orange-800">Admin</span>
                        <p class="text-orange-600">admin.user@example.com</p>
                    </div>
                    <span class="px-2 py-1 bg-orange-100 text-orange-800 rounded-full text-xs font-medium">Niveau 50</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                    <div>
                        <span class="font-medium text-purple-800">Manager</span>
                        <p class="text-purple-600">manager@example.com</p>
                    </div>
                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">Niveau 25</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <span class="font-medium text-green-800">User</span>
                        <p class="text-green-600">user@example.com</p>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">Niveau 10</span>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-3 text-center">
                Mot de passe pour tous : <code class="bg-gray-100 px-1 rounded">password123</code>
            </p>
        </div>
    </div>
</div>

<!-- Notifications -->
<x-notification />

<!-- JavaScript pour la validation -->
<script>
// Fonction de validation des champs individuels
function fieldValidation(fieldName, rules) {
    return {
        value: '',
        error: '',

        init() {
            // Récupérer la valeur initiale
            const input = document.getElementById(fieldName);
            if (input) {
                this.value = input.value || '';
            }
        },

        validate() {
            this.error = '';

            // Validation required
            if (rules.required && !this.value.trim()) {
                this.error = rules.required;
                return;
            }

            // Validation email
            if (rules.email && this.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.value)) {
                this.error = rules.email;
                return;
            }

            // Validation minLength
            if (rules.minLength && this.value.length < rules.minLength) {
                this.error = rules.minLength;
                return;
            }
        }
    }
}

// Fonction pour vider les champs
function clearFields() {
    // Vider les champs
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';

    // Réinitialiser les erreurs Alpine.js
    setTimeout(() => {
        const emailElements = document.querySelectorAll('[x-data*="email"]');
        const passwordElements = document.querySelectorAll('[x-data*="password"]');

        emailElements.forEach(el => {
            const data = Alpine.$data(el);
            if (data) {
                data.value = '';
                data.error = '';
            }
        });

        passwordElements.forEach(el => {
            const data = Alpine.$data(el);
            if (data) {
                data.value = '';
                data.error = '';
            }
        });
    }, 100);

    // Afficher notification de succès
    showNotification('Champs vidés avec succès', 'success');
}

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    // Créer l'élément de notification
    const notification = document.createElement('div');

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
        width: 100%;
        background: white;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        animation: slideIn 0.3s ease-out;
    `;

    const iconColor = type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : type === 'warning' ? '#f59e0b' : '#3b82f6';

    notification.innerHTML = `
        <div style="padding: 16px;">
            <div style="display: flex; align-items: flex-start;">
                <div style="flex-shrink: 0; margin-right: 12px;">
                    <svg style="width: 24px; height: 24px; color: ${iconColor};" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div style="flex: 1; padding-top: 2px;">
                    <p style="margin: 0; font-size: 14px; font-weight: 500; color: #111827;">${message}</p>
                </div>
                <div style="flex-shrink: 0; margin-left: 16px;">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" style="background: white; border: none; border-radius: 4px; padding: 4px; color: #6b7280; cursor: pointer;">
                        <svg style="width: 20px; height: 20px;" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Ajouter l'animation CSS
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }

    // Ajouter au DOM
    document.body.appendChild(notification);

    // Auto-suppression après 5 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideIn 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Gestion des messages de session Laravel
document.addEventListener('DOMContentLoaded', function() {
    // Afficher les messages de succès/erreur de Laravel
    @if(session('success'))
        showNotification('{{ session('success') }}', 'success');
    @endif

    @if(session('error'))
        showNotification('{{ session('error') }}', 'error');
    @endif

    // Afficher les erreurs de validation Laravel
    @if($errors->any())
        @foreach($errors->all() as $error)
            showNotification('{{ $error }}', 'error');
        @endforeach
    @endif
});
</script>
@endsection
