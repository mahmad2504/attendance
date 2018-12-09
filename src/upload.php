<?php
//turn on php error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('UPLOAD_FOLDER','../data/upload');
if($_SESSION['admin']==0)
	return json_encode(array());
							
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	 
	$name     = $_FILES['file']['name'];
	$tmpName  = $_FILES['file']['tmp_name'];
	$error    = $_FILES['file']['error'];
	$size     = $_FILES['file']['size'];
	$ext      = strtolower(pathinfo($name, PATHINFO_EXTENSION));
   
	$response = "Success";
	
	switch ($error) {
		case UPLOAD_ERR_OK:
			$valid = true;
			//validate file extensions
			if ( !in_array($ext, array('xlsx')) ) {
				$valid = false;
				$response = 'Invalid file extension.';
			}
			//validate file size
			if ( $size/1024/1024 > 2 ) {
				$valid = false;
				$response = 'File size is exceeding maximum allowed size.';
			}
			//upload file
			if ($valid) {
				$targetPath =  UPLOAD_FOLDER . DIRECTORY_SEPARATOR. $name;
				move_uploaded_file($tmpName,$targetPath);
				$_GET['file'] = $name;
				require_once('data.php');
				$a = array();
				$a['response'] = $response;
				echo json_encode($a);
				exit;
			}
			break;
		case UPLOAD_ERR_INI_SIZE:
			$response = 'The uploaded file exceeds the upload_max_filesize directive in php.ini.';
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$response = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.';
			break;
		case UPLOAD_ERR_PARTIAL:
			$response = 'The uploaded file was only partially uploaded.';
			break;
		case UPLOAD_ERR_NO_FILE:
			$response = 'No file was uploaded.';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$response = 'Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$response = 'Failed to write file to disk. Introduced in PHP 5.1.0.';
			break;
		case UPLOAD_ERR_EXTENSION:
			$response = 'File upload stopped by extension. Introduced in PHP 5.2.0.';
			break;
		default:
			$response = 'Unknown error';
		break;
	}
	$a = array();
	$a['response'] = $response;
	echo json_encode($a);
}
?>