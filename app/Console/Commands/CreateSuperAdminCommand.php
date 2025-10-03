<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'user:create-super-admin
                           {--name= : Nom du super administrateur}
                           {--email= : Email du super administrateur}
                           {--password= : Mot de passe (sera demandé si non fourni)}
                           {--force : Forcer la création même si l\'email existe}';

    /**
     * The console command description.
     */
    protected $description = 'Créer un nouveau super administrateur';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Création d\'un Super Administrateur');
        $this->line('═══════════════════════════════════════');

        // Récupérer ou demander les informations
        $name = $this->option('name') ?: $this->ask('Nom complet');
        $email = $this->option('email') ?: $this->ask('Adresse email');
        $password = $this->option('password') ?: $this->secret('Mot de passe');
        $force = $this->option('force');

        // Validation
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error("❌ {$error}");
            }
            return self::FAILURE;
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && !$force) {
            $this->error("❌ Un utilisateur avec cet email existe déjà.");
            $this->info("💡 Utilisez --force pour remplacer l'utilisateur existant.");
            return self::FAILURE;
        }

        try {
            // Vérifier que le rôle super_admin existe
            $superAdminRole = Role::where('name', 'super_admin')->first();
            if (!$superAdminRole) {
                $this->error("❌ Le rôle 'super_admin' n'existe pas. Exécutez d'abord les migrations et seeders.");
                return self::FAILURE;
            }

            // Créer ou mettre à jour l'utilisateur
            if ($existingUser && $force) {
                $user = $existingUser;
                $user->update([
                    'name' => $name,
                    'password' => Hash::make($password),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                $action = 'mis à jour';
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                $action = 'créé';
            }

            // Assigner le rôle super_admin
            if (!$user->hasRole('super_admin')) {
                $user->roles()->attach($superAdminRole->id, [
                    'assigned_by' => $user->id,
                    'assigned_at' => now()
                ]);
            }

            $this->line('');
            $this->info("✅ Super Administrateur {$action} avec succès !");
            $this->line('');
            $this->table(
                ['Champ', 'Valeur'],
                [
                    ['ID', $user->id],
                    ['Nom', $user->name],
                    ['Email', $user->email],
                    ['Statut', $user->status],
                    ['Rôle', 'Super Administrateur'],
                    ['Créé le', $user->created_at->format('d/m/Y H:i:s')],
                ]
            );

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la création : {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
