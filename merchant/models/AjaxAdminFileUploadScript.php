<?php
ob_start();
session_start();
/* by jeeva */
$error = "";
$msg = "";
$file_types_array = array(
    "1" => "image/jpeg",
    "2" => "image/pjpeg",
    "3" => "image/jpg",
    "4" => "image/png"
);
$mystore_array 	= array( "myStore_1","myStore_2","myStore_3","myStore_4","myStore_5","myStore_6","myStore_7","myStore_8","myStore_9","myStore_10");
$imagePath 		= '../webresources/uploads/temp/';
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
				
				//echo "(($res[0] > '100' || $res[0] < '200') || ($res[1] > '100' || $res[1] < '200')))";
				//image dimension checking
				if(($fileElementName == 'merchant_photo' || in_array($fileElementName, $mystore_array))&& (($res[0] > '700' || $res[0] < '600') || ($res[1] > '300' || $res[1] < '250')))
					$error = 'Image dimension should be LARGER than 600X250 and SMALLER than 700X300';
				else if($fileElementName == 'product_photo' && (($res[0] > '350' || $res[0] < '250') || ($res[1] > '350' || $res[1] < '250')))
					$error = 'Image dimension should be greater than 250X250 and lesser than 350X350';
				else if($fileElementName == 'icon_photo' && (($res[0] > '200' || $res[0] < '100') || ($res[1] > '200' || $res[1] < '100')))
					$error = 'Image dimension should be greater than 100X100 and lesser than 200X200';
				else if($res[0] < '100' || $res[1] < '100')
					$error = 'Image dimension should be greater than 100X100';
					
				//image type / size checking
				if (!in_array($file['type'], $file_types_array)) {
					$error = 'Please upload JPEG, JPG and PNG images only.';
				}
				else if ($file['size'] > 1500000) {
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
				//$image_name = $fileElementName.'_'.time();
				if(isset($_GET['imagetot']) && !empty($_GET['imagetot']))
					$image_name =	$_SESSION['merchantInfo']['MerchantId']."_".$_GET['imagetot']."_".$fileElementName;
				else
					$image_name =	$_SESSION['merchantInfo']['MerchantId']."_".$fileElementName;
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