@extends ('layouts.email-noAccount')
@section('title', 'email')

{{--  
<b> APPLICATION REF : {{ $data['id'] }} </b>
<br />
<br>  --}}

@section('subject', 'CANCELLATION OF LOAN APPLICATION')
@section('reference') {{ $data['id'] }} @endsection
@section('full name') 
{{ $data['first_name'] }} {{ $data['last_name'] }} 
@endsection
@section('content')
<p>

This is to inform you that your loan application has been cancelled. <br><br>Thanks for applying for our short term loan. <br><br> Please, feel free to re-apply again if the circumstance changes</b><br><br>

@endsection