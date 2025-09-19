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
            $blueprint->enum('type', ['credit', 'debit']);
            $blueprint->decimal('amount', 15, 2);
            $blueprint->string('description')->nullable();
            $blueprint->enum('status', ['success', 'failed'])->default('success');
            $blueprint->string('fail_reason')->nullable();
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};
