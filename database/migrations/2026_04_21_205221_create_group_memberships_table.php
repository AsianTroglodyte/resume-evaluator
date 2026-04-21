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
        Schema::create('group_memberships', function (Blueprint $table) {
            $table->id("group_membership_id");
            $table->foreignId("group_id")->constrained()->cascadeOnDelete();
            $table->foreignId("user_id")->constrained();
            $table->string("role_in_group");
            $table->string("status");
            $table->foreignID("added_by_user_id")->constrained();
            $table->foreignId("removed_by_user_id")->nullable()->constrained();
            $table->timestamp("joined_at");
            $table->timestamp("removed_at")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_memberships');
    }
};
