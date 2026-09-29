<?= $this->extend('layouts/auth-layouts') ?>

<?= $this->section('content') ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger" role="alert">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success" role="alert">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('auth/login') ?>" method="POST">
    <?= csrf_field() ?>
    
    <div class="form-group">
        <label class="form-label">Email / Username</label>
        <input type="text" class="form-control" name="email" placeholder="" required>
    </div>
    
    <div class="form-group">
        <label class="form-label">Password</label>
        <div class="password-wrapper">
            <input type="password" class="form-control" id="password" name="password" placeholder="" required>
            <button type="button" class="toggle-password" onclick="togglePassword('password', 'eye-icon')">
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
            </button>
        </div>
    </div>
    
    <button type="submit" class="btn btn-login">Log in</button>
</form>

<?= $this->endSection() ?>