<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
class PasswordResetController extends Controller
{
    // Étape 1 : Demande de réinitialisation
    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $token = Str::random(60);

        // Stocker ou mettre à jour le token
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => Carbon::now()]
        );

        // Envoyer email avec token
        Mail::to($request->email)->send(
    new ResetPasswordMail($token, $request->email)
);


        return response()->json([
            'message' => 'Email de réinitialisation envoyé !'
        ]);
    }
    public function reset(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'token' => 'required|string',
        'password' => 'required|string|min:8|confirmed'
    ]);
    
    $record = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('token', $request->token)
                ->first();

    if (!$record) {
        return response()->json(['message' => 'Token invalide'], 400);
    }
    if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
    return response()->json(['message' => 'Token expiré'], 400);
    }
    DB::table('users')
        ->where('email', $request->email)
        ->update(['password' => bcrypt($request->password)]);

    // Supprimer le token après usage
    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Mot de passe réinitialisé !']);
}

}
