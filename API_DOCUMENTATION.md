# 📚 Documentation API - Système de Gestion des Utilisateurs et Rôles

## 🎯 Vue d'ensemble

Ce système Laravel offre une gestion complète des utilisateurs et des rôles avec :
- Hiérarchie des permissions
- Actions en lot
- Export Excel
- Recherche avancée
- Système d'archivage

## 🔐 Authentification

Toutes les routes admin nécessitent une authentification et des permissions appropriées.

```http
POST /login
Content-Type: application/json

{
    "email": "superadmin@app.com",
    "password": "Super123!"
}
```

## 👥 API Utilisateurs

### Lister les utilisateurs
```http
GET /admin/users
Authorization: Bearer {token}

Paramètres optionnels:
- search: Recherche par nom/email/téléphone
- role: Filtrer par rôle
- status: Filtrer par statut (active/inactive/archived)
- sort: Champ de tri (created_at, name, email)
- direction: Direction du tri (asc/desc)
```

**Réponse:**
```json
{
    "users": [
        {
            "id": 1,
            "name": "Super Administrateur",
            "email": "superadmin@app.com",
            "status": "active",
            "roles": ["Super Administrateur"],
            "created_at": "2025-09-22T17:12:47.000000Z"
        }
    ],
    "stats": {
        "total": 7,
        "active": 5,
        "inactive": 1,
        "archived": 1
    }
}
```

### Créer un utilisateur
```http
POST /admin/users
Content-Type: application/json

{
    "name": "Nouvel Utilisateur",
    "email": "nouveau@example.com",
    "password": "MotDePasse123!",
    "password_confirmation": "MotDePasse123!",
    "phone": "+33 1 23 45 67 89",
    "address": "123 Rue Example",
    "birth_date": "1990-01-01",
    "status": "active",
    "roles": [1, 2]
}
```

### Modifier un utilisateur
```http
PUT /admin/users/{id}
Content-Type: application/json

{
    "name": "Nom Modifié",
    "email": "modifie@example.com",
    "roles": [2]
}
```

### Changer le statut d'un utilisateur
```http
PATCH /admin/users/{id}/status
Content-Type: application/json

{
    "status": "inactive"
}
```

**Statuts disponibles:** `active`, `inactive`, `suspended`, `archived`

### Actions en lot
```http
POST /admin/users/bulk-action
Content-Type: application/json

{
    "action": "deactivate",
    "user_ids": [1, 2, 3]
}
```

**Actions disponibles:** `activate`, `deactivate`, `archive`, `delete`

### Recherche rapide (AJAX)
```http
GET /admin/users/search?q=john
Authorization: Bearer {token}
```

**Réponse:**
```json
{
    "results": [
        {
            "id": 5,
            "name": "John Doe",
            "email": "john@example.com",
            "status": "active",
            "roles": "Utilisateur",
            "avatar": "https://app.com/storage/avatars/john.jpg"
        }
    ]
}
```

### Export Excel
```http
GET /admin/users/export
Authorization: Bearer {token}

Paramètres optionnels (mêmes que la liste):
- search, role, status, date_from, date_to
```

**Réponse:** Fichier Excel téléchargeable

### Analytics
```http
GET /admin/analytics?period=30
Authorization: Bearer {token}
```

**Réponse:**
```json
{
    "total_users": 100,
    "active_users": 85,
    "new_registrations": [
        {"date": "2025-09-22", "count": 5}
    ],
    "role_distribution": [
        {"name": "Utilisateur", "count": 50, "color": "#10B981"}
    ],
    "email_domains": [
        {"domain": "gmail.com", "count": 25}
    ]
}
```

## 🎭 API Rôles

### Lister les rôles
```http
GET /admin/roles
Authorization: Bearer {token}

Paramètres optionnels:
- search: Recherche par nom/description
- level: Niveau minimum
- is_default: Rôles par défaut (true/false)
```

### Créer un rôle
```http
POST /admin/roles
Content-Type: application/json

{
    "name": "nouveau_role",
    "display_name": "Nouveau Rôle",
    "description": "Description du rôle",
    "color": "#3B82F6",
    "level": 50,
    "is_default": false,
    "permissions": [
        "users.view",
        "users.create"
    ]
}
```

### Dupliquer un rôle
```http
POST /admin/roles/{id}/duplicate
Authorization: Bearer {token}
```

### Obtenir les utilisateurs d'un rôle
```http
GET /admin/roles/{id}/users
Authorization: Bearer {token}
```

## 🔒 Système de Permissions

### Hiérarchie des niveaux
- **100**: Super Administrateur (accès total)
- **80**: Administrateur (gestion utilisateurs/rôles)
- **60**: Manager (gestion utilisateurs limitée)
- **40**: Auteur (création de contenu)
- **20**: Utilisateur (accès basique)

### Permissions disponibles
```json
{
    "users": [
        "users.view",
        "users.create", 
        "users.edit",
        "users.delete",
        "users.export",
        "users.bulk_actions"
    ],
    "roles": [
        "roles.view",
        "roles.create",
        "roles.edit", 
        "roles.delete"
    ],
    "system": [
        "analytics.view",
        "logs.view",
        "settings.manage"
    ]
}
```

## 🚨 Gestion d'erreurs

### Codes d'erreur
- **400**: Erreur générale
- **403**: Permission refusée
- **404**: Ressource non trouvée
- **409**: Conflit (email dupliqué, dernier super admin)
- **422**: Erreur de validation

### Format des erreurs
```json
{
    "error": true,
    "type": "permission_denied",
    "message": "Vous n'avez pas les permissions pour effectuer cette action.",
    "context": {
        "target_user_id": 1,
        "current_user_id": 2
    }
}
```

## 🛠️ Commandes Artisan

### Créer un super administrateur
```bash
php artisan user:create-super-admin \
  --name="Admin" \
  --email="admin@domain.com" \
  --password="SecurePass123!"
```

### Diagnostic système
```bash
php artisan system:diagnostic --detailed --fix
```

### Initialiser les données
```bash
php artisan db:seed --class=ProductionReadySeeder
```

## 📊 Monitoring et Logs

Toutes les actions sensibles sont automatiquement loggées :
- Création/modification/suppression d'utilisateurs
- Changements de rôles
- Actions en lot
- Tentatives d'accès non autorisées

**Exemple de log:**
```json
{
    "level": "info",
    "message": "Utilisateur créé avec succès",
    "context": {
        "user_id": 15,
        "created_by": 1,
        "roles": [2, 3],
        "ip": "192.168.1.100",
        "timestamp": "2025-09-22T17:12:47.000000Z"
    }
}
```

## 🔧 Configuration

### Variables d'environnement importantes
```env
APP_DEBUG=false                 # JAMAIS true en production
DB_CONNECTION=mysql
MAIL_MAILER=smtp
QUEUE_CONNECTION=database
```

### Sécurité en production
- Utiliser HTTPS uniquement
- Configurer les CORS appropriés
- Activer la limitation de taux (rate limiting)
- Sauvegardes automatiques de la DB
- Monitoring des erreurs (Sentry, Bugsnag)

---

## 📞 Support

Pour toute question ou problème :
1. Vérifiez les logs dans `storage/logs/`
2. Utilisez `php artisan system:diagnostic`
3. Consultez cette documentation

**Version:** 1.0.0  
**Dernière mise à jour:** 22 septembre 2025
