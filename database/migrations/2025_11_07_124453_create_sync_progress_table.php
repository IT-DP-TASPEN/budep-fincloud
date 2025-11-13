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
        Schema::create('sync_progress', function (Blueprint $table) {
            $table->id();
            $table->string('process_name')->default('deposito_sync');
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->enum('status', ['idle', 'running', 'completed', 'failed'])->default('idle');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_progress');
    }
};
