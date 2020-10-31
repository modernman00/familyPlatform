<?php $__env->startSection('title','next step'); ?>

<?php $__env->startSection('content'); ?>

<div class="jumbotron">
    <h1 class="display-3">Ref: <?php echo e($_SESSION['id']); ?> - NEXT STEP</h1>
    <p class="lead">Hello <?php echo e($_SESSION['firstName']); ?>, <br> Your application has been successfully submitted. Once reviewed by the admin team, a decision will be emailed to you within the next 24 hours. <br>If your application approved, then you should be able to log in to your account and access the family social network<br><br>
    
    Regards,<br>
    Admin team<br>
    <?php echo e(getenv("ADMIN_EMAIL")); ?></p>
    <hr class="my-2">
 
</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\familyPlatform\resources\view/registration/nextStep.blade.php ENDPATH**/ ?>