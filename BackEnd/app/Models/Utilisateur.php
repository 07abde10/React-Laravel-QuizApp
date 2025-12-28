<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens;
    
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
