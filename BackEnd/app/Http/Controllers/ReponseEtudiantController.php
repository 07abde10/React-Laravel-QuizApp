<?php

namespace App\Http\Controllers;

use App\Models\ReponseEtudiant;
use App\Models\Tentative;
use App\Models\Question;
use App\Models\ChoixReponse;
use Illuminate\Http\Request;

class ReponseEtudiantController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ReponseEtudiant::with(['tentative', 'question', 'choix']);

            if ($request->has('tentative_id')) {
                $query->where('tentative_id', $request->tentative_id);
            }

            if ($request->has('question_id')) {
                $query->where('question_id', $request->question_id);
            }

            if ($request->has('est_correct')) {
                $query->where('est_correct', $request->boolean('est_correct'));
            }

            $perPage = $request->get('per_page', 50);
            $responses = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $responses,
                'message' => 'Responses retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve responses: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'tentative_id' => 'required|exists:tentatives,id',
                'question_id' => 'required|exists:questions,id',
                'choix_id' => 'required|exists:choix_reponses,id',
            ]);

            $tentative = Tentative::find($validated['tentative_id']);
            $question = Question::find($validated['question_id']);
            $choix = ChoixReponse::find($validated['choix_id']);

            if ($question->id !== $choix->question_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected answer choice does not belong to this question'
                ], 422);
            }

            $validated['est_correct'] = $choix->est_correct;

            $response = ReponseEtudiant::create($validated);

            return response()->json([
                'success' => true,
                'data' => $response->load(['tentative', 'question', 'choix']),
                'message' => 'Response submitted successfully'
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
                'message' => 'Failed to submit response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $response = ReponseEtudiant::with(['tentative', 'question', 'choix'])->find($id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Response not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Response retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $response = ReponseEtudiant::find($id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Response not found'
                ], 404);
            }

            $validated = $request->validate([
                'choix_id' => 'sometimes|required|exists:choix_reponses,id',
            ]);

            if ($request->has('choix_id')) {
                $choix = ChoixReponse::find($validated['choix_id']);
                
                if ($response->question_id !== $choix->question_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The selected answer choice does not belong to this question'
                    ], 422);
                }

                $validated['est_correct'] = $choix->est_correct;
            }

            $response->update($validated);

            return response()->json([
                'success' => true,
                'data' => $response->load(['tentative', 'question', 'choix']),
                'message' => 'Response updated successfully'
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
                'message' => 'Failed to update response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $response = ReponseEtudiant::find($id);

            if (!$response) {
                return response()->json([
                    'success' => false,
                    'message' => 'Response not found'
                ], 404);
            }

            $response->delete();

            return response()->json([
                'success' => true,
                'message' => 'Response deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete response: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByAttempt($tentativeId)
    {
        try {
            $tentative = Tentative::find($tentativeId);

            if (!$tentative) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found'
                ], 404);
            }

            $responses = ReponseEtudiant::where('tentative_id', $tentativeId)
                ->with(['question', 'choix'])
                ->get();

            $correctCount = $responses->where('est_correct', true)->count();
            $totalCount = $responses->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'tentative_id' => $tentativeId,
                    'responses' => $responses,
                    'correct_answers' => $correctCount,
                    'total_questions' => $totalCount,
                    'accuracy' => $totalCount > 0 ? round(($correctCount / $totalCount) * 100, 2) : 0,
                ],
                'message' => 'Attempt responses retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve responses: ' . $e->getMessage()
            ], 500);
        }
    }
}
