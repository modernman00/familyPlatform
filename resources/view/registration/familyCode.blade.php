@extends('baseBulma2')

@section('title','Family Code')

@section('content')

    <style>
   
        /* Target the input placeholder text with the "::placeholder" pseudo-element */
        input::placeholder {
            font-size: 16px;
            font-weight: bold;
            /* Set the desired font size for the input placeholder text */
        }
body{
  font-size: 1.5rem;
}
     
         @media screen and (max-width: 600px) {
  .title {
    font-size: 1.5rem;
  }
  #clipcopy {
    font-size: 0.4em;
  }
}

@media screen and (min-width: 601px) and (max-width: 1024px) {
  .title {
    font-size: 1.65rem;
  }
}

@media screen and (min-width: 1025px) {
  .title {
    font-size: 2rem;
  }
}

#clipcopy {
  font-size: 0.45em;
  padding-left: 0.3em;

}


    </style>



<h1 class="title is-responsive">Your experience on the Family Platform becomes even more enriching with the Family Code feature, which enables seamless connections among all members of your family. If you haven't received a Family Code from a registered family member, worry not! You can create one below to bring your family together:</h1>

<br>

<p class="subtitle">
<div class="field has-addons is-expanded has-addons-centered">
  <div class="control ">
    <p class="control">
      <input class="input is-large is-hovered" type="text" id="yourSurname" placeholder="Enter your surname here">
    </p>
      </div>
</div> 

    <br>
    <div class="field has-addons has-addons-centered">
  <div class="control is-expanded">
    <p class="control">
      <button type="button" id="buttonCreateFamilCode" class="button is-large  is-success is-responsive">Click to generate your
        Family Code</button>
    </p>
  </div>
</div>

   <div class="field has-addons is-expanded has-addons-centered">
      <div class="control" id="createFamCode">
        <input class="input is-large" type="text" id="createCode" placeholder="Your family code will appear here" disabled>
      </div>
      {{-- <i id="copyIcon" class="fas fa-copy" style="cursor: pointer;"></i> --}}
      <div class="control">
        <button id="copyIcon" class="button is-primary is-large"><i class="fa fa-copy" style="cursor: pointer;color:red"></i><span id="clipcopy"> copy</span></button>

      </div>
    </div>
    <br>

    <h3 class="control">By generating a unique Family Code, you'll unlock the full potential of our platform, allowing your family to share moments, memories, and experiences like never before. Embrace the power of togetherness on the Family Platform today! </h3>
</p>


@endsection