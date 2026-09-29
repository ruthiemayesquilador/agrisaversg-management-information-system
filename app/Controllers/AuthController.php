<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    public function index()
    {
        // Redirect to dashboard if already logged in
        if (session()->get('user_id')) {
            return redirect()->to('dashboard');
        }
        return view('auth/login');
    }

    public function login()
    {
        // Redirect if already logged in
        if (session()->get('user_id')) {
            return redirect()->to('dashboard');
        }

        // Show form on GET
        if ($this->request->getMethod() === 'get') {
            return view('auth/login');
        }

        // Handle POST
        $identifier = trim((string) $this->request->getPost('email'));
        $password   = trim((string) $this->request->getPost('password'));

        if (empty($identifier) || empty($password)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email/username and password are required.');
        }

        $model = new UserModel();
        $user  = $model->findByEmailOrUsername($identifier);

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email/username or password.');
        }

        // Support both plain-text (legacy) and hashed passwords
        $passwordMatch = false;
        if (password_verify($password, $user['password'])) {
            // Already hashed — OK
            $passwordMatch = true;
        } elseif ($password === $user['password']) {
            // Plain text match — upgrade to hash automatically
            $model->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
            $passwordMatch = true;
        }

        if (!$passwordMatch) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email/username or password.');
        }

        // Store user info in session
        session()->set([
            'user_id'   => $user['id'],
            'name'      => $user['name'],
            'username'  => $user['username'],
            'email'     => $user['email'],
            'branch'    => $user['branch'],
            'role'      => $user['role'],
            'logged_in' => true,
        ]);

        return redirect()->to('dashboard');
    }

    public function logout()
    {
        session()->remove([
            'user_id',
            'name',
            'username',
            'email',
            'branch',
            'role',
            'logged_in',
        ]);
        session()->destroy();
        return redirect()->to('/');
    }
}
