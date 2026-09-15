<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->integer('batch_timestamp');
            $table->integer('ticket_count')->default(0);
            $table->string('status')->default('generating');
            $table->string('zip_path')->nullable();
            $table->timestamps();

            $table->unique(['ticket_type_id', 'batch_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
