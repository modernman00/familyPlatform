@extends ('email')
@section('title', 'email')

@section('subject', 'Apply to join the OLAOGUN Family Network')

@section('name') 
{{ $data['name'] }}
@endsection

@section('content')

Hello {{ $data['name'] }}, <br> <br>

Trust you are doing well. <br><br> 

{{ $data['yourName'] }} has just joined your family network and recommended that you do the same. On the family network, you will be able to find other family members, exchange messages, see the family tree structure and get alerts on important events <br><br>

Please, follow the link below to join the family network. <br><br>

{{ getenv('APP_URL') }}register <br><br>

We will look forward to seeing you join your family network. <br><br>

@endsection