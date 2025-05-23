@extends('baseBulma')
@section('title', 'Register')

@section('data-page-id', 'register')
@section('content')
<link rel="stylesheet" type="text/css" href="https://kraaden.github.io/autocomplete/autocomplete.css">

<div class="styleForm" style="margin-top: 2rem;">

<img src={{ getenv('APP_LOGO')}} alt="logo" class="mb-3 form__login__logo">


<div class="columns">
  <div class="column" style="text-align:center; margin-left:10%; margin-right:10%;">
    <h3 class="title is-3"> Register To Join Your Family Network </h3>

  </div>

</div>

<div id="setLoader"></div>

<div class="notification" id="register_notify_div" style="display: none;">
  <p id="register_notify_div_msg"></p>
</div>

<form action="/register" method="POST" class="register" id="register" enctype="multipart/form-data" autocomplete="off">

  @php

  if(isset($registerPostData)) {
  echo "<h1> IT WORKED SO WELL </h1>";
  }

  $formArray = [
  // PERSONAL
  'Personal Information' => 'title',

  // names

  'name' => [
  'mixed',
  'label' => ['first Name', 'last Name', 'Family code'],
  'attribute' => ['firstName', 'lastName', 'famCode'],
  'placeholder' => ['Toyin', 'Edwars', 'check your email for the code '],
  'inputType' => ['text', 'text', 'text'],
  'value' => [
  isset($registerPostData['firstName']) ? $registerPostData['firstName'] : '',
  isset($registerPostData['lastName']) ? $registerPostData['lastName'] : '',
  isset($registerPostData['famCode']) ? $registerPostData['famCode'] : ''
  ],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-barcode"></i>'
  ]
  ],

  // BIRTHDAY

  'date_of_birth' => 'birthday',

  // married status and gender

  'married_gender' => [
  'mixed',
  'label' => ['Marital status', 'gender', 'maiden name'],
  'attribute' => ['maritalStatus', 'gender', 'maidenName'],
  'placeholder' => ['marital status', "gender",'maiden name'],
  'inputType' => ['select','select', 'text'],
  'options' => [
  ['select', 'Yes', 'No'],
  ['select', 'Male', 'Female'],
  ],
  'icon' => [
  '<i class="far fa-kiss-wink-heart"></i>',
  '<i class="fas fa-user-friends"></i>',
  '<i class="fas fa-user-friends"></i>',
  ]
  ],
  // spouse

  'spouse' => [
  'mixed',
  'label' => ["Spouse's name", "Spouse's Email","Spouse's mobile"],
  'attribute' => ['spouseName', 'spouseEmail', 'spouseMobile'],
  'placeholder' => ['Toyin', "toyin@gmail.com",'23480364168089'],
  'inputType' => ['text','email', 'text'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-envelope-square"></i>',
  '<i class="fas fa-user"></i>'
  ]
  ],


  'Parent' => 'title',

  // mother

  'mother' => [
  'mixed',
  'label' => ["mother's name", "mother's email", "mother's mobile"],
  'attribute' => ['motherName', 'motherEmail','motherMobile'],
  'placeholder' => ['Toyin Olaogun', "mother@yahoo.com", '23480364168089'],
  'inputType' => ['text', 'email', 'text'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-envelope-square"></i>',
  '<i class="fas fa-mobile-alt"></i>',
  ]
  ],

  // father

  'father' => [
  'mixed',
  'label' => ["Father's name", "Father's email", "Father's mobile"],
  'attribute' => ['fatherName','fatherEmail', 'fatherMobile' ],
  'placeholder' => ['Yommy Olaogun',"yomi@email.com", '447809789098'],
  'inputType' => ['text', 'text', 'text'],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-envelope-square"></i>',
  '<i class="fas fa-mobile-alt"></i>'
  ]
  ],

  'children and siblings' => 'title',

  // kids

  'childrenAndSiblings' => [
  'select-many',
  'label' => ['Numbers of children', 'Number of Siblings'],
  'attribute' => ['kids', 'siblings'],
  // 'inputType' => ['select', 'select'],
  'options' => [
  ['select', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
  ['select', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
  ],
  'icon' => [
  '<i class="fas fa-child"></i>',
  '<i class="fas fa-user-friends"></i>'
  ]
  ],



  // CONTACT INFORMATION
  'Contact Information' => 'title',

  // COUNTRY, EMAIL N MOBILE

  'country_email_mobile' => [
  'mixed',
  'label' => ["Country", "Email", 'Mobile'],
  'attribute' => ['country', 'email', 'mobile'],
  'placeholder' => ['Your first line of address', 'toyin@yahoo.com', 'include the area code - 234 or 1 or 44'],
  'inputType' => ['select', 'text', 'text'],
  'options' => [
  [
  'select',
  "United Kingdom",
  "United States",
  "Nigeria",
  "Canada",
  "Afghanistan",
  "Albania",
  "Algeria",
  "Andorra",
  "Angola",
  "Antigua and Barbuda",
  "Argentina",
  "Armenia",
  "Australia",
  "Austria",
  "Azerbaijan",
  "Bahamas",
  "Bahrain",
  "Bangladesh",
  "Barbados",
  "Belarus",
  "Belgium",
  "Belize",
  "Benin",
  "Bhutan",
  "Bolivia",
  "Bosnia and Herzegovina",
  "Botswana",
  "Brazil",
  "Brunei",
  "Bulgaria",
  "Burkina Faso",
  "Burundi",
  "Cambodia",
  "Cameroon",
  "Cape Verde",
  "Central African Republic",
  "Chad",
  "Chile",
  "China",
  "Colombia",
  "Comoros",
  "Congo (Brazzaville)",
  "Congo (Kinshasa)",
  "Costa Rica",
  "Côte d'Ivoire",
  "Croatia",
  "Cuba",
  "Cyprus",
  "Czech Republic",
  "Denmark",
  "Djibouti",
  "Dominica",
  "Dominican Republic",
  "East Timor",
  "Ecuador",
  "Egypt",
  "El Salvador",
  "Equatorial Guinea",
  "Eritrea",
  "Estonia",
  "Ethiopia",
  "Fiji",
  "Finland",
  "France",
  "Gabon",
  "Gambia",
  "Georgia",
  "Germany",
  "Ghana",
  "Greece",
  "Grenada",
  "Guatemala",
  "Guinea",
  "Guinea-Bissau",
  "Guyana",
  "Haiti",
  "Honduras",
  "Hungary",
  "Iceland",
  "India",
  "Indonesia",
  "Iran",
  "Iraq",
  "Ireland",
  "Israel",
  "Italy",
  "Jamaica",
  "Japan",
  "Jordan",
  "Kazakhstan",
  "Kenya",
  "Kiribati",
  "North Korea",
  "South Korea",
  "Kosovo",
  "Kuwait",
  "Kyrgyzstan",
  "Laos",
  "Latvia",
  "Lebanon",
  "Lesotho",
  "Liberia",
  "Libya",
  "Liechtenstein",
  "Lithuania",
  "Luxembourg",
  "Macedonia",
  "Madagascar",
  "Malawi",
  "Malaysia",
  "Maldives",
  "Mali",
  "Malta",
  "Marshall Islands",
  "Mauritania",
  "Mauritius",
  "Mexico",
  "Micronesia",
  "Moldova",
  "Monaco",
  "Mongolia",
  "Montenegro",
  "Morocco",
  "Mozambique",
  "Myanmar",
  "Namibia",
  "Nauru",
  "Nepal",
  "Netherlands",
  "New Zealand",
  "Nicaragua",
  "Niger",
  "Norway",
  "Oman",
  "Pakistan",
  "Palau",
  "Panama",
  "Papua New Guinea",
  "Paraguay",
  "Peru",
  "Philippines",
  "Poland",
  "Portugal",
  "Qatar",
  "Romania",
  "Russia",
  "Rwanda",
  "Saint Kitts and Nevis",
  "Saint Lucia",
  "Saint Vincent and the Grenadines",
  "Samoa",
  "San Marino",
  "Sao Tome and Principe",
  "Saudi Arabia",
  "Senegal",
  "Serbia",
  "Seychelles",
  "Sierra Leone",
  "Singapore",
  "Slovakia",
  "Slovenia",
  "Solomon Islands",
  "Somalia",
  "South Africa",
  "South Sudan",
  "Spain",
  "Sri Lanka",
  "Sudan",
  "Suriname",
  "Swaziland",
  "Sweden",
  "Switzerland",
  "Syria",
  "Taiwan",
  "Tajikistan",
  "Tanzania",
  "Thailand",
  "Togo",
  "Tonga",
  "Trinidad and Tobago",
  "Tunisia",
  "Turkey",
  "Turkmenistan",
  "Tuvalu",
  "Uganda",
  "Ukraine",
  "United Arab Emirates",
  "Uruguay",
  "Uzbekistan",
  "Vanuatu",
  "Vatican City",
  "Venezuela",
  "Vietnam",
  "Yemen",
  "Zambia",
  "Zimbabwe",
  ]

  ],
  'icon' => [
  '<i class="fas fa-user"></i>',
  '<i class="fas fa-envelope-square"></i>',
  '<i class="fas fa-mobile-alt"></i>',
  ]
  ],

  // INTEREST

  // 'interest' => 'title',

  // 'your_nterest' => [
  // 'mixed',
  // 'label' => ['Favourite Sport', 'Football team', 'Passion'],
  // 'attribute' => ['favSport', 'footballTeam', 'passion'],
  // 'placeholder' => ['Football, Tennis, F1', 'Chelsea, Liverpool', 'singing or tech or travelling'],
  // 'inputType' => ['text', 'text', 'text'],
  // 'icon' => [
  // '<i class="fas fa-swimmer"></i>',
  // '<i class="	far fa-futbol"></i>',
  // '<i class="fas fa-heart"></i>',
  // ]
  // ],

  // work info

  'work_information' => 'title',

  'work' => [
  'mixed',
  'label' => ['employment status', 'Occupation'],
  'attribute' => ['employmentStatus', 'occupation'],
  'placeholder' => ['null', 'Accountant, Housewife, Student, Business man etc'],
  'inputType' => ['select', 'text'],
  "options" => [
  ['select', 'Self-employed', 'Unemployed', 'Full-time-employment', 'Student']
  ],
  'icon' => [
  '<i class="fas fa-info-circle"></i>',
  '<i class="fas fa-user-md"></i>',
  ]
  ],

  'create an account' => 'title',
  // account

  'account' => [
  'mixed',
  'label' => ['Password', 'Confirm password'],
  'attribute' => ['password', 'confirm_password'],
  'placeholder' => ['xxxx', 'xxxx'],
  'inputType' => ['password', 'password'],
  'icon' => [
  '<i class="fas fa-user-secret"></i>',
  '<i class="fas fa-user-secret"></i>',
  // '<i class="fas fa-user-secret"></i>',
  ]
  ],

  'checkbox' => 'By submitting this form, you agree to the handling of your information as outlined in our <a href="/privacy">
    PRIVACY POLICY </a>',
  'token'=> 'token',

  'Submit form'=> [
            'button_captcha',
            'js'=> 'processForm',
            'key'=>getenv('RECAPTCHA_KEY'),
            'action'=> 'register'
          ], // 'submit' => 'button'

  // 'Submit Application' => 'submit'

  ];

  $form = new App\classes\BuildFormBulma($formArray);
  $form->genForm();

  @endphp

  {{-- THE FAMILY CODE MODAL --}}

  <div id="modal-familyCode" class="modal">
    <div class="modal-background"></div>

    <div class="modal-content">
      <div class="box">

        <div class="styleForm" style="margin-top: 2rem;">

          <img src={{ getenv('IMG_CONTRACT2')}} alt="logo" class="mb-4 form__login__logo" style="margin-left:43%; margin-bottom:5rem;">

          @php

          $formArray = [
          // 'Your experience on the Family Platform becomes even more enriching with the Family Code feature, which enables seamless connections among all members of your family. If you have not received a Code from a registered family member, worry not! You can create one below to bring your family together:' => 'p',

          'surname' => 'text'
          ];

          $form = new App\classes\BuildFormBulma($formArray);
          $form->genForm();
          @endphp

           <div class="field">
                    <p class="control">
                        <button name="btnFamCode" id="btnFamCode" type="button" class="button is-success btnFamCode is-large is-fullwidth">
                            Generate your family code
                        </button>
                    </p>
                </div>

          <br>


          <div class="field has-addons is-expanded has-addons-centered">
            <div class="control" id="createFamCode">

              <input class="input is-large" type="text" id="createCode" placeholder="Your family code will appear here" readonly>

            </div>

            <div class="control">

              <button id="copyIcon" class="button is-primary is-large">

                <i class="fa fa-copy" style="cursor: pointer;color:red"></i>
                <span id="clipCopy"> copy</span>

              </button>

            </div>
          </div>
          <br>

          <p class="subtitle">
            By generating a unique Family Code, you'll unlock the full potential of our platform, allowing your family to share moments, memories, and experiences like never before. Embrace the power of togetherness on the Family Platform today! </p>
          </p>

        </div>
      </div>
    </div>

    <button type="button" id="modal-close-code" class="modal-close is-large" aria-label="close"></button>
  </div>

</form>

</div>

@endsection
