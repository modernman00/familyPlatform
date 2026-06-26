@extends('layouts.landing_layout')

@section('title', 'Register')
@section('data-page-id', 'register')

@section('extra_css')
<link rel="stylesheet" type="text/css" href="https://kraaden.github.io/autocomplete/autocomplete.css">
<style>
    .register-card {
        background: white;
        border-radius: 12px;
        padding: 3rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid var(--border-color);
        margin-bottom: 4rem;
    }
    
    .register-title {
        color: var(--brand-secondary);
        font-weight: 800;
        margin-bottom: 0.5rem;
    }
    
    .register-subtitle {
        color: var(--text-muted);
        margin-bottom: 2.5rem;
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    
    .modal.is-active {
        display: flex;
    }

    .modal-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 41, 130, 0.4);
        backdrop-filter: blur(8px);
    }
    
    .modal-content {
        position: relative;
        width: 100%;
        max-width: 500px;
        z-index: 1060;
        animation: modalFadeIn 0.3s ease-out;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0,0,0,0.3);
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        z-index: 1070;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    .modal-close:hover {
        background: rgba(0,0,0,0.5);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="register-card">
                <div class="text-center mb-5">
                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 64px;" class="mb-4">
                    <h1 class="register-title h2">Join Your Family Network</h1>
                    <p class="register-subtitle">Complete the steps below to create your secure family account</p>
                </div>

                @include('partials.loader', ['notificationId'=> 'register'])

                <form class="register" id="register" enctype="multipart/form-data" autocomplete="off">
                    @php
                        $formArray = [
                            'Personal Information' => 'title',
                            'name' => [
                                'mixed',
                                'label' => ['first Name', 'last Name', 'Family code <button type="button" id="generateFamilyCode" class="button is-small is-primary ms-2 js-modal-trigger" data-target="modal-familyCode" style="font-size: 0.7rem; padding: 0.2rem 0.5rem; vertical-align: middle;">Generate</button>'],
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
                            'date_of_birth' => 'birthday',
                            'married_gender' => [
                                'mixed',
                                'label' => ['Marital status', 'gender', 'maiden name'],
                                'attribute' => ['maritalStatus', 'gender', 'maidenName'],
                                'placeholder' => ['marital status', "gender",'maiden name'],
                                'inputType' => ['select','select', 'text'],
                                'options' => [
                                    ['select', 'Married', 'Single', 'Divorced', 'Seperated', 'Widowed'],
                                    ['select', 'Male', 'Female'],
                                ],
                                'icon' => [
                                    '<i class="far fa-kiss-wink-heart"></i>',
                                    '<i class="fas fa-user-friends"></i>',
                                    '<i class="fas fa-user-friends"></i>',
                                ]
                            ],
                            'spouse' => [
                                'mixed',
                                'label' => ["Spouse's name", "Spouse's Email","Spouse's mobile"],
                                'attribute' => ['spouse_name', 'spouse_email', 'spouse_mobile'],
                                'placeholder' => ['Toyin', "toyin@gmail.com",'23480364168089'],
                                'inputType' => ['text','email', 'text'],
                                'icon' => [
                                    '<i class="fas fa-user"></i>',
                                    '<i class="fas fa-envelope-square"></i>',
                                    '<i class="fas fa-user"></i>'
                                ]
                            ],
                            'Parent' => 'title',
                            'mother' => [
                                'mixed',
                                'label' => ["mother's name", "mother's email", "mother's mobile"],
                                'attribute' => ['mother_name', 'mother_email','mother_mobile'],
                                'placeholder' => ['Toyin Olaogun', "mother@yahoo.com", '23480364168089'],
                                'inputType' => ['text', 'email', 'text'],
                                'icon' => [
                                    '<i class="fas fa-user"></i>',
                                    '<i class="fas fa-envelope-square"></i>',
                                    '<i class="fas fa-mobile-alt"></i>',
                                ]
                            ],
                            'father' => [
                                'mixed',
                                'label' => ["Father's name", "Father's email", "Father's mobile"],
                                'attribute' => ['father_name','father_email', 'father_mobile' ],
                                'placeholder' => ['Yommy Olaogun',"yomi@email.com", '447809789098'],
                                'inputType' => ['text', 'text', 'text'],
                                'icon' => [
                                    '<i class="fas fa-user"></i>',
                                    '<i class="fas fa-envelope-square"></i>',
                                    '<i class="fas fa-mobile-alt"></i>'
                                ]
                            ],
                            'children and siblings' => 'title',
                            'childrenAndSiblings' => [
                                'select-many',
                                'label' => ['Numbers of children', 'Number of Siblings'],
                                'attribute' => ['children', 'sibling'],
                                'options' => [
                                    ['select', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                                    ['select', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                                ],
                                'icon' => [
                                    '<i class="fas fa-child"></i>',
                                    '<i class="fas fa-user-friends"></i>'
                                ]
                            ],
                            'Contact Information' => 'title',
                            'country_email_mobile' => [
                                'mixed',
                                'label' => ["Country", "Email", 'Mobile'],
                                'attribute' => ['country', 'email', 'mobile'],
                                'placeholder' => ['Your first line of address', 'toyin@yahoo.com', 'include the area code - 234 or 1 or 44'],
                                'inputType' => ['select', 'text', 'text'],
                                'options' => [
                                    ['select', "United Kingdom", "United States", "Nigeria", "Canada", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombia", "Comoros", "Congo (Brazzaville)", "Congo (Kinshasa)", "Costa Rica", "Côte d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "North Korea", "South Korea", "Kosovo", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Sudan", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"]
                                ],
                                'icon' => [
                                    '<i class="fas fa-user"></i>',
                                    '<i class="fas fa-envelope-square"></i>',
                                    '<i class="fas fa-mobile-alt"></i>',
                                ]
                            ],
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
                            'account' => [
                                'mixed',
                                'label' => ['Password', 'Confirm password'],
                                'attribute' => ['password', 'confirm_password'],
                                'placeholder' => ['xxxx', 'xxxx'],
                                'inputType' => ['password', 'password'],
                                'icon' => [
                                    '<i class="fas fa-user-secret"></i>',
                                    '<i class="fas fa-user-secret"></i>',
                                ]
                            ],
                            'checkbox' => 'By submitting this form, you agree to the handling of your information as outlined in our <a href="/privacy" class="text-decoration-none fw-bold" style="color: var(--brand-primary);">PRIVACY POLICY</a>',
                            'token'=> 'token',
                            'Submit form'=> [
                                'button_captcha',
                                'js'=> 'processForm',
                                'key'=>getenv('RECAPTCHA_KEY'),
                                'action'=> 'register'
                            ],
                        ];

                        $form = new \Src\BuildFormBulma($formArray);
                        $form->genForm();
                    @endphp

                    <div id="modal-familyCode" class="modal">
                        <div class="modal-background"></div>
                        <div class="modal-content">
                            <div class="box p-5" style="border-radius: 16px; border: none;">
                                <div class="text-center mb-4">
                                    <img src="{{ getenv('APP_LOGO') }}" alt="logo" style="height: 48px;" class="mb-3">
                                    <h3 class="fw-bold" style="color: var(--brand-secondary);">Generate Family Code</h3>
                                    <p class="text-muted small">Create a unique code to invite your family members</p>
                                </div>
                                
                                @php
                                    $codeFormArray = [
                                        'surname_id' => 'text'
                                    ];
                                    $codeForm = new \Src\BuildFormBulma($codeFormArray);
                                    $codeForm->genForm();
                                @endphp

                                <div class="field mt-4">
                                    <p class="control">
                                        <button name="btnFamCode" id="btnFamCode" type="button" class="button is-success btnFamCode is-large is-fullwidth">
                                            Generate Code
                                        </button>
                                    </p>
                                </div>

                                <div class="field has-addons is-expanded has-addons-centered mt-4">
                                    <div class="control is-expanded" id="createFamCode">
                                        <input class="input is-large" type="text" id="createCode" placeholder="Code will appear here" readonly style="background: #f8fafc;">
                                    </div>
                                    <div class="control">
                                        <button id="copyIcon" class="button is-primary is-large">
                                            <i class="fa fa-copy me-2"></i> Copy
                                        </button>
                                    </div>
                                </div>

                                <div class="mt-4 text-center">
                                    <p class="text-muted small">Share this code with your family members so they can join your secure network.</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="modal-close-code" class="modal-close is-large" aria-label="close"></button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p class="text-muted small">Already have an account? <a href="/login" class="fw-bold text-decoration-none" style="color: var(--brand-primary);">Log in</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

