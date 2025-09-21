<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operations', function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->foreignId('user_id')->constrained()->onDelete('cascade');
            $blueprint->enum('type', ['deposit', 'withdrawal']);
            $blueprint->decimal('amount', 15, 2);
            $blueprint->text('description')->nullable();
            $blueprint->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $blueprint->timestamps();

            $blueprint->index(['user_id', 'created_at']);
            $blueprint->index('status');
            $blueprint->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
