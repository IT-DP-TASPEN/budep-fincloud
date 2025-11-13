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
        Schema::create('proyeksi_depositos', function (Blueprint $table) {
            $table->id();
            $table->string('rek_deposito')->index();
            $table->string('cif')->nullable();
            $table->string('nama_nasabah')->nullable();
            $table->integer('jangka_waktu')->nullable();
            $table->decimal('nominal', 20, 2)->default(0);
            $table->decimal('total_bunga', 20, 2)->default(0);
            $table->decimal('total_pajak', 20, 2)->default(0);
            $table->decimal('total_bayar', 20, 2)->default(0);
            $table->string('tanggal_bayar', 2)->nullable();
            $table->date('jatuh_tempo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyeksi_depositos');
    }
};
