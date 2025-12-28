<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Question::with(['quiz', 'choixReponses', 'reponseEtudiants']);

            if ($request->has('quiz_id')) {
                $query->where('quiz_id', $request->quiz_id);
            }

            if ($request->has('type_question')) {
                $query->where('type_question', $request->type_question);
            }

            $perPage = $request->get('per_page', 15);
            $questions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $questions,
                'message' => 'Questions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve questions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'enonce' => 'required|string',
                'type_question' => 'required|in:QCM3,QCM4',
                'points' => 'required|numeric|min:0',
            ]);

            $question = Question::create($validated);

            return response()->json([
                'success' => true,
                'data' => $question->load(['quiz', 'choixReponses']),
                'message' => 'Question created successfully'
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
                'message' => 'Failed to create question: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $question = Question::with([
                'quiz',
                'choixReponses',
                'reponseEtudiants'
            ])->find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $question,
                'message' => 'Question retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve question: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $question = Question::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found'
                ], 404);
            }

            $validated = $request->validate([
                'enonce' => 'sometimes|required|string',
                'type_question' => 'sometimes|required|in:QCM3,QCM4',
                'points' => 'sometimes|required|numeric|min:0',
            ]);

            $question->update($validated);

            return response()->json([
                'success' => true,
                'data' => $question->load(['quiz', 'choixReponses']),
                'message' => 'Question updated successfully'
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
                'message' => 'Failed to update question: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $question = Question::find($id);

            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found'
                ], 404);
            }

            $enonce = substr($question->enonce, 0, 50);
            $question->delete();

            return response()->json([
                'success' => true,
                'message' => "Question deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question: ' . $e->getMessage()
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

            $questions = Question::where('quiz_id', $quizId)
                ->with('choixReponses')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'quiz' => $quiz,
                    'questions' => $questions,
                    'total_points' => $questions->sum('points')
                ],
                'message' => 'Quiz questions retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve quiz questions: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkCreate(Request $request)
    {
        try {
            $validated = $request->validate([
                'quiz_id' => 'required|exists:quizzes,id',
                'questions' => 'required|array|min:1',
                'questions.*.enonce' => 'required|string',
                'questions.*.type_question' => 'required|in:QCM3,QCM4',
                'questions.*.points' => 'required|numeric|min:0',
            ]);

            $createdQuestions = [];
            foreach ($validated['questions'] as $questionData) {
                $questionData['quiz_id'] = $validated['quiz_id'];
                $createdQuestions[] = Question::create($questionData);
            }

            return response()->json([
                'success' => true,
                'data' => $createdQuestions,
                'message' => count($createdQuestions) . ' questions created successfully'
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
                'message' => 'Failed to create questions: ' . $e->getMessage()
            ], 500);
        }
    }
}
