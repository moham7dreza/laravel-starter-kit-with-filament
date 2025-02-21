<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('health_metric_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->tinyInteger('type'); // MetricTypeEnum
            $table->smallInteger('status_code')->nullable();
            $table->string('meta')->nullable();
            $table->json('data')->nullable();
            $table->index(['type', 'tracking_type']);
            $table->index(['type', 'meta']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_metric_logs');
    }
};
