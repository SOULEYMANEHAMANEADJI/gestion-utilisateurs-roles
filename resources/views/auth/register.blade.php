@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <!-- Logo et titre -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full flex items-center justify-center mb-4">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Créer un compte
            </h2>
            <p class="text-gray-600">
                Rejoignez notre plateforme de gestion
            </p>
        </div>

        <!-- Formulaire d'inscription -->
        <div class="bg-white rounded-2xl shadow-xl p-8">
        <form method="POST" action="{{ route('register') }}" 
              class="space-y-6">
                        @csrf

                <!-- Nom complet -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom complet
                    </label>
                    <div x-data="fieldValidation('name', { required: '👤 Veuillez saisir votre nom complet', minLength: '📝 Le nom doit contenir au moins 2 caractères' })">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        <input id="name" 
                               name="name" 
                               type="text" 
                               autocomplete="name" 
                               autofocus
                               x-model="value"
                               @input="validate()"
                               @blur="validate()"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                               :class="{ 'border-red-500 ring-red-500': error }"
                               placeholder="Jean Dupont"
                               value="{{ old('name') }}">
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

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Adresse email
                    </label>
                    <div x-data="fieldValidation('email', { required: '📧 Veuillez saisir votre adresse email', email: '⚠️ Format d\'email invalide - Exemple: nom@domaine.com' })">
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
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
                                   :class="{ 'border-red-500 ring-red-500': error }"
                                   placeholder="jean.dupont@email.com"
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
                    <div x-data="fieldValidation('password', { required: '🔐 Veuillez créer un mot de passe', minLength: '💪 Le mot de passe doit contenir au moins 8 caractères' })">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   x-model="value"
                                   @input="validate()"
                                   @blur="validate()"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
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

                <!-- Confirmation mot de passe -->
                <div>
                    <label for="password-confirm" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmer le mot de passe
                    </label>
                    <div x-data="fieldValidation('password-confirm', { required: '🔄 Veuillez confirmer votre mot de passe', matchPassword: '❌ Les mots de passe ne correspondent pas' })">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input id="password-confirm" 
                                   name="password_confirmation" 
                                   type="password" 
                                   autocomplete="new-password" 
                                   x-model="value"
                                   @input="validate()"
                                   @blur="validate()"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition duration-200"
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

                <!-- Conditions d'utilisation -->
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="terms" 
                               name="terms" 
                               type="checkbox" 
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="terms" class="text-gray-700">
                            J'accepte les 
                            <a href="#" class="text-purple-600 hover:text-purple-500 font-medium">conditions d'utilisation</a>
                            et la 
                            <a href="#" class="text-purple-600 hover:text-purple-500 font-medium">politique de confidentialité</a>
                        </label>
                            </div>
                        </div>

                <!-- Boutons d'action -->
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Bouton principal - Créer mon compte -->
                    <button type="submit" 
                            style="width: 100%; display: flex; justify-content: center; align-items: center; padding: 16px 24px; font-size: 18px; font-weight: bold; color: white; background: linear-gradient(135deg, #8b5cf6, #ec4899); border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); cursor: pointer; transition: all 0.3s ease; transform: scale(1);"
                            onmouseover="this.style.transform='scale(1.05)'; this.style.boxShadow='0 8px 15px rgba(0, 0, 0, 0.2)'"
                            onmouseout="this.style.transform='scale(1)'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'">
                        <svg style="width: 24px; height: 24px; margin-right: 12px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Créer mon compte
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

            <!-- Lien vers la connexion -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Déjà un compte ?
                    <a href="{{ route('login') }}" class="font-medium text-purple-600 hover:text-purple-500 transition duration-200">
                        Se connecter
                    </a>
                </p>
            </div>
        </div>

        <!-- Informations sur les rôles -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center">
                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Hiérarchie des rôles
            </h3>
            <div class="space-y-2 text-xs text-gray-600">
                <p>• <strong>Super Admin</strong> : Accès complet à tous les utilisateurs</p>
                <p>• <strong>Admin</strong> : Gestion des utilisateurs de niveau inférieur</p>
                <p>• <strong>Manager</strong> : Gestion limitée selon les permissions</p>
                <p>• <strong>User</strong> : Accès de base à la plateforme</p>
            </div>
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
            
            // Validation correspondance des mots de passe
            if (rules.matchPassword && this.value) {
                const passwordField = document.getElementById('password');
                if (passwordField && this.value !== passwordField.value) {
                    this.error = rules.matchPassword;
                    return;
                }
            }
        }
    }
}

// Fonction pour vider les champs
function clearFields() {
    // Vider les champs
    document.getElementById('name').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('password-confirm').value = '';
    
    // Décocher la checkbox
    const checkbox = document.getElementById('terms');
    if (checkbox) {
        checkbox.checked = false;
    }
    
    // Réinitialiser les erreurs Alpine.js
    setTimeout(() => {
        const elements = document.querySelectorAll('[x-data*="fieldValidation"]');
        
        elements.forEach(el => {
            const data = Alpine.$data(el);
            if (data) {
                data.value = '';
                data.error = '';
            }
        });
    }, 100);
    
    // Afficher notification de succès
    showNotification('✅ Champs vidés avec succès', 'success');
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
