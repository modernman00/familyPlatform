<?php $__env->startSection('title', 'new applications'); ?>

<?php $__env->startSection('content-title', " New Applications"); ?>

<?php $__env->startSection('content'); ?>

<div class="table-container">
    <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
    <thead class="has-background-primary">
        <tr class="has-text-white-bis">
            <th>No</th>
            <th>ID</th>
            <th>Date</th>
            <th>Name</th>
            <th>Alias</th>
            <th>Spouse</th>
            <th>Father</th>
            <th>Mother</th>
            <th>Birth Date</th>
            <th>Mobile</th>
            <th>Country</th>
            
            <th>Email</th>
        </tr>
    </thead>

    <tbody>
        <?php $__currentLoopData = $result; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td> <?php echo e($no++); ?> </td>
             <td> <?php echo e($data['id']); ?> </td>
             
              <td> <?php echo e($data['created_at']); ?> </td>
               <td> <?php echo e($data['firstName']); ?> <?php echo e($data['lastName']); ?> </td>
                <td> <?php echo e($data['alias']); ?> </td>
                 <td> <?php echo e($data['spouse']); ?> </td>
                  <td> <?php echo e($data['fatherName']); ?> </td>
                   <td> <?php echo e($data['motherName']); ?> </td>
                    <td> <?php echo e($data['birthDate']); ?> </td>
                     <td> <?php echo e($data['mobile']); ?> </td>
                      <td> <?php echo e($data['country']); ?> </td>
                 <td> <?php echo e($data['email']); ?> </td>

                   <td>
                    <a href="/admin/approval?id=<?php echo e($data['id']); ?>"
                    data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to approve this application?');"
                              title="Approve">                               <i class="far fa-thumbs-up fa-lg" style="color:#264A0A">
                                </i>
                            </a>
                        </td>
                          <td>
                              <a href="/admin/decline?id=<?php echo e($data['id']); ?>" data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to decline this application?');"
                              title="Decline">
                            <i class="far fa-thumbs-up fa-lg fa-rotate-180" style="color:#A21016"></i>
                                </a>
                        </td>
                          <td><a href="/admin/cancel?id=<?php echo e($data['id']); ?>"
                          data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to cancel this application?');"
                              title="Cancel">
                          <i class="far fa-window-close fa-lg" style="color:#F00A0A"></i>
                          </a>
                        </td>
                        <td><a href="/admin/delete?id=<?php echo e($data['id']); ?>"
                        data-toggle="tooltip"
                              onClick="javascript: return confirm('Are you sure you want to delete this application?');"
                              title="Delete">
                            <i class="fas fa-trash-alt fa-lg" style="color:#F00A0A"></i>
                            </a>
                        </td>

        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>




    </tbody>
    </table>


    <?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.base', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\familyPlatform\resources\view/admin/ReviewApps.blade.php ENDPATH**/ ?>