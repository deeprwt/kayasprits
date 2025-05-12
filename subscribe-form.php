

<!-- Alertify default theme -->
<link rel="stylesheet" href="css/alertify.css"/>
	<link rel="stylesheet" href="css/alertify-default.css"/>
<!-- Alertify default theme End-->
    



<!--<div class="container-fluid light section no-padding" style="background-image: linear-gradient(rgba(0,0,0,.7),rgba(0,0,0,.7)), url(images/Martell_sudy_1024x670.jpg); background-size:cover">-->
<div class="container-fluid light section" style="background-image:linear-gradient(rgba(255,255,255,.5),rgba(255,255,255,.5)), url(images/Martell_sudy_1024x670.jpg); background-size:cover; background-attachment:fixed">
<div class="container">
<div class="row">

<div class="col-sm-3 matchHeight"></div>
<div class="col-sm-6 " style="background-image:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.8)); margin:30px 0px; border-radius:5px">
<section class="" style="padding:15px">
<div class="contact_info">

                    <div class="contact_detail">
                		<div id="contact_form">
                  		<form id="contact_us-form" class="clearfix">
                    		
                    		<input class="input-text form-control" type="text" name="email" id="email" style="height: 36px;" value="Your E-mail *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                    		<div class="cleaner_h10"></div>
                           
                    		<input name="submit" class="input-btn" type="submit" value="Send message" id="contact-submit">
                  		</form>
                        
                        <!--<form action="contact-mail.php" method="post" name="form" class="clearfix">
                    		<input class="input-text form-control" type="text" name="name" style="height: 36px;" value="Your Name *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                   			<div class="cleaner_h10"></div>
                    		<input class="input-text form-control" type="text" name="email" style="height: 36px;" value="Your E-mail *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                    		<div class="cleaner_h10"></div>
                            <input class="input-text form-control" type="text" name="mobile" style="height: 36px;" value="Your Phone *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                    		<div class="cleaner_h10"></div>
                      		<textarea name="comment" class="form-control" rows="5" cols="5" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;"></textarea>
                    		<div class="cleaner_h10"></div>
                            <input class="input-text form-control" type="file" name="attachment" style="height: 36px;">
                            <div class="cleaner_h10"></div>
                    		<input name="submit" class="input-btn" type="submit" value="Send message">
                  		</form>-->
                        
                        
                		</div>
              		</div>
              
            	</div>


</section>


</div>
<div class="col-sm-3 matchHeight"></div>
</div>
</div>
</div>



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
                url: 'subscribe1.php',
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
    </script>



