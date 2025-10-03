@extends('layouts.app')

@section('title', 'Nouvel Utilisateur')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Nouvel Utilisateur
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Créez un nouvel utilisateur et assignez-lui des rôles
        </p>
    </div>

    <!-- Form -->
    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}" 
              x-data="userForm()" 
              @submit="validateForm($event)"
              class="space-y-6">
            @csrf
            
            <div class="card-body">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom complet
                    </label>
                    <div x-data="fieldValidation('name', { required: 'Le nom complet est obligatoire', minLength: 'Le nom doit contenir au moins 2 caractères' })">
                        <input type="text" 
                               name="name" 
                               id="name"
                               x-model="value"
                               @input="validate()"
                               @blur="validate()"
                               value="{{ old('name') }}"
                               class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                               :class="{ 'border-red-300 ring-red-500': error }"
                               placeholder="Jean Dupont">
                        <div x-show="error" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-1 text-sm text-red-600 flex items-center">
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
                    <div x-data="fieldValidation('email', { required: 'L\'adresse email est obligatoire', email: 'Veuillez entrer une adresse email valide' })">
                        <input type="email" 
                               name="email" 
                               id="email"
                               x-model="value"
                               @input="validate()"
                               @blur="validate()"
                               value="{{ old('email') }}"
                               class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                               :class="{ 'border-red-300 ring-red-500': error }"
                               placeholder="jean.dupont@example.com">
                        <div x-show="error" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-1 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span x-text="error"></span>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Mot de passe
                    </label>
                    <div x-data="fieldValidation('password', { required: 'Le mot de passe est obligatoire', strongPassword: 'Le mot de passe doit contenir au moins 8 caractères avec majuscules, minuscules, chiffres et symboles' })">
                        <input type="password" 
                               name="password" 
                               id="password"
                               x-model="value"
                               @input="validate()"
                               @blur="validate()"
                               class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                               :class="{ 'border-red-300 ring-red-500': error }"
                               placeholder="••••••••">
                        <div x-show="error" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-1 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span x-text="error"></span>
                        </div>
                        <div x-show="!error && value" 
                             class="mt-1 text-xs text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span>Minimum 8 caractères avec majuscules, minuscules, chiffres et symboles</span>
                        </div>
                    </div>
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Confirmation du mot de passe
                    </label>
                    <div x-data="fieldValidation('password_confirmation', { required: 'La confirmation du mot de passe est obligatoire', matchPassword: 'Les mots de passe ne correspondent pas' })">
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation"
                               x-model="value"
                               @input="validate()"
                               @blur="validate()"
                               class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
                               :class="{ 'border-red-300 ring-red-500': error }"
                               placeholder="••••••••">
                        <div x-show="error" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             class="mt-1 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span x-text="error"></span>
                        </div>
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Rôles
                    </label>
                    <div x-data="roleValidation()" class="space-y-2">
                        @php
                            $currentUser = auth()->user();
                            $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;
                        @endphp
                        @foreach($roles as $role)
                            @php
                                // Un utilisateur peut assigner des rôles de niveau inférieur ou égal
                                $canAssign = $role->level <= $currentUserMaxLevel;
                                $isDisabled = !$canAssign;
                            @endphp
                            <div class="flex items-center {{ $isDisabled ? 'opacity-50' : '' }}">
                                <input type="checkbox" 
                                       name="roles[]" 
                                       id="role_{{ $role->id }}"
                                       value="{{ $role->id }}"
                                       x-model="selectedRoles"
                                       @change="validate()"
                                       {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}
                                       {{ $isDisabled ? 'disabled' : '' }}
                                       class="form-checkbox @error('roles') border-red-300 @enderror">
                                <label for="role_{{ $role->id }}" class="ml-3 text-sm text-gray-700">
                                    <span class="font-medium">{{ ucfirst($role->name) }}</span>
                                    @if($role->description)
                                        <span class="text-gray-500">- {{ $role->description }}</span>
                                    @endif
                                    @if($isDisabled)
                                        <span class="text-red-500 text-xs ml-2">(Permissions insuffisantes)</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
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
            </div>

            <!-- Actions -->
            <div class="card-body border-t border-gray-200 bg-gray-50">
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.users.index') }}" 
                       class="group inline-flex items-center px-6 py-3 border-2 border-gray-300 text-base font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-4 focus:ring-gray-200/50 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg">
                        <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <span class="group-hover:tracking-wide transition-all duration-300">Annuler</span>
                    </a>
                    
                    <button type="submit" 
                            class="group relative inline-flex items-center px-8 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:via-purple-700 hover:to-pink-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 rounded-xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                        <span class="relative flex items-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="group-hover:tracking-wide transition-all duration-300">Créer l'utilisateur</span>
                        </span>
                    </button>
                </div>
            </div>
        </form>
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
        isValid: false,
        
        init() {
            // Récupérer la valeur initiale si elle existe
            const input = this.$el.querySelector('input');
            if (input && input.value) {
                this.value = input.value;
                this.validate();
            }
        },
        
        validate() {
            this.clearError();
            
            if (!this.value.trim()) {
                if (rules.required) {
                    this.error = rules.required;
                    this.isValid = false;
                }
                return;
            }
            
            // Validation email
            if (rules.email && !this.isValidEmail(this.value)) {
                this.error = rules.email;
                this.isValid = false;
                return;
            }
            
            // Validation longueur minimale
            if (rules.minLength && this.value.length < rules.minLength) {
                this.error = rules.minLength;
                this.isValid = false;
                return;
            }
            
            // Validation mot de passe fort
            if (rules.strongPassword && !this.isStrongPassword(this.value)) {
                this.error = rules.strongPassword;
                this.isValid = false;
                return;
            }
            
            // Validation correspondance des mots de passe
            if (rules.matchPassword) {
                const passwordField = document.getElementById('password');
                if (passwordField && this.value !== passwordField.value) {
                    this.error = rules.matchPassword;
                    this.isValid = false;
                    return;
                }
            }
            
            // Si tout est valide
            this.isValid = true;
        },
        
        clearError() {
            this.error = '';
        },
        
        isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        isStrongPassword(password) {
            // Au moins 8 caractères, une majuscule, une minuscule, un chiffre et un symbole
            const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
            return re.test(password);
        }
    }
}

