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
        Schema::create('oauth_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('microsoft'); // microsoft, google, etc.
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('token_type')->default('Bearer');
            $table->text('scope')->nullable();
            $table->string('auth_method')->default('client_credentials'); // authorization_code or client_credentials
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['provider', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oauth_tokens');
    }
};
