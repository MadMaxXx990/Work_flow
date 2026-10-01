<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('assigned_by_user_id');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_assignments');
    }
};
