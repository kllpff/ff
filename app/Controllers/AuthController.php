<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\EmailService;
use FF\Http\Request;
use FF\Http\Response;
use FF\Security\Hash;
use FF\Validation\Validator;
use FF\Log\Logger;
use FF\Security\RateLimiter;

/**
 * AuthController
 * 
 * Handles user authentication (register, login, logout, email verification, password reset).
 * Demonstrates: validation, logging, rate limiting, security.
 */
class AuthController
{
    /**
     * The email service
     * 
     * @var EmailService
     */
    protected EmailService $emailService;

    /**
     * The logger instance
     * 
     * @var Logger
     */
    protected Logger $logger;

    /**
     * The rate limiter instance
     * 
     * @var RateLimiter
     */
    protected RateLimiter $rateLimiter;

    /**
     * Create a new AuthController instance
     * 
     * @param EmailService $emailService
     * @param Logger $logger
     * @param RateLimiter $rateLimiter
     */
    public function __construct(EmailService $emailService, Logger $logger, RateLimiter $rateLimiter)
    {
        $this->emailService = $emailService;
        $this->logger = $logger;
        $this->rateLimiter = $rateLimiter;
    }

    /**
     * Show registration form
     * 
     * @return Response
     */
    public function showRegisterForm(): Response
    {
        $content = view('auth/register', [
            'title' => 'Register - FF Framework'
        ]);
        return response($content);
    }

