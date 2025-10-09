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
        Schema::table('payments', function (Blueprint $table) {
            // Track which admin manually marked payment as paid
            $table->unsignedInteger('completed_by')->nullable()->after('payment_status');
            
            // Store remarks/notes when manually marking payment as paid
            $table->text('manual_payment_remarks')->nullable()->after('completed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop columns
            $table->dropColumn(['completed_by', 'manual_payment_remarks']);
        });
    }
};
