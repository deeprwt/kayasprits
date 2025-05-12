<?php
if(!empty($_POST)){
    
    $servername = "localhost";
    $username = "kayasmu3_eazyuse";
    $password = 'f(&oat{)E3$c';
    $dbname = "kayasmu3_eazypay";
    
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    // print_r($_POST);
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $amount = $_POST['amount'];
    $ref_number = rand(10,10000);
    
    $sql = "INSERT INTO pr_eazypay_data(name, email, phone, transaction_amount, ReferenceNo) VALUES ('$name', '$email', '$phone', '$amount', '$ref_number')";
    
    mysqli_query($conn, $sql);
    mysqli_close($conn);
    
    $merchant_id = "258785"; 
    $key = "1101622111105500"; 
    $ref_no = $ref_number;
    $sub_mer_id = "45"; 
    $amt = $amount; 
    // $return_url = "https://www.kayaspirits.com/index.php";
    // $return_url = "";
    $paymode = "9"; 
    $man_fields = $ref_no."|".$sub_mer_id."|".$amt; 
    $opt_fields = $name.$phone.$email;
    $e_sub_mer_id = aes128Encrypt($sub_mer_id, $key); 
    $e_ref_no = aes128Encrypt($ref_no, $key); 
    $e_amt = aes128Encrypt($amt, $key); 
    $e_return_url = aes128Encrypt($return_url, $key); 
    $e_paymode = aes128Encrypt($paymode, $key); 
    $e_man_fields = aes128Encrypt($man_fields, $key);
    
    $link = "https://eazypay.icicibank.com/EazyPG?merchantid=$merchant_id&mandatory fields=$e_man_fields&optional fields=$opt_fields&returnurl=$e_return_url&Reference No=$e_ref_no&submerchantid=$e_sub_mer_id&transaction amount=$e_amt&paymode=$e_paymode";
    
    header("Location: $link");
    exit();
}

?>
<?php
function aes128Encrypt($plaintext,$key){ 
    $cipher = "AES-128-ECB"; 
    if (in_array($cipher, openssl_get_cipher_methods())) 
    { 
        $ivlen = openssl_cipher_iv_length($cipher); 
        $iv = openssl_random_pseudo_bytes($ivlen); 
        $ciphertext = openssl_encrypt($plaintext, $cipher, $key, $options=0, $iv); 
        //return $ciphertext."n"; 
        return $ciphertext; 
    
    }
}
?>

<?php include 'header.php'; ?>

<style>
#hero.single-page {
    height: 350px!important;
    min-height: 350px;
    border-bottom: 1px solid #cbb27c;
    background-position: center;
    background-size: cover;
}
.light .btn, .light .btn:hover {
    color: #fff;
    box-shadow: inset 0 0 0 2px #866525;
}
.form-control {
   
    margin-bottom: 20px;
}
</style>
<div id="hero" class="single-page section" style="background-image:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.95)), url(images/slider/eazypay.jpg); height:300px">

    <div class="container">
        <div class="row blurb scrollme animateme" data-when="exit" data-from="0" data-to="1" data-opacity="0" data-translatey="100">
        <div class="contact-grid">
            
            
        </div>
        </div>
    </div>
</div>

<div class="container-fluid light section" style="background-image:linear-gradient(rgba(255,255,255,.5),rgba(255,255,255,.5)), url(images/Martell_sudy_1024x670.jpg); background-size:cover; background-attachment:fixed">
<div class="container">
<!--<header>
<h2 style="text-align:center">Get in touch with Kaya Spirits<span class="title-under-black"></span></h2>
</header>-->
<div class="row">

<div class="col-sm-3 matchHeight"></div>
<div class="col-sm-6 col-md-6" style="background-image:linear-gradient(rgba(0,0,0,.8),rgba(0,0,0,.8)); margin:30px 0px; border-radius:5px">

<section class="" style="padding:25px">
<div class="contact_info">

                    <div class="contact_detail">
                		<div id="contact_form">
                        
                        	<h1 style="margin-bottom: 20px; color:#FFF; text-align:center">Payment Form</h1>
                            <?php if(!empty($link)){ ?>
                            <div>
                                <a href="<?php echo $link ?>" style="display:block;" id="pay-button-eazypay">Pay</a>
                            </div>
                            <?php } ?>
                            <?php if(empty($_POST)){ ?>
                        	
							<form action="eazy-pay-form.php" method="post"><br>
                            <div class="col-sm-6">
                            
                            <input class="input-text form-control" type="text" name="name" style="height: 36px;" placeholder="Your Name *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;" required="">
                   			<div class="cleaner_h10"></div>
                            
                        <input class="input-text form-control" type="number" name="phone" style="height: 36px;" placeholder="Your Mobile No *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                            <div class="cleaner_h10"></div>
                            </div>
                            
                            <div class="col-sm-6">
                            <input class="input-text form-control" type="email" name="email" style="height: 36px;" placeholder="Your E-mail *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                    		<div class="cleaner_h10"></div>
                            
                            <input class="input-text form-control" type="number" name="amount" style="height: 36px;" placeholder="Amount *" onFocus="if(this.value==this.defaultValue)this.value='';" onBlur="if(this.value=='')this.value=this.defaultValue;">
                          
                     
                        	<!--<input class="input-text form-control" type="text" name="city" id="city" style="height: 36px;" placeholder="Your City *">-->
                            <div class="cleaner_h10"></div>
                            </div>
                            <div class="col-sm-6">
                            
                    		<div class="cleaner_h10"></div>
                            </div>
                            
                            
                            <div class="col-sm-12" style="text-align:right; margin-top:20px">
                    		<input type="submit" class="btn btn-default" value="Proceed & Pay">
                            <div class="cleaner_h10"><br></div>
                            </div>
                  		</form>
                        <?php
						}
						?>
                        
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

<div class="container-fluid dark section no-padding" style="background-image:linear-gradient(rgba(255,255,255,1),rgba(255,255,255,1))">
    
    <div class="col-md-6 col-md-offset-3">
        <h2 style="color: #000; margin-bottom: 20px;">Payment Form</h2>
        <?php if(!empty($link)){ ?>
        <div>
            <a href="<?php echo $link ?>" style="display:block;" id="pay-button-eazypay">Pay</a>
        </div>
        <?php } ?>
        <?php if(empty($_POST)){ ?>
       <form action="eazy-pay-form.php" method="post">
           <div class="form-group">   
               <label>Name*</label>
               <input type="text" name="name" required>
           </div>
           <div class="form-group">   
               <label>Email*</label>
               <input type="email" name="email" required>
           </div>
           <div class="form-group">   
               <label>Phone</label>
               <input type="number" name="phone">
           </div>
           <div class="form-group">   
               <label>Amount*</label>
               <input type="number" name="amount" required>
           </div>
           <div class="form-group">   
               <input type="submit" class="btn btn-primary" value="Proceed & Pay">
           </div>
       </form>
       <?php
        }
       ?>
    </div>
</div>
-->
<?php include 'footer.php'; ?>