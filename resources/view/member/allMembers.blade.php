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
        <img class="card-img-top" src="img/profile/{{ $result['img'] }}" alt="{{ $result['img'] }}" height="250"
            width="200">
        <div class="card-body">
            <h4 class='card-title'>{{ $result['firstName'] }} {{ $result['lastName'] }}</h4>
            <p class="card-text">Alias: {{ $result['alias'] }} 
            <br> Father: {{ $result['fatherName'] }} 
            <br> Mother: {{ $result['motherName'] }} 
            @if($result['spouseName'])
            <br> Spouse: {{ $result['spouseName'] }} 
            @endif
            <br> Siblings: {{ $result['sibling_name'] }}
            <br> contact: {{ $result['email'] }} | {{ $result['mobile'] }} </p>
            <a href="/setProfile?id={{ $result['id'] }}" class="btn btn-primary stretched-link">See Profile</a>
        </div>
    </div>

    @endforeach
</div>

@endsection