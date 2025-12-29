<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Professeur;
use App\Models\Etudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'email' => 'required|email|max:150|unique:utilisateurs',
                'password' => 'required|string|min:8|confirmed',
                'role' => 'required|in:Professeur,Etudiant,Administrateur',
            ]);

            $validated['password'] = Hash::make($validated['password']);

            $user = Utilisateur::create($validated);

            // Create Professeur or Etudiant record based on role
            if ($validated['role'] === 'Professeur') {
                Professeur::create([
                    'user_id' => $user->id,
                    'specialite' => 'General',
                ]);
            } elseif ($validated['role'] === 'Etudiant') {
                Etudiant::create([
                    'user_id' => $user->id,
                    'numero_etudiant' => 'STU' . $user->id,
                    'niveau' => 'L1',
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
                'message' => 'User registered successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = Utilisateur::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Load relationships
            if ($user->role === 'Professeur') {
                $user->load('professeur');
            } elseif ($user->role === 'Etudiant') {
                $user->load('etudiant');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                ],
                'message' => 'Login successful'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }
}
