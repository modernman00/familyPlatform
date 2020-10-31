<?php $__env->startSection('title','Register'); ?>
<?php $__env->startSection('content'); ?>


<div class="styleForm">

  <form action="/register" method="POST" class="register">

    <?php $token = urlencode(base64_encode((random_bytes(32))));
      $_SESSION['token'] = $token;
    ?>

    <br><br><br>

    <h2 class="text-center text-uppercase">Personal Details</h2>
    <div class="row" id="personal">
      
    </div>

    <h2 class="text-center text-uppercase">Contact</h2>
    <div id="contact"></div>

    <h2 class="text-center text-uppercase">Work details</h2>
    <div id="work"></div>

    <h2 class="text-center text-uppercase">Interests</h2>
    <div id="interest"></div>

    <h2 class="text-center text-uppercase">Create Account</h2>
    <div id="account"></div>


    <div class="form-group form-check">
      <input type="checkbox" class="form-check-input" id="checkbox">
      <label class="form-check-label" for="checkbox">By submitting this form, you agree handling your information as
        outlined in our <a href="/privacy"> PRIVACY POLICY</a></label>
    </div>

    <!-- Button trigger modal -->

    <input type="hidden" id="token" name="token" value=<?php echo e($token); ?>>

     <input class="btn btn-primary btn-lg btn-block submit" type="button" id="submit" name="submit"
        value="Submit Form">

     


        <br><br> 


  </form>

</div>


         <?php if ($__env->exists("registration.include.kidsModal")) echo $__env->make("registration.include.kidsModal", \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        


<?php $__env->stopSection(); ?>
<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\familyPlatform\resources\view/registration/register.blade.php ENDPATH**/ ?>