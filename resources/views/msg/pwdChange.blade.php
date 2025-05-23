@extends ('email')
@section('title', 'email')

@section('subject', 'SUBJECT: YOUR ACCOUNT PASSWORD HAS JUST BEEN CHANGED')

@section('content')

 Please, be informed that your login password has just been changed at this time {{ date('jS \of F Y h:i:sa', strtotime(date('Y-m-d h:i:sa'))) }}.<br><br> If you did not make this change, please change your password immediately. <br><br> If you have any question, please call our online team on the number below<br>

@endsection