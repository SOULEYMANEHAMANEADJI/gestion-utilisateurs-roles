@props(['field' => '', 'rules' => []])

<div x-data="formValidation('{{ $field }}', @js($rules))" 
     class="space-y-1">
    
    <!-- Champ de saisie -->
    <div>
        <input {{ $attributes->merge(['class' => 'form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition duration-200']) }}
               x-model="value"
               @input="validate()"
               @blur="validate()"
               @focus="clearError()">
    </div>
    
    <!-- Messages d'erreur -->
    <div x-show="error" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="text-sm text-red-600 flex items-center">
        <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
        </svg>
        <span x-text="error"></span>
    </div>
    
    <!-- Messages d'aide -->
    <div x-show="help" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="text-sm text-gray-500 flex items-center">
        <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <span x-text="help"></span>
    </div>
</div>

<script>
function formValidation(field, rules) {
    return {
        value: '',
        error: '',
        help: '',
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
            
            // Validation longueur maximale
            if (rules.maxLength && this.value.length > rules.maxLength) {
                this.error = rules.maxLength;
                this.isValid = false;
                return;
            }
            
            // Validation mot de passe fort
            if (rules.strongPassword && !this.isStrongPassword(this.value)) {
                this.error = rules.strongPassword;
                this.isValid = false;
                return;
            }
            
            // Si tout est valide
            this.isValid = true;
            if (rules.help) {
                this.help = rules.help;
            }
        },
        
        clearError() {
            this.error = '';
            this.help = '';
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
