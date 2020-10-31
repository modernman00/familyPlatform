@extends ('layouts.email-noAccount')
@section('title', 'email')

@section('subject', 'LOAN APPLICATION DECISION')
@section('reference') {{ $data['id'] }} @endsection
@section('full name') 
{{ $data['first_name'] }} {{ $data['last_name'] }} 
@endsection
@section('content')
<p>

Thanks for applying for our short term loan. Unfortunately, based on the information you provided and a further review of your application, your loan application has been declined. <br><br> Your application was given full consideration through our Customer information and Affordability check system. This system takes into information provided directly by you, on your completed application form and credit referencing agency. <br><br>We understand that you may be unhappy with our decision and wish to discuss the matter further. If you remain un-satisfied, you do have the right to appeal, in writing through email to application@loaneasyfinance.com. <br> We will endeavour to address your concerns, however please be advised that we will not disclose details of our credit criteria or give specific reasons for decline.<br><br>
        Please rest assured that our decision to decline your application is known only to yourself and our team who dealt with this transaction and has not been disclosed to any outside organisation.<br><br>Please, feel free to re-apply again if the circumstance changes.</b><br><br>

@endsection