@props(['field' => '', 'rules' => [], 'type' => 'text', 'placeholder' => '', 'value' => ''])

<div x-data="fieldValidation('{{ $field }}', @js($rules))" class="space-y-1">
    <input type="{{ $type }}" 
           name="{{ $field }}" 
           id="{{ $field }}"
           x-model="value"
           @input="validate()"
           @blur="validate()"
           value="{{ $value }}"
           placeholder="{{ $placeholder }}"
           class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200"
           :class="{ 'border-red-300 ring-red-500': error }">
    
    <!-- Messages d'erreur -->
    <div x-show="error" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="text-sm text-red-600 flex items-center">
        <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <span x-text="error"></span>
    </div>
    
    <!-- Messages d'aide -->
    <div x-show="!error && value && help" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         class="text-sm text-gray-500 flex items-center">
        <svg class="w-4 h-4 mr-1 flex-shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
        </svg>
        <span x-text="help"></span>
    </div>
</div>

<script>
function fieldValidation(fieldName, rules) {
    return {
        value: '',
        error: '',
        help: rules.help || '',
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
</script>
