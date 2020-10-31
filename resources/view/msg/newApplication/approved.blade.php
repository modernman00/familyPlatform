@extends ('layouts.email-noAccount')
@section('title', 'email')

{{--  
<b> APPLICATION REF : {{ $data['id'] }} </b>
<br />
<br>  --}}

@section('subject') 
    LOAN APPROVAL NOTIFICATION for {{ $data['first_name'] }} {{ $data['last_name'] }} 
@endsection

@section('reference') {{ $data['id'] }} @endsection

@section('content')
<p>

Hello,<br><br>
{{ $data['first_name'] }} {{ $data['last_name'] }}'s application has been approved .<br><br>
            Therefore it can now be managed the customer account from admin page on the portal.  <br><br>Thanks.<br><br>
           

@endsection