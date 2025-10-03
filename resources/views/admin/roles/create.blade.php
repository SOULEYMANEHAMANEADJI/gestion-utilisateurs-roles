@extends('layouts.app')

@section('title', 'Nouveau Rôle')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Nouveau Rôle
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Créez un nouveau rôle pour organiser les permissions des utilisateurs
        </p>
    </div>

    <!-- Form -->
    <div class="card">
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-6">
            @csrf
            
            <div class="card-body">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nom du rôle <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name') }}"
                               class="form-input @error('name') border-red-300 @enderror"
                               placeholder="moderator"
                               required>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Utilisez des lettres minuscules et des underscores uniquement (ex: moderator, content_manager)
                    </p>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <div class="mt-1">
                        <textarea name="description" 
                                  id="description"
                                  rows="3"
                                  class="form-input @error('description') border-red-300 @enderror"
                                  placeholder="Décrivez les permissions et responsabilités de ce rôle...">{{ old('description') }}</textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Optionnel : Décrivez les permissions et responsabilités de ce rôle
                    </p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Examples -->
                <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                    <h4 class="text-sm font-medium text-blue-900 mb-2">Exemples de noms de rôles</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                        <div class="space-y-1">
                            <div class="font-mono text-blue-800">moderator</div>
                            <div class="font-mono text-blue-800">editor</div>
                            <div class="font-mono text-blue-800">reviewer</div>
                        </div>
                        <div class="space-y-1">
                            <div class="font-mono text-blue-800">content_manager</div>
                            <div class="font-mono text-blue-800">support_agent</div>
                            <div class="font-mono text-blue-800">analyst</div>
                        </div>
                    </div>
                </div>

                <!-- System Roles Info -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Rôles système existants</h3>
                            <div class="mt-2 text-sm text-yellow-700">
                                <p>Les rôles suivants sont déjà définis dans le système :</p>
                                <ul class="mt-1 list-disc list-inside space-y-1">
                                    <li><span class="font-mono">admin</span> - Administrateur avec tous les privilèges</li>
                                    <li><span class="font-mono">author</span> - Auteur pouvant créer et modifier du contenu</li>
                                    <li><span class="font-mono">user</span> - Utilisateur standard avec accès limité</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card-body border-t border-gray-200 bg-gray-50">
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Créer le rôle
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Name Input Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    
    nameInput.addEventListener('input', function() {
        // Convert to lowercase and replace spaces/special chars with underscores
        let value = this.value.toLowerCase();
        value = value.replace(/[^a-z0-9_]/g, '_');
        value = value.replace(/_+/g, '_'); // Replace multiple underscores with single
        value = value.replace(/^_|_$/g, ''); // Remove leading/trailing underscores
        
        this.value = value;
    });
});
</script>
@endsection
