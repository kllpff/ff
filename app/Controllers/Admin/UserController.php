<?php

namespace App\Controllers\Admin;

use App\Models\User;
use FF\Http\Request;
use FF\Http\Response;
use FF\Validation\Validator;
use FF\Log\Logger;

/**
 * Admin User Controller
 *
 * Handles user management in admin panel
 */
class UserController extends AdminController
{
    protected Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display list of all users
     */
    public function index(Request $request): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $query = User::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            $this->logger->debug('Searching users', ['search' => $search]);
        }

        // Role filter
        if ($request->get('role') === 'admin') {
            $query->where('is_admin', '=', 1);
            $this->logger->debug('Filtering users by role', ['role' => 'admin']);
        } elseif ($request->get('role') === 'user') {
            $query->where('is_admin', '=', 0);
            $this->logger->debug('Filtering users by role', ['role' => 'user']);
        }

        $users = $query->orderBy('created_at', 'DESC')->paginate(20);

        return response(view('admin.users.index', [
            '__layout' => 'admin/layouts/app',
            'users' => $users,
            'filters' => [
                'search' => $search ?? '',
                'role' => $request->get('role') ?? ''
            ],
            'title' => 'Manage Users'
        ]));
    }

    /**
     * Show form to edit user
     */
    public function edit(int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $user = User::findOrFail($id);

        return response(view('admin.users.edit', [
            '__layout' => 'admin/layouts/app',
            'user' => $user,
            'title' => 'Edit User: ' . $user->name
        ]));
    }

    /**
     * Update user
     */
    public function update(Request $request, int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        $user = User::findOrFail($id);

        // Validate input
        $rules = [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'is_admin' => 'in:0,1'
        ];

        // Only validate password if it's provided
        if ($request->input('password')) {
            $rules['password'] = 'min:8';
        }

        $validator = new Validator($request->all(), $rules);

        if ($validator->fails()) {
            session()->flash('error', 'Validation failed');
            session()->flash('errors', $validator->errors());
            return response()->redirect('/admin/users/' . $id . '/edit');
        }

        try {
            $updateData = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'is_admin' => (int) $request->input('is_admin', 0)
            ];

            // Only update password if provided
            if ($password = $request->input('password')) {
                $updateData['password'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $user->update($updateData);

            $this->logger->info('User updated via admin panel', [
                'user_id' => $user->id,
                'admin_id' => $this->getAdminId()
            ]);

            session()->flash('success', 'User updated successfully!');
            return response()->redirect('/admin/users');
        } catch (\Exception $e) {
            $this->logger->error('Failed to update user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Failed to update user. Please try again.');
            return response()->redirect('/admin/users/' . $id . '/edit');
        }
    }

    /**
     * Delete user
     */
    public function destroy(int $id): Response
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        // Prevent deleting yourself
        if ($id === $this->getAdminId()) {
            session()->flash('error', 'You cannot delete your own account!');
            return response()->redirect('/admin/users');
        }

        try {
            $user = User::findOrFail($id);
            $name = $user->name;

            $user->delete();

            $this->logger->warning('User deleted via admin panel', [
                'user_id' => $id,
                'name' => $name,
                'admin_id' => $this->getAdminId()
            ]);

            session()->flash('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete user', [
                'user_id' => $id,
                'error' => $e->getMessage()
            ]);

            session()->flash('error', 'Failed to delete user.');
        }

        return response()->redirect('/admin/users');
    }
}
