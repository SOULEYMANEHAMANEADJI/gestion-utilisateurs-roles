@extends('layouts.app')

@section('title', 'Détails de l\'Utilisateur')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    {{ $user->name }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Détails et informations de l'utilisateur
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- User Profile -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Avatar -->
                    <div class="mx-auto h-24 w-24 rounded-full bg-primary-500 flex items-center justify-center mb-4">
                        <span class="text-white font-bold text-2xl">
                            {{ substr($user->name, 0, 1) }}
                        </span>
                    </div>
                    
                    <!-- Name and Email -->
                    <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                    
                    <!-- Status -->
                    <div class="mt-4">
                        @if($user->email_verified_at)
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-warning">En attente</span>
                        @endif
                    </div>
                    
                    <!-- Roles -->
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Rôles assignés</h4>
                        <div class="flex flex-wrap justify-center gap-1">
                            @forelse($user->roles as $role)
                                <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                            @empty
                                <span class="text-sm text-gray-500">Aucun rôle</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Actions rapides</h3>
                </div>
                <div class="card-body space-y-3">
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full btn {{ $user->email_verified_at ? 'btn-warning' : 'btn-success' }}"
                                    onclick="return confirm('Êtes-vous sûr de vouloir {{ $user->email_verified_at ? 'désactiver' : 'activer' }} cet utilisateur ?')">
                                @if($user->email_verified_at)
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"></path>
                                    </svg>
                                    Désactiver
                                @else
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Activer
                                @endif
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('admin.users.edit', $user) }}" class="w-full btn btn-outline">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier
                    </a>
                    
                    @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full btn btn-danger"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Supprimer
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="lg:col-span-2">
            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Informations personnelles</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nom complet</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Adresse email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email vérifié</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Oui</span>
                                    <span class="text-gray-500 text-xs ml-2">({{ $user->email_verified_at->format('d/m/Y à H:i') }})</span>
                                @else
                                    <span class="badge badge-warning">Non</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Statut du compte</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-warning">En attente de vérification</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Roles Information -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Rôles et permissions</h3>
                </div>
                <div class="card-body">
                    @if($user->roles->count() > 0)
                        <div class="space-y-4">
                            @foreach($user->roles as $role)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-900">{{ ucfirst($role->name) }}</h4>
                                            @if($role->description)
                                                <p class="text-sm text-gray-500 mt-1">{{ $role->description }}</p>
                                            @endif
                                        </div>
                                        <span class="badge badge-primary">{{ ucfirst($role->name) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun rôle assigné</h3>
                            <p class="mt-1 text-sm text-gray-500">Cet utilisateur n'a aucun rôle assigné.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Assigner des rôles
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Information -->
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="text-lg font-medium text-gray-900">Informations du compte</h3>
                </div>
                <div class="card-body">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Créé le</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dernière modification</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y à H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID utilisateur</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $user->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nombre de rôles</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->roles->count() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
