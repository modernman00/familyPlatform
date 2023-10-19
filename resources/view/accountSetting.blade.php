@extends ('layouts.w3s_member')
@section('title', 'ACCOUNT SETTING')
@section('data-page-id', 'AccountSettingPage')
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
<br><br>


<div class="container">

    <div class="styleForm" style="margin-top: 2rem;">

      <form action="/accountSetting" method="POST" class="accountSetting styleform_form" id="accountSettingForm" enctype="multipart/form-data">

          <div id="setLoader" class="loader" style="display: none;">
          </div>
          
        
              <p class="subtitle"> Your Account ID : {{ $accountData['id'] }} <br>
               Your Family Code : {{ $accountData['famCode'] }}</p>
              
                <div class="notification" id="accountSettingForm_notification" style="display: none;">

                <p id="accountSettingForm_notification_error"></p>
            </div>

            <div class="form-group">

              @php
              $acctId = "Your Account ID:  {$accountData['id']} ";

                  $formArray = [
                  

                    'Make Account Changes' => 'title',
 

                    'contact' => [
                      'mixed', 
                      'label' => ['mobile number', 'email', 'country'], 
                      'attribute' => ['mobile', 'email', 'country'], 
                      'inputType' => ['text', 'email', 'text'], 
                      'value' => [$accountData['mobile'], $accountData['email'], $accountData['country']],
                      'icon' => [ 
                        '<i class="fa fa-envelope-square"></i>', 
                        '<i class="fa fa-mobile"></i>',
                        '<i class="fa fa-user"></i>'
                      ] 
                    ],

                    // kids

                    'childrenAndSiblings' => [
                        'mixed',
                        'label' => ['Add Kids?', 'Add Siblings?'],
                        'attribute' => ['kids', 'siblings'],
                        'inputType' => ['select', 'select'],
                        'options' => [
                        ['select', 0, 1, 2, 3],
                        ['select', 0, 1, 2, 3]
                        ],
                        'icon' => [
                        '<i class="fa fa-child"></i>',
                        '<i class="fa fa-user"></i>'
                        ]
                    ],

                    'token' => 'token',
                    'button' => 'submit'

                






                  ];


                  $form = new App\classes\BuildFormBulma($formArray);
                  $form->genForm();

              @endphp

            </div>


      </form>

    </div>

  
</div>








@endsection