<?php

namespace App\Http\Controllers;

use App\Models\Utilisateur;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Module;
use App\Models\Tentative;
use App\Models\Professeur;
use App\Models\Etudiant;
use App\Models\Groupe;
use App\Models\ChoixReponse;
use App\Models\ReponseEtudiant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function getUsers()
    {
        try {
            $users = Utilisateur::with(['professeur', 'etudiant'])->paginate(50);
            return response()->json([
                'success' => true,
                'data' => $users,
                'message' => 'Users retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve users: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = Utilisateur::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStudents()
    {
        try {
            $students = Utilisateur::where('role', 'Etudiant')
                ->with('etudiant')
                ->paginate(50);
            
            $transformedData = $students->getCollection()->map(function($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'numero_etudiant' => $user->etudiant->numero_etudiant ?? 'N/A',
                    'niveau' => $user->etudiant->niveau ?? 'N/A',
                    'created_at' => $user->created_at->format('Y-m-d'),
                ];
            });
            
            $students->setCollection($transformedData);
            
            return response()->json([
                'success' => true,
                'data' => $students,
                'message' => 'Students retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve students: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createStudent(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'email' => 'required|email|unique:utilisateurs',
                'password' => 'required|string|min:8',
                'numero_etudiant' => 'required|string|unique:etudiants',
                'niveau' => 'required|string',
            ]);

            $user = Utilisateur::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Etudiant'
            ]);

            Etudiant::create([
                'user_id' => $user->id,
                'numero_etudiant' => $validated['numero_etudiant'],
                'niveau' => $validated['niveau']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStudent(Request $request, $id)
    {
        try {
            $user = Utilisateur::where('role', 'Etudiant')->with('etudiant')->find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'email' => 'required|email|unique:utilisateurs,email,' . $id,
                'numero_etudiant' => 'required|string|unique:etudiants,numero_etudiant,' . $user->etudiant->id,
                'niveau' => 'required|string',
            ]);

            $user->update([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email']
            ]);

            $user->etudiant->update([
                'numero_etudiant' => $validated['numero_etudiant'],
                'niveau' => $validated['niveau']
            ]);

            return response()->json(['success' => true, 'message' => 'Student updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update student: ' . $e->getMessage()], 500);
        }
    }

    public function deleteStudent($id)
    {
        try {
            $user = Utilisateur::where('role', 'Etudiant')->find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'Student deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getProfessors()
    {
        try {
            $professors = Utilisateur::where('role', 'Professeur')
                ->with('professeur')
                ->paginate(50);
            
            $transformedData = $professors->getCollection()->map(function($user) {
                return [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'email' => $user->email,
                    'role' => $user->role,
                    'specialite' => $user->professeur->specialite ?? 'N/A',
                    'created_at' => $user->created_at->format('Y-m-d'),
                ];
            });
            
            $professors->setCollection($transformedData);
            
            return response()->json([
                'success' => true,
                'data' => $professors,
                'message' => 'Professors retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve professors: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createProfessor(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'email' => 'required|email|unique:utilisateurs',
                'password' => 'required|string|min:8',
                'specialite' => 'required|string',
            ]);

            $user = Utilisateur::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Professeur'
            ]);

            Professeur::create([
                'user_id' => $user->id,
                'specialite' => $validated['specialite']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Professor created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create professor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateProfessor(Request $request, $id)
    {
        try {
            $user = Utilisateur::where('role', 'Professeur')->with('professeur')->find($id);
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Professor not found'], 404);
            }

            $validated = $request->validate([
                'nom' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'email' => 'required|email|unique:utilisateurs,email,' . $id,
                'specialite' => 'required|string',
            ]);

            $user->update([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'email' => $validated['email']
            ]);

            $user->professeur->update([
                'specialite' => $validated['specialite']
            ]);

            return response()->json(['success' => true, 'message' => 'Professor updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update professor: ' . $e->getMessage()], 500);
        }
    }

    public function deleteProfessor($id)
    {
        try {
            $user = Utilisateur::where('role', 'Professeur')->find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Professor not found'
                ], 404);
            }

            $user->delete();
            return response()->json([
                'success' => true,
                'message' => 'Professor deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete professor: ' . $e->getMessage()
            ], 500);
        }
    }

    // Groups Management
    public function getGroups()
    {
        try {
            $groups = Groupe::withCount(['etudiants', 'quizzes'])->paginate(50);
            return response()->json([
                'success' => true,
                'data' => $groups,
                'message' => 'Groups retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve groups: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteGroup($id)
    {
        try {
            $group = Groupe::find($id);
            if (!$group) {
                return response()->json([
                    'success' => false,
                    'message' => 'Group not found'
                ], 404);
            }

            $group->delete();
            return response()->json([
                'success' => true,
                'message' => 'Group deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete group: ' . $e->getMessage()
            ], 500);
        }
    }

    // Answer Choices Management
    public function getChoices()
    {
        try {
            $choices = ChoixReponse::with(['question.quiz'])->paginate(50);
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

    public function deleteChoice($id)
    {
        try {
            $choice = ChoixReponse::find($id);
            if (!$choice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Choice not found'
                ], 404);
            }

            $choice->delete();
            return response()->json([
                'success' => true,
                'message' => 'Choice deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete choice: ' . $e->getMessage()
            ], 500);
        }
    }

    // Student Responses Management
    public function getResponses()
    {
        try {
            $responses = ReponseEtudiant::with(['tentative.etudiant.user', 'question.quiz', 'choix'])->paginate(50);
            return response()->json([
                'success' => true,
                'data' => $responses,
                'message' => 'Student responses retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve responses: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteResponse($id)
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

    // Existing methods...
    public function getQuizzes()
    {
        try {
            $quizzes = Quiz::with(['professeur.user', 'module'])->paginate(50);
            
            // Transform data to show readable information instead of IDs
            $transformedData = $quizzes->getCollection()->map(function($quiz) {
                return [
                    'id' => $quiz->id,
                    'code_quiz' => $quiz->code_quiz,
                    'titre' => $quiz->titre,
                    'description' => $quiz->description,
                    'professeur' => ($quiz->professeur && $quiz->professeur->user) ? 
                        $quiz->professeur->user->prenom . ' ' . $quiz->professeur->user->nom : 'N/A',
                    'module' => $quiz->module->nom_module ?? 'N/A',
                    'duree' => $quiz->duree . ' min',
                    'actif' => $quiz->actif,
                    'questions_count' => $quiz->questions()->count(),
                    'created_at' => $quiz->created_at->format('Y-m-d'),
                ];
            });
            
            $quizzes->setCollection($transformedData);
            
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

    public function deleteQuiz($id)
    {
        try {
            $quiz = Quiz::find($id);
            if (!$quiz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz not found'
                ], 404);
            }

            $quiz->delete();
            return response()->json([
                'success' => true,
                'message' => 'Quiz deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createQuiz(Request $request)
    {
        try {
            $validated = $request->validate([
                'titre' => 'required|string',
                'description' => 'nullable|string',
                'duree' => 'required|integer|min:1',
                'module_id' => 'required|exists:modules,id',
            ]);

            $quiz = Quiz::create([
                'titre' => $validated['titre'],
                'description' => $validated['description'],
                'duree' => $validated['duree'],
                'module_id' => $validated['module_id'],
                'professeur_id' => 1, // Default professor
                'code_quiz' => 'QUIZ_' . strtoupper(uniqid()),
                'actif' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create quiz: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateQuiz(Request $request, $id)
    {
        try {
            $quiz = Quiz::find($id);
            if (!$quiz) {
                return response()->json(['success' => false, 'message' => 'Quiz not found'], 404);
            }

            $validated = $request->validate([
                'actif' => 'required|boolean',
            ]);

            $quiz->update(['actif' => $validated['actif']]);

            return response()->json(['success' => true, 'message' => 'Quiz updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update quiz: ' . $e->getMessage()], 500);
        }
    }

    public function getQuestions()
    {
        try {
            $questions = Question::with(['quiz', 'choixReponses'])->paginate(50);
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

    public function deleteQuestion($id)
    {
        try {
            $question = Question::find($id);
            if (!$question) {
                return response()->json([
                    'success' => false,
                    'message' => 'Question not found'
                ], 404);
            }

            $question->delete();
            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete question: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getModules()
    {
        try {
            $modules = Module::withCount('quizzes')->paginate(50);
            
            // Transform data to show readable information
            $transformedData = $modules->getCollection()->map(function($module) {
                return [
                    'id' => $module->id,
                    'nom_module' => $module->nom_module,
                    'description' => $module->description ?? 'N/A',
                    'quizzes_count' => $module->quizzes_count,
                    'created_at' => $module->created_at->format('Y-m-d'),
                ];
            });
            
            $modules->setCollection($transformedData);
            
            return response()->json([
                'success' => true,
                'data' => $modules,
                'message' => 'Modules retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve modules: ' . $e->getMessage()
            ], 500);
        }
    }

    public function createModule(Request $request)
    {
        try {
            $validated = $request->validate([
                'nom_module' => 'required|string',
                'description' => 'nullable|string',
            ]);

            Module::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Module created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create module: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateModule(Request $request, $id)
    {
        try {
            $module = Module::find($id);
            if (!$module) {
                return response()->json(['success' => false, 'message' => 'Module not found'], 404);
            }

            $validated = $request->validate([
                'nom_module' => 'required|string',
                'description' => 'nullable|string',
            ]);

            $module->update($validated);

            return response()->json(['success' => true, 'message' => 'Module updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update module: ' . $e->getMessage()], 500);
        }
    }

    public function deleteModule($id)
    {
        try {
            $module = Module::find($id);
            if (!$module) {
                return response()->json([
                    'success' => false,
                    'message' => 'Module not found'
                ], 404);
            }

            $module->delete();
            return response()->json([
                'success' => true,
                'message' => 'Module deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete module: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAttempts()
    {
        try {
            $attempts = Tentative::with(['etudiant.user', 'quiz'])->paginate(50);
            return response()->json([
                'success' => true,
                'data' => $attempts,
                'message' => 'Attempts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve attempts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAttempt($id)
    {
        try {
            $attempt = Tentative::find($id);
            if (!$attempt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attempt not found'
                ], 404);
            }

            $attempt->delete();
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

    // Dashboard Stats
    public function getStats()
    {
        try {
            $stats = [
                'total_users' => Utilisateur::count(),
                'total_professors' => Utilisateur::where('role', 'Professeur')->count(),
                'total_students' => Utilisateur::where('role', 'Etudiant')->count(),
                'total_admins' => Utilisateur::where('role', 'Administrateur')->count(),
                'total_quizzes' => Quiz::count(),
                'total_questions' => Question::count(),
                'total_attempts' => Tentative::count(),
                'total_groups' => Groupe::count(),
                'total_choices' => ChoixReponse::count(),
                'total_responses' => ReponseEtudiant::count(),
                'total_modules' => Module::count(),
                'active_quizzes' => Quiz::where('actif', true)->count(),
                'completed_attempts' => Tentative::where('statut', 'termine')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Stats retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve stats: ' . $e->getMessage()
            ], 500);
        }
    }
}