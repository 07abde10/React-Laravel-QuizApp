<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChoixReponse extends Model
{
    protected $table = 'choix_reponses';
    
    protected $fillable = [
        'question_id',
        'texte_choix',
        'est_correct',
        // 'ordre',
    ];

    protected $casts = [
        'est_correct' => 'boolean',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function reponseEtudiants()
    {
        return $this->hasMany(ReponseEtudiant::class, 'choix_id');
    }
}
