<?php $__env->startSection('title','next step'); ?>

<?php $__env->startSection('content'); ?>

<h1> Ref: <?php echo e($_SESSION['id']); ?> </h1><br><br>
    <h3><?php echo e($_SESSION['firstName']); ?>, thanks for the applications.</h3> 

<?php $__env->stopSection(); ?>
<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\familyPlatform\resources\view/registration/nextStep.blade.php ENDPATH**/ ?>