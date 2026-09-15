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
        if (! Schema::hasColumn('clients', 'public_access_token')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('public_access_token')->nullable()->unique()->after('emergency_contact_phone');
            });
        }

        if (! Schema::hasColumn('clients', 'public_access_code')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('public_access_code')->nullable()->after('public_access_token');
            });
        }

        if (! Schema::hasColumn('clients', 'public_access_expires_at')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->timestamp('public_access_expires_at')->nullable()->after('public_access_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['public_access_token', 'public_access_code', 'public_access_expires_at']);
        });
    }
};
