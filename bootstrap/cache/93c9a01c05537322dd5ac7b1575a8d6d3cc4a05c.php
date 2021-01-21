<?php $__env->startSection('title', 'login'); ?>
<?php $__env->startSection('content'); ?>

<div class="styleForm" style="margin-top: 4rem;">

  <img src=<?php echo e(getenv('IMG_CONTRACT2')); ?> alt="logo" class="mb-4 form__login__logo" width="72" height="72"
    style="margin-left:40%">

  <h4 class="text-center "> Please sign in </h4>
  
  <form action="<?php echo e($_SESSION['loginType']); ?>" method="POST">
    <div class="form-group" id="login">

      <?php $token = urlencode(base64_encode((random_bytes(32))));
      $_SESSION['token'] = $token;
    ?>

    <a href="/login/forgot"> Forgot password? Please click this link</a>
    <br><br>

      <div class="checkbox mb-3">
        <label>
          <input type="checkbox" value="remember-me"> Remember me
        </label>
      </div>

      <input type="hidden" id="token" name="token" value=<?php echo e($token); ?>>

      <button type="submit" id="submit" class="btn btn-lg btn-primary btn-block">Submit</button>

    </div>
  </form>

</div>


<?php $__env->stopSection(); ?>
<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/familyPlatform/resources/view/login.blade.php ENDPATH**/ ?>