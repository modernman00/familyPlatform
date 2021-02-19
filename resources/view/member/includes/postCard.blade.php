<div class="w3-container w3-card w3-white w3-round w3-margin"><br>

  {{--  POST PROFILE PICTURE  --}}
  <img src="/img/profile/{{ $allData['profileImg'] }}" alt="Avatar" class="w3-left w3-circle w3-margin-right" style="width:60px">

  {{--  POST TIME  --}}
  <span class="w3-right w3-opacity">
   {{ humanTiming($allData['date_created'])  }} ago
  </span>

  {{--  POST NAME  --}}
  <h5 id="fullName"> {{ $allData['fullName'] }} 
  </h5>
  {{-- 
        <canvas id="user-icon" width="25" height="25"></canvas> --}}

  <hr class="w3-clear">
  {{--  POST MESSAGE  --}}
  <p> {{ $allData['postMessage'] }} </p> <br>
  {{-- PICTURES --}}
  <div class="w3-row-padding" style="margin:0 -16px">

    @for ($i = 0; $allData["post_img$i"]; $i++) <div class="w3-half">

      <img src="/img/post/{{ $allData["post_img$i"] }}" style="width:100%" alt="images{{ $i }}" class="w3-margin-bottom w3-hover-sepia" id="postImage{{ $i }}">
      
    </div>
    @endfor
<br>
  </div>
  <button type="button" name="likeButton" class="w3-button w3-tiny w3-theme-d1 w3-margin-bottom"><i class="fa fa-thumbs-up"></i>
     Like</button>
  <button type="button" class="w3-button w3-tiny w3-theme-d2 w3-margin-bottom"><i class="fa fa-comment"></i>
     Comment</button>

  {{-- show comment --}}
@foreach ($comment as $comment)
     @if ($allData['post_no'] === $comment['post_no'] )
    @include('member/includes/comment')
  @endif
@endforeach
 
 <br>

  <form action="/postCommentProfile" method="post" id="postCommentProfile{{ $allData['post_no'] }}">
    {{-- like and comment --}}


    {{-- COMMENTS --}}
    <input name='post_no' type="hidden" name="{{ $allData['post_no'] }}" value={{ $allData['post_no'] }} />
    {{-- 
    <input name='post_no' type="hidden" value={{ $allData['post_no'] }} /> --}}

    <input class="w3-input w3-border w3-round-large" type="text" placeholder="Write a comment"
      id={{ $allData['post_no'] }} name='comment'><br>
    <button type='submit' class="w3-button w3-green">Submit</button>
  </form>

  <br><br>
</div>