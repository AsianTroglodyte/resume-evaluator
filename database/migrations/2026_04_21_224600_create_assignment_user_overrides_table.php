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
        Schema::create('assignment_user_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments');
            $table->foreignId('user_id')->constrained('users');
            $table->string('due_at_override')->nullable();
            $table->boolean('is_exempt')->default(true);
            $table->boolean('allow_resubmission_override')->nullable();
            // $table->text
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_user_overrides');
    }
};
