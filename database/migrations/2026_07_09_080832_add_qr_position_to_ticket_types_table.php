<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->decimal('qr_x', 8, 2)->nullable()->comment('QR position X in mm');
            $table->decimal('qr_y', 8, 2)->nullable()->comment('QR position Y in mm');
            $table->decimal('qr_size', 8, 2)->nullable()->default(30)->comment('QR size in mm');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropColumn(['qr_x', 'qr_y', 'qr_size']);
        });
    }
};
