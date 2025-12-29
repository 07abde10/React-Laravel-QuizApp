<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TentativeController;
use App\Http\Controllers\ReponseEtudiantController;
use App\Http\Controllers\ChoixReponseController;
use App\Http\Controllers\AdminController;

Route::get('/test', function () {
    return response()->json(['message' => 'TESSSST Hello World']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::get('/academic-levels', function() {
    return response()->json([
        'success' => true,
        'data' => \App\Models\Etudiant::select('niveau')->distinct()->whereNotNull('niveau')->pluck('niveau')
    ]);
});
Route::get('/groups', function() {
    return response()->json([
        'success' => true,
        'data' => \App\Models\Groupe::all()
    ]);
});
Route::get('/specializations', function() {
    return response()->json([
        'success' => true,
        'data' => \App\Models\Professeur::select('specialite')->distinct()->whereNotNull('specialite')->pluck('specialite')
    ]);
});

Route::get('/quizzes/code/{code}', [QuizController::class, 'getByCode']);
Route::get('/quizzes/{id}', [QuizController::class, 'show']);
Route::get('/modules', function() {
    return response()->json([
        'success' => true,
        'data' => \App\Models\Module::all()
    ]);
});
Route::get('/debug/quizzes', function() {
    return response()->json(\App\Models\Quiz::select('id', 'code_quiz', 'titre')->get());
});

Route::prefix('quizzes')->group(function () {
    Route::get('/', [QuizController::class, 'index']);
    Route::post('/', [QuizController::class, 'store']);
    Route::put('/{id}', [QuizController::class, 'update']);
    Route::delete('/{id}', [QuizController::class, 'destroy']);
    Route::get('/{id}/statistics', [QuizController::class, 'statistics']);
});

Route::prefix('questions')->group(function () {
    Route::get('/', [QuestionController::class, 'index']);
    Route::post('/', [QuestionController::class, 'store']);
    Route::post('/bulk', [QuestionController::class, 'bulkCreate']);
    Route::get('/{id}', [QuestionController::class, 'show']);
    Route::put('/{id}', [QuestionController::class, 'update']);
    Route::delete('/{id}', [QuestionController::class, 'destroy']);
    Route::get('/quiz/{quizId}', [QuestionController::class, 'getByQuiz']);
});

Route::prefix('attempts')->group(function () {
    Route::get('/', [TentativeController::class, 'index']);
    Route::post('/', [TentativeController::class, 'store']);
    Route::get('/{id}', [TentativeController::class, 'show']);
    Route::put('/{id}', [TentativeController::class, 'update']);
    Route::delete('/{id}', [TentativeController::class, 'destroy']);
    Route::post('/{id}/finish', [TentativeController::class, 'finish']);
    Route::get('/student/{etudiantId}', [TentativeController::class, 'getByStudent']);
    Route::get('/quiz/{quizId}', [TentativeController::class, 'getByQuiz']);
});

Route::prefix('responses')->group(function () {
    Route::get('/', [ReponseEtudiantController::class, 'index']);
    Route::post('/', [ReponseEtudiantController::class, 'store']);
    Route::post('/bulk', [ReponseEtudiantController::class, 'bulkSubmit']);
    Route::get('/{id}', [ReponseEtudiantController::class, 'show']);
    Route::put('/{id}', [ReponseEtudiantController::class, 'update']);
    Route::delete('/{id}', [ReponseEtudiantController::class, 'destroy']);
    Route::get('/attempt/{tentativeId}', [ReponseEtudiantController::class, 'getByAttempt']);
});

Route::prefix('choices')->group(function () {
    Route::get('/', [ChoixReponseController::class, 'index']);
    Route::post('/', [ChoixReponseController::class, 'store']);
    Route::post('/bulk', [ChoixReponseController::class, 'bulkCreate']);
    Route::get('/{id}', [ChoixReponseController::class, 'show']);
    Route::put('/{id}', [ChoixReponseController::class, 'update']);
    Route::delete('/{id}', [ChoixReponseController::class, 'destroy']);
    Route::get('/question/{questionId}', [ChoixReponseController::class, 'getByQuestion']);
});

Route::prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'getStats']);
    
    Route::get('/students', [AdminController::class, 'getStudents']);
    Route::post('/students', [AdminController::class, 'createStudent']);
    Route::put('/students/{id}', [AdminController::class, 'updateStudent']);
    Route::delete('/students/{id}', [AdminController::class, 'deleteStudent']);
    
    Route::get('/professors', [AdminController::class, 'getProfessors']);
    Route::post('/professors', [AdminController::class, 'createProfessor']);
    Route::put('/professors/{id}', [AdminController::class, 'updateProfessor']);
    Route::delete('/professors/{id}', [AdminController::class, 'deleteProfessor']);
    
    Route::get('/modules', [AdminController::class, 'getModules']);
    Route::post('/modules', [AdminController::class, 'createModule']);
    Route::put('/modules/{id}', [AdminController::class, 'updateModule']);
    Route::delete('/modules/{id}', [AdminController::class, 'deleteModule']);
    
    Route::get('/quizzes', [AdminController::class, 'getQuizzes']);
    Route::put('/quizzes/{id}', [AdminController::class, 'updateQuiz']);
    Route::delete('/quizzes/{id}', [AdminController::class, 'deleteQuiz']);
});