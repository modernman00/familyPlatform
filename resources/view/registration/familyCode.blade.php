@extends('baseBulma2')

@section('title','ERROR')

@section('content')



<h1 class="title">Hello, registration to your family platform is by invitation so if you have been given your family reference code, please enter it below:</h1>
<h1 class="title">Please enter your family reference code:</h1>

<div class="field has-addons">
  <div class="control">
    <input class="input is-large" type="text" id="familyCodeInput" name="familyCodeInput">

  </div>
  <div class="control">
    <a class="button is-info">
      Search
    </a>
  </div>
</div>

<button class="button" onclick="validateCode()">Submit</button>
<p id="errorMessage"></p>

</div>
</div>


@endsection