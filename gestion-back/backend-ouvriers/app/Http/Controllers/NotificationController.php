<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->get();
        
        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification introuvable'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Notification marquée comme lue']);
    }

    public function markAllAsRead()
    {
        auth()->user()->notifications()->update(['is_read' => true]);

        return response()->json(['message' => 'Toutes les notifications marquées comme lues']);
    }

    public function destroy($id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification introuvable'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification supprimée']);
    }

    public function clearAll()
    {
        auth()->user()->notifications()->delete();

        return response()->json(['message' => 'Toutes les notifications supprimées']);
    }
}