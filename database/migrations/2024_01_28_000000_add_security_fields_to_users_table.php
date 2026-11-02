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
        Schema::table('users', function (Blueprint $table) {
            // Password expiration
            $table->timestamp('password_changed_at')->nullable()->after('password');
            $table->timestamp('password_expires_at')->nullable()->after('password_changed_at');

            // Session tracking
            $table->string('session_token')->nullable()->after('remember_token');

            // Account lockout fields
            $table->timestamp('locked_until')->nullable()->after('session_token');
            $table->integer('failed_login_attempts')->default(0)->after('locked_until');

            // 2FA fields
            $table->string('two_factor_secret')->nullable()->after('failed_login_attempts');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_enabled');

            // Login tracking
            $table->string('last_login_ip')->nullable()->after('two_factor_confirmed_at');
            $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
            $table->string('last_login_device')->nullable()->after('last_login_at');

            // Indexes
            $table->index('locked_until');
            $table->index('session_token');
            $table->index('password_expires_at');
            $table->index('two_factor_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'password_changed_at',
                'password_expires_at',
                'session_token',
                'locked_until',
                'failed_login_attempts',
                'two_factor_secret',
                'two_factor_enabled',
                'two_factor_confirmed_at',
                'last_login_ip',
                'last_login_at',
                'last_login_device',
            ]);

            $table->dropIndex(['locked_until']);
            $table->dropIndex(['session_token']);
            $table->dropIndex(['password_expires_at']);
            $table->dropIndex(['two_factor_enabled']);
        });
    }
};

