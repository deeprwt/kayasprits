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
		$mail->Username = "chillpriya.ranjan@gmail.com";
		$mail->Password = "priya78275308";
		$mail->SMTPSecure = "ssl"; //TLS
		$mail->Port = 465; //587

        $mail->setFrom($_POST['email'],$_POST['name']);
		$mail->addAddress('web@kayaspiritsoffice.com');
		$mail->addReplyTo($_POST['email'],$_POST['name']);
		
//		$mail->addAddress('web@kayaspiritsoffice.com');
//		$mail->setFrom($email);
		$mail->Subject = $_POST['name']. '-Contact Form - Kayaspirits.com';
		$mail->isHTML(true);
		$mail->name = $name;
		$mail->mobile = $mobile;
//		$mail->Body = $message;

		$mail->Body ='Name: '.$_POST['name'].'<br>Email: '.$_POST['email'].'<br>Mobile: '.$_POST['mobile'].'<br>City: '.$_POST['city'].'<br>Massage:<br> '.$_POST['message'].'' ;
		
		$mail->addAttachment($file);

		if ($mail->send())
		    $msg = "Your email has been sent, thank you!";
		else
		    $msg = "Please try again!";

		unlink($file);
	}
?>
