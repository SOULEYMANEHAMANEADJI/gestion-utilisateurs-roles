@extends('layouts.app')

@section('title', 'Modifier le Rôle')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Modifier le Rôle
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Modifiez les informations du rôle {{ ucfirst($role->name) }}
        </p>
    </div>

    <!-- Form -->
    <div class="card">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
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
                               value="{{ old('name', $role->name) }}"
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
                                  placeholder="Décrivez les permissions et responsabilités de ce rôle...">{{ old('description', $role->description) }}</textarea>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Optionnel : Décrivez les permissions et responsabilités de ce rôle
                    </p>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Info -->
                <div class="bg-gray-50 p-4 rounded-md">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Informations sur le rôle</h4>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Créé le</dt>
                            <dd class="text-gray-900">{{ $role->created_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Dernière modification</dt>
                            <dd class="text-gray-900">{{ $role->updated_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Utilisateurs assignés</dt>
                            <dd class="text-gray-900">{{ $role->users_count }} utilisateur(s)</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Type de rôle</dt>
                            <dd class="text-gray-900">
                                @if(in_array($role->name, ['admin', 'author', 'user']))
                                    <span class="badge badge-secondary">Système</span>
                                @else
                                    <span class="badge badge-primary">Personnalisé</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- System Role Warning -->
                @if(in_array($role->name, ['admin', 'author', 'user']))
                    <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Rôle système</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Ce rôle fait partie des rôles système. Modifiez-le avec précaution car cela peut affecter le fonctionnement de l'application.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Users with this role -->
                @if($role->users_count > 0)
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">Utilisateurs avec ce rôle</h4>
                        <p class="text-sm text-blue-700">
                            {{ $role->users_count }} utilisateur(s) ont actuellement ce rôle assigné. 
                            <a href="{{ route('admin.roles.show', $role) }}" class="font-medium underline hover:text-blue-800">
                                Voir la liste des utilisateurs
                            </a>
                        </p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="card-body border-t border-gray-200 bg-gray-50">
                <div class="flex justify-between">
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Voir
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline">
                            Annuler
                        </a>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Mettre à jour
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
