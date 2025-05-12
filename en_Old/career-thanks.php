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
		$mail->addAddress('info@kayaspiritsoffice.com');
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



<!-- Alertify default theme -->

<link rel="stylesheet" href="css/alertify.css"/>
<link rel="stylesheet" href="css/alertify-default.css"/>

<style>
@media(max-width:1440px) {
	.popup { background-color:rgba(0,0,0, 0.8);
  width:100%;
  height:100%;
  position:absolute;
  top:0;
  left:0;
  z-index:9999;
  transition: all .5s;
  text-align: center;
  }
.popup-content { width:440px; background:#fff; position:absolute; top:10%; left:50%; transform: translate(-50%, -50%); padding:1em; border-radius: 1em; transition: all .5s; }

.popup-title { color:#000; margin-bottom:1em; text-align: center;}
.popup-para { line-height:1.5em; text-align: justify;}
.modal-open { display:inline-block; color:#039; margin:2em;}
#popupfoot{font-size: 16pt;padding: 15px 20px;}
#popupfoot a{text-decoration: none;padding: 5px 20px;margin:10px;border:1px solid #ccc;border-radius:5px;}
#popupfoot a:hover{text-decoration: none;}
.agree:hover{background-color: #D1D1D1;}
	}
@media(max-width:768px) {
.popup { background-color:rgba(0,0,0, 0.8);
  width:100%;
  height:100%;
  position:absolute;
  top:0;
  left:0;
  z-index:9999;
  transition: all .5s;
  text-align: center;
  }
.popup-content { width:340px; background:#fff; position:absolute; top:8%; left:50%; transform: translate(-50%, -50%); padding:1em; border-radius: 1em; transition: all .5s; }

.popup-title { color:#000; margin-bottom:1em; text-align: center;}
.popup-para { line-height:1.5em; text-align: justify;}
.modal-open { display:inline-block; color:#039; margin:2em;}
#popupfoot{font-size: 16pt;padding: 15px 20px;}
#popupfoot a{text-decoration: none;padding: 5px 20px;margin:10px;border:1px solid #ccc;border-radius:5px;}
#popupfoot a:hover{text-decoration: none;}
.agree:hover{background-color: #D1D1D1;}

}
  </style>

<div class="container-fluid">
  
  <!-- Modal -->
  <div class="popup">
      <!-- Modal content-->
      <div class="popup-content">
          <h2 class="popup-title">Thank You</h2>
          <p class="popup-para">Thank you for Career. You are very important to us, all information received will always remain confidential. We will contact you as soon as we review your message.</p>
          <div id="popupfoot"> <a href="career.php" class="agree">OK</a> </div>
      </div>
      
    </div>
</div> 
    
<div class="container-fluid light section" style="background-image: url(images/block-bg-2.jpg);">
<div class="container">
<div class="row">
<div class="col-sm-5 col-sm-push-1 matchHeight">
<img src="images/careers.png" alt="" />
</div>
<div class="col-sm-5 col-sm-push-1 matchHeight">
<section class="alignMiddle mobile-center">
<header>
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
                <p>info@kayaspiritsoffice.com</p>
                
                </div>
                
</section>



</div>



<div class="col-sm-6 col-sm-push-1 matchHeight" style="background-image:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.8)); margin:30px 0px; border-radius:5px">
<section class="" style="padding:15px">
<div class="contact_info">

                    <div class="contact_detail">
                		<div id="contact_form">
                		    
                		    <h3 align="center" style="color:#fff"><?php if ($msg != "") echo "$msg<br><br>"; ?></h3>
                		    
                		    <form action="career.php" method="post" name="form" class="clearfix" enctype="multipart/form-data">
                		    
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



</div>

</div>
</div>
</div>

   

<?php include 'footer.php'; ?>

