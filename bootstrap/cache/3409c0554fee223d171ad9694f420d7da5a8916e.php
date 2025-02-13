<?php $__env->startSection('title', 'Register'); ?>
<link rel="stylesheet" type="text/css" href="https://kraaden.github.io/autocomplete/autocomplete.css">

<?php $__env->startSection('data-page-id', 'registration'); ?>
<?php $__env->startSection('content'); ?>

<div class="columns">
  <div class="column" style="text-align:center; margin-left:20%; margin-right:20%;">
    <h2 class="title is-2"> Register To Join Your Family Network </h2>

  </div>

</div>

<div id="setLoader" tabindex="-1">
  <div id="loader">
    <div class="notification" id="register_notify">
      <p id="error"></p>
    </div>
  </div>
</div>



<form action="/register" method="POST" class="register" id="register" enctype="multipart/form-data" autocomplete="off">

  <?php

  $formArray = [
  // PERSONAL
  'Personal Information' => 'title',

  // names

  'name' => [
  'mixed',
  'label' => ['first Name', 'last Name', 'alias'],
  'attribute' => ['firstName', 'lastName', 'alias'],
  'placeholder' => ['Toyin', 'olaogun', "aka modernman"],
  'inputType' => ['text', 'text', 'text'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-user"></i>'
  ]
  ],

  // BIRTHDAY

  'date_of_birth' => 'birthday',

  // married status and gender

  'married_gender' => [
  'select-many',
  'label' => ['Marital status', 'gender'],
  'attribute' => ['maritalStatus', 'gender'],
  'options' => [
  ['select', 'Yes', 'No'],
  ['select', 'Male', 'Female']
  ],
  'icon' => [
  '<i class="far fa-kiss-wink-heart"></i>',
  '<i class="fas fa-user-friends"></i>',

  ]
  ],



  // spouse

  'spouse' => [
  'mixed',
  'label' => ["Spouse's name", "Spouse's mobile", "Spouse's Email"],
  'attribute' => ['spouseName', 'spouseMobile', 'spouseEmail'],
  'placeholder' => ['Toyin', '23480364168089', "toyin@gmail.com"],
  'inputType' => ['text', 'text', 'email'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-envelope-square"></i>'
  ]
  ],


  'Parent' => 'title',

  // mother

  'mother' => [
  'mixed',
  'label' => ["mother's name", "mother's mobile", "Mother's maiden name", "mother's email"],
  'attribute' => ['motherName', 'motherMobile', 'motherMaiden', 'motherEmail'],
  'placeholder' => ['Toyin Olaogun', '23480364168089', "surname before marriage", "mother@yahoo.com"],
  'inputType' => ['text', 'text', 'text', 'text'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-mobile-alt"></i>',
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-envelope-square"></i>'
  ]
  ],

  // father

  'father' => [
  'mixed',
  'label' => ["Father's name", "Father's mobile", "Father's email"],
  'attribute' => ['fatherName', 'fatherMobile', 'fatherEmail'],
  'placeholder' => ['Yommy Olaogun', '447809789098', "yomi@email.com"],
  'inputType' => ['text', 'text', 'text'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-mobile-alt"></i>',
  '<i class="fas fa-envelope-square"></i>'
  ]
  ],

  'children and siblings' => 'title',

  // kids

  'childrenAndSiblings' => [
  'select-many',
  'label' => ['Numbers of children', 'Number of Siblings'],
  'attribute' => ['kids', 'siblings'],
  'inputType' => ['select', 'select'],
  'options' => [
  ['select', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
  ['select', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
  ],
  'icon' => [
  '<i class="fas fa-child"></i>',
  '<i class="fas fa-user-friends"></i>',
  ]
  ],



  // CONTACT INFORMATION
  'Contact Information' => 'title',
  'address' => [
  'mixed',
  'label' => ["Address", "Postcode /zip code/area code", 'Region / State / District'],
  'attribute' => ['address', 'postcode', 'region'],
  'placeholder' => ['Your first line of address', 'SN2 3BF / 234', 'London / Lagos / New York'],
  'inputType' => ['text', 'text', 'text'],
  'icon' => [
  '<i class="far fa-address-book"></i>',
  '<i class="far fa-address-card"></i>',
  '<i class="far fa-address-card"></i>',
  ]
  ],

  // COUNTRY, EMAIL N MOBILE

  'country_email_mobile' => [
  'mixed',
  'label' => ["Country", "Email", 'Mobile'],
  'attribute' => ['country', 'email', 'mobile'],
  'placeholder' => ['Your first line of address', 'toyin@yahoo.com', 'include the area code - 234 or 1 or 44'],
  'inputType' => ['select', 'text', 'text'],
  'options' => ['select', 'Nigeria', 'UK', 'Canada', 'Europe', 'USA', 'China', 'Asia', 'Latin America'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-envelope-square"></i>',
  '<i class="fas fa-mobile-alt"></i>',
  ]
  ],

  // INTEREST

  'interest' => 'title',

  'your_nterest' => [
  'mixed',
  'label' => ['Favourite Sport', 'Football team', 'Passion'],
  'attribute' => ['favSport', 'footballTeam', 'passion'],
  'placeholder' => ['Football, Tennis, F1', 'Chelsea, Liverpool', 'singing or tech or travelling'],
  'inputType' => ['text', 'text', 'text'],
  'icon' => [
  '<i class="fas fa-swimmer"></i>',
  '<i class="	far fa-futbol"></i>',
  '<i class="fas fa-heart"></i>',
  ]
  ],

  // work info

  'work_information' => 'title',

  'work' => [
  'mixed',
  'label' => ['employment status', 'Occupation'],
  'attribute' => ['employmentStatus', 'occupation'],
  'placeholder' => ['null', 'Accountant, Housewife, Student, Business man etc'],
  'inputType' => ['select', 'text'],
  "options" => ['select', 'Self-employed', 'Unemployed', 'Full-time-employment', 'Student'],
  'icon' => [
  '<i class="fas fa-info-circle"></i>',
  '<i class="fas fa-user-md"></i>',
  ]
  ],

  'create an account' => 'title',
  // account

  'account' => [
  'mixed',
  'label' => ['Password', 'Confirm password', 'Secret word'],
  'attribute' => ['password', 'confirm_password', 'secretWord'],
  'placeholder' => ['xxxx', 'xxxx', 'one time security code'],
  'inputType' => ['password', 'password', 'password'],
  'icon' => [
  '<i class="fas fa-user-secret"></i>',
  '<i class="fas fa-user-secret"></i>',
  '<i class="fas fa-user-secret"></i>',
  ]
  ],

  'checkbox' => 'By submitting this form, you agree handling your information as outlined in our <a href="/privacy">
    PRIVACY POLICY </a>',
  'token'=> 'token',

  'Submit Application' => 'submit'

  ];

  $form = new App\classes\BuildFormBulma($formArray);
  $form->genForm();

  ?>

</form> <?php $__env->stopSection(); ?>
<?php echo $__env->make('baseBulma', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Applications/MAMP/htdocs/familyPlatform/resources/view/registration/register.blade.php ENDPATH**/ ?>