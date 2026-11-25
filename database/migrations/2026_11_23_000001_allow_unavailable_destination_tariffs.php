<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['mysql' => 'destinasi', 'warehouse' => 'dim_destinasi'] as $connection => $tableName) {
            Schema::connection($connection)->table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('harga_tiket_dewasa')->nullable()->change();
                $table->unsignedBigInteger('harga_tiket_anak')->nullable()->change();
                $table->string('jam_operasional', 60)->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['mysql' => 'destinasi', 'warehouse' => 'dim_destinasi'] as $connection => $tableName) {
            foreach (['harga_tiket_dewasa' => 0, 'harga_tiket_anak' => 0, 'jam_operasional' => ''] as $field => $fallback) {
                DB::connection($connection)->table($tableName)->whereNull($field)->update([$field => $fallback]);
            }
            Schema::connection($connection)->table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('harga_tiket_dewasa')->default(0)->change();
                $table->unsignedBigInteger('harga_tiket_anak')->default(0)->change();
                $table->string('jam_operasional', 60)->change();
            });
        }
    }
};
