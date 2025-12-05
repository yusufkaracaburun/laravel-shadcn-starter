<?php $__env->startSection('title', __('Forbidden')); ?>
<?php $__env->startSection('code', '403'); ?>
<?php $__env->startSection('message', __($exception->getMessage() ?: 'Forbidden')); ?>

<?php echo $__env->make('errors::minimal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/yusufkaracaburun/Sites/localhost/laravel/skeleton-1/modern-vue-starter-kit-auth/vendor/laravel/framework/src/Illuminate/Foundation/Exceptions/views/403.blade.php ENDPATH**/ ?>