    /**
     * Handle registration
     * 
     * Demonstrates: rate limiting, validation, logging.
     * 
     * @param Request $request
     * @return Response
     */
    public function register(Request $request): Response
    {
        // Rate limiting: max 3 registrations per hour per IP
        $key = 'register:' . $request->ip();
        if ($this->rateLimiter->isLimited($key, 3, 60)) {
            $this->logger->warning('Registration rate limit exceeded', [
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Too many registration attempts. Please try again later.');
            return redirect('/register');
        }
        
        $this->rateLimiter->recordAttempt($key, 60);

        $this->logger->info('Registration attempt', ['ip' => $request->ip()]);

        // Validate input
        $validator = new Validator($request->all(), [
            'name' => 'required|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|max:255',
            'password_confirmation' => 'required|same:password'
        ]);

        if (!$validator->validate()) {
            $this->logger->warning('Registration validation failed', [
                'ip' => $request->ip(),
                'errors' => $validator->getErrors()
            ]);
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/register');
        }

        $data = $request->all(); // Get validated data

        // Check if email already exists
        $existingUser = User::where('email', '=', $data['email'])->first();
        if ($existingUser) {
            $this->logger->warning('Registration with existing email', [
                'email' => $data['email'],
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Email already registered');
            return redirect('/register');
        }

        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));

        // Create user
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'verification_token' => hash('sha256', $verificationToken),
            'email_verified_at' => null
        ]);

        $this->logger->info('User registered successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        // Send verification email
        $this->emailService->sendVerificationEmail($user, $verificationToken);

        session()->flash('success', 'Registration successful! Please check your email to verify your account.');
        return redirect('/login');
    }

    /**
     * Verify email address
     * 
     * @param Request $request
     * @param string $token The verification token
     * @return Response
     */
    public function verifyEmail(Request $request, string $token): Response
    {
        $hashedToken = hash('sha256', $token);
        
        $user = User::where('verification_token', '=', $hashedToken)->first();

        if (!$user) {
            $this->logger->warning('Invalid email verification token', [
                'token_hash' => substr($hashedToken, 0, 10) . '...',
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Invalid verification token');
            return redirect('/login');
        }

        // Mark email as verified
        $user->markEmailAsVerified();

        $this->logger->info('Email verified successfully', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        session()->flash('success', 'Email verified successfully! You can now log in.');
        return redirect('/login');
    }

    /**
     * Show login form
     * 
     * @return Response
     */
    public function showLoginForm(): Response
    {
        $content = view('auth/login', [
            'title' => 'Login - FF Framework'
        ]);
        return response($content);
    }

    /**
     * Handle login
     * 
     * Demonstrates: rate limiting, validation, logging, security.
     * 
     * @param Request $request
     * @return Response
     */
    public function login(Request $request): Response
    {
        // Rate limiting: max 5 login attempts per 15 minutes per IP
        $key = 'login:' . $request->ip();
        if ($this->rateLimiter->isLimited($key, 5, 15)) {
            $this->logger->warning('Login rate limit exceeded', [
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Too many login attempts. Please try again later.');
            return redirect('/login');
        }
        
        $this->rateLimiter->recordAttempt($key, 15);

        $this->logger->debug('Login attempt', ['ip' => $request->ip()]);

        // Validate input
        $validator = new Validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!$validator->validate()) {
            $this->logger->warning('Login validation failed', [
                'ip' => $request->ip(),
                'errors' => $validator->getErrors()
            ]);
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/login');
        }

        $data = $request->all(); // Get validated data

        // Find user by email
        $user = User::where('email', '=', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            $this->logger->warning('Failed login attempt - invalid credentials', [
                'email' => $data['email'],
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Invalid credentials');
            return redirect('/login');
        }

        // Check if email is verified
        if (!$user->isEmailVerified()) {
            $this->logger->warning('Login attempt with unverified email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Please verify your email before logging in');
            return redirect('/login');
        }

        // Login user
        session()->regenerate();
        session()->put('auth_user_id', $user->id);
        session()->put('auth_user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        $this->logger->info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        session()->flash('success', 'Welcome back, ' . $user->name . '!');
        return redirect('/dashboard');
    }

    /**
     * Handle logout
     * 
     * @return Response
     */
    public function logout(): Response
    {
        $userId = session('auth_user_id');
        
        $this->logger->info('User logged out', ['user_id' => $userId]);
        
        session()->forget('auth_user_id');
        session()->forget('auth_user');
        session()->regenerate();
        session()->flash('success', 'Logged out successfully');
        return redirect('/');
    }

    /**
     * Show forgot password form
     * 
     * @return Response
     */
    public function showForgotPasswordForm(): Response
    {
        $content = view('auth/forgot-password', [
            'title' => 'Forgot Password - FF Framework'
        ]);
        return response($content);
    }

    /**
     * Send password reset link
     * 
     * Demonstrates: rate limiting, logging.
     * 
     * @param Request $request
     * @return Response
     */
    public function sendPasswordResetLink(Request $request): Response
    {
        // Rate limiting: max 3 password reset requests per hour per IP
        $key = 'password_reset:' . $request->ip();
        if ($this->rateLimiter->isLimited($key, 3, 60)) {
            $this->logger->warning('Password reset rate limit exceeded', [
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Too many password reset requests. Please try again later.');
            return redirect('/forgot-password');
        }
        
        $this->rateLimiter->recordAttempt($key, 60);

        // Validate input
        $validator = new Validator($request->all(), [
            'email' => 'required|email'
        ]);

        if (!$validator->validate()) {
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/forgot-password');
        }

        $data = $request->all(); // Get validated data

        // Find user
        $user = User::where('email', '=', $data['email'])->first();

        if (!$user) {
            // Don't reveal if email exists (but still log it)
            $this->logger->info('Password reset requested for non-existent email', [
                'email' => $data['email'],
                'ip' => $request->ip()
            ]);
            session()->flash('success', 'If that email exists, a password reset link has been sent.');
            return redirect('/forgot-password');
        }

        // Generate reset token
        $resetToken = $user->generatePasswordResetToken();

        $this->logger->info('Password reset link sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        // Send reset email
        $this->emailService->sendPasswordResetEmail($user, $resetToken);

        session()->flash('success', 'Password reset link sent to your email.');
        return redirect('/forgot-password');
    }

    /**
     * Show reset password form
     * 
     * @param Request $request
     * @param string $token The reset token
     * @return Response
     */
    public function showResetPasswordForm(Request $request, string $token): Response
    {
        $content = view('auth/reset-password', [
            'title' => 'Reset Password - FF Framework',
            'token' => $token
        ]);
        return response($content);
    }

    /**
     * Reset password
     * 
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request): Response
    {
        $this->logger->info('Password reset attempt', ['ip' => $request->ip()]);

        // Validate input
        $validator = new Validator($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|max:255',
            'password_confirmation' => 'required|same:password'
        ]);

        if (!$validator->validate()) {
            $this->logger->warning('Password reset validation failed', [
                'ip' => $request->ip(),
                'errors' => $validator->getErrors()
            ]);
            session()->flash('error', $validator->getErrors()[array_key_first($validator->getErrors())]);
            return redirect('/reset-password/' . $request->input('token'));
        }

        $data = $request->all(); // Get validated data

        // Find user
        $user = User::where('email', '=', $data['email'])->first();

        if (!$user || !$user->isValidPasswordResetToken($data['token'])) {
            $this->logger->warning('Invalid password reset token', [
                'email' => $data['email'],
                'ip' => $request->ip()
            ]);
            session()->flash('error', 'Invalid or expired reset token');
            return redirect('/forgot-password');
        }

        // Reset password
        $user->password = Hash::make($data['password']);
        $user->save();
        $user->clearPasswordResetToken();

        $this->logger->info('Password reset successfully', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip()
        ]);

        session()->flash('success', 'Password reset successfully! You can now log in.');
        return redirect('/login');
    }
}
