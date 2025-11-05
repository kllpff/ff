<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Post;
use FF\Framework\Http\Request;
use FF\Framework\Http\Response;
use FF\Framework\Validation\Validator;

/**
 * DashboardController
 * 
 * Handles dashboard and profile management.
 */
class DashboardController
{
    /**
     * Show dashboard
     * 
     * @return Response
     */
    public function index(): Response
    {
        $userId = session('auth_user_id');
        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', 'Please log in');
            return redirect('/login');
        }

        // Get user's posts count
        $postsCount = Post::where('user_id', '=', $userId)->count();
        $publishedCount = Post::where('user_id', '=', $userId)
                              ->where('status', '=', 'published')
                              ->count();
        $draftCount = Post::where('user_id', '=', $userId)
                          ->where('status', '=', 'draft')
                          ->count();

        $content = view('dashboard/index', [
            'title' => 'Dashboard - FF Framework',
            'user' => $user,
            'postsCount' => $postsCount,
            'publishedCount' => $publishedCount,
            'draftCount' => $draftCount
        ]);
        
        return response($content);
    }

    /**
     * Show profile page
     * 
     * @return Response
     */
    public function profile(): Response
    {
        $userId = session('auth_user_id');
        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', 'Please log in');
            return redirect('/login');
        }

        $content = view('dashboard/profile', [
            'title' => 'Profile - FF Framework',
            'user' => $user
        ]);
        
        return response($content);
    }

    /**
     * Update profile
     * 
     * @param Request $request
     * @return Response
     */
    public function updateProfile(Request $request): Response
    {
        $userId = session('auth_user_id');
        $user = User::find($userId);

        if (!$user) {
            session()->flash('error', 'Please log in');
            return redirect('/login');
        }

        // Validate input
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|max:255'
        ]);

        if (!$validator->validate()) {
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/dashboard/profile');
        }

        $data = $request->all();

        // Check if email changed and already exists
        if ($data['email'] !== $user->email) {
            $existingUser = User::where('email', '=', $data['email'])->first();
            if ($existingUser && $existingUser->id !== $user->id) {
                session()->flash('error', 'Email already in use');
                return redirect('/dashboard/profile');
            }
        }

        // Update user
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->save();

        // Update session data
        session()->put('auth_user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        session()->flash('success', 'Profile updated successfully');
        return redirect('/dashboard/profile');
    }
}
