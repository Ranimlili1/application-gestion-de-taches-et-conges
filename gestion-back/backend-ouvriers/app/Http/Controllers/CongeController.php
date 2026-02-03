<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conge;
use App\Models\User;
use App\Models\Notification;
use App\Events\NotificationCreated;
use Illuminate\Support\Carbon;

class CongeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'motif' => 'nullable|string|max:255',
        ]);

        // Vérifier s'il existe déjà un congé en attente
        $existe = Conge::where('user_id', auth()->id())->where('status', 'pending')->first();
        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Une demande de congé est déjà en attente de validation'
            ], 409);
        }

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin   = Carbon::parse($request->date_fin);
        $jours = $dateDebut->diffInDays($dateFin) + 1;

        if ($jours > 10) {
            return response()->json([
                'message' => "La durée du congé ne peut pas dépasser 10 jours"
            ], 400);
        }

        // Création du congé
        $conge = Conge::create([
            'user_id' => auth()->id(),
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'motif' => $request->motif,
            'status' => 'en_attente',
        ]);

        // Notifications aux admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $notification = Notification::create([
                'user_id' => $admin->id,
                'title' => 'Nouvelle demande de congé',
                'message' => 'Un ouvrier a demandé un congé.',
                'type' => 'info'
            ]);
            event(new NotificationCreated($notification));
        }

        return response()->json($conge, 201);
    }

    public function decision(Request $request, Conge $conge)
    {
        $request->validate([
            'status' => 'required|in:accepte,refuse',
            'commentaire_admin' => 'nullable|string'
        ]);

        $conge->update([
            'status' => $request->status,
            'commentaire_admin' => $request->commentaire_admin
        ]);

        // Notification à l'ouvrier
        Notification::create([
            'user_id' => $conge->user_id,
            'title' => 'Décision congé',
            'message' => 'Votre congé a été ' . $request->status,
            'type' => 'info'
        ]);

        return response()->json([
            'message' => 'Décision enregistrée',
            'conge' => $conge
        ]);
    }

    public function index()
    {
        $conges = Conge::with('user')->get();
        return response()->json($conges);
    }

    public function myConges(Request $request)
    {
        $conges = $request->user()
            ->conges()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($conges);
    }

    public function stats()
    {
        $totalMois = Conge::whereMonth('created_at', now()->month)->count();

        $parOuvrier = Conge::select('user_id')
            ->selectRaw('count(*) as total')
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $parStatut = Conge::select('status')
            ->selectRaw('count(*) as total')
            ->groupBy('status')
            ->get();

        return response()->json([
            'total_mois' => $totalMois,
            'par_ouvrier' => $parOuvrier,
            'par_statut' => $parStatut
        ]);
    }

    public function annuler(Conge $conge)
{
    if ($conge->user_id !== auth()->id()) {
        return response()->json(['message' => 'Non autorisé'], 403);
    }

    // ✅ CORRECTION ICI - utiliser 'en_attente' au lieu de 'pending'
    if ($conge->status !== 'en_attente') {
        return response()->json(['message' => 'Impossible d\'annuler un congé déjà traité'], 400);
    }

    $conge->delete();

    return response()->json(['message' => 'Congé annulé avec succès']);
}
}
