<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type', 20);                   // email | sms | push
            $table->string('recipient', 255);
            $table->string('subject', 255)->nullable();
            $table->text('body');
            $table->string('status', 20)->default('pending'); // pending | sent | failed
            $table->jsonb('metadata')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->datetimes();

            $table->index('type');
            $table->index('status');
            $table->index('recipient');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
