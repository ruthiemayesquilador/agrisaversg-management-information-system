<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-4">
                <i class="fa fa-user"></i> My Profile
            </h2>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><strong>Name</strong></label>
                        <p class="form-control-plaintext"><?= esc($user['name']) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><strong>Username</strong></label>
                        <p class="form-control-plaintext"><?= esc($user['username']) ?></p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><strong>Email</strong></label>
                        <p class="form-control-plaintext"><?= esc($user['email']) ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><strong>Role</strong></label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-<?= $user['role'] === 'super_admin' ? 'danger' : 'primary' ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><strong>Branch</strong></label>
                        <p class="form-control-plaintext"><?= esc($user['branch'] ?? '—') ?></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label"><strong>Account Status</strong></label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-success">Active</span>
                        </p>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                        <i class="fa fa-edit"></i> Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('profile/update') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" value="<?= esc($user['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_username" name="username" value="<?= esc($user['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_email" name="email" value="<?= esc($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="display_role" class="form-label">Role</label>
                        <input type="text" class="form-control" id="display_role" value="<?= ucfirst($user['role']) ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="display_branch" class="form-label">Branch</label>
                        <input type="text" class="form-control" id="display_branch" value="<?= esc($user['branch'] ?? '—') ?>" disabled>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
