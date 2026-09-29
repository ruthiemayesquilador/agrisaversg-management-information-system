<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    protected UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('auth/login');
        }

        $user_id = session()->get('user_id');
        $data['user'] = $this->model->find($user_id);

        if (!$data['user']) {
            return redirect()->to('dashboard')->with('error', 'User not found.');
        }

        return view('profile/index', $data);
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('profile');
        }

        $user_id = session()->get('user_id');
        $user = $this->model->find($user_id);

        if (!$user) {
            return redirect()->to('profile')->with('error', 'User not found.');
        }

        $name = trim($this->request->getPost('name'));
        $username = trim($this->request->getPost('username'));
        $email = trim($this->request->getPost('email'));

        // Validation
        if (empty($name) || empty($username) || empty($email)) {
            return redirect()->to('profile')->with('error', 'All fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('profile')->with('error', 'Invalid email format.');
        }

        // Check if username is already taken by another user
        $existingUser = $this->model->where('username', $username)->where('id !=', $user_id)->first();
        if ($existingUser) {
            return redirect()->to('profile')->with('error', 'Username is already taken.');
        }

        // Check if email is already taken by another user
        $existingEmail = $this->model->where('email', $email)->where('id !=', $user_id)->first();
        if ($existingEmail) {
            return redirect()->to('profile')->with('error', 'Email is already in use.');
        }

        // Update user
        $this->model->update($user_id, [
            'name'     => $name,
            'username' => $username,
            'email'    => $email
        ]);

        // Update session
        session()->set([
            'name'     => $name,
            'username' => $username,
            'email'    => $email
        ]);

        $this->logHistorySafe(
            'Profile Updated',
            'Updated profile for user "' . $username . '"',
            (int) $user_id,
            $username,
            null,
            'Auth'
        );

        return redirect()->to('profile')->with('success', 'Profile updated successfully.');
    }
}
