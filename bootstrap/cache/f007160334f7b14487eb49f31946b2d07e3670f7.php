<?php $__env->startSection('title','register'); ?>
<?php $__env->startSection('content'); ?>

<form action="/register" method="POST">

<?php $token = urlencode(base64_encode((random_bytes(32))));
$_SESSION['token'] = $token;
?>


    <h2>Personal Details</h2> <br>
    <div id="personal" class="form-group"></div>

    <h2>Contact</h2>
    <div id="contact" class="form-group"></div>
    
    <h2>Work details</h2>
    <div id="work" class="form-group"></div>

    <input type="hidden" name="token" value= <?php echo e($token); ?>>

      <button type="submit" id="submit" class="btn btn-primary">Submit</button>
</form>

 

<?php $__env->stopSection(); ?>
<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\familyPlatform\resources\view/register.blade.php ENDPATH**/ ?>