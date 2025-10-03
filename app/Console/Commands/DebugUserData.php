<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DebugUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:user-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug user data retrieval for advanced user management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== DEBUG USER DATA ===');

        // Test database connection
        try {
            $connection = DB::connection();
            $this->info('✓ Database connection successful');
            $this->info('Database name: ' . $connection->getDatabaseName());
        } catch (\Exception $e) {
            $this->error('✗ Database connection failed: ' . $e->getMessage());
            return 1;
        }

        // Count users directly in database
        try {
            $userCount = DB::table('users')->count();
            $this->info("Users count in database: {$userCount}");
        } catch (\Exception $e) {
            $this->error('✗ Error counting users: ' . $e->getMessage());
        }

        // Test User model
        try {
            $users = User::all();
            $this->info("Users count via model: " . $users->count());
            
            if ($users->count() > 0) {
                $this->info("First 3 users:");
                foreach ($users->take(3) as $user) {
                    $this->line("- ID: {$user->id}, Name: {$user->name}, Email: {$user->email}");
                }
            }
        } catch (\Exception $e) {
            $this->error('✗ Error with User model: ' . $e->getMessage());
        }

        // Test pagination exactly as controller
        try {
            $this->info('=== TESTING CONTROLLER LOGIC ===');
            
            $query = User::with('roles');
            $users = $query->paginate(15);
            
            $this->info("Controller-style query results:");
            $this->info("Collection count: " . $users->count());
            $this->info("Total: " . $users->total());
            $this->info("Current page: " . $users->currentPage());
            $this->info("Last page: " . $users->lastPage());
            
            // Test the jsData structure like in controller
            $jsData = [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem()
                ],
                'filters' => [
                    'search' => '',
                    'role' => '',
                    'status' => ''
                ]
            ];
            
            $this->info("JS Data structure:");
            $this->info("Users count in jsData: " . count($jsData['users']));
            $this->info("Total in pagination: " . $jsData['pagination']['total']);
            $this->info("From: " . ($jsData['pagination']['from'] ?? 'null'));
            $this->info("To: " . ($jsData['pagination']['to'] ?? 'null'));
            
            if (count($jsData['users']) > 0) {
                $firstUser = $jsData['users'][0];
                $this->info("First user in jsData:");
                $this->line("ID: " . $firstUser->id);
                $this->line("Name: " . $firstUser->name);
                $this->line("Email: " . $firstUser->email);
                
                // Check if roles are loaded
                if (isset($firstUser->roles)) {
                    $this->line("Roles loaded: " . ($firstUser->roles ? 'Yes' : 'No'));
                    if ($firstUser->roles) {
                        $this->line("Roles count: " . $firstUser->roles->count());
                    }
                }
            }
            
            // Test JSON encoding
            try {
                $jsonData = json_encode($jsData);
                $this->info("JSON encoding: " . (json_last_error() === JSON_ERROR_NONE ? 'SUCCESS' : 'FAILED'));
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error("JSON Error: " . json_last_error_msg());
                }
            } catch (\Exception $e) {
                $this->error("JSON encoding exception: " . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            $this->error('✗ Error with controller logic test: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }

        $this->info('=== END DEBUG ===');
        return 0;
    }
}
