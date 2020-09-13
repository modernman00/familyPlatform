<?php $__env->startSection('title','Olaoguns'); ?>
<?php $__env->startSection('content'); ?>

<header class="masthead">
  <div class="container">
    <div class="intro-text">
      
      <div class="intro-heading text-uppercase">THE OLAOGUNS</div>
      <div class="intro-lead-in">
          <form class="form-inline subscribeForm" method="POST" action="/subscribe" >
            <div class="input-group mb-3">
                <input type="email" class="form-control homePageSubscribe" size="80" placeholder="enter your email address" name="email"
                    id="email" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger"> Subscribe</button>
                </div>
            </div>
        </form>
      </div>

      <div class="rowo">

           <div class="colo ">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/message">Message</a>
          </div>
          <div class="colo">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/login">Login</a>
          </div>

           <div class="colo">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/about">About</a>
          </div>

          <div class="colo">
                   <a class="btn btn-warning btn-xl text-uppercase js-scroll-trigger opo" href="/register">Register</a>
          </div>
       

  
      </div>
  

    </div>
  </div>
</header>



   

    




<?php $__env->stopSection(); ?>
<?php echo $__env->make('base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\familyPlatform\resources\view/index.blade.php ENDPATH**/ ?>