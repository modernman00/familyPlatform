@extends('baseBulma')

@section('title', 'Register')
@section('data-page-id', 'registration')
@section('content')

<div class="columns">
    <div class="column" style="text-align:center; margin-left:20%; margin-right:20%;">
        <h1 class="title is-2">Register to join your family network</h1>
    </div>
</div>

  <form action="/register" method="POST" class="register" enctype="multipart/form-data">

  @php

    $formArray = [
      // PERSONAL 
            'Personal Information' => 'title',

            // names

            'name' => [
              'mixed',
              'label'=> ['first Name', 'last Name', 'alias'],
              'attribute'=> ['firstName', 'lastName', 'alias'],
              'placeholder'=> ['Toyin', 'olaogun', "aka modernman"],
              'inputType' => ['text', 'text', 'text'],
              'icon'=> [
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
              'label'=> ['Marital status', 'gender'],
              'attribute'=> ['maritalStatus', 'gender'],
              'options' => [
                ['select', 'Yes','No' ],
                ['select', 'Male', 'Female']
                ],
              'icon'=> [
                '<i class="fas fa-people-carry"></i>',
                '<i class="fas fa-user-friends"></i>',

              ]
            ],

            

            // spouse

              'spouse' => [
                'mixed',
                'label'=> ["Spouse's name", "Spouse's mobile", "Spouse's  Email"],
                'attribute'=> ['spouseName', 'spouseMobile', 'spouseEmail'],
                'placeholder'=> ['Toyin', '23480364168089', "toyin@gmail.com"],
                'inputType' => ['text', 'number', 'email'],
                'icon'=> [
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>'
                ]
              ],


   

                'Parent' => 'title',

            // mother

              'mother' => [
                'mixed',
                'label'=> ["mother's name", "mother's mobile", "Mother's maiden name"],
                'attribute'=> ['motherName', 'motherMobile', 'motherMaiden'],
                'placeholder'=> ['Toyin', '23480364168089', "surname before marriage"],
                'inputType' => ['text', 'number'],
                'icon'=> [
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>'
                ]
            ],

              // father

              'father' => [
                'mixed',
                'label'=> ["Father's name", "Father's mobile"],
                'attribute'=> ['fatherName', 'fatherMobile'],
                 'placeholder'=> ['Toyin', '23480364168089', "surname before marriage"],
                'inputType' => ['text', 'number'],
                'icon'=> [
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                ]
            ],


            'children and siblings' => 'title',
          
          

                        // kids

            'children' => [
              'select-many',
              'label'=> ['Numbers of children', 'Number of Siblings'],
              'attribute'=> ['kids', 'noSiblings'],
              'inputType' => ['select', 'select'],
              'options' => [
                ['select', 0, 1, 2, 3, 4, 5, 6 ],
                ['select', 0, 1, 2, 3, 4, 5, 6]
                ],
              'icon'=> [
                '<i class="fas fa-people-carry"></i>',
                '<i class="fas fa-user-friends"></i>',
              ]
            ],

            'addChildren' => 'empty', 


            'children2' => [
              'mixed',
              'label'=> ['Numbers of children', 'Number of Siblings'],
              'attribute'=> ['kids2', 'noSiblings2'],
                 'placeholder'=> ['Toyin',  "surname before marriage"],
              'inputType' => ['text', 'email'],
              'icon'=> [
                '<i class="fas fa-people-carry"></i>',
                '<i class="fas fa-user-friends"></i>',
              ]
            ],



        // CONTACT INFORMATION

           'Contact Information' => 'title',

           'address' => [
                'mixed',
                'label'=> ["Address", "Postcode /zip code/area code", 'Region / State / District'],
                'attribute'=> ['address', 'postcode', 'region'],
                 'placeholder'=> ['Your first line of address', 'SN2 3BF / 234', 'London / Lagos / New York'],
                'inputType' => ['text', 'text', 'text'],
                'icon'=> [
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                ]
            ],

            // COUNTRY, EMAIL N MOBILE

              'country_email_mobile' => [
                'mixed',
                'label'=> ["Country", "Email", 'Mobile'],
                'attribute'=> ['country', 'email', 'mobile'],
                'placeholder'=> ['Your first line of address', 'toyin@yahoo.com', 'include the area code - 234 or 1 or 44'],
                'inputType' => ['select', 'text', 'text'],
                'options' => ['select','Nigeria', 'UK', 'Canada', 'Europe', 'USA', 'China', 'Asia', 'Latin America'],
                'icon'=> [
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                ]
            ],

            // INTEREST

            'interest' => 'title',

            'your_nterest' => [
                'mixed',
                'label'=> ['Favourite Sport', 'Football team', 'Passion'],
                'attribute'=> ['favSport', 'footballTeam', 'passion'],
                'placeholder'=> ['Football, Tennis, F1', 'Chelsea, Liverpool', 'singing or tech or travelling'],
                'inputType' => ['text', 'text', 'text'],
                'icon'=> [
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                ]
            ],

            // work info

             'work_information' => 'title',

            'work' => [
                'mixed',
                'label'=> ['employment status', 'Occupation'],
                'attribute'=> ['employmentStatus', 'occupation'],
                'placeholder'=> ['null', 'Accountant, Housewife, Student, Business man etc'],
                'inputType' => ['select', 'text'],
                "options"=> ['select', 'Self-employed', 'Unemployed', 'Full-time-employment', 'Student'],
                'icon'=> [
                  '<i class="fas fa-user"></i>',
                  '<i class="fas fa-user"></i>',
                ]
            ],

            'create an account' => 'title',
          // account

            'account' => [
                'mixed',
                'label'=> ['Password', 'Confirm password', 'Secret word'],
                'attribute'=> ['password', 'confirm_password', 'secretWord'],
                'placeholder'=> ['xxxx', 'xxxx', 'one time security code'],
                'inputType' => ['password', 'password', 'password'],
                'icon'=> [
                  '<i class="fas fa-password"></i>',
                  '<i class="fas fa-password"></i>',
                  '<i class="fas fa-password"></i>',
                ]
            ],



            'checkbox' => 'By submitting this form, you agree handling your information as
        outlined in our <a href="/privacy"> PRIVACY POLICY</a>',

        'token'=> 'token',

            'Submit Application' => 'button'

    ];

$form = new App\classes\BuildFormBulma($formArray);
$form->genForm();

@endphp
  
</form>

@endsection