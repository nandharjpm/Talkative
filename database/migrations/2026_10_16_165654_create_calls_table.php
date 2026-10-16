<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('caller_id');
            $table->unsignedBigInteger('receiver_id');

            $table->enum('type', [
                'audio',
                'video'
            ]);

            $table->enum('status', [
                'ringing',
                'accepted',
                'rejected',
                'missed',
                'ended',
                'cancelled'
            ])->default('ringing');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
            $table->index(['receiver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};