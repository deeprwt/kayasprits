<?php
    $msg = "";
	use PHPMailer\PHPMailer\PHPMailer;
	include_once "PHPMailer/PHPMailer.php";
	include_once "PHPMailer/Exception.php";
	include_once "PHPMailer/SMTP.php";

	if (isset($_POST['submit'])) {
		$name = $_POST['name'];
		$email = $_POST['email'];
		$moble = $_POST['moble'];
		$city = $_POST['city'];
		$message = $_POST['message'];

		if (isset($_FILES['attachment']['name']) && $_FILES['attachment']['name'] != "") {
			$file = "attachment/" . basename($_FILES['attachment']['name']);
			move_uploaded_file($_FILES['attachment']['tmp_name'], $file);
		} else
			$file = "";

		$mail = new PHPMailer();

		//if we want to send via SMTP
		$mail->Host = "smtp.gmail.com";
		//$mail->isSMTP();
		$mail->SMTPAuth = true;
		$mail->Username = "......@gmail.com";
		$mail->Password = "";
		$mail->SMTPSecure = "ssl"; //TLS
		$mail->Port = 465; //587

        $mail->setFrom($_POST['email'],$_POST['name']);
		$mail->addAddress('info@kayagroupindia.com');
		$mail->addReplyTo($_POST['email'],$_POST['name']);
		
//		$mail->addAddress('web@kayaspiritsoffice.com');
//		$mail->setFrom($email);
		$mail->Subject = $_POST['name']. ': Career Form - Kayaspirits.com';
		$mail->isHTML(true);
		$mail->name = $name;
		$mail->mobile = $mobile;
//		$mail->Body = $message;

		$mail->Body ='<h4>Name: '.$_POST['name'].'<br>Email: '.$_POST['email'].'<br>Mobile: '.$_POST['mobile'].'<br>City: '.$_POST['city'].'<br>Massage:<br><br> '.$_POST['message'].'</h4>' ;
		
		$mail->addAttachment($file);

		if ($mail->send())
		    $msg = "Your email has been sent, thank you!";
		else
		    $msg = "Please try again!";

		unlink($file);
	}
?>

<?php $currentpage = 'career'; ?>

<?php include 'header.php'; ?>

<?php include 'career-slider.php'; ?>

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


</style>



<!-- Alertify default theme -->
<link rel="stylesheet" href="css/alertify.css"/>
	<link rel="stylesheet" href="css/alertify-default.css"/>
<!-- Alertify default theme End-->
    
<!--    
<div id="hero" class="single-page section" style="background-image: linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.8)), url(images/career.jpg)">

<div class="container">
<div class="row blurb scrollme animateme" data-when="exit" data-from="0" data-to="1" data-opacity="0" data-translatey="100">
<div class="col-md-10 col-md-offset-1">
<h1>Kaya Spirits Careers<span class="title-under"></span></h1>


</div>
</div>
</div>
</div>
-->





<div class="container-fluid light section" style="background-image: url(images/block-bg-2.jpg);">
<div class="container">
<div class="row">
<div class="col-sm-5 col-sm-push-1">
<img src="images/careers.png" alt="" />
</div>
<div class="col-sm-5 col-sm-push-1 matchHeight">
<section class="mobile-center">
<header><meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<h1>Find your dream job at Kaya Spirits. </h1>
<h2>Careers at Kaya Spirits & More </h2>
</header>
<p>Kaya Blenders & Distillers Limited popularly known as KayaSpirits, is a fast growing Indian spirits company involved in the manufacture, marketing and distribution of alcoholic beverages. Kaya works with the purpose of "Innovation in product development and marketing offresh and unique blends of finest liquors".</p>
<a href="" class="btn btn-default"><span>Learn more</span></a>

</section>
</div>
</div>
</div>

</div>

<div class="container-fluid light section">
    <div class="container gallery fadeIn animated">

			<div class="row">
            	<div class="col-md-4 col-sm-4"> 
                	<div class="career-box">
					<img src="images/career_img1.jpg" alt="">
                        
					<div class="career-detail">
                    <h3>WHO ARE WE?</h3>
                    <p>We are the largest kaya Spirits company in India. A professional team of people lends to the company the expertise and experience from a diverse range of functions to lead us into new horizons every day. With more than 46 different work-locations that have a world-class setup of breweries, sales offices ...</p>
                    
                    </div>
                    </div>
				</div> 
                
                <div class="col-md-4 col-sm-4"> 
                <div class="career-box">
					<img src="images/career_img2.jpg" alt="">
                    
                    <div class="career-detail">
                    <h3>WHY WORK FOR US?</h3>
                    <p>kaya Spirits is a unique place to work in, blending the complexity of working in a challenging Indian alco-beverage space, with the fun and liveliness of a contemporary brand. Our employees therefore, are hired with an objective of managing volatility, uncertainty, complexity and ambiguity in an ...</p>
                   
                    </div>
                    
				</div> 
                </div>
                
                <div class="col-md-4 col-sm-4"> 
                <div class="career-box">
					<img src="images/career_img3.jpg" alt="">

					
                       <div class="career-detail">
                    <h3>WHAT WE LOOK FOR?</h3>
                    <p>Professionals who have passion for excellence and bring to work:</p>
                    <ul>
                    <li>High levels of commitment</li>
                    <li>Integrity</li>
                    <li>Good team-playing skills</li>
                    <li>Good communication skills</li>
                    <li>A willingness to learn</li>
                    
                    </ul>
                    

                    </div>
				</div> 
                </div>

		</div>
        
       
        
			
	</div>
