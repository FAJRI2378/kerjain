<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('category_id')->constrained('job_categories');
            $table->foreignId('worker_id')->nullable()->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('budget');
            $table->string('location');
            $table->string('status')->default('pending')->index();
            $table->string('proof_url')->nullable();
            $table->string('deadline')->nullable();
            $table->timestamps();

            $table->index('owner_id');
            $table->index('category_id');
            $table->index('worker_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
