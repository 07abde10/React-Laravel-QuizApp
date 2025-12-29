<?php

namespace App\Http\Controllers;

use App\Models\ChoixReponse;
use App\Models\Question;
use Illuminate\Http\Request;

class ChoixReponseController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ChoixReponse::with('question');

            if ($request->has('question_id')) {
                $query->where('question_id', $request->question_id);
            }

            if ($request->has('est_correct')) {
                $query->where('est_correct', $request->boolean('est_correct'));
            }

            $perPage = $request->get('per_page', 50);
            $choices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $choices,
                'message' => 'Answer choices retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve choices: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:questions,id',
                'texte_choix' => 'required|string|max:255',
                'est_correct' => 'required|boolean',
            ]);

            $choix = ChoixReponse::create($validated);

            return response()->json([
                'success' => true,
                'data' => $choix->load('question'),
                'message' => 'Answer choice created successfully'
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
                'message' => 'Failed to create answer choice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $choix = ChoixReponse::with(['question', 'reponseEtudiants'])->find($id);

            if (!$choix) {
                return response()->json([
                    'success' => false,
                    'message' => 'Answer choice not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $choix,
                'message' => 'Answer choice retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve answer choice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $choix = ChoixReponse::find($id);

            if (!$choix) {
                return response()->json([
                    'success' => false,
                    'message' => 'Answer choice not found'
                ], 404);
            }

            $validated = $request->validate([
                'texte_choix' => 'sometimes|required|string|max:255',
                'est_correct' => 'sometimes|required|boolean',
            ]);

            $choix->update($validated);

            return response()->json([
                'success' => true,
                'data' => $choix->load('question'),
                'message' => 'Answer choice updated successfully'
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
                'message' => 'Failed to update answer choice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $choix = ChoixReponse::find($id);

            if (!$choix) {
                return response()->json([
                    'success' => false,
                    'message' => 'Answer choice not found'
                ], 404);
            }

            $choix->delete();

            return response()->json([
                'success' => true,
                'message' => 'Answer choice deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete answer choice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getByQuestion($questionId)
    {
        try {
            $question = Question::find($questionId);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found'
                ], 404);
            }

            $choices = ChoixReponse::where('question_id', $questionId)
                ->orderBy('id', 'asc')
                ->get();

            $correctChoice = $choices->where('est_correct', true)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'question' => $question,
                    'choices' => $choices,
                    'total_choices' => $choices->count(),
                    'correct_choice_id' => $correctChoice?->id,
                ],
                'message' => 'Question choices retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve choices: ' . $e->getMessage()
            ], 500);
        }
    }
}
