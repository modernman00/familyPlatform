<div class="w3-ul w3-border" id="comment" name='commentDiv'>
    <div class="w3-container">
        <img src="/img/profile/{{ $comment['profileImg'] }}" alt="Avatar" class="w3-left w3-circle w3-margin-right w3-margin-top" style="width:40px; height:40px" >
          {{--  POST TIME  --}}
        <span class="w3-right w3-opacity" style="font-size: 10px;">
            {{ humanTiming($comment['date_created'])  }} ago
        </span>
        <p style="font-size: 12px;"> {{ $comment['comment'] }}</p><hr>
    </div>
</div>