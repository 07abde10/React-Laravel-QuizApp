<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'code_module',
        'nom_module',
        'description',
        // 'semestre',
    ];

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function professeurs()
    {
        return $this->belongsToMany(Professeur::class, 'enseigner', 'module_id', 'professeur_id');
    }

    public function groupes()
    {
        return $this->belongsToMany(Groupe::class, 'inscrire', 'module_id', 'groupe_id');
    }
}
