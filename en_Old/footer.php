
<?php
    $msg = "";
	use PHPMailer\PHPMailer\PHPMailer;
	include_once "PHPMailer/PHPMailer.php";
	include_once "PHPMailer/Exception.php";
	include_once "PHPMailer/SMTP.php";

	if (isset($_POST['subscribe'])) {
		
		$email = $_POST['email'];
		

		

		$mail = new PHPMailer();

		//if we want to send via SMTP
		$mail->Host = "smtp.gmail.com";
		//$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Username = "......@gmail.com";
		$mail->Password = "";
		$mail->SMTPSecure = "ssl"; //TLS
		$mail->Port = 465; //587

        $mail->setFrom($_POST['email']);
		$mail->addAddress('web@kayaspiritsoffice.com');
		$mail->addReplyTo($_POST['email']);
		
		$mail->Subject = $_POST['email']. ': Subscribe Form - Kayaspirits.com';
		$mail->isHTML(true);
		$mail->name = $name;
		$mail->mobile = $mobile;
//		$mail->Body = $message;

		$mail->Body ='<h4>Email: '.$_POST['email'].'</h4>' ;
		
		$mail->addAttachment($file);

		if ($mail->send())
		    $msg = "Your Subscribe has been sent, thank you!";
		else
		    $msg = "Please try again!";

		unlink($file);
	}
?>

<script>
$(window).scroll(function() {
    var height = $(window).scrollTop();
    if (height > 100) {
        $('.scroll-top').fadeIn();
    } else {
        $('.scroll-top').fadeOut();
    }
});
$(document).ready(function() {
    $(".scroll-top").click(function(event) {
        event.preventDefault();
        $("html, body").animate({ scrollTop: 0 }, "slow");
        return false;
    });

});

</script>

<div class="rvps2">	<div id="dfdfd" class="dfdfd">
<div class="rvps0"><img  alt="" title="The Original Brand Quality & Satisfaction " src="images/quality_side.png"></div>
	<div class="clear"></div>
	</div>
</div>



