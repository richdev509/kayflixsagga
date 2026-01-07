<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Video;
use App\Models\Creator;
use App\Models\Subscription;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_creators' => Creator::where('status', 'approved')->count(),
            'pending_creators' => Creator::where('status', 'pending')->count(),
            'total_videos' => Video::count(),
            'published_videos' => Video::where('is_published', true)->count(),
            'active_subscriptions' => Subscription::where('status', 'active')
                ->where('expires_at', '>', now())
                ->count(),
            'total_revenue' => Subscription::where('status', 'active')->sum('amount'),
        ];

        $recent_videos = Video::with('creator.user')
            ->latest()
            ->take(10)
            ->get();

        $pending_creators = Creator::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('admin.dashboard', compact('stats', 'recent_videos', 'pending_creators'));
    }

    public function users()
    {
        $users = User::with('roles')->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function creators()
    {
        $creators = Creator::with('user')->latest()->paginate(20);
        return view('admin.creators.index', compact('creators'));
    }

    public function approveCreator($id)
    {
        $creator = Creator::findOrFail($id);
        $creator->update(['status' => 'approved']);
        
        // Assigner le rôle creator à l'utilisateur
        $creator->user->assignRole('creator');

        return redirect()->back()->with('success', 'Créateur approuvé avec succès');
    }

    public function rejectCreator($id)
    {
        $creator = Creator::findOrFail($id);
        $creator->delete();

        return redirect()->back()->with('success', 'Demande de créateur rejetée');
    }
}
