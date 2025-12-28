<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';
    
    protected $fillable = [
        'professeur_id',
        'module_id',
        'code_quiz',
        'titre',
        'description',
        'duree',
        'date_debut_disponibilite',
        'date_fin_disponibilite',
        'afficher_correction',
        'nombre_tentatives_max',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'afficher_correction' => 'boolean',
        'date_debut_disponibilite' => 'datetime',
        'date_fin_disponibilite' => 'datetime',
    ];

    public function professeur()
    {
        return $this->belongsTo(Professeur::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function groupes()
    {
        return $this->belongsToMany(Groupe::class, 'destiner', 'quiz_id', 'groupe_id');
    }

    public function tentatives()
    {
        return $this->hasMany(Tentative::class);
    }
}
