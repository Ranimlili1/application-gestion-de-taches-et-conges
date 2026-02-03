<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OuvrierController;
use App\Models\User;
use App\Models\Task;
use App\Models\Conge;
use App\Http\Controllers\PasswordResetController;
// ================= PUBLIC (sans authentification) =================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/password/forgot', [App\Http\Controllers\PasswordResetController::class, 'forgot']);
Route::post('/password/reset', [App\Http\Controllers\PasswordResetController::class, 'reset']);
// routes/api.php
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

// ================= AUTHENTIFIÉ (auth:sanctum) =================
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/clear-all', [NotificationController::class, 'clearAll']);
    Route::get('/profil', [AuthController::class, 'getProfil']);
    Route::put('/profil', [AuthController::class, 'updateProfil']);
    // ================= ADMIN UNIQUEMENT =================
    Route::middleware('admin')->group(function () {

        Route::get('/admin/dashboard', function () {
            return response()->json(['message' => 'Bienvenue Admin']);
        });

        // 👥 OUVRIERS
        Route::prefix('ouvriers')->group(function () {
            Route::get('/', [OuvrierController::class, 'index']);           
            Route::put('/{id}', [OuvrierController::class, 'update']);      
            Route::delete('/{id}', [OuvrierController::class, 'destroy']);  
        });
    
        // ✅ TÂCHES (ADMIN)
        // ADMIN : TÂCHES
    Route::prefix('tasks')->group(function () {
     Route::get('/', [TaskController::class, 'index']); // GET /api/tasks
    Route::get('/ouvrier/{userId}', [TaskController::class, 'getByOuvrier']);
    Route::post('/', [TaskController::class, 'store']);
    Route::put('/{id}', [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);
    Route::patch('/{task}/status', [TaskController::class, 'updateStatus']);
});


        // 🏖️ CONGÉS (ADMIN)
        Route::prefix('conges')->group(function () {
            Route::get('/', [CongeController::class, 'index']);                      
            Route::put('/{conge}/decision', [CongeController::class, 'decision']);   
            Route::get('/stats', [CongeController::class, 'stats']);                 
        });

        // 🔔 NOTIFICATIONS (ADMIN)
       

        // ================= STATS ADMIN =================
        Route::middleware('auth:sanctum')->get('/admin/stats', function () {
    // Mapping des statuts
    $statusMap = [
        'pending' => 'À faire',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
    ];

    // Comptage des ouvriers et tâches
    $totalOuvriers = User::where('role', 'ouvrier')->count();
    $totalTaches = Task::count();
    $congesEnAttente = Conge::where('status', 'pending')->count();

    // 5 dernières tâches
    $taches = Task::with('user:id,name')
        ->latest()
        ->take(5)
        ->get()
        ->map(function($t) use ($statusMap) {
            return [
                'titre' => $t->title,
                'ouvrier' => $t->user->name,
                'statut' => $statusMap[$t->status] ?? $t->status,
                'dateFin' => $t->date_fin ? $t->date_fin->format('Y-m-d') : null,
            ];
        });

    // Statistiques pour le graphique
    $statsBar = [
        'aFaire' => Task::where('status', 'pending')->count() * 100 / max($totalTaches, 1),
        'enCours' => Task::where('status', 'in_progress')->count() * 100 / max($totalTaches, 1),
        'terminee' => Task::where('status', 'completed')->count() * 100 / max($totalTaches, 1),
    ];

    return response()->json([
        'totalOuvriers' => $totalOuvriers,
        'totalTaches' => $totalTaches,
        'congesEnAttente' => $congesEnAttente,
        'taches' => $taches,
        'statsBar' => $statsBar,
    ]);
});

    });

    // ================= OUVRIER UNIQUEMENT =================
    Route::middleware('ouvrier')->group(function () {

        Route::get('/ouvrier/dashboard', function () {
            return response()->json(['message' => 'Bienvenue Ouvrier']);
        });

        // ✅ TÂCHES (OUVRIER)
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);               
        Route::post('/tasks/{task}/finish', [TaskController::class, 'finish']);    
        Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']); 

        // 🏖️ CONGÉS (OUVRIER)
        Route::prefix('conges')->group(function () {
            Route::post('/', [CongeController::class, 'store']);                   
            Route::get('/my-conges', [CongeController::class, 'myConges']);        
            Route::delete('/{conge}/annuler', [CongeController::class, 'annuler']); 
        });

    });

    // PROFIL
    


});
