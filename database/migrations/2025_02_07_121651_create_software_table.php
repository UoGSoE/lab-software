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
        Schema::create('software', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('os')->nullable();
            $table->string('building')->nullable();
            $table->string('lab')->nullable();
            $table->text('config')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_new')->default(false);
            $table->boolean('is_free')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('academic_session')->constrained('academic_sessions');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('software');
    }
};
