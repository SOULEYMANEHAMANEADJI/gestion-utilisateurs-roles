@extends('layouts.app')

@section('title', 'Détails du Rôle')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    {{ ucfirst($role->name) }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Détails et informations du rôle
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Role Info -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Role Icon -->
                    <div class="mx-auto h-16 w-16 rounded-lg bg-primary-500 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    
                    <!-- Role Name -->
                    <h3 class="text-xl font-medium text-gray-900">{{ ucfirst($role->name) }}</h3>
                    
                    <!-- Role Type -->
                    <div class="mt-2">
                        @if(in_array($role->name, ['admin', 'author', 'user']))
                            <span class="badge badge-secondary">Rôle Système</span>
                        @else
                            <span class="badge badge-primary">Rôle Personnalisé</span>
                        @endif
                    </div>
                    
                    <!-- Users Count -->
                    <div class="mt-4">
                        <div class="text-2xl font-bold text-gray-900">{{ $role->users_count }}</div>
                        <div class="text-sm text-gray-500">utilisateur(s) assigné(s)</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Actions rapides</h3>
                </div>
                <div class="card-body space-y-3">
                    <a href="{{ route('admin.roles.edit', $role) }}" class="w-full btn btn-outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier le rôle
                    </a>
                    
                    @if(!in_array($role->name, ['admin', 'author', 'user']) && !$role->hasUsers())
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full btn btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Supprimer le rôle
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="w-full btn btn-outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Voir les utilisateurs
                    </a>
                </div>
            </div>
        </div>

        <!-- Role Details -->
        <div class="lg:col-span-2">
            <!-- Role Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Informations du rôle</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nom du rôle</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $role->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type de rôle</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if(in_array($role->name, ['admin', 'author', 'user']))
                                    <span class="badge badge-secondary">Système</span>
                                @else
                                    <span class="badge badge-primary">Personnalisé</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Utilisateurs assignés</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $role->users_count }} utilisateur(s)</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Créé le</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $role->created_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dernière modification</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $role->updated_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID du rôle</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $role->id }}</dd>
                        </div>
                    </dl>
                    
                    @if($role->description)
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $role->description }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Users with this role -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Utilisateurs avec ce rôle</h3>
                </div>
                <div class="card-body">
                    @if($role->users->count() > 0)
                        <div class="space-y-4">
                            @foreach($role->users as $user)
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-primary-500 flex items-center justify-center">
                                                <span class="text-white font-medium text-sm">
                                                    {{ substr($user->name, 0, 1) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if($user->email_verified_at)
                                            <span class="badge badge-success">Actif</span>
                                        @else
                                            <span class="badge badge-warning">En attente</span>
                                        @endif
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="text-primary-600 hover:text-primary-900" 
                                           title="Voir l'utilisateur">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-4 text-center">
                            <a href="{{ route('admin.users.index', ['role' => $role->name]) }}" class="btn btn-outline">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                                Voir tous les utilisateurs avec ce rôle
                            </a>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun utilisateur assigné</h3>
                            <p class="mt-1 text-sm text-gray-500">Aucun utilisateur n'a actuellement ce rôle assigné.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    Gérer les utilisateurs
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Role Statistics -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Statistiques</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $role->users_count }}</div>
                            <div class="text-sm text-gray-500">Utilisateurs assignés</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $role->users->where('email_verified_at', '!=', null)->count() }}
                            </div>
                            <div class="text-sm text-gray-500">Utilisateurs actifs</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $role->users->where('email_verified_at', null)->count() }}
                            </div>
                            <div class="text-sm text-gray-500">En attente</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
