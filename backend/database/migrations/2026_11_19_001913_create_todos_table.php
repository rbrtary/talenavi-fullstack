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
        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('assignee')->nullable()->index();
            $table->date('due_date')->index();
            $table->decimal('time_tracked', 10, 2)->default(0);
            $table->enum('status', ['pending', 'open', 'in_progress', 'completed'])->default('pending')->index();
            $table->enum('priority', ['low', 'medium', 'high'])->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};