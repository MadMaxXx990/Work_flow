<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id('approval_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('approver_user_id');
            $table->string('approval_status'); // Approved | Revision Requested
            $table->text('remarks')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('approval_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
