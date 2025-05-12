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
		$mail->Username = "....@gmail.com";
		$mail->Password = "";
		$mail->SMTPSecure = "ssl"; //TLS
		$mail->Port = 465; //587

        $mail->setFrom($_POST['email'],$_POST['name']);
		$mail->addAddress('info@kayaspiritsoffice.com');
		$mail->addReplyTo($_POST['email'],$_POST['name']);
		
//		$mail->addAddress('web@kayaspiritsoffice.com');
//		$mail->setFrom($email);
		$mail->Subject = $_POST['name']. ': Contact Form - Kayaspirits.com';
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

<?php $currentpage = 'contact'; ?>

<?php include 'header.php'; ?>
<style>

</style>

<!-- Alertify default theme -->
<link rel="stylesheet" href="css/alertify.css"/>
	<link rel="stylesheet" href="css/alertify-default.css"/>
<!-- Alertify default theme End-->
    
<div id="hero" class="single-page section" style="background-image:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.8)), url(images/slider/contact.jpg)">

    <div class="container">
        <div class="row blurb scrollme animateme" data-when="exit" data-from="0" data-to="1" data-opacity="0" data-translatey="100">
        <div class="contact-grid">
            <div class="col-sm-6 col-md-3 contact-grid1">
            
            <h4>Distribution/Dealership/Business </h4>
            <h3>roop@kayaspirits.com</h3>
            </div>
            <div class="col-sm-6 col-md-3 contact-grid1">
           
            <h4>All HR/admin related queries</h4>
            <h3>latika@kayaspiritsoffice.com</h3>
            </div>
            <div class="col-sm-6 col-md-3 contact-grid1">
            
            <h4>All Financials/Legal</h4>
            <h3>cmo@kayaspirits.com</h3>
            </div>
            
            <div class="col-sm-6 col-md-3 contact-grid1">
            <h4>Feedback/Any Other Queries</h4>
            <h3>info@kayaspiritsoffice.com</h3>
            </div>
            </div>
        </div>
    </div>
</div>


<div class="container-fluid super-dark section no-padding" style="background-image:linear-gradient(rgba(0,0,0,.2),rgba(0,0,0,.2)), url(images/bg_back.jpg); background-size:cover">

	<div class="row">
        <div class="col-md-4 col-sm-4 icon-grid">
            <a href="#"></a>
            <i class="fa fa fa-phone"></i>
            <h4>Toll Free Number</h4>
            <h3>1800 270 3010</h3>
        </div>
        <div class="col-md-4 col-sm-4 icon-grid">
            <a href="#"></a>
             <i class="fa fa-clock-o"></i>
            <h4>SCHEDULE</h4>
            <h3>Mon-Sat 10:00-18:30 | Sun-closed</h3>
        </div>
        <div class="col-md-4 col-sm-4 icon-grid">
            <a href="mailto:info@kayaspiritsoffice.com"></a>
            <i class="fa fa-envelope"></i>
            <h4>Email</h4>
            <h3><span><a href="mailto:info@kayaspiritsoffice.com" target="_blank" class="__cf_email__" data-cfemail="">[info@kayaspiritsoffice.com]</a></span></h3>
        </div>
    
        <!--<div class="col-md-3 col-sm-6 icon-grid">
            <a href="#"></a>
            <img src="images/icon_4.png" class="svg" alt="Unrivalled taste" />
            <h2>Competitive Price</h2>
            <h3>If you're looking for a deal, you've come to the right place!</h3>
        </div>-->
    </div>
    
    
<br>


<div class="row">

<div class="col-sm-6" style="border-right: 1px solid rgba(255,255,255,.08)">
    <ul class="contact-list">
        <li>
            <i class="fa fa-location-arrow"></i>
            <div>
            Zonal office
            <span><b>Kaya Blenders & Distillers Limited</b><br>SCO 18, 2nd Floor, City Centre,<br> Bupindra Road, Patiala<br> Punjab - 147001</span>
            </div>
            </li>
        <li>
            <i class="fa fa-phone"></i>
            <div>
            Telephone
            <span>+91 9914209010</span>
            </div>
        </li>
    <br>
    </ul>
<div>
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3443.2867556649353!2d76.37649481460096!3d30.342799311372406!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x391028955555554b%3A0x4cd5162e06f3b444!2sKaya%20Blenders%20and%20Distillers%20Ltd!5e0!3m2!1sen!2sin!4v1575524743102!5m2!1sen!2sin" width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
</div>
<br>
</div>

<div class="col-sm-6">

