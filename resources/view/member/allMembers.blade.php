@extends ('base2')
@section('title', 'MEMBER_PAGE')
@section('data-page-id', 'allMemberPage')
@section('content')
<br><br>
<form>
    {{--  <div class="form-group" style="margin-top: 30px;">  --}}
        <div class="mb-3 mt-3">
               <input type="text" placeholder="search" name="searchFamily" class="form-control" id="searchFamily">
         <p id="searchHidden"></p>
        </div>
      
    {{--  </div>  --}}
   
</form>


<div class="row" id="allMembers">

  
</div>

@endsection