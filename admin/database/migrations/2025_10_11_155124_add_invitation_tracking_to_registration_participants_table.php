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
        Schema::table('registration_participants', function (Blueprint $table) {
            $table->timestamp('invitation_sent_at')->nullable()->after('attendance_verified_at');
            $table->unsignedBigInteger('invitation_sent_by')->nullable()->after('invitation_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_participants', function (Blueprint $table) {
            $table->dropColumn(['invitation_sent_at', 'invitation_sent_by']);
        });
    }
};
