<?php

if ($_POST['action'] == 'contact-submit'){

     $name = $_POST['name'];
	 $email = $_POST['email'];
	 $mobile = $_POST['mobile'];
	 $comment = $_POST['comment'];
	 
	 $file = "attachment/" . basename($_FILES['attachment']['name']);
		if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file)) {
		
		} else
			$smg = "Plase check your attachment!";
     

//  ------------------------------------------------------------------------------------
//  --------------------------------------- Mail ---------------------------------------


         $to = "web@kayaspiritsoffice.com";
      
		
        $subject = $name . 'Contacts Now Form | Kaya Spirits';    
        
        $from="$name"; 
    
//          Always set content-type when sending HTML email
//         $headers = "MIME-Version: 1.0" . "\r\n";
// 	       $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
//         $headers .= "Reply-To: " . $firstname . $lastname . " <" . $email ."> \r\n";
//         $headers .= "Return-Path: " . $email ." \r\n";
// 		   $headers .= "From: info@kortek.in" . "\r\n" .
// 			"CC: ";
			
			
			
        $message =  'Name : ' .$name;
        $message .=  'Email : ' .$email;
        $message .=  'Mobile no. : ' .$mobile;
        $message .=  'Comment : ' .$comment;
        
        $message .=  'Comment : ' .$comment;
        
       
        
    //     $message =  '
        
    //    Name: '.$name . '
    //    Email ID:    '.$email . '
    //    Mobile No:  '.$mobile . '
	//	Comment:   '.$comment . '';
        
        $message="Name: ".$name."\n"."Email: ".$email."\n"."Mobile: ".$mobile."\n"."Comment: "."\n\n".$comment;
		

    $send_mail = mail($to,$subject,$message,"From:".$from);
    
    if($send_mail)
    {
        $response['status'] = 'success';
        $response['message'] = 'Your message successfully sent !';
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