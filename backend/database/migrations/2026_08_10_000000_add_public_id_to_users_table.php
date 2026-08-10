<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('public_id')->nullable()->unique()->after('id');
        });

        DB::table('users')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $id): void {
                DB::table('users')
                    ->where('id', $id)
                    ->update(['public_id' => (string) Str::uuid7()]);
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->uuid('public_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['public_id']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('public_id');
        });
    }
};
