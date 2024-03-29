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
 $fileElementName = $_POST['process'];
if (!empty($_FILES[$fileElementName]['error'])) {
    switch ($_FILES[$fileElementName]['error']) {
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
}
elseif (empty($_FILES[$fileElementName]['tmp_name']) || $_FILES[$fileElementName]['tmp_name'] == 'none') {
    $error = 'No file was uploaded..';
}
else {
	if ($_FILES[$fileElementName]['tmp_name'] != '') {
		$res = getImageSize($_FILES[$fileElementName]['tmp_name']);
		if($fileElementName == 'user_photo' && ( $res[0] < '100' || $res[1] < '100' ) ){
			$error = 'Image dimension should be greater than 100x100';
		}
		if($fileElementName == 'category_photo'){
			if(( $res[0] < '80' || $res[0] > '100' ) || ( $res[1] < '80' || $res[1] > '100' ))
				$error = 'Image dimension should be greater than 80x80 and less then 100x100';
		}
		if($fileElementName == 'merchant_photo' && (($res[0] > '700' || $res[0] < '600') || ($res[1] > '300' || $res[1] < '200')))
				$error = 'Image dimension should be greater than 600X200 and lesser than 700X300';
		if($fileElementName == 'icon_photo' && (($res[0] > '200' || $res[0] < '100') || ($res[1] > '200' || $res[1] < '100')))
				$error = 'Image dimension should be greater than 100X100 and lesser than 200X200';
		if (strstr($fileElementName, 'Slider_Image')) {
			if( $res[0] != '640' || $res[1] != '570' ) 
				$error = 'Image dimension should be 640x570';
		}
		if (strstr($fileElementName, 'Tutorial_Image')) {
			if( $res[0] != '640' || $res[1] != '570' ) 
				$error = 'Image dimension should be 640x570';
		}
		if($fileElementName == 'admin_logo' && ( $res[0] != '100' || $res[1] != '100' ) ){
			$error = 'Image dimension should be 100x100';
		}
		/*if( $fileElementName == 'category_photo' && ( $res[0] > '80' || $res[1] > '80' ) ){
			$error = 'Image dimension should be less than 100x100';
		}*/
		/*if( $fileElementName == 'com_photo' && ( $res[0] < '640' || $res[1] < '240' ) ){
			$error = 'Image dimension should be greater than 640x240';
		}
		if( $fileElementName == 'merchant_photo' && ( $res[0] < '640' || $res[1] < '240' ) ){
			$error = 'Image dimension should be greater than 640x240';
		}*/
		/*if($fileElementName == 'product_photo'){
			if(( $res[0] > '400' || $res[0] < '300' ) || ( $res[1] > '400' || $res[1] < '300' ))
				$error = 'Image dimension should be greater than 300x300 and less then 400x400';
		}*/
		if (!in_array($_FILES[$fileElementName]['type'], $file_types_array)) {
            $error = 'Please upload JPEG, JPG and PNG images only.';
        }
		/*else if( $res[0] < '100' || $res[1] < '100' ){
			$error = 'Image dimension should be greater than 100x100';
		} */
		else if ($_FILES[$fileElementName]['size'] > 1500000) {
            $error = 'Image size should not be greater than 5 MB';
        }
        else if (!is_writable($imagePath)) {
            $error = 'The image folder is write protected. Try again';
        }
    }
    else
        $error = 'Upload any of jpg, png or gif image.';
    if ($error == '') {
        $imageType = explode("/", $_FILES[$fileElementName]['type']);
        $image_name = $fileElementName;//$_SESSION['tuplit_admin_user_id']."_".
        if (file_exists($imagePath . $image_name . ".".$imageType[1] )) //. $imageType[1]
		{
			@unlink($imagePath . $image_name . ".".$imageType[1] );
		}
       // copy($_FILES[$fileElementName]['tmp_name'], $imagePath . $image_name . ".".$imageType[1]);
	   move_uploaded_file($_FILES[$fileElementName]['tmp_name'], $imagePath . $image_name . ".".$imageType[1] );
        $msg .= $image_name . '####'.$imageType[1] ;
    }
    //for security reason, we force to remove all uploaded file
    @unlink($_FILES[$fileElementName]);
}
$result = array("error"=>$error,"msg"=>$msg);
echo json_encode($result);
?>