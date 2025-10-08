<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing role values from old system to new system
        // super_admin -> admin
        DB::table('admins')->where('role', 'super_admin')->update(['role' => 'admin']);
        
        // Change column type from ENUM to VARCHAR if needed
        // This handles both ENUM and existing VARCHAR columns
        Schema::table('admins', function (Blueprint $table) {
            $table->string('role', 50)->default('admin')->change();
        });
        
        // Set any null or empty roles to 'admin'
        DB::table('admins')
            ->whereNull('role')
            ->orWhere('role', '')
            ->update(['role' => 'admin']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert admin roles back to super_admin for rollback
        Schema::table('admins', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'admin'])->default('admin')->change();
        });
        
        // Note: This won't restore secretariat, finance, executive users
        // as they didn't exist in the old system
    }
};
