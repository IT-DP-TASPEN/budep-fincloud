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
        Schema::create('rekening_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('norek_deposito')->index();
            $table->string('cif')->nullable();
            $table->string('norek_tujuan')->nullable();
            $table->string('bank_tujuan')->nullable();
            $table->string('kode_bank')->nullable();
            $table->string('nama_rekening')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekening_transfers');
    }
};
