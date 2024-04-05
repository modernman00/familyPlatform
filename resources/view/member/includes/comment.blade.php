<div class="w3-ul w3-border" id="comment" name='commentDiv'>

      <div class="w3-container commentDiv">

        @isset($comment['img'])
     
        <img src="/public/img/profile/{{ $comment['img'] }}" alt="Avatar" class="w3-left w3-circle w3-margin-right commentImg" style="width:60px; height:60px">

        @else

        <img src="/avatar/avatarM.jpeg" alt="Avatar" class="w3-left w3-circle w3-margin-right commentImg" style="width:30px; height:30px" >

        @endisset

          {{--  POST TIME  --}}
        <p class="w3-right w3-opacity commentTiming">  {{ humanTiming($comment['date_created'])  }} ago </p> 

        
          
        </span>

        <p class="commentFont"> {{ $comment['comment'] }} </p>
        
        <hr>

    </div>
</div>