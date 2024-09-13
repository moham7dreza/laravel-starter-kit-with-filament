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
            $table->tinyInteger('tracking_type')->nullable()->comment('for tracking specific metrics like some gateways');
            $table->tinyInteger('requested')->default(0)->comment('the api was called then middleware fills it');
            $table->tinyInteger('terminated')->default(0)->comment('response returned then middleware updates it');
//            $table->nullableMorphs('metricable', indexName: 'health_metrics_metricable_index')->comment('for tracking specific entities like some reservations');
            $table->smallInteger('status_code')->nullable();
            $table->string('meta')->nullable();
            $table->index(['type', 'created_at', 'tracking_type']);
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
