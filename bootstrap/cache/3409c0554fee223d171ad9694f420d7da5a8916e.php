<?php $__env->startSection('title','Register'); ?>
<?php $__env->startSection('data-page-id', 'registration'); ?>
<?php $__env->startSection('content'); ?>

<div class="styleForm">
  <form action="/register" method="POST" class="register" enctype="multipart/form-data">

    <?php
    $token = urlencode(base64_encode((random_bytes(32))));
    $_SESSION['token'] = $token;
    ?>

    <br><br><br>
    <div class="row" id="personal"></div><hr>
    <div id="contact"></div><hr>
    <div id="work"></div><hr>
    <div id="interest"></div><hr>
    <div id="account"></div><hr>

    
    <div class="form-group form-check">
      <input type="checkbox" class="form-check-input" id="checkbox">
      <label class="form-check-label" for="checkbox">By submitting this form, you agree handling your information as
        outlined in our <a href="/privacy"> PRIVACY POLICY</a></label>
    </div>
    
    <!-- Button trigger modal -->

    <input type="hidden" id="token" name="token" value=<?php echo e($token); ?>>

    <input class="btn btn-primary btn-lg btn-block submit" type="button" id="submit" name="submit" value="Submit Form">




    


  </form>

</div>


<?php if ($__env->exists("registration.include.kidsModal")) echo $__env->make("registration.include.kidsModal", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>



<?php $__env->stopSection(); ?>


<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/familyPlatform/resources/view/registration/register.blade.php ENDPATH**/ ?>