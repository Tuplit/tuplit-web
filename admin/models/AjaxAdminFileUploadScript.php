<?php
ob_start();
session_start();
$error = "";
$msg = "";
$file_types_array = array(
    "1" => "image/jpeg",
    "2" => "image/pjpeg",
    "3" => "image/jpg",
    "4" => "image/png"
);
$imagePath = '../webresources/uploads/temp/';
$fileElementName	=	$_GET['filename'];
if(isset($_GET['files']))
{	
	$error = false;
	$files = array();
	foreach($_FILES as $file)
	{
		if (!empty($file['error'])) {
			switch ($file['error']) {
				case '1':
					$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
					break;
				case '2':
					$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
					break;
				case '3':
					$error = 'The uploaded file was only partially uploaded';
					break;
				case '4':
					$error = 'No file was uploaded.';
					break;
				case '6':
					$error = 'Missing a temporary folder';
					break;
				case '7':
					$error = 'Failed to write file to disk';
					break;
				case '8':
					$error = 'File upload stopped by extension';
					break;
				case '999':
				default:
					$error = 'No error code avaiable';
			}
		} else if (empty($file['tmp_name']) || $file['tmp_name'] == 'none') {
			$error = 'No file was uploaded..';
		} else {
			if ($file['tmp_name'] != '') {
				$res = getImageSize($file['tmp_name']);
				
				//image dimension checking
				if(($fileElementName == 'cover_image') && ($res[0] != '640' || $res[1] != '640'))
					$error = 'Image dimension must be 640X640';
				else if($fileElementName == 'coach_image' && ($res[0] < '320' || $res[1] < '320'))
					$error = 'Image dimension should be greater than 320X320';
				if (strstr($fileElementName, 'Slider_Image')) {
					if( $res[0] != '640' || $res[1] != '1136' ) 
						$error = 'Image dimension should be 640x1136';
				}
				else if($res[0] < '100' || $res[1] < '100')
					$error = 'Image dimension should be greater than 100X100';
				//tutor images
				if (strstr($fileElementName, 'Tutorial_Image')) {
					if( $res[0] != '640' || $res[1] != '1136' ) 
						$error = 'Image dimension should be 640x1136';
				}
				else if($res[0] < '100' || $res[1] < '100')
					$error = 'Image dimension should be greater than 100X100';
					
				//image type / size checking
				if (!in_array($file['type'], $file_types_array)) {
					$error = 'Please upload JPEG, JPG and PNG images only.';
				}
				else if ($file['size'] > 5242880) {
					$error = 'Image size should not be greater than 5 MB';
				}
				else if (!is_writable($imagePath)) {
					$error = 'The image folder is write protected. Try again';
				}
			}
			else
				$error = 'Upload any of jpg, png or gif image.';
			if ($error == '') {
				$imageType = explode("/", $file['type']);
				$image_name = $fileElementName.'_'.time();
				if (file_exists($imagePath . $image_name . ".".$imageType[1] )) //. $imageType[1]
				{
					@unlink($imagePath . $image_name . ".".$imageType[1] );
				}
				copy($file['tmp_name'], $imagePath . $image_name . ".".$imageType[1]);
				$msg .= $image_name . '####'.$imageType[1] ;
			}
			//for security reason, we force to remove all uploaded file
			//@unlink($_FILES[$fileElementName]);
		}
		$result = array("error"=>$error,"msg"=>$msg);
		echo json_encode($result);	
	}
}
die();

?>