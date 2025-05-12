

<style>


/* Images Slider 
================================= */
.carousel-inner {
    position: relative;
    width: 100%;
    overflow: hidden;
    background: #000;
    opacity: .3;
}
.carousel-home img {
  width: 100%;
}
.carousel-home .carousel-control {
  height: 80px;
  width: 40px;
  top: 50%;
  margin-top: -40px;
  -moz-transition: width, 0.3s;
  -o-transition: width, 0.3s;
  -webkit-transition: width, 0.3s;
  transition: width, 0.3s;
}
.carousel-home .carousel-control .fa {
  font-size: 2.5em;
  padding-top: 12px;
}
.carousel-home .carousel-control:hover {
  width: 50px;
}
.carousel-home .carousel-caption {
  top: 80%;
  bottom: 10px;
  transform: translateY(-50%);
}
.carousel-home .carousel-title {
  color: #fff;
  font-family: "Dosis", sans-serif;
  font-size: 30px;
  font-weight: bold;
  text-transform: uppercase;
}
.carousel-home .carousel-subtitle {
  font-size: 18px;
  text-transform: uppercase;
  color:#fff;
}
.carousel-home .btn {
  margin-top: 30px;
}

</style>

<link rel="stylesheet" href="css/font-awesome.min.css"/>

 <div id="homeCarousel" class="carousel slide carousel-home" data-ride="carousel">

          <!-- Indicators -->
          <ol class="carousel-indicators">
            <li data-target="#homeCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#homeCarousel" data-slide-to="1"></li>
            <li data-target="#homeCarousel" data-slide-to="2"></li>
            <li data-target="#homeCarousel" data-slide-to="3"></li>
          </ol>

          <div class="carousel-inner" role="listbox">

            <div class="item active">
			
               <img src="images/slider/career-image-1.jpg" alt="">
			
              <div class="container">

                <div class="carousel-caption">

                  <!--<h2 class="carousel-title bounceInDown animated slow">Because they need your help</h2>
                  <h4 class="carousel-subtitle bounceInUp animated slow ">Do not let them down</h4>
                  <!--<a href="#" class="btn btn-lg btn-secondary hidden-xs bounceInUp animated slow" data-toggle="modal" data-target="#donateModal">DONATE NOW</a>-->

                </div> <!-- /.carousel-caption -->

              </div>

            </div> <!-- /.item -->
            
            <div class="item">
			
               <img src="images/slider/career-image-2.jpg" alt="">
			
              <div class="container">

                <div class="carousel-caption">

                  <!--<h2 class="carousel-title bounceInDown animated slow">Because they need your help</h2>
                  <h4 class="carousel-subtitle bounceInUp animated slow ">Do not let them down</h4>
                  <!--<a href="#" class="btn btn-lg btn-secondary hidden-xs bounceInUp animated slow" data-toggle="modal" data-target="#donateModal">DONATE NOW</a>-->

                </div> <!-- /.carousel-caption -->

              </div>

            </div> <!-- /.item -->


            <div class="item ">

               <img src="images/slider/career-image-3.jpg" alt="">

              <div class="container">

                <div class="carousel-caption">

                  <!--<h2 class="carousel-title bounceInDown animated slow">Because they need your help</h2>
                  <h4 class="carousel-subtitle bounceInUp animated slow"> So let's do it !</h4>
                  <!--<a href="#" class="btn btn-lg btn-secondary hidden-xs bounceInUp animated slow" data-toggle="modal" data-target="#donateModal">DONATE NOW</a>-->

                </div> <!-- /.carousel-caption -->

              </div>

            </div> <!-- /.item -->




            <div class="item ">

               <img src="images/slider/career-image-4.jpg" alt="">

              <div class="container">

                <div class="carousel-caption">

                  <!--<h2 class="carousel-title bounceInDown animated slow">Because they need your help</h2>
                  <h4 class="carousel-subtitle bounceInUp animated slow">You can make the diffrence !</h4>
                 <!--<a href="#" class="btn btn-lg btn-secondary hidden-xs bounceInUp animated slow" data-toggle="modal" data-target="#donateModal">DONATE NOW</a>-->

                </div> <!-- /.carousel-caption -->

              </div>

            </div> <!-- /.item -->
             

          </div>

          <a class="left carousel-control" href="#homeCarousel" role="button" data-slide="prev">
            <span class="fa fa-angle-left" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
          </a>

          <a class="right carousel-control" href="#homeCarousel" role="button" data-slide="next">
            <span class="fa fa-angle-right" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
          </a>

    </div><!-- /.carousel -->











