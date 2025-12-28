<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    protected $table = 'etudiants';
    
    protected $fillable = [
        'user_id',
        'numero_etudiant',
        'niveau',
    ];

    public function user()
    {
        return $this->belongsTo(Utilisateur::class, 'user_id');
    }

    public function groupes()
    {
        return $this->belongsToMany(Groupe::class, 'appartenir', 'etudiant_id', 'groupe_id')
            ->withTimestamps()
            ->withPivot('date_inscription');
    }

    public function tentatives()
    {
        return $this->hasMany(Tentative::class);
    }
}
