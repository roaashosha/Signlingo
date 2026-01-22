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
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender',['male','female'])->nullable();
            $table->enum('lang',['ar','en']);
            $table->string('email');
            $table->string('phone')->unique()->nullable();
            $table->enum('type',['admin','user'])->default('user');
            $table->string('img',200)->nullable();
            $table->enum('mode',['l','a']);
            $table->string('address')->nullable();
            $table->enum("blood_type",['A+,A-,B+,B-,AB+,AB-,O+,O-'])->nullable();
            $table->integer('age')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_verified')->default(0);
            $table->boolean('agreement')->default0(0);
            $table->rememberToken();
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
