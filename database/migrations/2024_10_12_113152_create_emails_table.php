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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->uuid('internal_reference');
            $table->string('message_id')->nullable();
            $table->string('to');
            $table->string('recipient_name');
            $table->string('subject');
            $table->string('status')->default('pending'); // pending, delivered, failed, complained
            $table->text('error_message')->nullable();
            $table->string('sender_email');
            $table->string('sender_name');
            $table->string('sender_domain');
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
