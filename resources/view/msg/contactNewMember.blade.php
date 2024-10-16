@extends ('email')
@section('title', 'email')

@section('subject', "Apply to join the {$data['name']} Family Network")

@section('name') 
{{ $data['name'] }}
@endsection

@section('content')

Hello {{ $data['name'] }}, <br> <br>


I hope this message finds you well.<br><br> {{ $data['yourName'] }} has recently joined your family network and highly recommends that you do the same. Your family network provides a wonderful platform for connecting with other family members, exchanging messages, exploring the family tree structure, and receiving important event alerts. <br><br>

To join the family network and start enjoying these benefits, simply go to our website below: <br><br>


<a href={{ getenv('APP_URL') }}register >JOIN YOUR FAMILY NETWORK</a></p>
<b>Please, use this family code {{ $data['familyCode'] }} in your application</b> <br><br>

We are excited to have you on board and look forward to seeing you become a part of our vibrant family network. Should you have any questions or need assistance with the registration process, please don't hesitate to reach out to our dedicated Admin Team. <br><br>

Thank you for taking the time to strengthen our family bonds. We can't wait to reconnect and share memorable moments together.

@endsection