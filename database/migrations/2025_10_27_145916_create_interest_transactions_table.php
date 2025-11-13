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
        Schema::create('interest_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('account_number');
            $table->string('customer_name');
            $table->decimal('interest_amount', 18, 2);
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('corebank_reference')->nullable();
            $table->enum('status', ['imported', 'validated', 'posted'])->default('imported');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_transactions');
    }
};
