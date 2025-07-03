<?php
    // The $schoolSettings variable is now available globally from the AppServiceProvider
    $logoPath = $schoolSettings->school_logo_path ?? null;
?>

<?php if($logoPath): ?>
    <img src="<?php echo e(asset('storage/' . $logoPath)); ?>" alt="<?php echo e($schoolSettings->school_name ?? 'School Logo'); ?>" <?php echo e($attributes); ?>>
<?php else: ?>
    <svg viewBox="0 0 316 316" xmlns="http://www.w3.org/2000/svg" <?php echo e($attributes); ?>>
        <path d="M305.8 81.1c-34.4-53.7-93.1-81.4-154.9-81.4C69.3 0 10.6 27.7-23.8 81.1L151 316l154.8-234.9z" fill="#FF2D20"/>
        <path d="M-23.8 81.1C10.6 27.7 69.3 0 151 0v316L-23.8 81.1z" fill="#2E343D"/>
    </svg>
<?php endif; ?><?php /**PATH C:\wamp64\www\school-portal-backend\resources\views/components/application-logo.blade.php ENDPATH**/ ?>