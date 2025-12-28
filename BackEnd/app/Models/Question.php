<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'quiz_id',
        'enonce',
        'type_question',
        'points',
        // 'ordre',
    ];

    protected $casts = [
        'points' => 'decimal:2',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function choixReponses()
    {
        return $this->hasMany(ChoixReponse::class);
    }

    public function reponseEtudiants()
    {
        return $this->hasMany(ReponseEtudiant::class);
    }
}
