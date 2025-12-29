<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Utilisateur extends Authenticatable
{
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    public function professeur()
    {
        return $this->hasOne(Professeur::class, 'user_id');
    }

    public function etudiant()
    {
        return $this->hasOne(Etudiant::class, 'user_id');
    }
}
