<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['paginator', 'currentPerPage' => 50]));

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

foreach (array_filter((['paginator', 'currentPerPage' => 50]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="flex items-center space-x-2 text-sm text-gray-700">
    <label for="per_page" class="font-medium">Show:</label>
    <select 
        id="per_page" 
        name="per_page" 
        onchange="updatePerPage(this.value)"
        class="px-2 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
    >
        <option value="10" <?php echo e($currentPerPage == 10 ? 'selected' : ''); ?>>10</option>
        <option value="25" <?php echo e($currentPerPage == 25 ? 'selected' : ''); ?>>25</option>
        <option value="50" <?php echo e($currentPerPage == 50 ? 'selected' : ''); ?>>50</option>
        <option value="100" <?php echo e($currentPerPage == 100 ? 'selected' : ''); ?>>100</option>
        <option value="200" <?php echo e($currentPerPage == 200 ? 'selected' : ''); ?>>200</option>
    </select>
    <span>per page</span>
</div>

<script>
function updatePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>
<?php /**PATH /opt/homebrew/var/www/payments_plus/admin/resources/views/components/per-page-selector.blade.php ENDPATH**/ ?>