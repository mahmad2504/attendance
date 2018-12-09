<?php
	require_once "config.php";

	if (isset($_SESSION['access_token']))
		$gClient->setAccessToken($_SESSION['access_token']);
	else if (isset($_GET['code'])) {
		$token = $gClient->fetchAccessTokenWithAuthCode($_GET['code']);
		$_SESSION['access_token'] = $token;
	} else {
		header('Location: login.php');
		exit();
	}

	$oAuth = new Google_Service_Oauth2($gClient);
	$userData = $oAuth->userinfo_v2_me->get();

	$_SESSION['id'] = $userData['id'];
	$_SESSION['email'] = $userData['email'];
	$_SESSION['gender'] = $userData['gender'];
	$_SESSION['picture'] = $userData['picture'];
	$_SESSION['familyName'] = $userData['familyName'];
	$_SESSION['givenName'] = $userData['givenName'];

	$users = file_get_contents('users.json');
	$users = json_decode($users);
	$_SESSION['admin'] = 0;
	$_SESSION['userid'] = 0;
	foreach($users as $user)
	{
		echo $_SESSION['email']."---".$user->email."<br>";
		if($_SESSION['email'] == $user->email)
		{
			$_SESSION['userid'] = $user->id;
			if($user->admin == 1)
				$_SESSION['admin'] = 1;
			
		}
	}
	header('Location: index.php');
	exit();
?>