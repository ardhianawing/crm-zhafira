<?php $__env->startSection('title', 'Kelola Users - Zhafira CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-person-gear" style="color: #0f3d2e;"></i> Kelola Users
    </h4>
    <a href="<?php echo e(route('admin.users.create')); ?>" class="btn" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
        <i class="bi bi-plus-circle"></i> Tambah User
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr style="background-color: #0f3d2e; color: #fff;">
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Username</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Nama Lengkap</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Role</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500; text-align: center;">Leads</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500; text-align: center;">Status</th>
                        <th style="background-color: #0f3d2e; color: #fff; font-weight: 500; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="<?php echo e(!$user->is_active ? 'table-secondary' : ''); ?>">
                        <td><?php echo e($user->username); ?></td>
                        <td><?php echo e($user->nama_lengkap); ?></td>
                        <td>
                            <span class="badge" style="background-color: <?php echo e($user->role === 'admin' ? '#0f3d2e' : '#0dcaf0'); ?>; color: <?php echo e($user->role === 'admin' ? '#fff' : '#000'); ?>;">
                                <?php echo e(ucfirst($user->role)); ?>

                            </span>
                        </td>
                        <td class="text-center"><?php echo e($user->leads_count); ?></td>
                        <td class="text-center">
                            <?php if($user->is_active): ?>
                                <span class="badge bg-success">Aktif</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if($user->id !== auth()->id()): ?>
                                    <form action="<?php echo e(route('admin.users.toggle-status', $user)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <button type="submit" class="btn btn-outline-<?php echo e($user->is_active ? 'warning' : 'success'); ?> btn-sm" title="<?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>">
                                            <i class="bi bi-<?php echo e($user->is_active ? 'x-circle' : 'check-circle'); ?>"></i>
                                        </button>
                                    </form>
                                    <?php if($user->leads_count === 0): ?>
                                    <form action="<?php echo e(route('admin.users.destroy', $user)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus user ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada user
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if($users->hasPages()): ?>
    <div class="card-footer">
        <?php echo e($users->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u861895257/domains/crm.zhafiravila.com/public_html/resources/views/admin/users/index.blade.php ENDPATH**/ ?>