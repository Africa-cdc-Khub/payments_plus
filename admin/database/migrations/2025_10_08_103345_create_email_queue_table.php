<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     Schema::create('email_queue', function (Blueprint $table) {
    //         $table->id();
    //         $table->string('to_email');
    //         $table->string('to_name')->nullable();
    //         $table->string('subject');
    //         $table->string('template_name')->nullable();
    //         $table->json('template_data')->nullable();
    //         $table->text('body_html')->nullable();
    //         $table->text('body_text')->nullable();
    //         $table->string('email_type')->default('general'); // general, invitation, notification, etc.
    //         $table->integer('priority')->default(5); // 1-10, lower is higher priority
    //         $table->enum('status', ['pending', 'processing', 'sent', 'failed'])->default('pending');
    //         $table->integer('attempts')->default(0);
    //         $table->integer('max_attempts')->default(3);
    //         $table->text('error_message')->nullable();
    //         $table->timestamp('sent_at')->nullable();
    //         $table->timestamp('next_attempt_at')->nullable();
    //         $table->timestamps();
            
    //         $table->index(['status', 'priority', 'next_attempt_at']);
    //         $table->index('email_type');
    //         $table->index('created_at');
    //     });
    // }

    // /**
    //  * Reverse the migrations.
    //  */
    // public function down(): void
    // {
    //     Schema::dropIfExists('email_queue');
    // }
};
