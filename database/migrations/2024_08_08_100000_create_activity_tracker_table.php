<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Get the migration connection name.
     */
    public function getConnection(): ?string
    {
        return config('activity-tracker.storage.database.connection');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection($this->getConnection());

        $schema->create('activity_tracker_visitor_ips', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('ip_address', 100)->nullable();
            $table->string('timezone', 100)->nullable();
            $table->string('country_code', 10)->nullable();
            $table->json('content')->nullable();
        });

        $schema->create('activity_tracker_visitor_requests', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->uuid('visitor_ip_uuid');
            $table->string('type', 10)->nullable();
            $table->json('content')->nullable();
            $table->dateTime('created_at')->nullable();

            $table->foreign('visitor_ip_uuid')
                  ->references('uuid')
                  ->on('activity_tracker_visitor_ips')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection($this->getConnection());

        $schema->dropIfExists('activity_tracker_visitor_ips');
        $schema->dropIfExists('activity_tracker_visitor_requests');
    }
};
