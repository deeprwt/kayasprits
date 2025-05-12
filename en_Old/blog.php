<?php $currentpage = 'blog'; ?>

<?php include 'header.php'; ?>

<style>


.blog-outer-container .new_title {
    border-bottom: 1px solid #e5e5e5;
    padding: 25px;
    margin-bottom: 0px;
}

.post-meta {
    list-style: outside none none;
    padding: 0 0px 8px;
}

.post-meta li {
    display: inline-block;
    margin-right: 10px;
    color: #a7a7a7;
    text-transform: none;
    font-size: 14px;
}

.post-meta li a {
    margin-right: 5px;
    color: #a7a7a7;
}

.post-meta li i {
    padding-right: 10px;
}

.title-primary {
    font-size: 24px;
    letter-spacing: 3px;
}

.blog-preview {
    padding: 60px;
}

/*
.blog-outer-container {
    margin-top: 10px;
    background: #fff;
    border: 1px solid #e5e5e5;
    display: inline-block;
    margin-bottom: 5px;
    width: 100%;
}
*/
.blog-preview_item {
    border-left: 1px solid #e5e5e5;
	box-shadow: 0 2px 20px 2px rgba(0,0,0,0.1);
    transition: box-shadow 500ms ease-in-out, transform 500ms ease-in-out;
    margin-bottom: 1.25rem;
	background-color:#fff;
}

.blog-preview_item:first-child {
    border-left: 1px solid #fff;
}

.blog-preview_image {
    float: left;
    width: 100%;
    position: relative;
}

.blog-preview_image img {
    float: left;
    width: 100%;
}

.blog-preview_info {
   
	padding: 0px 15px 15px 15px;
    width: 100%;
}

.blog-preview_title a {
    font-size: 18px;
    font-weight: 600;
    letter-spacing: 0px;
    line-height: 1.3em;
    margin: auto;
    padding-bottom: 6px;
    color: #000;
	text-transform: capitalize;
	
}
.blog-preview_title a:hover {
	color:#C93;
    
	
}

.blog-preview_desc {
    color: #666;
    font-size: 16px;
    line-height: 20px;
    padding-bottom: 5px;
    letter-spacing: 0.2px;
    text-align: justify;
}

.blog-preview_btn {
    font-size: 12px;
    margin: 0px;
    padding: 8px 15px 5px;
    font-weight: 700;
    letter-spacing: 1px;
    height: 33px;

    display: inline-block;
    background: transparent;
    color: #333;
   
    line-height: initial;
    border-radius: 2px;
	
	color: #fff;
    border-color: #131314;
    background: #cbb27c;
    margin: 15px 0px;
}

a.btn, .btn, .btn:hover {
    padding: 8px 12px;
}





.blog-preview_btn:focus,
.blog-preview_btn:hover {
    color: #fff;
    background: #c92434;
    border: 1px #c92434 solid;
    text-decoration: none;
}

.blog-preview_posted {
    color: #333;
    background: rgba(255, 255, 255, 0.9);
    bottom: 10px;
    height: 60px;
    right: 10px;
    position: absolute;
    width: 60px;
    z-index: 10;
}

.blog-preview_date {
    float: left;
    font-size: 13px;
    padding: 6px 0 10px;
    position: relative;
    text-align: center;
    width: 100%;
    text-transform: uppercase;
    font-weight: 300;
    letter-spacing: 1px;
}

.blog-preview_date span {
    font-size: 18px;
    font-weight: 700;
}

.blog-preview_comments {
    float: right;
    font-size: 12px;
    padding-top: 3px;
    text-align: center;
}

.blog-preview_comments i {
    color: #014693;
}

.blog-preview_image:hover .blog-preview_posted {
    color: #000;
}

.blog-container {
    padding-top: 25px;
    padding-bottom: 90px;
    text-align: center;
}

.blog-container .row {
    padding-top: 83px;
}

.blog-preview-small {
    float: left;
    position: relative;
    width: 100%;
}

.blog-preview-small_img {
    float: left;
    width: 100%;
}

.blog-preview-small_link {
    height: 100%;
    left: 0;
    position: absolute;
    top: 0;
    width: 100%;
}

.blog-preview-small_link:hover {
    background-color: rgba(0, 0, 0, 0.4);
}

.blog-preview-small_link:hover .blog-preview_posted {
    color: #fff;
}

.blog-preview-small_txt {
    bottom: 30px;
    left: 0;
    position: absolute;
    text-align: center;
    width: 100%;
}

