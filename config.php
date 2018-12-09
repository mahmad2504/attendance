<?php
	session_start();
	require_once "GoogleAPI/vendor/autoload.php";
	$gClient = new Google_Client();
	$gClient->setClientId("654082223162-2ggb91lji90npidu759joaq14bpr9e4r.apps.googleusercontent.com");
	$gClient->setClientSecret("6LjKk81AAtUsWGrz9cY-kN1N");
	$gClient->setApplicationName("Mentor Lahore Attendance");
	$gClient->setRedirectUri("http://localhost/excel/g-callback.php");
	$gClient->addScope("https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email");
	
?>
