<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professeur extends Model
{
    protected $table = 'professeurs';
    
    protected $fillable = [
        'user_id',
        'specialite',
    ];

    public function user()
    {
        return $this->belongsTo(Utilisateur::class, 'user_id');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'enseigner', 'professeur_id', 'module_id');
    }
}
