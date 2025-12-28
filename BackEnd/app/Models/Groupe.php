<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Groupe extends Model
{
    protected $table = 'groupes';
    
    protected $fillable = [
        'nom_groupe',
        'annee_academique',
        'effectif',
    ];

    public function etudiants()
    {
        return $this->belongsToMany(Etudiant::class, 'appartenir', 'groupe_id', 'etudiant_id')
            ->withTimestamps()
            ->withPivot('date_inscription');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'inscrire', 'groupe_id', 'module_id');
    }

    public function quizzes()
    {
        return $this->belongsToMany(Quiz::class, 'destiner', 'groupe_id', 'quiz_id');
    }
}
