<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_updates', function (Blueprint $table) {
            $table->id('update_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('employee_id');
            $table->text('update_message');
            $table->integer('progress_percent')->default(0);
            $table->timestamp('update_date')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_updates');
    }
};
