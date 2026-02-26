<?php $__env->startSection('title', 'Distribusi Lead - Zhafira CRM'); ?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4 px-2 px-md-0">
        <h4 class="mb-0 fs-5 fs-md-4">
            <i class="bi bi-person-plus" style="color: #0f3d2e;"></i> Distribusi Lead
        </h4>
        <a href="<?php echo e(route('admin.leads.create')); ?>" class="btn btn-sm" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff; padding: 0.25rem 0.5rem; font-size: 0.75rem;">
            <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Tambah Lead</span><span class="d-inline d-sm-none">Tambah</span>
        </a>
    </div>

    <div class="row g-3 g-md-4">
        <!-- Left Column: Unassigned Leads -->
        <div class="col-xl-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white py-2 py-md-3 d-flex justify-content-between align-items-center border-bottom">
                    <h6 class="mb-0 fw-bold small-mobile">
                        <i class="bi bi-person-x text-danger me-1 me-md-2"></i> Lead Baru
                        <span class="badge bg-danger ms-1"><?php echo e($unassignedLeads->total()); ?></span>
                    </h6>
                    <div class="form-check small p-0 m-0 d-flex align-items-center">
                        <input class="form-check-input me-2" type="checkbox" id="selectAllUnassignedMaster" style="margin-top: 0;">
                        <label class="form-check-label small d-none d-sm-block" for="selectAllUnassignedMaster">Pilih Semua</label>
                        <label class="form-check-label small d-block d-sm-none" for="selectAllUnassignedMaster">Semua</label>
                    </div>
                </div>
                <div class="card-body p-0">
                    <form action="<?php echo e(route('admin.assignment.bulk')); ?>" method="POST" id="bulkAssignForm">
                        <?php echo csrf_field(); ?>
                        <div class="p-2 p-md-3 border-bottom sticky-top" style="top: 0; z-index: 10; background-color: #f8f9fa;">
                            <div class="row g-1 g-md-2 align-items-center">
                                <div class="col">
                                    <select name="marketing_id" class="form-select form-select-sm" required>
                                        <option value="">Pilih Marketing...</option>
                                        <?php $__currentLoopData = $marketingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($user->id); ?>">
                                                <?php echo e($user->nama_lengkap); ?> (<?php echo e($user->leads_count); ?>)
                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm px-2 px-md-3" style="background-color: #0f3d2e; border-color: #0f3d2e; color: #fff;">
                                        <i class="bi bi-person-check"></i> <span class="d-none d-sm-inline">Bagi Lead</span><span class="d-inline d-sm-none">Bagi</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php if($unassignedLeads->count() > 0): ?>
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="sticky-top" style="top: 0;">
                                        <tr style="background-color: #0f3d2e; color: #fff; font-size: 0.875rem; font-weight: 600;">
                                            <th style="width: 35px; background-color: #0f3d2e; color: #fff; padding-left: 0.5rem;" class="ps-2 ps-md-3">#</th>
                                            <th style="background-color: #0f3d2e; color: #fff;">Customer</th>
                                            <th class="d-none d-md-table-cell" style="background-color: #0f3d2e; color: #fff;">Status</th>
                                            <th class="text-end pe-2 pe-md-3" style="background-color: #0f3d2e; color: #fff;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $unassignedLeads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="small-mobile-row">
                                                <td class="ps-2 ps-md-3">
                                                    <input class="form-check-input unassigned-checkbox" type="checkbox"
                                                        name="lead_ids[]" value="<?php echo e($lead->id); ?>">
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-truncate" style="max-width: 150px;"><?php echo e($lead->nama_customer); ?></div>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <small class="text-muted d-block"><?php echo e($lead->no_hp); ?></small>
                                                        <span class="d-md-none"><?php if (isset($component)) { $__componentOriginal435aefee4aa6dd7f20df034696ae03b9 = $component; } ?>
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
<?php endif; ?></span>
                                                    </div>
                                                </td>
                                                <td class="d-none d-md-table-cell"><?php if (isset($component)) { $__componentOriginal435aefee4aa6dd7f20df034696ae03b9 = $component; } ?>
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
<?php endif; ?></td>
                                                <td class="text-end pe-2 pe-md-3">
                                                    <div class="dropdown">
                                                        <button class="btn dropdown-toggle py-1" type="button" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; background-color: #f8f9fa; border-color: #dee2e6;"
                                                            data-bs-toggle="dropdown">
                                                            <span class="d-none d-sm-inline">Assign</span><i class="bi bi-person-plus d-inline d-sm-none"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                            <?php $__currentLoopData = $marketingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <li>
                                                                    <form action="<?php echo e(route('admin.assignment.single', $lead)); ?>"
                                                                        method="POST">
                                                                        <?php echo csrf_field(); ?>
                                                                        <input type="hidden" name="marketing_id"
                                                                            value="<?php echo e($user->id); ?>">
                                                                        <button type="submit" class="dropdown-item py-2 small">
                                                                            <i class="bi bi-person me-2"></i> <?php echo e($user->nama_lengkap); ?>

                                                                            <span
                                                                                class="badge bg-light text-dark ms-1"><?php echo e($user->leads_count); ?></span>
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-2 p-md-3 border-top">
                                <?php echo e($unassignedLeads->links()); ?>

                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-check2-all fs-1 d-block mb-3 text-success"></i>
                                <p>Semua lead baru sudah di-assign!</p>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Assigned Leads & Controls -->
        <div class="col-xl-6">
            <div class="row g-4">
                <!-- Sidebar Info (Rotator & Stats) -->
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div
                                class="card shadow-sm border-0 border-start border-4 <?php echo e($rotatorEnabled ? 'border-success' : 'border-secondary'); ?>">
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="text-muted mb-1 small uppercase fw-bold">Lead Rotator</h6>
                                            <h5 class="mb-0 <?php echo e($rotatorEnabled ? 'text-success' : 'text-secondary'); ?>">
                                                <?php echo e($rotatorEnabled ? 'AKTIF' : 'NONAKTIF'); ?>

                                            </h5>
                                        </div>
                                        <form action="<?php echo e(route('admin.assignment.toggle-rotator')); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="btn btn-sm <?php echo e($rotatorEnabled ? 'btn-outline-danger' : 'btn-success'); ?>">
                                                <?php echo e($rotatorEnabled ? 'Matikan' : 'Aktifkan'); ?>

                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0" style="border-left: 4px solid #0f3d2e !important;">
                                <div class="card-body py-3">
                                    <h6 class="text-muted mb-1 small uppercase fw-bold">Total Terdistribusi</h6>
                                    <h5 class="mb-0" style="color: #0f3d2e;"><?php echo e($assignedLeads->total()); ?> Leads</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Leads Table -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div
                            class="card-header bg-white py-2 py-md-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 gap-sm-3 border-bottom">
                            <h6 class="mb-0 fw-bold small-mobile">
                                <i class="bi bi-person-check-fill text-success me-1 me-md-2"></i> Terdistribusi
                            </h6>
                            <form action="<?php echo e(route('admin.assignment.index')); ?>" method="GET" class="d-flex gap-2">
                                <input type="hidden" name="per_page" value="<?php echo e($perPage); ?>">
                                <select name="marketing_filter" class="form-select form-select-sm" style="min-width: 140px;"
                                    onchange="this.form.submit()">
                                    <option value="">Semua Marketing</option>
                                    <?php $__currentLoopData = $marketingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>" <?php echo e(request('marketing_filter') == $user->id ? 'selected' : ''); ?>>
                                            <?php echo e($user->nama_lengkap); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </form>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-2 p-md-3 bg-light border-bottom">
                                <div class="d-flex flex-wrap gap-1 gap-md-2 align-items-center">
                                    <div class="form-check me-1 me-md-2 p-0 d-flex align-items-center">
                                        <input class="form-check-input me-1" type="checkbox" id="selectAllAssignedMaster" style="margin-top: 0;">
                                        <label class="form-check-label small" for="selectAllAssignedMaster">Pilih</label>
                                    </div>

                                    <form action="<?php echo e(route('admin.assignment.transfer')); ?>" method="POST" id="transferForm"
                                        class="d-flex gap-1 flex-grow-1">
                                        <?php echo csrf_field(); ?>
                                        <div id="transferLeadIds"></div>
                                        <select name="marketing_id" class="form-select form-select-sm"
                                            style="min-width: 120px;" required>
                                            <option value="">Oper ke...</option>
                                            <?php $__currentLoopData = $marketingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->nama_lengkap); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                        <button type="submit" class="btn btn-warning btn-sm text-nowrap px-2">
                                            <i class="bi bi-arrow-left-right"></i> <span class="d-none d-sm-inline">Oper</span>
                                        </button>
                                    </form>

                                    <form action="<?php echo e(route('admin.assignment.delete')); ?>" method="POST" id="deleteForm"
                                        onsubmit="return confirm('Yakin hapus lead yang dipilih?')">
                                        <?php echo csrf_field(); ?>
                                        <div id="deleteLeadIds"></div>
                                        <button type="submit" class="btn btn-outline-danger btn-sm px-2">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <?php if($assignedLeads->count() > 0): ?>
                                <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="sticky-top" style="top: 0;">
                                            <tr style="background-color: #0f3d2e; color: #fff; font-size: 0.875rem; font-weight: 600;">
                                                <th style="width: 35px; background-color: #0f3d2e; color: #fff; padding-left: 0.5rem;" class="ps-2 ps-md-3">#</th>
                                                <th style="background-color: #0f3d2e; color: #fff;">Customer</th>
                                                <th style="background-color: #0f3d2e; color: #fff;">Marketing</th>
                                                <th class="d-none d-md-table-cell" style="background-color: #0f3d2e; color: #fff;">Follow-up</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $assignedLeads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr class="small-mobile-row">
                                                    <td class="ps-2 ps-md-3">
                                                        <input class="form-check-input assigned-checkbox" type="checkbox"
                                                            value="<?php echo e($lead->id); ?>">
                                                    </td>
                                                    <td>
                                                        <a href="<?php echo e(route('admin.leads.show', $lead)); ?>"
                                                            class="text-decoration-none fw-bold text-dark d-block text-truncate" style="max-width: 140px;">
                                                            <?php echo e($lead->nama_customer); ?>

                                                        </a>
                                                        <div class="d-flex align-items-center gap-1">
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
                                                            <div class="d-md-none small text-muted">
                                                                <?php if($lead->tgl_next_followup): ?>
                                                                    <?php echo e($lead->tgl_next_followup->format('d/m')); ?>

                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge" style="background-color: rgba(15,61,46,0.1); color: #0f3d2e; border: 1px solid rgba(15,61,46,0.2);">
                                                            <?php echo e($lead->assignedUser->nama_lengkap ?? '-'); ?>

                                                        </span>
                                                    </td>
                                                    <td class="d-none d-md-table-cell">
                                                        <?php if($lead->tgl_next_followup): ?>
                                                            <?php if($lead->isOverdue()): ?>
                                                                <span class="text-danger small"><i class="bi bi-exclamation-circle"></i>
                                                                    <?php echo e($lead->tgl_next_followup->format('d/m/y')); ?></span>
                                                            <?php elseif($lead->isDueToday()): ?>
                                                                <span class="text-warning small fw-bold"><i class="bi bi-clock"></i> Hari
                                                                    ini</span>
                                                            <?php else: ?>
                                                                <span
                                                                    class="text-muted small"><?php echo e($lead->tgl_next_followup->format('d/m/y')); ?></span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="text-muted small">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="p-2 p-md-3 border-top">
                                    <?php echo e($assignedLeads->links()); ?>

                                </div>
                            <?php else: ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                    <p>Belum ada lead yang di-assign.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Marketing Team List -->
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h6 class="mb-0 fw-bold">
                                <i class="bi bi-people-fill me-2" style="color: #0f3d2e;"></i> Tim Marketing
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <?php $__currentLoopData = $marketingUsers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="col-md-6 border-end border-bottom">
                                        <div class="p-3 d-flex justify-content-between align-items-center h-100">
                                            <div>
                                                <div class="fw-bold"><?php echo e($user->nama_lengkap); ?></div>
                                                <small class="text-muted">@</small><small><?php echo e($user->username); ?></small>
                                            </div>
                                            <div class="text-end">
                                                <div class="h5 mb-0" style="color: #0f3d2e;"><?php echo e($user->leads_count); ?></div>
                                                <small class="text-muted small">Leads</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Per Page Selector at Bottom -->
    <div class="mt-4 mb-2 d-flex justify-content-center justify-content-md-end">
        <div class="bg-white p-2 rounded shadow-sm border d-flex align-items-center gap-2">
            <form action="<?php echo e(url()->current()); ?>" method="GET" class="d-flex align-items-center gap-2 mb-0">
                <input type="hidden" name="marketing_filter" value="<?php echo e(request('marketing_filter')); ?>">
                <span class="text-muted small fw-bold">Tampilkan:</span>
                <select name="per_page" onchange="this.form.submit()" class="form-select form-select-sm"
                    style="width: auto;">
                    <option value="50" <?php echo e($perPage == 50 ? 'selected' : ''); ?>>50</option>
                    <option value="100" <?php echo e($perPage == 100 ? 'selected' : ''); ?>>100</option>
                    <option value="500" <?php echo e($perPage == 500 ? 'selected' : ''); ?>>500</option>
                    <option value="1000" <?php echo e($perPage == 1000 ? 'selected' : ''); ?>>1000</option>
                </select>
                <span class="text-muted small">data per halaman</span>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Select All - Unassigned
            const selectAllUnassignedMaster = document.getElementById('selectAllUnassignedMaster');
            const unassignedCheckboxes = document.querySelectorAll('.unassigned-checkbox');

            if (selectAllUnassignedMaster) {
                selectAllUnassignedMaster.addEventListener('change', function () {
                    unassignedCheckboxes.forEach(cb => cb.checked = this.checked);
                });
            }

            // Select All - Assigned
            const selectAllAssignedMaster = document.getElementById('selectAllAssignedMaster');
            const assignedCheckboxes = document.querySelectorAll('.assigned-checkbox');

            if (selectAllAssignedMaster) {
                selectAllAssignedMaster.addEventListener('change', function () {
                    assignedCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                        updateSelectedLeads();
                    });
                });
            }

            // Individual checkbox changes
            assignedCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectedLeads);
            });

            function updateSelectedLeads() {
                const transferContainer = document.getElementById('transferLeadIds');
                const deleteContainer = document.getElementById('deleteLeadIds');

                if (!transferContainer || !deleteContainer) return;

                transferContainer.innerHTML = '';
                deleteContainer.innerHTML = '';

                const checked = document.querySelectorAll('.assigned-checkbox:checked');
                checked.forEach(box => {
                    const input1 = document.createElement('input');
                    input1.type = 'hidden';
                    input1.name = 'lead_ids[]';
                    input1.value = box.value;
                    transferContainer.appendChild(input1);

                    const input2 = document.createElement('input');
                    input2.type = 'hidden';
                    input2.name = 'lead_ids[]';
                    input2.value = box.value;
                    deleteContainer.appendChild(input2);
                });
            }
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u861895257/domains/crm.zhafiravila.com/public_html/resources/views/admin/assignment/index.blade.php ENDPATH**/ ?>