<?php

namespace App\Http\Controllers;

use App\Models\Tentative;
use App\Models\Etudiant;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TentativeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Tentative::with(['etudiant', 'quiz', 'reponseEtudiants']);

            if ($request->has('etudiant_id')) {
                $query->where('etudiant_id', $request->etudiant_id);
            }

            if ($request->has('quiz_id')) {
                $query->where('quiz_id', $request->quiz_id);
            }

            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            $perPage = $request->get('per_page', 15);
            $tentatives = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $tentatives,
                'message' => 'Attempts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attempts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'etudiant_id' => 'nullable|exists:etudiants,id',
                'quiz_id' => 'required|exists:quizzes,id',
                'date_passage' => 'nullable|date',
            ]);

            if (!isset($validated['etudiant_id'])) {
                $firstStudent = Etudiant::first();
                if ($firstStudent) {
                    $validated['etudiant_id'] = $firstStudent->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No students found in the system'
                    ], 400);
                }
            }

            $quiz = Quiz::find($validated['quiz_id']);

            $validated['date_passage'] = $validated['date_passage'] ?? now();
            $validated['heure_debut'] = now();
            $validated['statut'] = 'en_cours';
            $validated['score_obtenu'] = 0;
            $validated['score_total'] = $quiz->questions()->sum('points') ?: 100;

            $tentative = Tentative::create($validated);

            return response()->json([
                'success' => true,
                'data' => $tentative->load(['etudiant', 'quiz']),
                'message' => 'Quiz attempt started successfully'
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
                'message' => 'Failed to start quiz attempt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $tentative = Tentative::with([
                'etudiant',
                'quiz',
                'reponseEtudiants.question'
            ])->find($id);

            if (!$tentative) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found'
                ], 404);
            }

            $percentage = 0;
            if ($tentative->score_total > 0) {
                $percentage = round(($tentative->score_obtenu / $tentative->score_total) * 100, 2);
            }
            
            $tentativeArray = $tentative->toArray();
            $tentativeArray['score'] = $percentage;

            return response()->json([
                'success' => true,
                'data' => $tentativeArray,
                'message' => 'Attempt retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attempt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $tentative = Tentative::find($id);

            if (!$tentative) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found'
                ], 404);
            }

            $validated = $request->validate([
                'statut' => 'sometimes|required|in:en_cours,termine,abandonne',
                'score_obtenu' => 'sometimes|required|numeric|min:0',
            ]);

            if ($request->has('statut') && $request->statut === 'termine') {
                $validated['heure_fin'] = now();
            }

            $tentative->update($validated);

            return response()->json([
                'success' => true,
                'data' => $tentative->load(['etudiant', 'quiz']),
                'message' => 'Attempt updated successfully'
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
                'message' => 'Failed to update attempt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $tentative = Tentative::find($id);

            if (!$tentative) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found'
                ], 404);
            }

            $tentative->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attempt deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attempt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function finish($id)
    {
        try {
            $tentative = Tentative::with('reponseEtudiants.question', 'reponseEtudiants.choix')->find($id);

            if (!$tentative) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found'
                ], 404);
            }

            if ($tentative->statut === 'termine') {
                return response()->json([
                    'success' => false,
                    'message' => 'This attempt is already finished'
                ], 400);
            }

            // Calculate score
            $score = 0;
            foreach ($tentative->reponseEtudiants as $response) {
                if ($response->choix && $response->choix->est_correct) {
                    $score += $response->question->points;
                }
            }

            $totalPoints = $tentative->score_total ?: 1; // Avoid division by zero
            $percentage = ($score / $totalPoints) * 100;

            $tentative->update([
                'statut' => 'termine',
                'heure_fin' => now(),
                'score_obtenu' => $score
            ]);

            return response()->json([
                'success' => true,
                'data' => $tentative->load(['etudiant', 'quiz']),
                'message' => 'Quiz attempt finished successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to finish attempt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByStudent($etudiantId)
    {
        try {
            $etudiant = Etudiant::find($etudiantId);

            if (!$etudiant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            $tentatives = Tentative::where('etudiant_id', $etudiantId)
                ->with(['quiz', 'reponseEtudiants'])
                ->orderBy('date_passage', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $tentatives,
                'message' => 'Student attempts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve student attempts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByQuiz($quizId)
    {
        try {
            $quiz = Quiz::find($quizId);

            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $tentatives = Tentative::where('quiz_id', $quizId)
                ->with(['etudiant', 'reponseEtudiants'])
                ->orderBy('date_passage', 'desc')
                ->get();

            $stats = [
                'total_attempts' => $tentatives->count(),
                'completed' => $tentatives->where('statut', 'termine')->count(),
                'in_progress' => $tentatives->where('statut', 'en_cours')->count(),
                'abandoned' => $tentatives->where('statut', 'abandonne')->count(),
                'average_score' => $tentatives->where('statut', 'termine')->avg('score_obtenu'),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz' => $quiz,
                    'attempts' => $tentatives,
                    'statistics' => $stats,
                ],
                'message' => 'Quiz attempts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz attempts: ' . $e->getMessage()
            ], 500);
        }
    }
}
