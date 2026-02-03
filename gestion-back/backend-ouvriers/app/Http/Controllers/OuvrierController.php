<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class OuvrierController extends Controller
{
    // Liste tous les ouvriers
    public function index()
    {
        $ouvriers = User::where('role', 'ouvrier')->get();
        return response()->json($ouvriers);
    }

    // Met à jour un ouvrier
    public function update(Request $request, $id)
    {
        $ouvrier = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'poste' => 'nullable|string',
            'telephone' => 'nullable|string',
            'date_embauche' => 'nullable|date',
            'adresse' => 'nullable|string'
        ]);

        $ouvrier->update($validated);
        
        return response()->json([
            'message' => 'Ouvrier mis à jour',
            'ouvrier' => $ouvrier
        ]);
    }

    // Supprime un ouvrier
    public function destroy($id)
    {
        $ouvrier = User::findOrFail($id);
        $ouvrier->delete();
        
        return response()->json(['message' => 'Ouvrier supprimé']);
    }
}