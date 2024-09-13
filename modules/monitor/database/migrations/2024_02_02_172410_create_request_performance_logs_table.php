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
        Schema::create('request_performance_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->tinyInteger('type');
            $table->unsignedInteger('duration')->index();
            $table->unsignedInteger('query_duration')->index()->nullable();
            $table->string('uri', 100)->index();
            $table->string('domain');
            $table->string('path');
            $table->string('ip', 16)->nullable();
            $table->foreignId('user_id')->nullable();
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_performance_logs');
    }
};
