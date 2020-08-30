@extends('base')

@section('title','Olaoguns')
@section('content')



   <div class="header">
        <h1>The Olaoguns</h1>
        <i> We are set for greater height</i>
        <form class="form-inline subscribeForm" method="POST" action="/subscribe" >
            <div class="input-group mb-3">
                <input type="email" class="form-control homePageSubscribe" size="80" placeholder="enter your email address" name="email"
                    id="email" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger"> Subscribe</button>
                </div>
            </div>
        </form>
    </div>




@endsection