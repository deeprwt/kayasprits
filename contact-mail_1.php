<?php

if(isset($_POST['submit'])) {

     	$name = $_POST['name'];
     	$email = $_POST['email'];
     	$mobile = $_POST['mobile'];
		$comment = $_POST['comment'];
	
		$to='chillpriya.ranjan@gmail.com';
		$subject='From Submission';
		$message="Name: ".$name."\n"."Mobile: ".$mobile."\n"."Comment: "."\n\n".$comment;
		$headers="From: ".$email;
		
     	if(mail($to, $subject, $message, $headers)){
			echo "<h1> Send Successfully! Thank you"." ".$name.", We will contact you shortly!"</h1>;
		}
		else{
			echo "Something went wrong!";
		}
		

	}
   


?>