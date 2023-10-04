@extends ('base2')
@section('title', 'MEMBER_PAGE')
@section('data-page-id', 'allMemberPage')
@section('content')
<br><br>

   
<form>
  
        <div class="mb-3 mt-1">
               <input type="text" placeholder="search the family network" name="searchFamily" class="form-control form-control-lg" id="searchFamily">
         <p id="searchHidden"></p>
        </div>
 
</form>
 <div id="setLoader" class="loader">

    </div>




<div class="row" id="allMembers">

  
</div>

@endsection