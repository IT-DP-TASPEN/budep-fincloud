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
        Schema::create('master_cifs', function (Blueprint $table) {
            $table->id();
            $table->string('cif')->unique();
            $table->string('alt_no')->nullable();
            $table->string('customer_name');
            $table->string('no_hp')->nullable();
            $table->string('customer_type')->nullable();
            $table->string('no_ktp')->nullable();
            $table->string('kode_cabang', 10)->nullable();
            $table->date('register_date')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_cifs');
    }
};
