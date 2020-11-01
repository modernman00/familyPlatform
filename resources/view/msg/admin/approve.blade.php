@extends ('layouts.email-noAccount')
@section('title', 'email')

{{--  
<b> APPLICATION REF : {{ $data['id'] }} </b>
<br />
<br>  --}}

@section('subject') 
    MEMBERSHIP APPROVAL NOTIFICATION for {{ $data['firstName'] }} {{ $data['lastName'] }} 
@endsection

@section('reference') {{ $data['id'] }} @endsection

@section('content')
<p>

Hello,<br><br>
{{ $data['firstName'] }} {{ $data['lastName'] }}'s application has been approved .<br><br>
            Therefore it can now be managed the customer account from admin page on the portal.  <br><br>Thanks.<br><br>
           

@endsection