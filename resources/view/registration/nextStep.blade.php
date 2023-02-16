@extends('baseBulma')
@section('title','next step')
@section('data-page-id', 'nextStep')
@section('content')


<section class="hero is-success is-fullheight">

    <div class="hero-body">
        <div class="container has-text-centered">
            <p class="title">
                NEXT STEP <br> Ref: {{ checkInput($_SESSION['id']) }}
            </p><br>
            <p class="subtitle">
                Hello {{ checkInput($_SESSION['firstName']) }}, <br> Your application has been successfully submitted.
                <br>Once reviewed by the admin team, a decision will be emailed to you within the next 24 hours. <br>If
                your application approved, then you should be able to log in to your account and access the family
                social network<br><br>

                Regards,<br>
                Admin team<br>
                {{ getenv("ADMIN_EMAIL") }}</p>
            </p>
        </div>
    </div>

</section>

@endsection