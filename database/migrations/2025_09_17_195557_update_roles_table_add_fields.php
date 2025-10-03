<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('display_name')->after('name');
            $table->string('color', 7)->default('#6B7280')->after('description');
            $table->json('permissions')->nullable()->after('color');
            $table->integer('level')->default(1)->after('permissions');
            $table->boolean('is_default')->default(false)->after('level');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'color', 'permissions', 'level', 'is_default']);
            $table->dropSoftDeletes();
        });
    }
};