.blog-preview-small .blog-preview_posted {
    border-style: none;
    border-width: 0;
    bottom: auto;
    left: 0;
    margin: 0 auto;
    right: 0;
    top: -25px;
}

.blog-preview-small_more {
    border-color: #ececec;
    float: left;
    font-size: 11px;
    margin: 28px 0 0;
    padding: 8px 15px;
}

.blog-inner {
    margin: 0px 5px -5px;
    display: inline-block;
}

.box-hover .add-to-links {
    margin: 0;
    list-style: none;
    padding: 0;
}

.box-hover .add-to-links li {
    margin: 3px 0;
}

.item-inner {
    overflow: hidden;
    position: relative;
    text-align: center;
    border: 1px solid #ddd;
    padding: 10px;
}

.box-hover {
    position: absolute;
    top: 55%;
    margin-top: -76px;
    overflow: hidden;
    left: -100%;
    float: right;
    text-align: left;
}

.item-img-info {
    width: 100%;
    height: 100%;
    ;
}

.image-hover2 a {
    position: relative;
    display: table;
}

.image-hover2 a:after {
    overflow: hidden;
    position: absolute;
    top: 0;
    content: "";
    z-index: 100;
    width: 100%;
    height: 100%;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    pointer-events: none;
    -webkit-transition: all 0.3s ease 0s;
    -o-transition: all 0.3s ease 0s;
    transition: all 0.3s ease 0s;
    background-color: rgba(0, 0, 0, 0.3);
    -webkit-transform: scale(0);
    -ms-transform: scale(0);
    transform: scale(0);
    z-index: 1;
}

.image-hover2 a:before {
    font: normal normal normal 18px/1 FontAwesome;
    content: "\f002";
    position: absolute;
    top: 45%;
    left: 50%;
    z-index: 2;
    color: #fff;
    ms-transform: translateY(-50%);
    -webkit-transform: translateY(-50%);
    transform: translateY(-50%);
    background: #126dd4;
    padding: 8px 12px;
    ms-transform: translateX(-50%);
    -webkit-transform: translateX(-50%);
    transform: translateX(-50%);
    opacity: 0;
    -webkit-transition: opacity 0.3s ease 0s;
    -o-transition: opacity 0.3s ease 0s;
    transition: opacity 0.3s ease 0s;
}

.image-hover2 a:hover:after {
    visibility: visible;
    opacity: 0.8;
    -webkit-transform: scale(1);
    -ms-transform: scale(1);
    transform: scale(1);
	background: rgba(31, 118, 189, 0.75);
}

.image-hover2 a:hover:before {
    opacity: 1;
}

.blog-outer-container .entry-thumb img {
    width: 100%;
	
}

.owl-carousel .owl-wrapper-outer {
    margin-top: 15px;
    margin-bottom: 15px;
}

.blog-outer-container .entry-thumb {
    position: relative;
	padding: 5px;
}

.item .item-inner:hover .item-img .box-hover {
    left: 0;
}

  .carousel-inner {
    position: relative;
    width: 100%;
    overflow: hidden;
    background: #000;
    opacity: .2;
}





</style>

<div id="homeCarousel" class="carousel slide carousel-home" data-ride="carousel">

          <!-- Indicators -->
          <ol class="carousel-indicators">
           
           
          </ol>

          <div class="carousel-inner" role="listbox">

            <div class="item active">
			
               <img src="images/slider/single-blog.jpg" alt="">
			
              <div class="container">

                <div class="carousel-caption">

                  <!--<h2 class="carousel-title bounceInDown animated slow">Because they need your help</h2>
                  <h4 class="carousel-subtitle bounceInUp animated slow ">Do not let them down</h4>
                  <!--<a href="#" class="btn btn-lg btn-secondary hidden-xs bounceInUp animated slow" data-toggle="modal" data-target="#donateModal">DONATE NOW</a>-->

                </div> <!-- /.carousel-caption -->

              </div>

            </div> <!-- /.item -->
     

          </div>
		
         
          

    </div>



<div class="container-fluid dark section no-padding">
<div class="container">
<div class="row">
<div class="col-sm-12">

<ul class="horz-menu center-menu">
<li class="active"><span><a href="blog.php">Recent</a></span></li>
<li><span><a href="blogpage2.php">Popular</a></span></li>

</ul>
</div>
</div>
</div>
</div>