</div>







<!--

<div class="container-fluid light section" style="background-image:linear-gradient(rgba(255,255,255,.90),rgba(255,255,255,.8)), url(images/Martell_sudy_1024x670.jpg); background-size:cover; background-attachment:fixed">

<div class="container">

<div class="col-sm-5 matchHeight">
<div class="alignMiddle mobile-center">
<header>
<h2>Contact</h2>
</header>
<p>If you have any queries regarding Kaya Blenders & Distllers Limited or wish to get associated with the organization in any way, then please contact :-</p>
<p>A tough one to drink. The use of molasses and licorice is simply overwhelming and without balance.</p>
</div>
</div>
<div class="col-sm-6 col-sm-push-1 matchHeight">
<ul class="contact-list alignMiddle">
<li>
<i class="fa fa-location-arrow"></i>
<div>
Head Office
<span><b>Kaya Blenders & Distillers Limited</b><br>SCO 18, 2nd Floor, City Centre, Bupindra Road, Patiala<br> Punjab - 147001</span>
</div>
</li>
<li>
<i class="fa fa-location-arrow"></i>
<div>
Corporate Office:
<span><b>Kaya Blenders & Distillers Limited</b><br>DPT 512, 5th Floor, DLF Prime Tower, Okhla Industrial Area Phase 1 <br>New Delhi - 110020</span>
</div>
</li>

<li>
<div class="col-md-6">
<i class="fa fa-envelope"></i>
<div>
Email
<span><a href="https://www.kayaspirits,com/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="c7b4a6aba2b487a4b5a6a1b3a5a2a2b5eaa9a6b3aea8a9e9a4a8aa">[info@kayaspirits.com]</a></span>
</div>
</div>
<div class="col-md-6">
<i class="fa fa-phone"></i>
<div>
Telephone
<span>011-40513780</span>

</div>
</div>
</li>
<br>
<!--
<li>
<i class="fa fa-phone"></i>
<div>
Telephone
<span>011-40513780</span>
<span>011-40513670</span>
</div>
</li>
</ul>
</div>
</div>
</div>


-->

<!--<div class="container-fluid light section no-padding" style="background-image: linear-gradient(rgba(0,0,0,.7),rgba(0,0,0,.7)), url(images/Martell_sudy_1024x670.jpg); background-size:cover">-->
<div class="container-fluid dark section no-padding" style="background-image: linear-gradient(rgba(0,0,0,.95),rgba(0,0,0,.95)), url(images/karun_sir_big_new.jpg); background-size:cover">
<div class="container">
<div class="row">
<div class="col-sm-5 matchHeight" >
 <section class="alignMiddle mobile-center">
                <div class="leader" style="margin-right:25px">
                 <!--<h2>Latika Jain | <span style="color:#fff">HR Head</span></h2>
                 <p></p>
                <p>Kaya Blenders & Distillers Limited<br>SCO 18, 2nd Floor, City Centre,<br>Patiala, Punjab - 147001</p>
                <p><b>Telephone :</b> +91 9355199981, 9914209010</p>
                <p><b>Email :</b> latika@kayaspiritsoffice.com </p>
                
                <p><b>Website :</b><span style="color:#0073c3;"> www.kayaspirits.com </span></p>-->
                <h2>Work with us, Share your CV's</h2>
                <p>info@kayagroupindia.com</p>
                
                </div>
                
</section>



</div>



