@extends ('base2')
@section('title', 'MEMBER_PAGE')
@section('data-page-id', 'allMemberPage')
@section('content')
<br><br>
<form>
    <div class="form-group" style="margin-top: 30px;">
         <input type="text" placeholder="search" name="searchFamily" class="form-control" id="searchFamily">
    </div>
   
</form>
 <br>
<div class="row allMember_card">

    @foreach($result as $result)

    <div class="card col-sm-4">
        <img class="card-img-top" src="img/profile/{{ $result['img'] }}" alt="{{ $result['img'] }}" height="250" width="200">
        <div class="card-body">
            <h4 class='card-title'>{{ $result['firstName'] }} {{ $result['lastName'] }}</h4>
            <p class="card-text"><b>Alias:</b> {{ $result['alias'] }} 
            <br> <b>Father:</b>  {{ $result['fatherName'] }} 
            <br> <b>Mother:</b> {{ $result['motherName'] }} 
            @if($result['spouseName'])
            <br> <b>Spouse:</b> {{ $result['spouseName'] }} 
            @endif
            <br> <b>Siblings:</b>  {{ $result['sibling_name'] }}
            <br> <b>Contact:</b>  {{ $result['email'] }} | {{ $result['mobile'] }} </p>

            <a href="/setProfile?id={{ $result['id'] }}" class="btn btn-primary stretched-link">See Profile</a>

             {{--  <a href="/setProfile?id={{ $result['id'] }}" class="btn btn-primary ">See Profile</a>  --}}

            {{--  <a href="/organogram?id={{ $result['id'] }}" class="btn btn-primary ">See Organogram</a>  --}}


        </div>
    </div>

    @endforeach
</div>

@endsection