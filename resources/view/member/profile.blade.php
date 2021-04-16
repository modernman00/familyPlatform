@extends ('member.custBase')
@section('title', 'Profile page')
@section('content')

<h1><b>WELCOME TO YOUR PROFILE PAGE - {{ $data['firstName'] }}</b></h1>


	<div class="wrap-body">
		
		<!-- /////////////////////////////////////////Content -->
		<!-- /////////////////////////////////////////Content -->
		<div id="page-content" class="container-fluid">
			<div class="wrap-content">
				
				<!-- ////////////Content Box Header -->
				<div class="col-lg-4">
					<section class="box-content box-header">

						<img src="/img/profile/{{ $data['img'] }}" alt="profile_pics">
					
					</section>
				</div>
				<div class="col-lg-4">
					<section class="box-content box-header">

						<img src="/img/profile/{{ $data['img'] }}" alt="profile_pics">
					
					</section>
				</div>

				<div class="col-lg-4">
					<section class="box-content box-header">

						<img src="/img/profile/{{ $data['img'] }}" alt="profile_pics">
					
					</section>
				</div>
				
	
		


			</div>
		</div>
	</div>
	


@endsection