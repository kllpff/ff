<?php

namespace App\Controllers\Admin;

use FF\Http\Response;

/**
 * AdminController - Base Controller for Admin Panel
 *
 * Provides common functionality for all admin controllers:
 * - Authentication check
 * - Admin role verification
 * - Common admin methods
 */
abstract class AdminController
{
    /**
     * Check if user is authenticated and is admin
     *
     * @return Response|null Returns redirect response if not authenticated, null otherwise
     */
    protected function ensureAdmin(): ?Response
    {
        // Check if user is logged in
        if (!session('auth_user_id')) {
            session()->flash('error', 'Please login to access admin panel');
            return response()->redirect('/login');
        }

        // Check if user is admin
        if (!session('is_admin')) {
            session()->flash('error', 'Access denied. Admin privileges required.');
            return response()->redirect('/dashboard');
        }

        return null;
    }

    /**
     * Get current admin user ID
     *
     * @return int
     */
    protected function getAdminId(): int
    {
        return (int)session('auth_user_id');
    }

    /**
     * Get current admin user name
     *
     * @return string
     */
    protected function getAdminName(): string
    {
        return session('auth_user_name', 'Admin');
    }
}
