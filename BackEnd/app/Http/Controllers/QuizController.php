<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Professeur;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Quiz::query();

            if ($request->has('module_id')) {
                $query->where('module_id', $request->module_id);
            }

            if ($request->has('professeur_id')) {
                $query->where('professeur_id', $request->professeur_id);
            }

            if ($request->has('actif')) {
                $query->where('actif', $request->boolean('actif'));
            }

            if ($request->has('date_debut') && $request->has('date_fin')) {
                $query->whereBetween('date_debut_disponibilite', [
                    $request->date_debut,
                    $request->date_fin
                ]);
            }

            $query->with(['questions']);

            $perPage = $request->get('per_page', 15);
            $quizzes = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $quizzes,
                'message' => 'Quizzes retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get or create professor record for authenticated user
            if ($user && $user->role === 'Professeur') {
                $prof = $user->professeur;
                if (!$prof) {
                    // Create professor record if it doesn't exist
                    $prof = Professeur::create([
                        'user_id' => $user->id,
                        'specialite' => 'General'
                    ]);
                }
                $request->merge(['professeur_id' => $prof->id]);
            }

            // Validate input
            $validated = $request->validate([
                'professeur_id' => 'nullable|exists:professeurs,id',
                'module_id' => 'required|exists:modules,id',
                'titre' => 'required|string|max:255',
                'description' => 'nullable|string',
                'duree' => 'required|integer|min:1',
                'date_debut_disponibilite' => 'nullable|date',
                'date_fin_disponibilite' => 'nullable|date|after:date_debut_disponibilite',
                'afficher_correction' => 'nullable|boolean',
                'nombre_tentatives_max' => 'nullable|integer|min:1',
                'actif' => 'nullable|boolean',
            ]);

            // Ensure professeur_id is set
            if (!isset($validated['professeur_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only professors can create quizzes'
                ], 403);
            }

            // Generate unique quiz code
            $validated['code_quiz'] = 'QUIZ-' . Str::upper(Str::random(8));

            // Set defaults
            $validated['date_debut_disponibilite'] = $validated['date_debut_disponibilite'] ?? now();
            $validated['date_fin_disponibilite'] = $validated['date_fin_disponibilite'] ?? now()->addDays(30);
            $validated['afficher_correction'] = $validated['afficher_correction'] ?? false;
            $validated['nombre_tentatives_max'] = $validated['nombre_tentatives_max'] ?? 1;
            $validated['actif'] = $validated['actif'] ?? true;

            // Create quiz
            $quiz = Quiz::create($validated);

            return response()->json([
                'success' => true,
                'data' => $quiz->load(['professeur', 'module']),
                'message' => 'Quiz created successfully'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            \Log::info('Loading quiz with ID: ' . $id);
            
            $quiz = Quiz::with(['questions.choixReponses'])->find($id);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $quiz,
                'message' => 'Quiz retrieved successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in show method: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $quiz = Quiz::find($id);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            if ($request->user() && $request->user()->role === 'Professeur') {
                $prof = $request->user()->professeur;
                if (!$prof || $quiz->professeur_id !== $prof->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Forbidden: you can only edit your own quizzes'
                    ], 403);
                }
            }

            $validated = $request->validate([
                'titre' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'duree' => 'sometimes|required|integer|min:1',
                'date_debut_disponibilite' => 'sometimes|required|date',
                'date_fin_disponibilite' => 'sometimes|required|date',
                'afficher_correction' => 'nullable|boolean',
                'nombre_tentatives_max' => 'nullable|integer|min:1',
                'actif' => 'nullable|boolean',
                'module_id' => 'sometimes|required|exists:modules,id',
            ]);

            $quiz->update($validated);

            return response()->json([
                'success' => true,
                'data' => $quiz->load(['professeur', 'module']),
                'message' => 'Quiz updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $quiz = Quiz::find($id);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $quizTitle = $quiz->titre;

            // Delete quiz (cascading deletes will handle related records)
            $quiz->delete();

            return response()->json([
                'success' => true,
                'message' => "Quiz '{$quizTitle}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function statistics($id)
    {
        try {
            $quiz = Quiz::with(['tentatives' => function($query) {
                $query->where('statut', 'termine');
            }])->find($id);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $completedAttempts = $quiz->tentatives;
            $totalAttempts = $completedAttempts->count();
            $totalQuestions = $quiz->questions()->count();
            
            if ($totalAttempts > 0) {
                $scores = $completedAttempts->map(function($attempt) {
                    return $attempt->score_total > 0 ? 
                        round(($attempt->score_obtenu / $attempt->score_total) * 100, 2) : 0;
                });
                
                $averageScore = $scores->avg();
                $passedCount = $scores->filter(function($score) { return $score >= 50; })->count();
                $failedCount = $totalAttempts - $passedCount;
            } else {
                $averageScore = 0;
                $passedCount = 0;
                $failedCount = 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz_id' => $quiz->id,
                    'quiz_title' => $quiz->titre,
                    'total_attempts' => $totalAttempts,
                    'passed_count' => $passedCount,
                    'failed_count' => $failedCount,
                    'average_score' => round($averageScore, 2),
                    'total_questions' => $totalQuestions,
                    'active' => $quiz->actif,
                ],
                'message' => 'Quiz statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByGroup($groupId)
    {
        try {
            $quizzes = Quiz::whereHas('groupes', function ($query) use ($groupId) {
                $query->where('groupe_id', $groupId);
            })
            ->where('actif', true)
            ->where('date_debut_disponibilite', '<=', now())
            ->where('date_fin_disponibilite', '>=', now())
            ->with(['professeur', 'module'])
            ->get();

            return response()->json([
                'success' => true,
                'data' => $quizzes,
                'message' => 'Available quizzes retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quizzes: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByCode($code)
    {
        try {
            \Log::info('Looking for quiz with code: ' . $code);
            
            // Simple query first to debug
            $quiz = Quiz::where('code_quiz', $code)->first();
            
            \Log::info('Quiz found: ' . ($quiz ? 'YES' : 'NO'));

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found with code: ' . $code
                ], 404);
            }

            // Load relationships after finding the quiz
            $quiz->load(['professeur', 'module', 'questions.choixReponses']);

            return response()->json([
                'success' => true,
                'data' => $quiz,
                'message' => 'Quiz retrieved by code successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getByCode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz: ' . $e->getMessage()
            ], 500);
        }
    }
}