<div class="container-fluid light section" style="background-image: linear-gradient(rgba(0,0,0,.3) ,rgba(0,0,0,.3)), url(images/block-bg-2.jpg); background-position:center center; background-size:cover;">
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="blog-outer-container">
				<div class="blog-inner">
                
					<div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-1.php" target="_blank" class="gallery-item lightbox">
                                    <img src="images/blog/latest-post-1.jpg" alt="">
                                </a>
                            </div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">8 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">12</span> <span class="month">Feb</span></li>
                             	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-1.php" target="_blank">The Emerging SMI kaya Towards its National Presence</a></h4>
                             	<div class="blog-preview_desc">Kaya Spirits achieving New milestone, currently launches it's premium brands in Kolkata. Kaya Blneders already makes it's presence in 15 States of the Country.
                                </div>
                                <a href="blog-single-1.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->  
                        	</div>
                    	</div>
                  	</div>
                            
                   	<div class="col-lg-4 col-md-4 col-sm-4">
                 		<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-2.php" target="_blank" class="gallery-item lightbox">
                                    <img src="images/blog/latest-post-2.jpg" alt="">
                                </a>
                  			</div>
               				<div class="blog-preview_info">
                                <ul class="post-meta">
                                    <li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">8 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">12</span> <span class="month">Feb</span></li>
                                </ul>
                            	<h4 class="blog-preview_title"><a href="blog-single-2.php" target="_blank" >CMD Mr. Karun Kaura being awarded with Price of India Award</a></h4>
                            	<div class="blog-preview_desc">Kaya Blenders and Distillers Limited also known as Kaya Spirits is a paramount Indian spirits company, famous for offering premium quality liquors and spirits.
                            	</div>
                               	<a href="blog-single-2.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                     		</div>
                 		</div>
                	</div>
                            
               		
                 	<div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-4.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-4.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-4.php" target="_blank" >Kaya Blenders towards its national presence Launches at Bengal</a></h4>
                                <div class="blog-preview_desc">Kaya Blenders and Distillers Limited under the Visionary Mr Karun Kaura has put its foot forward in the National market. Kaya, towards its National presence...
                            	</div>
                               	<a href="blog-single-4.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-9.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-9.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-9.php" target="_blank" >CMD Karun Kaura awarded with Asia Pacific Excellence Award at Kathmandu, Nepal</a></h4>
                                <div class="blog-preview_desc">The International Conference on 'Indo-Nepal Friendship and Economic Cooperation' was jointly organized by Citizens Integration Peace Institute and Nepal India...
                            	</div>
                               	<a href="blog-single-9.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-6.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-6.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-6.php" target="_blank" >UAE Honoured Mr. Karun Kaura With Global Achievers Award At Dubai</a></h4>
                                <div class="blog-preview_desc">H.E. Shri. Navdeep Singh Suri,Ambassador of India at Dubai, UAE Honoured Mr. Karun Kaura with Global Achievers Award at Dubai on Jan 2018.
                                <p><br></p>
                            	</div>
                               	<a href="blog-single-6.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-7.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-7.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-7.php" target="_blank" >Mr Karun Kaura being honoured with Dr. APJ Abdul Kalam Excellence Award</a></h4>
                                <div class="blog-preview_desc">Mr. Karun Kaura Awarded with DR. APJ Abdul Excellence Award for Outstanding Individual achievements & Distinguished Services to the nation by Hon'ble Shri. Shivraj V. Patil...
                            	</div>
                               	<a href="blog-single-7.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <!-- <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-8.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-8.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-8.php" target="_blank" >Old Professor Whisky awarded with label of the year 2017 by spiritz Magazine</a></h4>
                                <div class="blog-preview_desc">Kaya Blenders and Distilleries Ltd has spent both effort & money in creating an eye-catching label for its Old Professor Whisky. The label design, layout and colours gel...
                            	</div>
                               	<a href="blog-single-8.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-9.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-80.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-9.php" target="_blank" >CMD Karun Kaura awarded with Asia Pacific Excellence Award at Kathmandu, Nepal</a></h4>
                                <div class="blog-preview_desc">The International Conference on 'Indo-Nepal Friendship and Economic Cooperation' was jointly organized by Citizens Integration Peace Institute and Nepal India...
                            	</div>
                               	<a href="blog-single-9.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                  		<div class="blog-preview_item">        
                      		<div class="entry-thumb image-hover2">     
                                <a href="blog-single-3.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-3.jpg" alt="">
                            	</a>
                       		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">8 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">12</span> <span class="month">Feb</span></li>
                             	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-3.php" target="_blank" >International Excellence Award</a></h4>
                                <div class="blog-preview_desc">Alcohol consumption is not a contemporary phenomenon. Its history takes us back to the Neolithic period as far as 700 BC in northern China. Scientists tested and confirmed...
                                </div>
                                <a href="blog-single-3.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    
                        	</div>
                    	</div>
                	</div>-->
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-10.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-10.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-10.php" target="_blank" >Taking his vision of success to newer heights</a></h4>
                                <div class="blog-preview_desc">Taking his vision of success to newer heights Kaya Blenders and Distillers Ltd, which began its journey under Karun Kaura from Patiala, has now taken their business...
                            	</div>
                               	<a href="blog-single-10.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-5.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-5.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-5.php" target="_blank" >Kaya Blenders & Distillers: The Fast Growing SME Liquor Company</a></h4>
                                <div class="blog-preview_desc">Kaya Gets Excellence Award for Quality Production Kaya Blenders and Distillers Limited has been award with international excellence award for manufacturing in Spirits.
                            	</div>
                               	<a href="blog-single-5.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-11.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-11.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-11.php" target="_blank" >Kaya Gets Excellence Award for Quality Production</a></h4>
                                <div class="blog-preview_desc">Kaya Blenders and Distillers Limited has been award with international excellence award for manufacturing in Spirits. The Award Ceremony was orgazined at Holiday Inn Resort in Goa, ...
                            	</div>
                               	<a href="blog-single-11.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-12.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-12.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-12.php" target="_blank" >Change with preferences to stay relevant in the market</a></h4>
                                <div class="blog-preview_desc">liquor importers always know the pulse of the market. The same applies for Kaya Spirits, whose current import profile includes...
                            	</div>
                               	<a href="blog-single-12.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-13.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-13.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-13.php" target="_blank" >kaya opening new frontiers with London event press clipping</a></h4>
                                <div class="blog-preview_desc">After establishing its presence in 13 states in the country, Kaya Blenders & Distillers Limited is also strengthening its presence in China, Africa ...
                            	</div>
                               	<a href="blog-single-13.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                         	</div>
                     	</div>
                   	</div>
                    
                    <div class="col-lg-4 col-md-4 col-sm-4">
                  		<div class="blog-preview_item">        
                      		<div class="entry-thumb image-hover2">     
                                <a href="blog-single-3.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-014.jpg" alt="">
                            	</a>
                       		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">8 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">12</span> <span class="month">Feb</span></li>
                             	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-3.php" target="_blank" >Establishing a benchmark for vintage and organic blend spirits</a></h4>
                                <div class="blog-preview_desc">Alcohol consumption is not a contemporary phenomenon. Its history takes us back to the Neolithic period as far as 700 BC in northern China. 
                                </div>
                                <a href="blog-single-3.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                    <!--<a class="blog-preview_btn" href="http:/kayaspirits.com/#">READ MORE</a>-->
                        	</div>
                    	</div>
                	</div>
                    
                    <!--<div class="col-lg-4 col-md-4 col-sm-4">
                    	<div class="blog-preview_item">
                        	<div class="entry-thumb image-hover2"> 
                                <a href="blog-single-14.php" target="_blank" class="gallery-item lightbox">
                                	<img src="images/blog/latest-post-14.jpg" alt="">
                            	</a>
                      		</div>
                            <div class="blog-preview_info">
                            	<ul class="post-meta">
                                	<li><i class="fa fa-user"></i>posted by <a href="http:/kayaspirits.com/#">admin</a> </li>
                                    <li><i class="fa fa-comments"></i><a href="http:/kayaspirits.com/#">4 comments</a> </li>
                                    <li><i class="fa fa-clock-o"></i><span class="day">10</span> <span class="month">Jan</span></li>
                              	</ul>
                                <h4 class="blog-preview_title"><a href="blog-single-14.php" target="_blank" >Sunny days for beer in India</a></h4>
                                <div class="blog-preview_desc">Ask anyone (teetotallers excluded) which was his first drink and he would say, beer! Other than being a wonderful and increasingly preferred social drink...
                            	</div>
                               	<a href="blog-single-14.php" target="_blank" class="btn btn-default"><span>READ MORE</span></a>
                                   
                         	</div>
                     	</div>
                   	</div>-->
                            
                            
                            
                            
                            
                          
                            
                            
				</div>
			</div>
		</div>                  
	</div>
</div>












<div class="container-fluid super-dark section no-padding">
<div class="container">
<div class="row">
<div class="col-sm-12">
<ul class="horz-menu center-menu pages">
<li class="active"><span><a href="#">1</a></span></li>
<li><span><a href="#">2</a></span></li>
<li><span><a href="#">3</a></span></li>
<li><span><a href="#">4</a></span></li>
</ul>
</div>
</div>
</div>
</div>

<?php include 'footer.php'; ?>