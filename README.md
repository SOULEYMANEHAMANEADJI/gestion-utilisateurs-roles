# 🚀 Gestion des Utilisateurs et des Rôles

[![CI/CD Pipeline](https://github.com/SOULEYMANEHAMANEADJI/gestion-utilisateurs-roles/actions/workflows/ci.yml/badge.svg)](https://github.com/SOULEYMANEHAMANEADJI/gestion-utilisateurs-roles/actions/workflows/ci.yml)
[![Security Audit](https://github.com/SOULEYMANEHAMANEADJI/gestion-utilisateurs-roles/actions/workflows/security.yml/badge.svg)](https://github.com/SOULEYMANEHAMANEADJI/gestion-utilisateurs-roles/actions/workflows/security.yml)
[![Deploy to Production](https://github.com/SOULEYMANEHAMANEADJI/gestion-utilisateurs-roles/actions/workflows/deploy.yml/badge.svg)](https://github.com/SOULEYMANEHAMANEADJI/gestion-utilisateurs-roles/actions/workflows/deploy.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

Une application Laravel moderne et professionnelle pour la gestion des utilisateurs avec un système de rôles hiérarchiques.

## ✨ Fonctionnalités

### 🔐 Authentification & Autorisation
- **Connexion/Inscription** avec validation en temps réel
- **Système de rôles hiérarchiques** : Super Admin > Admin > Manager > User
- **Middleware de sécurité** pour protéger les routes
- **Pages d'erreur personnalisées** (404, 403, 500)

### 👥 Gestion des Utilisateurs
- **CRUD complet** : Création, lecture, modification, archivage
- **Liste avancée** avec pagination, recherche et filtres
- **Assignation de rôles** avec restrictions hiérarchiques
- **Gestion des statuts** : Actif, Inactif, Suspendu, Archivé
- **Export de données** en CSV/Excel
- **Actions en lot** pour la gestion multiple

### 🎭 Gestion des Rôles
- **CRUD des rôles** avec niveaux hiérarchiques
- **Couleurs personnalisées** pour l'interface
- **Protection des rôles système**
- **Permissions granulaires**

### 🎨 Interface Utilisateur
- **Design moderne** avec Tailwind CSS
- **Responsive** : Mobile-first, tous écrans
- **Notifications toast** avec timer automatique
- **Validation client** en temps réel avec Alpine.js
- **UX optimisée** : Animations, tooltips, feedback visuel

## 🛠️ Technologies Utilisées

- **Backend** : Laravel 11, PHP 8.2+
- **Frontend** : Tailwind CSS, Alpine.js
- **Base de données** : MySQL/SQLite
- **Tests** : PHPUnit avec attributs PHP 8
- **Validation** : Laravel Requests + Alpine.js

## 📋 Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js & NPM
- MySQL ou SQLite

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone https://github.com/votre-username/gestion-utilisateurs-roles.git
cd gestion-utilisateurs-roles
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Configuration
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Base de données
```bash
# Configurer votre base de données dans .env
php artisan migrate
php artisan db:seed
```

### 5. Compiler les assets
```bash
npm run build
```

### 6. Lancer l'application
```bash
php artisan serve
```

## 👤 Comptes de Test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Super Admin | admin@example.com | password123 |
| Admin | admin.user@example.com | password123 |
| Manager | manager@example.com | password123 |
| User | user@example.com | password123 |

## 🧪 Tests

```bash
# Lancer tous les tests
php artisan test

# Tests avec couverture
php artisan test --coverage
```

**Résultats** : ✅ 20 tests passent (100%)

## 📁 Structure du Projet

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Contrôleurs d'administration
│   │   └── Auth/           # Contrôleurs d'authentification
│   ├── Middleware/          # Middleware personnalisés
│   └── Requests/            # Validation des formulaires
├── Models/                  # Modèles Eloquent
├── Services/               # Services métier
└── Exports/               # Export de données

resources/
├── views/
│   ├── admin/             # Vues d'administration
│   ├── auth/              # Vues d'authentification
│   ├── components/        # Composants réutilisables
│   └── layouts/           # Layouts
└── css/                   # Styles Tailwind

database/
├── migrations/            # Migrations de base de données
└── seeders/              # Seeders pour les données de test
```

## 🔒 Sécurité

- **CSRF Protection** sur tous les formulaires
- **Validation multi-niveaux** (client + serveur)
- **Hiérarchie des rôles** respectée
- **Audit trail** des actions utilisateurs
- **Protection OWASP** Top 10

## 🎯 Fonctionnalités Avancées

### 📊 Dashboard Analytique
- Statistiques en temps réel
- Graphiques d'activité
- Métriques d'utilisation

### 🔍 Recherche Avancée
- Recherche multi-critères
- Filtres dynamiques
- Suggestions automatiques

### 📤 Export de Données
- Export CSV/Excel
- Filtrage des données
- Formatage personnalisé

## 🤝 Contribution

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit vos changements (`git commit -am 'Ajouter nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👨‍💻 Auteur

- GitHub: [@SOULEYMANEHAMANEADJI](https://github.com/SOULEYMANEHAMANEADJI/gestion-utilisateurs-roles.git)
- Email: shamaneadji@gmail.com

## 🙏 Remerciements

- [Laravel](https://laravel.com/) - Framework PHP
- [Tailwind CSS](https://tailwindcss.com/) - Framework CSS
- [Alpine.js](https://alpinejs.dev/) - Framework JavaScript

---

⭐ **N'hésitez pas à donner une étoile si ce projet vous a aidé !**