<div class="col-sm-6 col-sm-push-1 matchHeight" style="background-image:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.8)); margin:30px 0px; border-radius:5px">
<section class="" style="padding:15px">
<div class="contact_info">

                    <div class="contact_detail">
                		<div id="contact_form">
                		    
                		    <h3 align="center" style="color:#fff"><?php if ($msg != "") echo "$msg<br><br>"; ?></h3>
                		    
                		    <form action="career-thanks.php" method="post" name="form" class="clearfix" enctype="multipart/form-data">
                		    
                  		<!--<form action="career_1.php" id="career-form" class="clearfix">-->
                        
                        	<input class="input-text form-control" type="text" name="name" id="name" style="height: 36px;" placeholder="Your name *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;" required="">
                            
                    		<!--<input class="input-text form-control" type="text" name="name" id="name"  style="height: 36px;" placeholder="Your Name *" >-->
                   			<div class="cleaner_h10"></div>
                    		<input class="input-text form-control" type="text" name="email" id="email" style="height: 36px;" placeholder="Your E-mail *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;" required="">
                    		<div class="cleaner_h10"></div>
                            <input class="input-text form-control" type="text" name="mobile" id="mobile" style="height: 36px;" placeholder="Your Phone *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;" required="">
                    		<div class="cleaner_h10"></div>
                            <input class="input-text form-control" type="text" name="city" id="city" style="height: 36px;" placeholder="Your City *" required="">
                            <div class="cleaner_h10"></div>
                      		<textarea name="message" class="form-control" rows="4" cols="4" id="comment" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;" placeholder="Comment *" required=""></textarea>
                    		<div class="cleaner_h10"></div>
                            
                            <input class="input-btn form-control" type="file" name="attachment" style="height: 36px; border-bottom:none">
                            <div class="cleaner_h10"></div>
                    		<input name="submit" class="input-btn" type="submit" value="Send message" id="career-submit">
                  		</form>
                		</div>
              		</div>
              
            	</div>


</section>



<!--
<section class="alignMiddle padding-80-0" >
<form action="https://formspree.io/your@email.com" method="POST" class=" scrollme animateme" data-when="enter" data-from="1" data-to="0" data-opacity="0" data-scale="1.1">
<input type="text" name="name" placeholder="Name">
<input type="email" name="email" placeholder="Email">
<input type="Mobile" name="mobile" placeholder="Mobile No">
<textarea name="message" placeholder="Message" rows="5"></textarea>
<input type="submit" value="Send" class="btn btn-default" id="contact-mail">
</form>
</section>
-->

</div>

</div>
</div>
</div>











<!--
<div class="container-fluid dark section no-padding">
<div class="row">
<div class="col-sm-3 col-xs-6 icon-grid">
<a href="https://www.facebook.com/kayaspiritsindia/"></a>
<i class="fa fa-facebook"></i>
<h4>Facebook</h4>
<p>Like our Facebook page for awesome deals &amp; new arrivals.</p>
</div>
<div class="col-sm-3 col-xs-6 icon-grid">
<a href="https://twitter.com/KayaBlenders"></a>
<i class="fa fa-twitter"></i>
<h4>Twitter</h4>
<p>Follow us on Twitter for all the latest in KayaBlenders brewing.</p>
</div>
<div class="col-sm-3 col-xs-6 icon-grid">
<a href="https://www.instagram.com/kaya_blenders/"></a>
<i class="fa fa-instagram"></i>
<h4>Instagram</h4>
<p>Hashtag <a href="https://www.instagram.com/kaya_blenders/">#kaya_blenders</a> to be in with a chance to win freebies.</p>
</div>

<div class="col-sm-3 col-xs-6 icon-grid">
<a href="https://www.linkedin.com/company/kaya-blenders-and-distillers-limited"></a>
<i class="fa fa-linkedin"></i>
<h4>Linkedin</h4>
<p>Like is on Linkedin for the latest &amp; greatest in kaya spirits.</p>
</div>
</div>
</div>-->

<!--
<script src="js/alertify.js"></script>

<script>
    $(document).ready(function(){
        $('#career-submit').on('click', function(e){
            e.preventDefault();
            
			
            var name = $('#name').val();
            var email = $('#email').val();
            var mobile = $('#mobile').val();
			var city = $('#city').val();
            var comment = $('#comment').val();

           
			if(name == ''){
                alertify.error('Please Enter the name');
                return false;
            }

			
            if(email == ''){
                alertify.error('Please Enter your Email');
                return false;
               }
            
            else{
                if (!validateEmail(email)) {
                    alertify.error('Please Enter Valid Email Address');
                    return false;
                }
            }

		
            if(mobile != ''){

                if (mobile.length != 10) {
                    alertify.error('Please Enter your Valid Mobile number of 10 digits');
                    return false;
                }
            }
            else{
                alertify.error('Please Enter your Mobile Number');
                return false;
            }
			
			if(city == ''){
                alertify.error('Please Enter the city');
                return false;
            }
			
			if(comment == ''){
                alertify.error('Please Enter the comment');
                return false;
            }

            function validateEmail(sEmail) {
                var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
                if (filter.test(sEmail)) {
                    return true;
                }
                else {
                    return false;
                }
            }

            $.ajax({
                type: 'POST',
                url: 'career-mail.php',
                dataType: 'json',
                data: {
                    action: 'career-submit',
					name: name,
					email: email,
					mobile: mobile,
					city: city,
					comment:comment,
                    
                },
                success: function (data) {
                    if(data.status == 'success'){
                    alertify.success(data.message);
                    $("#career-form")[0].reset();
                    }
                    else{
                    alertify.error(data.error);
                    }
                }
            })

        });
    });
    </script>-->
    
    
      

<?php include 'footer.php'; ?>

