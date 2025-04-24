@extends ('layouts.w3s_member')
@section('title', 'All members')
@section('data-page-id', 'allMemberPage')
@section('content')

<style>
.member-card:hover {
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

/* Add to your styles */
@media (prefers-color-scheme: dark) {
  .w3-card, .w3-white {
    background-color: #1d2226 !important;
    color: #e4e6eb !important;
  }
  .w3-text-black { color: #e4e6eb !important; }
  .w3-text-gray { color: #a0a4a8 !important; }
  .w3-light-grey { background-color: #2d3b44 !important; }
  .w3-hover-shadow:hover { box-shadow: 0 4px 12px rgba(0, 0, 0, 0.6) !important; }
}

/* Custom CSS for perfect LinkedIn styling */
</style>


<br>

<div class="w3-container w3-content" style="max-width:1400px;margin-top:40px">

        <div class="w3-card w3-white w3-round-large w3-padding" style="margin-bottom:20px">
                <form class="w3-container">
                        <div class="w3-row">
                                <div class="w3-col s10 m11">
                                        <input type="text" placeholder="Search the family network..."
                                                name="searchFamily" class="w3-input w3-border w3-round-large"
                                                id="searchFamily" style="padding:12px;font-size:16px">
                                </div>
                                <div class="w3-col s2 m1">
                                        <button type="submit" class="w3-button w3-blue w3-round-large"
                                                style="width:100%;height:100%">
                                                <i class="fa fa-search"></i>
                                        </button>
                                </div>
                        </div>
                        <p id="searchHidden"></p>
                </form>
        </div>

        <!-- Loading indicator -->
        <div id="setLoader" class="w3-center w3-padding-32 loader">
             
        </div>


        <!-- Members Grid -->
        <div class="w3-row-padding" id="allMembers">
                <!-- Member cards will be inserted here dynamically -->
        </div>

</div>


@endsection