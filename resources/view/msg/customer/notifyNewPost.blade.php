@extends ('email')
@section('title', 'New post')

@section('subject', 'SUBJECT: NEW FAMILY POST')

@section('reference') Family Platform @endsection

@section('content')

<style>
  #toProfile {
    width: 100%;
    margin: 0 auto;
    text-decoration: none;
  }

  button {
    background-color: #4CAF50;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    cursor: pointer;
    width: 100%;
  }
</style>

  <p>
  

    Hello {{ $data['firstName'] }},

    <br><br>
  
      {{ $data['postOriginName'] }} just posted an update. <br>

    
    <button style="background-color: #4CAF50;
      color: white;
      padding: 14px 20px;
      margin: 8px 0;
      border: none;
      cursor: pointer;
      width: 100%;">
      <a href={{ $data['url'] }}
        login style="text-decoration: none;color: white;">
        Log in to check it out here
      </a>
    </button>

    <br><br>

    Thank you. 
      <br><br><br>



@endsection
