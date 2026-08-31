<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users');
            $table->foreignId('reviewee_id')->constrained('users');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['task_id', 'reviewer_id']);
            $table->index('reviewer_id');
            $table->index('reviewee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
