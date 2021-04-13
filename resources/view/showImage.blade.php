@extends('base')

@section('title', 'Image')
@section('data-page-id', 'showImage')
@section('content')

<div class="showImageContainer">

  <div>
    <img src={{ $imagePath }} alt="image" class="showPics">
  </div>

  <hr>

  <form action="/postCommentProfile" method="post">

    <div class="showComment">

      @foreach ($comment as $comment)

      @include('member/includes/comment')

      <input name='post_no' type="hidden" name="{{ $comment['post_no'] }}" value={{ $comment['post_no'] }} />

      @endforeach

      <textarea placeholder="Write a comment" id={{ $comment['post_no'] }} name='comment'>  </textarea>

      <br><br>

      <button type='submit' class="w3-button w3-green submitComment">Submit</button>
    </div>
  </form>

</div>



@endsection