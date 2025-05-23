@extends ('email')
@section('title', 'email')

@section('subject', "{$data['approverName']} has {$data['decision']} your request")

{{-- @section('name') 
{{ $data['name'] }}
@endsection --}}

@section('content')

Hello {{ $data['firstName'] }}, <br> <br>


{{ $data['approverName'] }} has {{ $data['decision'] }} your request to join his family network <br><br>


@endsection