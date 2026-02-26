<?php $__env->startSection('title', 'Database Leads - Zhafira CRM'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0 fw-bold"><i class="bi bi-people-fill" style="color: #0f3d2e;"></i> Database Leads</h4>
        <p class="text-muted mb-0">Total database: <b><?php echo e($leads->total()); ?></b> baris data.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo e(route('admin.leads.create')); ?>" class="btn" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
            <i class="bi bi-plus-circle"></i> Tambah Lead
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-6">
                <form action="<?php echo e(route('admin.leads.index')); ?>" method="GET" class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2 bg-light px-3 py-1 rounded border">
                        <span class="small fw-bold text-nowrap">Tampilkan:</span>
                        <select name="per_page" class="form-select form-select-sm border-0 bg-transparent fw-bold" style="width: 100px;" onchange="this.form.submit()">
                            <?php $__currentLoopData = [50, 100, 500, 1000]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($value); ?>" <?php echo e($perPage == $value ? 'selected' : ''); ?>><?php echo e($value); ?> Baris</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama/hp..." value="<?php echo e(request('search')); ?>">
                        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted small">Menampilkan <?php echo e($leads->firstItem()); ?> - <?php echo e($leads->lastItem()); ?> dari <?php echo e($leads->total()); ?> data</span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr style="background-color: #0f3d2e; color: #fff;">
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Customer</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">WhatsApp</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Sumber</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Marketing</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500;">Status</th>
                    <th style="background-color: #0f3d2e; color: #fff; font-weight: 500; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $leads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div class="fw-bold"><?php echo e($lead->nama_customer); ?></div>
                        <small class="text-muted"><?php echo e($lead->created_at->format('d M Y')); ?></small>
                    </td>
                    <td><?php echo e($lead->no_hp); ?></td>
                    <td><span class="badge" style="background-color: #f8f9fa; color: #212529; border: 1px solid #dee2e6;"><?php echo e($lead->sumber_lead ?? '-'); ?></span></td>
                    <td>
                        <?php if($lead->assignedUser): ?>
                            <span class="badge" style="background-color: rgba(13,202,240,0.1); color: #0dcaf0; border: 1px solid rgba(13,202,240,0.2);"><?php echo e($lead->assignedUser->nama_lengkap); ?></span>
                        <?php else: ?>
                            <span class="badge" style="background-color: rgba(220,53,69,0.1); color: #dc3545;">Belum Di-assign</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($component)) { $__componentOriginal435aefee4aa6dd7f20df034696ae03b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal435aefee4aa6dd7f20df034696ae03b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.badge-status','data' => ['status' => $lead->status_prospek]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('badge-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($lead->status_prospek)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal435aefee4aa6dd7f20df034696ae03b9)): ?>
<?php $attributes = $__attributesOriginal435aefee4aa6dd7f20df034696ae03b9; ?>
<?php unset($__attributesOriginal435aefee4aa6dd7f20df034696ae03b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal435aefee4aa6dd7f20df034696ae03b9)): ?>
<?php $component = $__componentOriginal435aefee4aa6dd7f20df034696ae03b9; ?>
<?php unset($__componentOriginal435aefee4aa6dd7f20df034696ae03b9); ?>
<?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="<?php echo e(route('admin.leads.edit', $lead->id)); ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil text-primary"></i></a>
                            <form action="<?php echo e(route('admin.leads.destroy', $lead->id)); ?>" method="POST" class="d-inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus data ini?')"><i class="bi bi-trash text-danger"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" class="text-center py-5">Tidak ada data leads.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white border-top-0 py-3">
        <?php echo e($leads->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u861895257/domains/crm.zhafiravila.com/public_html/resources/views/admin/leads/index.blade.php ENDPATH**/ ?>