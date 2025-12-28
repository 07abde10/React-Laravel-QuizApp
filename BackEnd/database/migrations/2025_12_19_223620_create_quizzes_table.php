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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professeur_id')->constrained('professeurs')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('code_quiz', 20)->unique();
            $table->string('titre', 200);
            $table->text('description')->nullable();
            $table->integer('duree');
            $table->dateTime('date_debut_disponibilite')->nullable();
            $table->dateTime('date_fin_disponibilite')->nullable();
            $table->boolean('afficher_correction')->default(true);
            $table->integer('nombre_tentatives_max')->default(1);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
