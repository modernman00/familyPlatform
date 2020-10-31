@extends('base')

@section('title','next step')

@section('content')

<div class="jumbotron">
    <h1 class="display-3">Ref: {{ $_SESSION['id'] }} - NEXT STEP</h1>
    <p class="lead">Hello {{ $_SESSION['firstName'] }}, <br> Your application has been successfully submitted. Once reviewed by the admin team, a decision will be emailed to you within the next 24 hours. <br>If your application approved, then you should be able to log in to your account and access the family social network<br><br>
    
    Regards,<br>
    Admin team<br>
    {{ getenv("ADMIN_EMAIL") }}</p>
    <hr class="my-2">
 
</div>


@endsection