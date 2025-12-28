<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReponseEtudiant extends Model
{
    protected $table = 'reponse_etudiants';
    
    protected $fillable = [
        'tentative_id',
        'question_id',
        'choix_id',
        'est_correct',
    ];

    protected $casts = [
        'est_correct' => 'boolean',
    ];

    public function tentative()
    {
        return $this->belongsTo(Tentative::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function choix()
    {
        return $this->belongsTo(ChoixReponse::class, 'choix_id');
    }
}
