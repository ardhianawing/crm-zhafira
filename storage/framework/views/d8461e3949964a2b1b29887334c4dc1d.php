<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['type' => 'info', 'message' => '']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['type' => 'info', 'message' => '']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="alert alert-<?php echo e($type); ?> alert-dismissible fade show" role="alert">
    <?php if($type === 'success'): ?>
        <i class="bi bi-check-circle me-2"></i>
    <?php elseif($type === 'danger'): ?>
        <i class="bi bi-exclamation-triangle me-2"></i>
    <?php elseif($type === 'warning'): ?>
        <i class="bi bi-exclamation-circle me-2"></i>
    <?php else: ?>
        <i class="bi bi-info-circle me-2"></i>
    <?php endif; ?>
    <?php echo e($message); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php /**PATH /home/u861895257/domains/crm.zhafiravila.com/public_html/resources/views/components/alert.blade.php ENDPATH**/ ?>