<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\User;
use App\Models\Notification;
use App\Events\NotificationCreated;
class TaskController extends Controller
{
    // 📋 1. LISTER TOUTES LES TÂCHES (pour admin)
    public function index()
    {
        // Récupère toutes les tâches avec les infos de l'ouvrier assigné
        $tasks = Task::with('user:id,name,email')->get();
        return response()->json($tasks);
    }

    // 📋 2. RÉCUPÉRER LES TÂCHES D'UN OUVRIER SPÉCIFIQUE
    public function getByOuvrier($userId)
    {
        // Vérifie que l'ouvrier existe
        $ouvrier = User::findOrFail($userId);
        
        // Récupère toutes ses tâches
        $tasks = Task::where('user_id', $userId)->get();
        
        return response()->json($tasks);
    }

    // ✏️ 3. CRÉER UNE NOUVELLE TÂCHE (admin assigne une tâche à un ouvrier)
    public function store(Request $request)
    {
        // Validation des données
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'date_fin' => 'nullable|date',
            'priorite' => 'nullable|in:Basse,Normale,Haute'
        ]);

        // Crée la tâche
        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'user_id' => $validated['user_id'],
            'status' => $validated['status'] ?? 'pending',
            'date_fin' => $validated['date_fin'] ?? null,
            'priorite' => $validated['priorite'] ?? 'Normale'
        ]);

        // Créer une notification pour l'ouvrier assigné
        Notification::create([
            'user_id' => $task->user_id,
            'title' => 'Nouvelle tâche assignée',
            'message' => "Vous avez une nouvelle tâche: {$task->title}",
            'type' => 'info'
        ]);

        // Retourne la tâche avec les infos de l'ouvrier
        return response()->json([
            'message' => 'Tâche créée avec succès',
            'task' => $task->load('user:id,name,email')
        ], 201);
    }

    // 🔄 4. MODIFIER UNE TÂCHE (admin peut tout modifier)
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        
        // Validation
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'user_id' => 'sometimes|exists:users,id',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'date_fin' => 'nullable|date',
            'priorite' => 'sometimes|in:Basse,Normale,Haute'
        ]);

        // Met à jour
        $task->update($validated);

        // Créer une notification pour l'ouvrier assigné si la tâche lui appartient
        if ($task->user_id) {
            Notification::create([
                'user_id' => $task->user_id,
                'title' => 'Tâche mise à jour',
                'message' => "Votre tâche '{$task->title}' a été mise à jour.",
                'type' => 'info'
            ]);
        }

        return response()->json([
            'message' => 'Tâche mise à jour',
            'task' => $task->load('user:id,name,email')
        ]);
    }

    // 🗑️ 5. SUPPRIMER UNE TÂCHE (admin uniquement)
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();
        
        return response()->json([
            'message' => 'Tâche supprimée'
        ]);
    }

    // ✅ 6. TERMINER UNE TÂCHE (ouvrier marque sa tâche comme terminée)
    public function finish(Task $task, Request $request)
    {
        // Vérifie que c'est bien l'ouvrier assigné
        if ($task->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
        
        // Marque comme terminée
        $task->update(['status' => 'completed']);
        $task->refresh(); // recharge les valeurs depuis la BDD
        return response()->json([
        'message' => 'Tâche terminée',
        'task' => $task->load('user:id,name,email') // inclut les infos ouvrier si besoin
        ]);

    }

    // 🔄 7. CHANGER LE STATUT (admin ou ouvrier)
    public function updateStatus(Task $task, Request $request)
    {
        $user = $request->user();
        
        // Si c'est un ouvrier, il ne peut modifier QUE ses propres tâches
        if ($user->role === 'ouvrier' && $task->user_id !== $user->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }
        
        // Validation du statut
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed'
        ]);
        
        // Met à jour le statut
        $task->update(['status' => $request->status]);
        $task->refresh();
        return response()->json([
            'message' => 'Statut mis à jour',
            'task' => $task->load('user:id,name,email')
        ]);
    }
    // Dans app/Http/Controllers/TaskController.php

public function myTasks(Request $request)
{
    $tasks = Task::with('user:id,name,email')
             ->where('user_id', $request->user()->id)
             ->orderBy('created_at', 'desc')
             ->get();

    
    return response()->json($tasks);
}
public function statsAdmin()
{
    $totalOuvriers = User::count();
    $totalTaches = Task::count();
    $congesEnAttente = Conge::where('status', 'pending')->count();

    $taches = Task::with('user:id,name')
                  ->orderBy('created_at', 'desc')
                  ->take(5)
                  ->get()
                  ->map(function($t) {
                      return [
                          'titre' => $t->title,
                          'ouvrier' => $t->user->name ?? 'Non assigné',
                          'statut' => $t->status,
                          'dateFin' => $t->date_fin
                      ];
                  });

    $statsBar = [
        'aFaire' => Task::where('status', 'pending')->count() * 100 / max($totalTaches,1),
        'enCours' => Task::where('status', 'in_progress')->count() * 100 / max($totalTaches,1),
        'terminee' => Task::where('status', 'completed')->count() * 100 / max($totalTaches,1)
    ];

    return response()->json([
        'totalOuvriers' => $totalOuvriers,
        'totalTaches' => $totalTaches,
        'congesEnAttente' => $congesEnAttente,
        'taches' => $taches,
        'statsBar' => $statsBar
    ]);
}

}