<ul class="contact-list ">

    <li>
    <i class="fa fa-location-arrow"></i>
        <div>
        Corporate Office:
        <span><b>Kaya Blenders & Distillers Limited</b><br>DPT 512, 5th Floor, DLF Prime Tower, <br>Okhla Industrial Area Phase 1 <br>New Delhi - 110020</span>
        </div>
    </li>
    <li>
    <i class="fa fa-phone"></i>
        <div>
        Telephone
        <span>011 - 40513780, 011 - 40513780</span>
        
        </div>
    </li>
    <br>
    
    </ul>
    <div>
    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3505.6771087732022!2d77.28171111441428!3d28.51936088246262!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce14ba115d61b%3A0x3b032b8fffdcc574!2sDLF%20Prime%20Towers!5e0!3m2!1sen!2sin!4v1575372646477!5m2!1sen!2sin" width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
   </div> 
    
</div>




</div>




<!--
<div class="row">
<div class="col-sm-6 matchHeight">
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3443.2867556649353!2d76.37649481460096!3d30.342799311372406!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x391028955555554b%3A0x4cd5162e06f3b444!2sKaya%20Blenders%20and%20Distillers%20Ltd!5e0!3m2!1sen!2sin!4v1575524743102!5m2!1sen!2sin" width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>




</div>
<div class="col-sm-6 matchHeight">
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3505.6771087732022!2d77.28171111441428!3d28.51936088246262!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390ce14ba115d61b%3A0x3b032b8fffdcc574!2sDLF%20Prime%20Towers!5e0!3m2!1sen!2sin!4v1575372646477!5m2!1sen!2sin" width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>
</div>

</div>-->

<br>


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
<div class="container-fluid light section" style="background-image:linear-gradient(rgba(255,255,255,.5),rgba(255,255,255,.5)), url(images/Martell_sudy_1024x670.jpg); background-size:cover; background-attachment:fixed">
<div class="container">
<!--<header>
<h2 style="text-align:center">Get in touch with Kaya Spirits<span class="title-under-black"></span></h2>
</header>-->
<div class="row">

<div class="col-sm-3 matchHeight"></div>
<div class="col-sm-6 " style="background-image:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.8)); margin:30px 0px; border-radius:5px">

<section class="" style="padding:15px">
<div class="contact_info">

                    <div class="contact_detail">
                		<div id="contact_form"><br>
                        
                        	<h3 align="center" style="color:#fff"><?php if ($msg != "") echo "$msg<br><br>"; ?></h3>
                        	
							<form action="contact.php" method="post" name="form" class="clearfix" enctype="multipart/form-data">
                            <div class="col-sm-6">
                            
                            <input class="input-text form-control" type="text" name="name" id="name" style="height: 36px;" placeholder="Your Name *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;" required="">
                   			<div class="cleaner_h10"></div>
                            
                        <input class="input-text form-control" type="text" name="mobile" id="mobile" style="height: 36px;" placeholder="Your Mobile No *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                            <div class="cleaner_h10"></div>
                            </div>
                            
                            <div class="col-sm-6">
                            <input class="input-text form-control" type="text" name="email" id="email" style="height: 36px;" placeholder="Your E-mail *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                    		<div class="cleaner_h10"></div>
                          
                      
                        
                        <input class="input-text form-control" type="text" name="city" id="city" style="height: 36px;" placeholder="Your City *">
                            <div class="cleaner_h10"></div>
                            </div>
                            <div class="col-sm-12">
                            <textarea name="message" class="form-control" id="comment" rows="4" cols="4" placeholder="Your Comment *" value="Your Comment *" ></textarea>
                    		<div class="cleaner_h10"></div>
                            
                            <input class="input-btn form-control" type="file" name="attachment" style="height: 36px; border-bottom:none">
                            <div class="cleaner_h10"></div>
                    		<input name="submit" class="input-btn" type="submit" value="Send message" id="contact-submit">
                            </div>
                  		</form>
                        
                        
                		</div>
              		</div>
              
            	</div>


</section>



</div>
<div class="col-sm-3 matchHeight"></div>
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
        $('#contact-submit').on('click', function(e){
            e.preventDefault();
            
			
            var name = $('#name').val();
            var email = $('#email').val();
            var mobile = $('#mobile').val();
            var comment = $('#comment').val();

           
			if(name == ''){
                alertify.error('Please Enter the Name');
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

			if(comment == ''){
                alertify.error('Please Enter the comment');
                return false;
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
                url: 'contact-mail.php',
                dataType: 'json',
                data: {
                    action: 'contact-submit',
					name: name,
					email: email,
					comment:comment,
                    mobile: mobile,
                },
                success: function (data) {
                    if(data.status == 'success'){
                    alertify.success(data.message);
                    $("#contact_us-form")[0].reset();
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

