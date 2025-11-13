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
        Schema::create('master_depositos', function (Blueprint $table) {
            $table->id();
            $table->string('cif_no', 30)->nullable();
            $table->string('norek_deposito', 50)->nullable()->unique();
            $table->string('nama_nasabah', 255)->nullable();
            $table->string('product_name', 255)->nullable();
            $table->string('account_type', 100)->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('nominal', 20, 2)->nullable();
            $table->decimal('bunga', 10, 2)->nullable();
            $table->string('kode_cabang', 10)->nullable();
            $table->string('kode_produk', 20)->nullable();
            $table->date('tgl_mulai')->nullable();
            $table->date('tgl_jatuh_tempo')->nullable();
            $table->string('tgl_bayar', 10)->nullable();
            $table->string('aro', 10)->nullable();
            $table->string('potong_pajak', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_depositos');
    }
};
