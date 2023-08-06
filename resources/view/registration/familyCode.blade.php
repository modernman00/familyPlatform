@extends('baseBulma')

@section('title','Family Code')

@section('data-page-id', 'familyCode')

@section('content')

    <style>
   
        /* Target the input placeholder text with the "::placeholder" pseudo-element */
        input::placeholder {
            font-size: 0.9rem;
            /* font-weight: bold; */
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

    <div class="styleForm" style="margin-top: 2rem;">

  <img src={{ getenv('IMG_CONTRACT2')}} alt="logo" class="mb-4 form__login__logo" style="margin-left:43%; margin-bottom:5rem;">



      @php

      $formArray = [
        'Your experience on the Family Platform becomes even more enriching with the Family Code feature, which enables seamless connections among all members of your family. If you havent received a Family Code from a registered family member, worry not! You can create one below to bring your family together:' => 'title',

        'surname' => 'text',

        'Generate your family code' => 'button'
      ];

      $form = new App\classes\BuildFormBulma($formArray);
      $form->genForm();
      @endphp
    
    <br>


   <div class="field has-addons is-expanded has-addons-centered">
      <div class="control" id="createFamCode">

        <input class="input is-large" type="text" id="createCode" placeholder="Your family code will appear here" readonly>

      </div>

      <div class="control">
        <button id="copyIcon" class="button is-primary is-large">
          <i class="fa fa-copy" style="cursor: pointer;color:red"></i>
          <span id="clipcopy"> copy</span>
          </button>

      </div>
    </div>
    <br>

    <p class="subtitle">By generating a unique Family Code, you'll unlock the full potential of our platform, allowing your family to share moments, memories, and experiences like never before. Embrace the power of togetherness on the Family Platform today! </p>
</p>

</div>

@endsection