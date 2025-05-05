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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('deleted_last_movie_watchlist')->default(false);
            $table->enum('config_scorer', ['starts', 'slider'])->default('starts');
            $table->tinyInteger('maximum_star_rating')->default(5);
            $table->tinyInteger('maximum_slider_rating')->default(10);
            $table->enum('vote_type', ['simple', 'advanced'])->default('simple');
            $table->enum('status', ['normal','blocked'])->default('normal');
            $table->tinyInteger('action_count')->default(0);
            $table->uuid('avatar_id')->nullable();
            $table->timestamp('last_action_at')->nullable();
            $table->foreign('avatar_id')->references('id')->on('avatars')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
