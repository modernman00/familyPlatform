@extends ('cust.custBase')
@section('title', 'Profile page')
@section('content')

<h1><b>WELCOME TO YOUR PROFILE PAGE - {{ $data['firstName'] }}</b></h1>


	<div class="wrap-body">
		
		<!-- /////////////////////////////////////////Content -->
		<!-- /////////////////////////////////////////Content -->
		<div id="page-content" class="container-fluid">
			<div class="wrap-content">
				
				<!-- ////////////Content Box Header -->
				<div class="col-lg-6">
					<section class="box-content box-header">
						<!-- Static Header -->
						<div class="header-text">
							<div class="logo visible-xs text-center">
								<a href="index.html"><img src="/img/custpage/logo-dark.png" class="img-responsive" alt=""></a>
							</div>
							<div class="header-text-inner">
								<h1>Your Favorite Source of Free<br> Bootstrap Themes</h1>
								
							</div>
						</div><!-- /header-text -->
					</section>
				</div>
				
				<div class="col-lg-6">
					<section class="box-content box-1 box-style-0">
						<div class="no-gutter">
							<div class="col-xs-4 social-box box-facebook">
								<a class="" href="#">
									<div class="social-box-inner">
										<div class="social-box-inner-content">
											<i class="fas fa-facebook"></i>
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-4 social-box box-twitter">
								<a class="" href="#">
									<div class="social-box-inner">
										<div class="social-box-inner-content">
											<i class="fas fa-twitter"></i>
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-4 social-box box-pinterest">
								<a class="" href="#">
									<div class="social-box-inner">
										<div class="social-box-inner-content">
											<i class="fas fa-pinterest"></i>
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-4 social-box box-google">
								<a class="" href="#">
									<div class="social-box-inner">
										<div class="social-box-inner-content">
											<i class="fas fa-google-plus"></i>
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-4 social-box box-instagram">
								<a class="" href="#">
									<div class="social-box-inner">
										<div class="social-box-inner-content">
											<i class="fas fa-instagram"></i>
										</div>
									</div>
								</a>
							</div>
							<div class="col-xs-4 social-box box-dribbble">
								<a class="" href="#">
									<div class="social-box-inner">
										<div class="social-box-inner-content">
											<i class="fas fa-dribbble"></i>
										</div>
									</div>
								</a>
							</div>
						</div>
					</section>
				</div>
				
				<div class="col-lg-6">
					<!-- ////////////Content Box 02 -->
					<section class="box-content box-2 box-style-0" id="box-featured" data-anchor="featured">
						<div class="flex-box no-gutter">
							<div class="col-sm-8 f-right">
								<div id="owl-slide" class="owl-carousel">
									<div class="item">
										<div class="box-/img/custpage">
											<img src="/img/custpages/2.jpg" alt="" />
										</div>
									</div>
									<div class="item">
										<div class="box-/img/custpage">
											<img src="/img/custpages/1.jpg" alt="" />
										</div>
									</div>
									<div class="item"> 
										<div class="box-/img/custpage">
											<img src="/img/custpages/3.jpg" alt="" />
										</div>
									</div>
								</div>
							</div>
							<div class="col-sm-4">
								<div class="box-text">
									<div class="box-text-inner pd-medium-lg pd-medium-md pd-small-xs t-right">
										<div class="heading">
											<h2 class="small">Right Picture Slide</h2>
										</div>
										<div class="owl-controls ">
											<div class="btn next next-b_2-slide"><i class="fa fa-arrow-left"></i></div>
											<div class="btn prev next-b_2-slide"><i class="fa fa-arrow-right"></i></div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
				
				<!-- ////////////Content Box 03 -->
				<div class="col-lg-6">
					<section class="box-content box-3 box-style-0">
						<div class="flex-box no-gutter">
							<div class="col-sm-4">
								<div class="box-/img/custpage">
									<img src="/img/custpages/1.jpg" alt="" />
								</div>
							</div>
							<div class="col-sm-8">
								<div class="box-text ">
									<div class="box-text-inner pd-large-lg pd-small-xs">
										<div class="heading">
											<h2>We Are Creative</h2>
										</div>
										<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam viverra convallis auctor. Sed accumsan libero quis mi commodo et suscipit enim lacinia. Morbi rutrum vulputate est sed faucibus.consectetur adipiscing elit. Aliquam viverra convallis auctor.</p>
										
										<a class="btn btn-skin m-top30">Learn More</a>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
				
				<!-- ////////////Content Box 04 -->
				<div class="col-lg-6">
					<section class="box-content box-4">
						<div class="no-gutter">
							<div class="col-lg-3 col-sm-6">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/10.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/10.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 nopadding">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/11.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/11.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 nopadding">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/12.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/12.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 nopadding">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/13.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/13.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 nopadding">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/14.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/14.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 nopadding">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/15.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/15.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 nopadding">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/16.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/16.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-3 col-sm-6 nopadding">
								<div class="portfolio-box zoom-effect">
									<img src="/img/custpages/17.jpg" class="img-responsive" alt="">
									<div class="portfolio-box-caption">
										<div class="portfolio-box-caption-content">
											<div class="project-name">
												Project Name
											</div>
											<div class="project-category">
												Category
											</div>
											<div class="project-social">
												<ul class="list-inline">
													<li><a href="#"><i class="fa fa-link"></i></a></li>
													<li><a href="/img/custpages/17.jpg" data-lightbox="example-set" data-title="Click the right half of the /img/custpage to move forward."><i class="fa fa-search"></i></a></li>
												</ul>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
				
				<!-- ////////////Content Box 05 -->
				<div class="col-lg-6">
					<section class="box-content box-5 box-style-0">
						<div class="flex-box no-gutter">
							<div class="col-md-7">
								<div class="box-text dark-style">
									<div class="box-text-inner pd-medium-md pd-small-xs t-right">
										<div class="heading">
											<h2 style="font-size: 43px; line-height: 43px">Meet Our Best Leader</h2>
										</div>
										<h4 class="h5">HTML5</h4>
										<div class="progress">
											<div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100" style="width:90%">
											  90%
											</div>
										</div>
										<h4 class="h5">CSS3</h4>
										<div class="progress">
											<div class="progress-bar progress-bar-light" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100" style="width:95%">
											  95%
											</div>
										</div>
										<h4 class="h5">Jquery/Javascript</h4>
										<div class="progress">
											<div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width:80%">
											  80%
											</div>
										</div>
										<h4 class="h5">Adobe Photoshop</h4>
										<div class="progress">
											<div class="progress-bar progress-bar-light" role="progressbar" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100" style="width:88%">
											  88%
											</div>
										</div>
										<h4 class="h5">Adobe Dreamweaver</h4>
										<div class="progress">
											<div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100" style="width:95%">
											  95%
											</div>
										</div>
										<ul class="list-inline social-link m-top20">
											<li><a href="#"><i class="fa fa-facebook"></i></a></li>
											<li><a href="#"><i class="fa fa-twitter"></i></a></li>
											<li><a href="#"><i class="fa fa-instagram"></i></a></li>
										</ul>
									</div>
								</div>
							</div>
							<div class="col-md-5">
								<div class="box-/img/custpage">
									<img src="/img/custpages/business.png" alt="" />
								</div>
							</div>
						</div>
					</section>
				</div>
				
				<!-- ////////////Content Box Contact info -->
				<div class="col-md-6">
					<section class="box-content box-contact-info">
						<div class="flex-box no-gutter">
							<div class="col-sm-12">
								<div class="contact-info">
									<!-- Show Info Button -->
									<a href="#" class="show-info-link"><i class="fa fa-info"></i> Show info</a>
									<div id="map" style="height: 550px;"></div>
									<address class="contact-info-wrapper pd-exlarge-sm pd-large-xs">
										<ul class="list-unstyled">
											<!-- Address -->
											<li class="contact-group">
											  <span class="adr-heading">Address</span>
											  <span class="adr-info">100 Street, Il, US</span>
											</li>
											<!-- Email -->
											<li class="contact-group">
											  <span class="adr-heading">Email</span>
											  <span class="adr-info">sayhello@email.com</span>
											</li>
										</ul>
										<ul class="list-unstyled">
											<!-- Phone -->
											<li class="contact-group">
											  <span class="adr-heading">Phone</span>
											  <span class="adr-info">+ 123 4567 890</span>
											</li>
											<!-- Mobile -->
											<li class="contact-group">
											  <span class="adr-heading">Mobile</span>
											  <span class="adr-info">+ 123 4567 890</span>
											</li>
										</ul>                  
										<a href="#" class="show-map"><i class="fa fa-map-o"></i> Show on map</a>
									</address>
								</div>
							</div>
						</div>
					</section>
				</div>
				
				<!-- ////////////Content Contact Form -->
				<div class="col-md-6">
					<section class="box-content box-contact-form box-style-0">
						<div class="flex-box no-gutter">
							<div class="col-sm-12">
								<div class="box-text">
									<div class="box-text-inner pd-exlarge-lg pd-small-xs">
										<div class="heading">
											<h2>Contact Us</h2>
										</div>
										<form role="form" id="contactForm" data-toggle="validator" >
											<!-- Name -->
											<div class="form-group">
												<label for="name">Name</label>
												<input type="text" name="name" id="name" class="form-control" placeholder="Enter your name" required>
												<div class="help-block with-errors"></div>
											</div>

											<!-- Email -->
											<div class="form-group">
												<label for="email">Email</label>
												<input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
												<div class="help-block with-errors"></div>
											</div>
											<!-- Message -->
											<div class="form-group">
												<label for="message">Message</label>
												<textarea class="form-control" name="message" id="message" rows="5" placeholder="Your Message" required></textarea>
												<div class="help-block with-errors"></div>
											</div>
											<button type="submit" id="form-submit" class="btn btn-skin pull-right">Submit</button>
											<div id="msgSubmit" class="h3 text-center hidden"></div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
				
			</div>
		</div><!-- End Content -->
		
		<footer id="page-footer" class="">
			<div class="bottom-footer">
				<div class="container-fluid">
					<div class="">
						<div class="col-md-6">
							<p>Copyright 20xx - Designed by <a href="https://www.html5xcss3.com">HTML5xCSS3</a></p>
						</div>
						<div class="col-md-6">
							<ul class="list-inline quicklinks text-right">
								<li><a href="#">Privacy Policy</a></li>
								<li><a href="#">Terms of Use</a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</footer><!-- Footer -->


	</div><!-- .wrap-body -->
	


@endsection