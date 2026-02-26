<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

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

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusValue = $status->value ?? $status;
    $badgeStyle = match($statusValue) {
        'New' => 'background-color: #6c757d; color: #fff;',
        'Cold' => 'background-color: #0dcaf0; color: #000;',
        'Warm' => 'background-color: #ffc107; color: #000;',
        'Hot' => 'background-color: #dc3545; color: #fff;',
        'Deal' => 'background-color: #198754; color: #fff;',
        default => 'background-color: #6c757d; color: #fff;',
    };
?>

<span class="badge" style="<?php echo e($badgeStyle); ?>"><?php echo e($statusValue); ?></span>
<?php /**PATH /home/u861895257/domains/crm.zhafiravila.com/public_html/resources/views/components/badge-status.blade.php ENDPATH**/ ?>