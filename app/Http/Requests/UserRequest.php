<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'
            ],
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => [
                $this->isMethod('POST') ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[0-9\s\-\(\)]+$/'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500'
            ],
            'birth_date' => [
                'nullable',
                'date',
                'before:today',
                'after:1900-01-01'
            ],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048' // 2MB max
            ],
            'status' => [
                'sometimes',
                'in:active,inactive,suspended'
            ],
            'roles' => [
                'sometimes',
                'array',
                'min:1'
            ],
            'roles.*' => [
                'required',
                'integer',
                'exists:roles,id'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'name.min' => 'Le nom doit contenir au moins :min caractères.',
            'name.max' => 'Le nom ne peut pas dépasser :max caractères.',
            'name.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins :min caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.letters' => 'Le mot de passe doit contenir au moins une lettre.',
            'password.mixed' => 'Le mot de passe doit contenir des majuscules et minuscules.',
            'password.numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
            'password.symbols' => 'Le mot de passe doit contenir au moins un symbole.',
            
            'phone.regex' => 'Le numéro de téléphone doit contenir uniquement des chiffres, espaces, tirets et parenthèses.',
            'phone.max' => 'Le numéro de téléphone ne peut pas dépasser :max caractères.',
            
            'address.max' => 'L\'adresse ne peut pas dépasser :max caractères.',
            
            'birth_date.date' => 'La date de naissance doit être une date valide.',
            'birth_date.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'birth_date.after' => 'La date de naissance doit être postérieure à 1900.',
            
            'avatar.image' => 'Le fichier doit être une image.',
            'avatar.mimes' => 'L\'image doit être au format JPEG, PNG, JPG ou GIF.',
            'avatar.max' => 'L\'image ne peut pas dépasser 2MB.',
            
            'status.in' => 'Le statut doit être actif, inactif ou suspendu.',
            
            'roles.array' => 'Les rôles doivent être sélectionnés.',
            'roles.min' => 'Au moins un rôle doit être sélectionné.',
            'roles.*.exists' => 'Le rôle sélectionné n\'existe pas.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'email' => 'adresse email',
            'password' => 'mot de passe',
            'phone' => 'téléphone',
            'address' => 'adresse',
            'birth_date' => 'date de naissance',
            'avatar' => 'photo de profil',
            'status' => 'statut',
            'roles' => 'rôles',
            'roles.*' => 'rôle',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('roles') && is_string($this->roles)) {
            $this->merge([
                'roles' => explode(',', $this->roles)
            ]);
        }
    }
}
