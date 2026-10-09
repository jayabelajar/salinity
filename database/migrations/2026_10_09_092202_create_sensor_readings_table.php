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
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('event_id');
            $table->decimal('ec_raw', 8, 3);
            $table->decimal('temperature', 8, 3);
            $table->decimal('ec_corrected', 8, 3);
            $table->decimal('ec_rate', 8, 3)->default(0);
            $table->decimal('fuzzy_score', 8, 3);
            $table->string('risk_level')->index();
            $table->timestamp('measured_at')->index();
            $table->timestamp('received_at')->index();
            $table->boolean('is_backfill')->default(false);
            $table->timestamps();

            $table->unique(['device_id', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
