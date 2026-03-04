<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cleaning_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('condominium_id')
                ->constrained('condominiums')
                ->cascadeOnDelete();

            $table->foreignId('cleaning_area_id')
                ->constrained('cleaning_areas')
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->enum('frequency_type', ['daily', 'weekly', 'monthly', 'custom']);
            $table->unsignedInteger('repeat_interval')->default(1);
            $table->json('days_of_week')->nullable();

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('condominium_id');
            $table->index('cleaning_area_id');
            $table->index(['condominium_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cleaning_schedules');
    }
};
