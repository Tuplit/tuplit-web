<?php
ob_start();
session_start();

// We're putting all our files in a directory called images.
$uploaddir = '../webresources/uploads/temp/';

// The posted data, for reference
$file = $_POST['value'];
$name = $_POST['name'];

// Get the mime
$getMime = explode(';', $file);
$mimeImg = explode('/', $getMime[0]);
$mime	=	$mimeImg[1];
// Separate out the data
$data = explode(',', $file);

// Encode it correctly
$encodedData = str_replace(' ','+',$data[1]);
$decodedData = base64_decode($encodedData);

// You can use the name given, or create a random name.
// We will create a random name!

//$randomName = substr_replace(sha1(microtime(true)), '', 12).'.'.$mime;
$randomName =	$_SESSION['merchantInfo']['MerchantId']."_".$_GET['img']."_myStore".'.'.$mime;
if (file_exists($uploaddir.$randomName)) //. $imageType[1]
{
	@unlink($uploaddir.$randomName );
}
if(file_put_contents($uploaddir.$randomName, $decodedData)) {
	echo '1';
}
else {
	// Show an error message should something go wrong.
	echo "Something went wrong. Check that the file isn't corrupted";
}


?>