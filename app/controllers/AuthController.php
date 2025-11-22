<?php
// FILE: /app/controllers/AuthController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../helpers/CSRF.php';

/**
 * AuthController
 * Handles user authentication
 */
class AuthController extends Controller {

    /**
     * Show login form
     */
    public function showLogin() {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth/login');
    }

    /**
     * Process login
     */
    public function login() {
        CSRF::requireToken();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $validator = new Validator($_POST);
        $validator->required('email')->email('email')
                  ->required('password');

        if ($validator->fails()) {
            View::setFlash('error', 'Please provide valid credentials');
            View::setOld($_POST);
            $this->redirect('/login');
        }

        $userModel = $this->model('User');
        $user = $userModel->verifyCredentials($email, $password);

        if (!$user) {
            View::setFlash('error', 'Invalid email or password');
            View::setOld($_POST);
            $this->redirect('/login');
        }

        if (!$user['is_active']) {
            View::setFlash('error', 'Your account is inactive');
            $this->redirect('/login');
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['tenant_id'] = $user['tenant_id'];

        // Redirect based on role
        $this->redirect('/dashboard');
    }

    /**
     * Logout
     */
    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }

    /**
     * Show registration form (for demo purposes)
     */
    public function showRegister() {
        $this->view('auth/register');
    }

    /**
     * Process registration (simplified for demo)
     */
    public function register() {
        CSRF::requireToken();

        $validator = new Validator($_POST);
        $validator->required('name')
                  ->required('email')->email('email')
                  ->required('password')->min('password', 6);

        if ($validator->fails()) {
            View::setFlash('error', 'Please check your input');
            View::setOld($_POST);
            $this->redirect('/register');
        }

        $userModel = $this->model('User');

        // Check if email exists
        if ($userModel->findByEmail($_POST['email'])) {
            View::setFlash('error', 'Email already registered');
            View::setOld($_POST);
            $this->redirect('/register');
        }

        // Create user (without tenant - demo only)
        $userId = $userModel->createUser([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => $_POST['password'],
            'role' => 'viewer',
            'is_active' => 1
        ]);

        View::setFlash('success', 'Account created! Please login.');
        $this->redirect('/login');
    }
}