<footer>
    <div class="container">
        <div class="row">
        	<div class="newsletter-wrap">
                   
            	<div class="newsletter">
                                    
                                        <div class="ng-scope" ng-controller="enquiry-ctrl">
                                            <div>
                                                <h3>News Letter</h3>
                                                <form ng-submit="saveSubscribe()" action="" method="post" name="form" class="clearfix" enctype="multipart/form-data">
                                               
                                                    <input type="email" placeholder="Enter your email address" class="input-text ng-pristine ng-untouched ng-empty ng-valid-email ng-invalid ng-invalid-required" title="Subscribe for our newsletter" id="newsletter1" name="email" ng-model="email" required="">
                                                    <button ng-disabled="submitBtn" name="subscribe" class="subscribe" title="Subscribe" type="submit"><span>Subscribe</span></button>
                                                    <h4 style="color:#fff; margin-top:25px; text-align:left; line-height: 15px;"><?php if ($msg != "") echo "$msg<br><br>"; ?></h4>
                                                </form>
                                            </div>
                                        </div>
                                </div>
                            
                            </div>
                            
    	</div>
        
        <div class="row">
            <div style="text-align:center;display: block;">
                <div class="col-sm-3">
                    <h6>Zonal office</h6>
                    <p style="text-align: center;">
                    <span class="cfont" style="text-align:center" data-size="13">SCO 18, 2nd Floor, City Centre, <br> Patiala, Punjab -<span class="rvts40"> </span><span class="rvts40">147001</span><br />www.kayaspirits.com</span>
                    </p>
               
                    <!--<ul>
                    <li><a href="index-2.html">Home</a></li>
                    <li><a href="shop.html">Shop</a></li>
                    <li><a href="our-story.html">Our story</a></li>
                    <li><a href="blog.html">Blog</a></li>
                    <li><a href="#">Login</a></li>
                    </ul>-->
                </div>
                <div class="col-sm-6">
                    
                        <h6>Contact</h6>
                        
                        <div><a href="contact.php" target="_top">Contact us</a><span class="rvts72">&nbsp; |&nbsp; </span><a class="rvts76" href="http://www.kayaspiritsoffice.com/webmail" target="_blank">Webmail Login</a><span class="rvts72"> &nbsp; |&nbsp; </span><a class="rvts76" href="https://www.kayaspirits.com/en/pdf/Annual_Information.pdf" target="_blank">Brochure</a>
                        </div>
                        <div><span class="cfont" style="">Toll Free Number : </span>
                        <span class="cfont" style="font: bold 22px 'Calibri',Candara,Optima,Arial,sans-serif;color:#c89739;line-height: 30px;" data-size="26">1800 270 3010</span></div>
                        <span class="cfont" style="">info@kayaspiritsoffice.com</span>
                    
                    
                </div>
                <!--<div class="col-sm-2">
                <h6>Shop</h6>
                <ul>
                <li><a href="#">Pale ale</a></li>
                <li><a href="#">Golden ale</a></li>
                <li><a href="#">Dark ale</a></li>
                <li><a href="#">IPA</a></li>
                </ul>
                </div>-->
                <div class="col-sm-3">
                    <h6>Corporate Office</h6>
                    <p style="text-align: center;">
                    <span class="cfont" style="text-align:center" data-size="13">DPT 512, 5th Floor, DLF Prime Tower, <br>Okhla Industrial Area Phase 1 <br>New Delhi -<span class="rvts40"> </span><span class="rvts40">110020</span></span>
                    </p>
                
                    <!--<ul>
                    <li><a href="index-2.html">Home</a></li>
                    <li><a href="shop.html">Shop</a></li>
                    <li><a href="our-story.html">Our story</a></li>
                    <li><a href="blog.html">Blog</a></li>
                    <li><a href="#">Login</a></li>
                    </ul>-->
                </div>
                
               <!-- <div class="col-sm-3">
                
                    
                        <h6>FOLLOW US</h6>
                        
                        <p style="padding-top:10px">
                        <a href="https://www.facebook.com/kayaspiritsindia/ " target="_blank"  class="fa fa-facebook-square"></a>
                        <a href="https://twitter.com/KayaBlenders" target="_blank" class="fa fa-twitter-square"></a>
                        <a href="https://www.instagram.com/kaya_blenders/" target="_blank" class="fa fa-instagram"></a>
                        <a href="https://www.linkedin.com/company/13289326/" target="_blank" class="fa fa-linkedin"></a>
                        </p>
                       
                        <a href="https://www.facebook.com/kayaspiritsindia/ " target="_blank" class="social-icon"><img  alt="" src="images/fb_icon.png"></a> 
                        <a href="https://twitter.com/KayaBlenders" target="_blank" class="social-icon"><img alt="" src="images/twiiter_icon.png"></a>
                        <a href="https://www.instagram.com/kaya_blenders/" target="_blank" class="social-icon"><img alt="" src="images/instagram_icon.png"></a>
                        <a href="https://www.linkedin.com/company/13289326/ " target="_blank" class="social-icon"><img alt="" src="images/link_in.png"></a>
                        <a href="https://www.youtube.com/channel/UC_O7frVjBQGwdh9U2Bj6dQQ " target="_blank" class="social-icon"><img alt="" src="images/youtube_icon.png"></a>
                        <!--<ul class="social">
                        <li><a href="https://www.facebook.com/kayaspiritsindia/ " target="_blank"  class="fa fa-facebook-square"></a></li>
                        <li><a href="https://twitter.com/KayaBlenders" target="_blank" class="fa fa-twitter-square"></a></li>
                        <li><a href="https://www.instagram.com/kaya_blenders/" target="_blank" class="fa fa-instagram"></a></li>
                        <li><a href="https://www.linkedin.com/company/13289326/" target="_blank" class="fa fa-linkedin"></a></li>
                        </ul>
                    
                </div>-->
                
            </div>
        </div>
    	<div class="copyright">
    		<!--<p>2017 &copy; Kaya Blenders & Distillers Limited / <a href="https://www.kayaspirits.com/">Web design by Priya Ranjan</a></p>-->
    		<span style=" text-align:center">Copyright &#169; Kaya Spirits 2019 </span>
    
    	</div>
    </div>
</footer>
</div>




<!--Gallery -->

<button class="scroll-top" data-scroll="up" type="button">
<img  src="images/arr.png">
</button>



<!--end Gallery -->



<script data-cfasync="false" src="js/jquery.min.js"></script>
<script src="js/jquery-ui.min.js"></script> 
<script src="js/bootstrap.min.js"></script>




<script src="js/headhesive.min.js"></script>
<script src="js/matchHeight.min.js"></script>
<script src="js/modernizr.custom.js"></script>
<script src="js/waypoints.min.js"></script>
<script src="js/counterup.js"></script>
<script src="js/scrollme.min.js"></script>
<script src="js/fakeLoader.min.js"></script>
<script src="js/owl.carousel.js"></script>
<script src="js/owl.autoplay.js"></script>
<!--<script src="js/4dfd2d448a.js"></script>
<script src="js/scroll.js"></script> -->
<script src="js/custom.js"></script>




</body>

<!-- Mirrored from www.klevermedia.co.uk/html_templates/craft_beer_nation_html/ by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 18 Oct 2019 14:23:14 GMT -->
</html>