<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('client_code')->nullable()->unique()->after('id');
        });

        DB::table('clients')->orderBy('id')->get(['id', 'name'])->each(
            fn (object $client, int $index) => DB::table('clients')
                ->where('id', $client->id)
                ->update(['client_code' => Str::upper(Str::substr(trim($client->name), 0, 1)).sprintf('%02d', $index + 1)])
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['client_code']);
            $table->dropColumn('client_code');
        });
    }
};
