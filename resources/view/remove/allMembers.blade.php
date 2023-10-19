@extends ('layouts.w3s_member')
@section('title', 'All members')
@section('data-page-id', 'allMemberPage')
@section('content')

<br>

<div class="w3-container w3-content" style="max-width:1400px;margin-top:40px">


        <form>


                <input type="text" placeholder="search the family network" name="searchFamily"
                        class="w3-input w3-border w3-large w3-padding w3-round-large" id="searchFamily">
                <p id="searchHidden"></p>


        </form>


</div>

@endsection