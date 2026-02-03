<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    // 🔹 Register (créer un utilisateur)
   public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed', // ⬅️ Ajout de "confirmed"
        'role' => 'sometimes|in:admin,ouvrier',
        'telephone' => 'nullable|string|max:20',
        'poste' => 'nullable|string|max:255',
        'date_embauche' => 'nullable|date',
        'adresse' => 'nullable|string|max:500'
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),   
        'role' => $request->role ?? 'ouvrier',
        'telephone' => $request->telephone,
        'poste' => $request->poste,
        'date_embauche' => $request->date_embauche,
        'adresse' => $request->adresse,
    ]);

    // Crée un token pour l'utilisateur
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
        'role' => $user->role, // ⬅️ AJOUTE CETTE LIGNE
        'message' => 'Inscription réussie'
    ], 201);
}
public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string'
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Identifiants invalides'], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('token')->plainTextToken;

    return response()->json([
        'user' => $user,
        'token' => $token,
        'role' => $user->role 
    ]);
}
    // 🔹 Récupérer tous les ouvriers
public function getOuvriers()
{
    // On récupère tous les utilisateurs dont le rôle est "ouvrier"
    $ouvriers = User::where('role', 'ouvrier')->get();

    // On retourne les résultats en JSON
    return response()->json([
        'ouvriers' => $ouvriers
    ], 200);
}

    // 🔹 Logout (supprime le token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Déconnexion réussie'
        ], 200);
    }
    // Récupérer le profil de l'utilisateur connecté
public function getProfil(Request $request)
{
    return response()->json($request->user());
}

// Mettre à jour le profil
public function updateProfil(Request $request)
{
    $user = $request->user();
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'telephone' => 'nullable|string|max:20',
        'poste' => 'nullable|string|max:255',
        'adresse' => 'nullable|string|max:500',
        'password' => 'nullable|string|min:8|confirmed'
    ]);

    // Si un nouveau mot de passe est fourni
    if (!empty($validated['password'])) {
        $validated['password'] = Hash::make($validated['password']);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return response()->json([
        'message' => 'Profil mis à jour avec succès',
        'user' => $user
    ]);
}
}
