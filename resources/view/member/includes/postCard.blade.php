<div class="w3-container w3-card w3-white w3-round w3-margin"><br>

  {{--  POST PROFILE PICTURE  --}}
  @isset($allData['img'])

   <a href="/profilepage/img?dir=img&pics={{ $allData["img"] }}&pID={{ $allData["post_no"] }}&path=profile">

  <img src="/img/profile/{{ $allData['img'] }}" alt="img" class="w3-left w3-circle w3-margin-right" style="width:60px">

   </a>

  @else
  <img src="/avatar/avatarF.png" alt="Avatar" class="w3-left w3-circle w3-margin-right" style="width:60px">
  @endisset

  {{--  POST TIME  --}}
  <span class="w3-right w3-opacity">
    {{ humanTiming($allData['date_created'])  }} ago
  </span>

  {{--  POST NAME  --}}
  <h5 id="fullName"> {{ $allData['fullName'] }}
  </h5>


  <hr class="w3-clear">
  {{--  POST MESSAGE  --}}
  <p class="postFont"> {{ $allData['postMessage'] }} </p> 

  {{-- PICTURES --}}
  <div class="w3-row-padding" style="margin:0 -16px">

    @for ($i = 0; $allData["post_img$i"]; $i++) 

      <a href="/profilepage/img?dir=img&pics={{ $allData["post_img$i"] }}&pID={{ $allData["post_no"] }}&path=post">

        <div class="w3-half">

        <img src="/img/post/{{ $allData["post_img$i"] }}" style="width:100%" alt="images{{ $i }}"
          class="w3-margin-bottom w3-hover-sepia" id="postImage{{ $i }}">

      </div>


      </a>
      
    @endfor
    <br>
  </div>

  {{--  LIKE BUTTON  --}}
  <button type="button" name="likeButton" class="w3-button w3-tiny w3-theme-d1 w3-margin-bottom"><em
      class="fa fa-thumbs-up"></em>
     Like</button>

  {{--  COMMENT BUTTON  --}}
  <button type="button" class="w3-button w3-tiny w3-theme-d2 w3-margin-bottom"><em class="fa fa-comment"></em> Comment </button>

  {{-- SHOW COMMENT --}}
  
  @foreach ($comment as $comment)
    @if ($allData['post_no'] === $comment['post_no'] )
      @include('member/includes/comment')
    @endif
  @endforeach

  <br>

  {{--  PROCESS COMMENT $_POST  --}}

  <form action="/postCommentProfile" method="post" id="postCommentProfile{{ $allData['post_no'] }}">

      {{-- every post has a number (post_no) that is linked to every comments (through comment_no) made under that post --}}
      
    <input name='post_no' type="hidden" name="{{ $allData['post_no'] }}" value={{ $allData['post_no'] }} />

    <input class="w3-input w3-border w3-round-large inputComment" 
    type="text" placeholder="Write a comment"
    id={{ $allData['post_no'] }} name='comment'>

    <br>

    <button type='submit' class="w3-button w3-green submitComment">Submit</button>
  </form>

  <br><br>
</div>