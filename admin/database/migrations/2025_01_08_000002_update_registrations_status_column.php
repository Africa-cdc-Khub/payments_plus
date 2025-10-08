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
        // First check if the column exists and what type it is
        $columnType = DB::select("SHOW COLUMNS FROM registrations WHERE Field = 'status'");
        
        if (!empty($columnType)) {
            // Modify the status column to allow more values
            DB::statement("ALTER TABLE registrations MODIFY COLUMN status VARCHAR(50) DEFAULT 'pending'");
        } else {
            // If column doesn't exist, add it
            Schema::table('registrations', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->after('currency');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum if needed
        // Note: This assumes the original was an enum with 'pending' and 'completed'
        DB::statement("ALTER TABLE registrations MODIFY COLUMN status ENUM('pending', 'completed') DEFAULT 'pending'");
    }
};

