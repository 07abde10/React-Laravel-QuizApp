<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use App\Models\Professeur;
use App\Models\Module;
use App\Models\Groupe;
use App\Models\Etudiant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class QuizAppSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->seedUtilisateurs();
        $this->seedModules();
        $this->seedGroupes();
    }

    private function seedUtilisateurs(): void
    {
        $prof1 = Utilisateur::create([
            'nom' => 'Ahmed',
            'prenom' => 'Hassan',
            'email' => 'prof.ahmed@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Professeur',
        ]);

        Professeur::create([
            'user_id' => $prof1->id,
            'specialite' => 'Mathematics',
        ]);

        $prof2 = Utilisateur::create([
            'nom' => 'Mohamed',
            'prenom' => 'Karim',
            'email' => 'prof.karim@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Professeur',
        ]);

        Professeur::create([
            'user_id' => $prof2->id,
            'specialite' => 'Physics',
        ]);

        $etud1 = Utilisateur::create([
            'nom' => 'Fatima',
            'prenom' => 'Ali',
            'email' => 'etudiant1@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Etudiant',
        ]);

        Etudiant::create([
            'user_id' => $etud1->id,
            'numero_etudiant' => 'STU001',
            'niveau' => 'L1',
        ]);

        $etud2 = Utilisateur::create([
            'nom' => 'Zahra',
            'prenom' => 'Mohammed',
            'email' => 'etudiant2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Etudiant',
        ]);

        Etudiant::create([
            'user_id' => $etud2->id,
            'numero_etudiant' => 'STU002',
            'niveau' => 'L1',
        ]);

        $etud3 = Utilisateur::create([
            'nom' => 'Omar',
            'prenom' => 'Hassan',
            'email' => 'etudiant3@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Etudiant',
        ]);

        Etudiant::create([
            'user_id' => $etud3->id,
            'numero_etudiant' => 'STU003',
            'niveau' => 'L2',
        ]);

        Utilisateur::create([
            'nom' => 'Admin',
            'prenom' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Administrateur',
        ]);
    }

    private function seedModules(): void
    {
        Module::create([
            'nom_module' => 'Calculus I',
            'description' => 'Introduction to Differential Calculus',
        ]);

        Module::create([
            'nom_module' => 'Physics I',
            'description' => 'Classical Mechanics and Kinematics',
        ]);

        Module::create([
            'nom_module' => 'Chemistry I',
            'description' => 'General Chemistry',
        ]);

        Module::create([
            'nom_module' => 'English',
            'description' => 'English Language and Communication',
        ]);
    }

    private function seedGroupes(): void
    {
        Groupe::create([
            'nom_groupe' => 'Group A - Semester 1',
            'annee_academique' => '2024-2025',
            'effectif' => 30,
        ]);

        Groupe::create([
            'nom_groupe' => 'Group B - Semester 1',
            'annee_academique' => '2024-2025',
            'effectif' => 28,
        ]);

        Groupe::create([
            'nom_groupe' => 'Group C - Semester 2',
            'annee_academique' => '2024-2025',
            'effectif' => 32,
        ]);
    }
}