// Fonction de validation des rôles
function roleValidation() {
    return {
        selectedRoles: [],
        error: '',
        
        init() {
            // Récupérer les rôles déjà sélectionnés
            const checkboxes = this.$el.querySelectorAll('input[type="checkbox"]');
            this.selectedRoles = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
        },
        
        validate() {
            this.clearError();
            
            if (this.selectedRoles.length === 0) {
                this.error = 'Veuillez sélectionner au moins un rôle';
                return false;
            }
            
            return true;
        },
        
        clearError() {
            this.error = '';
        }
    }
}

// Fonction principale du formulaire
function userForm() {
    return {
        isValid: false,
        
        validateForm(event) {
            // Récupérer tous les composants de validation
            const fieldValidations = this.$el.querySelectorAll('[x-data*="fieldValidation"]');
            const roleValidation = this.$el.querySelector('[x-data*="roleValidation"]');
            
            let isFormValid = true;
            
            // Valider chaque champ
            fieldValidations.forEach(field => {
                const component = Alpine.$data(field);
                component.validate();
                if (!component.isValid) {
                    isFormValid = false;
                }
            });
            
            // Valider les rôles
            if (roleValidation) {
                const roleComponent = Alpine.$data(roleValidation);
                if (!roleComponent.validate()) {
                    isFormValid = false;
                }
            }
            
            if (!isFormValid) {
                event.preventDefault();
                this.showNotification('error', 'Veuillez corriger les erreurs dans le formulaire');
                return false;
            }
            
            return true;
        },
        
        showNotification(type, message) {
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { type, message }
            }));
        }
    }
}

// Gestion des messages de session Laravel
document.addEventListener('DOMContentLoaded', function() {
    // Afficher les messages de succès/erreur de Laravel
    @if(session('success'))
        window.dispatchEvent(new CustomEvent('show-notification', {
            detail: { 
                type: 'success', 
                message: '{{ session('success') }}' 
            }
        }));
    @endif
    
    @if(session('error'))
        window.dispatchEvent(new CustomEvent('show-notification', {
            detail: { 
                type: 'error', 
                message: '{{ session('error') }}' 
            }
        }));
    @endif
    
    // Afficher les erreurs de validation Laravel
    @if($errors->any())
        @foreach($errors->all() as $error)
            window.dispatchEvent(new CustomEvent('show-notification', {
                detail: { 
                    type: 'error', 
                    message: '{{ $error }}' 
                }
            }));
        @endforeach
    @endif
});
</script>
@endsection
