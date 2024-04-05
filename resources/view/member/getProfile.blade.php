@extends ('layouts.w3s_member')
@section('title', 'Get Profile')
@section('content')

<!-- About section -->
<div class="w3-container w3-dark-grey w3-center w3-text-light-grey w3-margin-top w3-padding-32" id="about">
  {{-- <div class="w3-panel w3-blue w3-round-xlarge">



  </div> --}}
<br><br>
  <h4> <a href="/organogram?id={{ $data['id'] }}">  
  {{ $data['firstName'] }} {{ $data['lastName'] }}'s FAMILY TREE
    <i class="fa fa-hand-pointer-o" aria-hidden="true"></i>
  </a>


  </h4>


  <img src="/public/img/profile/{{ $data['img'] }}" alt="Me" class="w3-image w3-padding-32" width="400" height="400">


    <h4><b>{{ $data['firstName'] }} {{ $data['lastName'] }}</b></h4>
    <h5>
      {{ $data['firstName'] }} {{ $data['lastName'] }} lives in {{ $data['country'] }}


    </h5>
  
     <h5>Father:  {{ $data['fatherName'] }}</h5>
      <h5>Mother:  {{ $data['motherName'] }}</h5>
       <h5>Partner:  {{ $data['spouseName'] }}</h5>
        {{-- <p>Occupation:  {{ $data['occupation'] }}</p> --}}
         <h5>BirthDate:  {{ $data['day'] }} / {{ $data['month'] }}</h5>
    Contact details:
    <h5>Email: <a href="mailto:{{ $data['email'] }}">{{ $data['email'] }}</a></h5>
    <h5>Tel:  {{ $data['mobile'] }}</h5>



  <!-- End page content -->
</div>
@endsection


