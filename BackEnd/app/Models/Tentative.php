<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tentative extends Model
{ protected $fillable = [
        'etudiant_id',
        'quiz_id',
        'date_passage',
        'heure_debut',
        'heure_fin',
        'score_obtenu',
        'score_total',
        'statut',
    ];

    protected $casts = [
        'date_passage' => 'date',
        'score_obtenu' => 'decimal:2',
        'score_total' => 'decimal:2',
    ];

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function reponseEtudiants()
    {
        return $this->hasMany(ReponseEtudiant::class);
    }
}
