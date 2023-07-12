@extends('baseBulma')
@section('title','next step')
@section('data-page-id', 'nextStep')
@section('content')


<section class="hero is-success is-fullheight">

    <div class="hero-body">
        <div class="container has-text-centered">
            <p class="title">
                Congratulations! Your Application Has Been Successfully Submitted!<br> Ref: {{ checkInput($_SESSION['id']) }}
            </p><br>
            <p class="subtitle">
                Hello {{ checkInput($_SESSION['firstName']) }}, <br> Thank you for submitting your application. Our admin team will review it, and within 24 hours, you will receive an email with the decision. Once approved, you can log in to your account and access our family social network to connect with relatives and enjoy meaningful interactions. <br><br>Once
                your application is approved, then you should be able to log in to your account and access the family
                social network<br><br>We appreciate your patience during this review process and look forward to welcoming you into our vibrant community. Get ready to embark on an incredible journey of meaningful connections and cherished family moments.

<br><br>

                Regards,<br>
                Admin team<br>
                {{ getenv("ADMIN_EMAIL") }}</p>
            </p>
        </div>
    </div>

</section>

@endsection