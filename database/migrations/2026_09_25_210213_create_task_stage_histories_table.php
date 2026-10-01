<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_stage_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->unsignedBigInteger('old_stage_id')->nullable();
            $table->unsignedBigInteger('new_stage_id');
            $table->text('remarks')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_stage_history');
    }
};
