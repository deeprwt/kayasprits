<?php

if ($_POST['action'] == 'subscribe-submit'){

    
	 $email = $_POST['email'];
	
	
//  ------------------------------------------------------------------------------------
//  --------------------------------------- Mail ---------------------------------------


         $to = "web@kayaspiritsoffice.com";
      
		
        $subject = $name . 'Subscribe Now Form | Kaya Spirits';    
        
        $from="$name"; 
    
//          Always set content-type when sending HTML email
//         $headers = "MIME-Version: 1.0" . "\r\n";
// 	       $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
//         $headers .= "Reply-To: " . $firstname . $lastname . " <" . $email ."> \r\n";
//         $headers .= "Return-Path: " . $email ." \r\n";
// 		   $headers .= "From: info@kortek.in" . "\r\n" .
// 			"CC: ";
			
			
			
       
        $message .=  'Email : ' .$email;
       
        
       
        
    $message =  '
        
	  Email:   '.$email . '';
        
     //   $message="Email: ".$email."\n"."Email: ".$email."\n"."Mobile: ".$mobile."\n"."Comment: "."\n\n".$comment;
		
		
		

    $send_mail = mail($to,$subject,$message,"From:".$from);
    
    if($send_mail)
    {
        $response['status'] = 'success';
        $response['message'] = 'Your subscribe successfully sent !';
        $response['data'] = '';
    
        echo json_encode($response);
    }

    else {  
        $response['status'] = 'failed';
        $response['error'] = "There is some problem, Please try again !!!";
        $response['data'] = '';
        echo json_encode($response);
    }

//  --------------------------------------- Mail ends-----------------------------------
die;
}

?>