@extends('layouts.app')

@section('title', 'Modifier l\'Utilisateur')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Modifier l'Utilisateur
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Modifiez les informations de {{ $user->name }}
        </p>
    </div>

    <!-- Form -->
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" 
              x-data="userForm()" 
              @submit="validateForm($event)"
              class="space-y-6">
            @csrf
            @method('PUT')
            
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
                               value="{{ old('name', $user->name) }}"
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
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Adresse email <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input type="email" 
                               name="email" 
                               id="email"
                               value="{{ old('email', $user->email) }}"
                               class="form-input @error('email') border-red-300 @enderror"
                               placeholder="jean.dupont@example.com"
                               required>
                    </div>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Nouveau mot de passe
                    </label>
                    <div class="mt-1">
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="form-input @error('password') border-red-300 @enderror"
                               placeholder="Laissez vide pour conserver le mot de passe actuel">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Laissez vide pour conserver le mot de passe actuel. Sinon, minimum 8 caractères avec majuscules, minuscules, chiffres et symboles
                    </p>
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirmation du nouveau mot de passe
                    </label>
                    <div class="mt-1">
                        <input type="password" 
                               name="password_confirmation" 
                               id="password_confirmation"
                               class="form-input @error('password_confirmation') border-red-300 @enderror"
                               placeholder="Confirmez le nouveau mot de passe">
                    </div>
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Roles Display -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Rôles actuels
                    </label>
                    <div class="flex flex-wrap gap-2 mb-3">
                        @forelse($user->roles as $role)
                            <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                        @empty
                            <span class="text-sm text-gray-500">Aucun rôle assigné</span>
                        @endforelse
                    </div>
                </div>

                <!-- Roles -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Assigner des rôles
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
                                       {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}
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

                <!-- User Info -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Informations sur l'utilisateur</h4>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Créé le</dt>
                            <dd class="text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Dernière modification</dt>
                            <dd class="text-gray-900">{{ $user->updated_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Email vérifié</dt>
                            <dd class="text-gray-900">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Oui ({{ $user->email_verified_at->format('d/m/Y') }})</span>
                                @else
                                    <span class="badge badge-warning">Non</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Statut</dt>
                            <dd class="text-gray-900">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-warning">En attente</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Actions -->
            <div class="card-body border-t border-gray-200 bg-gray-50">
                <div class="flex justify-between">
                    <div class="flex space-x-4">
                        <a href="{{ route('admin.users.show', $user) }}" 
                           class="group inline-flex items-center px-4 py-2 border-2 border-blue-200 text-sm font-medium rounded-xl text-blue-700 bg-blue-50 hover:bg-blue-100 hover:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-200/50 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg">
                            <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span class="group-hover:tracking-wide transition-all duration-300">Voir</span>
                        </a>
                        
                        <a href="{{ route('admin.users.index') }}" 
                           class="group inline-flex items-center px-4 py-2 border-2 border-gray-300 text-sm font-medium rounded-xl text-gray-700 bg-white hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-4 focus:ring-gray-200/50 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg">
                            <svg class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="group-hover:tracking-wide transition-all duration-300">Annuler</span>
                        </a>
                    </div>
                    
                    <button type="submit" 
                            class="group relative inline-flex items-center px-8 py-3 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 hover:from-green-700 hover:via-blue-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-green-500/50 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-2xl shadow-lg">
                        <div class="absolute inset-0 bg-gradient-to-r from-green-600 via-blue-600 to-purple-600 rounded-xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                        <span class="relative flex items-center">
                            <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="group-hover:tracking-wide transition-all duration-300">Mettre à jour</span>
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