<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
        $roleId = $this->route('role')?->id;

        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[a-z][a-z0-9_]*$/i',
                Rule::unique('roles', 'name')->ignore($roleId)
            ],
            'display_name' => [
                'required',
                'string',
                'min:2',
                'max:100'
            ],
            'description' => [
                'nullable',
                'string',
                'max:500'
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
            ],
            'level' => [
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'is_default' => [
                'sometimes',
                'boolean'
            ],
            'permissions' => [
                'sometimes',
                'array'
            ],
            'permissions.*' => [
                'string',
                'max:100'
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
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.min' => 'Le nom du rôle doit contenir au moins :min caractères.',
            'name.max' => 'Le nom du rôle ne peut pas dépasser :max caractères.',
            'name.regex' => 'Le nom du rôle doit commencer par une lettre et ne contenir que des lettres, chiffres et underscores.',
            'name.unique' => 'Ce nom de rôle existe déjà.',

            'display_name.required' => 'Le nom d\'affichage est obligatoire.',
            'display_name.min' => 'Le nom d\'affichage doit contenir au moins :min caractères.',
            'display_name.max' => 'Le nom d\'affichage ne peut pas dépasser :max caractères.',

            'description.max' => 'La description ne peut pas dépasser :max caractères.',

            'color.regex' => 'La couleur doit être un code hexadécimal valide (ex: #FF0000).',

            'level.required' => 'Le niveau est obligatoire.',
            'level.integer' => 'Le niveau doit être un nombre entier.',
            'level.min' => 'Le niveau doit être au minimum :min.',
            'level.max' => 'Le niveau ne peut pas dépasser :max.',

            'is_default.boolean' => 'Le champ par défaut doit être vrai ou faux.',

            'permissions.array' => 'Les permissions doivent être sous forme de liste.',
            'permissions.*.string' => 'Chaque permission doit être une chaîne de caractères.',
            'permissions.*.max' => 'Le nom de permission ne peut pas dépasser :max caractères.',
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
            'name' => 'nom du rôle',
            'display_name' => 'nom d\'affichage',
            'description' => 'description',
            'color' => 'couleur',
            'level' => 'niveau',
            'is_default' => 'rôle par défaut',
            'permissions' => 'permissions',
            'permissions.*' => 'permission',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer le nom du rôle
        if ($this->has('name')) {
            $this->merge([
                'name' => strtolower(trim($this->name))
            ]);
        }

        // Assurer que is_default est un booléen
        if ($this->has('is_default')) {
            $this->merge([
                'is_default' => filter_var($this->is_default, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Nettoyer les permissions
        if ($this->has('permissions') && is_array($this->permissions)) {
            $this->merge([
                'permissions' => array_filter(array_unique($this->permissions))
            ]);
        }
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Validation métier supplémentaire
        $this->validateBusinessRules();
    }

    /**
     * Validation métier supplémentaire
     */
    private function validateBusinessRules(): void
    {
        $currentUser = auth()->user();

        // Vérifier que l'utilisateur peut créer/modifier ce niveau de rôle
        if (!$currentUser->hasRole('super_admin')) {
            $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

            if ($this->level >= $currentUserMaxLevel) {
                $this->validator->errors()->add('level',
                    "Vous ne pouvez pas créer un rôle de niveau supérieur ou égal au vôtre (niveau {$currentUserMaxLevel})."
                );
            }
        }

        // Vérifier les permissions critiques
        $criticalPermissions = ['users.delete', 'roles.delete', 'settings.manage'];
        $requestedPermissions = $this->permissions ?? [];

        $hasCriticalPermissions = array_intersect($criticalPermissions, $requestedPermissions);

        if (!empty($hasCriticalPermissions) && !$currentUser->hasRole('super_admin')) {
            $this->validator->errors()->add('permissions',
                'Seuls les Super Administrateurs peuvent assigner des permissions critiques.'
            );
        }
    }
}
