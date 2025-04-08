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
        Schema::create('movies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->unique();
            $table->string('original_title')->unique();
            $table->text('overview')->nullable();
            $table->string('original_language')->nullable();
            $table->integer('score')->nullable();
            $table->date('release_date')->nullable();
            $table->bigInteger('budget')->nullable();
            $table->bigInteger('revenue')->nullable();
            $table->smallInteger('runtime')->nullable();
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
            $table->string('tagline')->nullable();
            $table->string('poster_id', 15)->nullable();
            $table->string('backdrop_id', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
