<?php $__env->startSection('title','ERROR'); ?>

<?php $__env->startSection('content'); ?>

<br><br>

<h1>THERE ARE ERRORS: </h1>

<?php $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

   <li> <?php echo e($error); ?></li>
    
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\familyPlatform\resources\view/error/form.blade.php ENDPATH**/ ?>