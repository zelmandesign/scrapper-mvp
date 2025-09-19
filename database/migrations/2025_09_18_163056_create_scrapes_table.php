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
        Schema::create('scrapes', function (Blueprint $table) {
            $table->id();
            $table->string('username')->index();
            $table->string('status')->default('queued');
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('webhook_url')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scrapes');
    }
};
