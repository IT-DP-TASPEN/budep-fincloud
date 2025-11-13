<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_depositos', function (Blueprint $table) {
            $table->id();

            // data utama dari proyeksi deposito
            $table->string('rek_deposito')->index();
            $table->string('cif')->nullable();
            $table->string('nama_nasabah')->nullable();
            $table->integer('jangka_waktu')->nullable();
            $table->decimal('nominal', 20, 2)->default(0);
            $table->decimal('total_bunga', 20, 2)->default(0);
            $table->decimal('total_pajak', 20, 2)->default(0);
            $table->decimal('total_bayar', 20, 2)->default(0);
            $table->string('tanggal_bayar')->nullable();
            $table->date('jatuh_tempo')->nullable();

            // rekening tujuan
            $table->string('norek_tujuan')->nullable();
            $table->string('bank_tujuan')->nullable();
            $table->string('kode_bank')->nullable();
            $table->string('nama_rekening')->nullable();

            // informasi tambahan payroll
            $table->string('emailcorporate')->default('bprtaspen@gmail.com');
            $table->string('ibuobu')->default('IBU');
            $table->string('currency')->default('IDR');
            $table->string('remark1')->default('Budep');
            $table->string('remark2')->nullable();
            $table->string('remark3')->nullable();

            // kolom adjust / tambahan
            $table->string('adjust1')->nullable();
            $table->string('adjust2')->nullable();
            $table->string('adjust3')->nullable();
            $table->string('adjust4')->nullable();
            $table->string('adjust5')->nullable();
            $table->string('adjust6')->nullable();
            $table->string('adjust7')->nullable();
            $table->string('adjust8')->nullable();
            $table->string('adjust9')->nullable();
            $table->string('adjust10')->nullable();
            $table->string('adjust11')->nullable();
            $table->string('adjust12')->nullable();
            $table->string('adjust13')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_depositos');
    }
};
