@extends ('email')
@section('title', 'email')

@section('subject', $data['subject'])

@section('name') 
{{ $data['approverFirstName'] }}
@endsection

@section('content')

Hello {{ $data['approverFirstName'] }}, <br> <br>


I hope this message finds you well.<br><br> 

{{ $data['requesterFirstName'] }} {{ $data['requesterLastName'] }} sent you a request to join his family network <br><br>

<table role="presentation" style="border-collapse: collapse; width: 100%; max-width: 100%;" border="0">
  <tr>
    <td style="padding: 20px 0;display: block; margin: 0 auto;">
      <table role="presentation" style="max-width: 400px; width: 100%; max-width: 100%;">
        <tr>
          <td style="text-align: center;">
            <div style="padding: 0px; background-color: #f8f8f8; border-radius: 8px;">
              <p style="font-size: 16px; padding-bottom: 10px; font-weight: bold;">Family Request</p>
              <p></p>

              <img src={{ getenv("MIX_ROUTE_PICS") }}{{ $data['requesterProfileImg'] }} alt="Avatar" width="50" height="50" style="display: block; margin: 10px auto;">

              <p style="font-size: 18px; margin: 0;">{{ $data['requesterFirstName'] }} {{ $data['requesterLastName'] }}</p>

              <br><br>


              <div style="display: flex; justify-content: space-between; margin-top: 10px; margin-left: 70px margin-bottom: 10px;">

                <a href="{{ getenv('MIX_APP_URL2') }}member/request/{{ $data['requesterId'] }}/{{ $data['approverId'] }}/50/{{ $data['requesterFamCode'] }}/email" style="text-decoration: none; background-color: #4CAF50; color: white; border: none; padding: 8px 16px; border-radius: 4px; margin-top: 10px;">
                 Accept
                </a>

                <a href="{{ getenv("MIX_APP_URL2") }}member/request/{{ $data['requesterId'] }}/{{ $data['approverId'] }}/10/request/email"  
                style="text-decoration: none;background-color: #FF0000; color: rgb(157, 15, 15); border: none; padding: 8px 16px; border-radius: 4px;margin-top: 10px;">Decline
                </a>
                <br><br>
              </div>
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

{{-- </body>
</html> --}}





@endsection