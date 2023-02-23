@extends('layouts.w3s_member')

@section('title', 'Image')
@section('data-page-id', 'showImage')
@section('content')

<div class="showImageContainer">

  {{-- IMAGE --}}

  <div class="showImageContainer__img">
    <img src={{ $imagePath }} alt="image" class="showImageContainer__image">
  </div>

  <hr>

  {{-- SHOW COMMENT --}}

  <div class="showImageContainer__comment">

    @foreach ($comment as $comment)

    @include('member/includes/comment')

    <input name='post_no' type="hidden" name="{{ $comment['post_no'] }}" value={{ $comment['post_no'] }} />

    @endforeach

    <br>

    {{-- SUBMIT COMMENT --}}

    <div class="showImageContainer__form">

      <form action="/postCommentProfile" method="post">

        <textarea placeholder="Write a comment" id={{ $comment['post_no'] }} name='comment'>  </textarea>

        <br><br>

        <button type='submit' class="w3-button w3-green submitComment">Submit</button>

      </form>
    </div>

  </div>

</div>








@endsection