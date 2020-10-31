@extends ('layouts.email-noAccount')
@section('title', 'email')


@section('subject') 
    LOAN APPROVAL NOTIFICATION for {{ $data['first_name'] }} {{ $data['last_name'] }} 
@endsection

@section('reference') {{ $data['id'] }} @endsection

@section('content')
<p>

Hello,<br><br>
{{ $data['first_name'] }} {{ $data['last_name'] }}'s application has been deleted .<br><br>
              <br><br>Thanks.<br><br>
           

@endsection