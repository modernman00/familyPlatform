<?php $__env->startSection('title', 'login'); ?>
<?php $__env->startSection('data-page-id', 'login'); ?>
<?php $__env->startSection('content'); ?>


<div class="styleForm" style="margin-top: 2rem;">

  <img src=<?php echo e(getenv('IMG_CONTRACT2')); ?> alt="logo" class="mb-4 form__login__logo" width="72" height="72" style="margin-left:40%; margin-bottom:5rem;">

  <form action="<?php echo e($formAction); ?>" method="POST" class="loginNow" id="loginNow" enctype="multipart/form-data" style="margin-left:15%; margin-right:15%;">

  <div id="setLoader" tabindex="-1">
  <div id="loader">
    <div class="notification" id="loginNow_notification">
      <p id="error"></p>
    </div>
  </div>
</div>
  
    <div class="form-group">

      <?php  

      $formArray = [ 'email' => 'email', 
      'password' => 'password', 
      // 'checkbox'=> 'Remember me', 
      'token' => 'token', 
      'button' => 'submit'];

      $form = new App\classes\BuildFormBulma($formArray);
      $form->genForm();

    ?>

    <label class="checkbox">
  <input type="checkbox" id="showPassword_id">
  Show Password
</label><br>

    <a href="/login/forgot"> Forgot password? Please click this link</a>
    <br><br>

    </div>
  </form>

</div>



<?php $__env->stopSection(); ?>
<?php echo $__env->make('baseBulma', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/familyPlatform/resources/view/login.blade.php ENDPATH**/ ?>