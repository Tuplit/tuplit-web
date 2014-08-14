<?php
require_once('includes/CommonIncludes.php');
//add/edit/delete category
if(isset($_GET['action']) && $_GET['action'] == 'ADD_EDIT_DELETE_CATEGORY'){
	//$_GET			=	unEscapeSpecialCharacters($_GET);
	//$_GET			=	escapeSpecialCharacters($_GET);
	$categoryName 	= $_GET['CategoryName'];
	$categoryId 	= $_GET['CategoryId'];
	$postType		= $_GET['type'];
	$data	=	array(
					'CategoryName' 			=> $_GET['CategoryName'],
					'CategoryId'			=> $categoryId
				);
	$url			=	WEB_SERVICE.'v1/categories/products';
	if($postType == 1){
		$method			=	'POST';		
		$curlResponse	=	curlRequest($url,$method,$data, $_SESSION['merchantInfo']['AccessToken']);	
	}
	else if($postType == 2){
		//$method			=	'PUT';	
		$curlResponse	=	curlRequest($url,'PUT',json_encode($data), $_SESSION['merchantInfo']['AccessToken']);	
	}
	else if($postType == 3){
		$delId 			= 	$categoryId;
		$delShow		=	1;
		$url			=	WEB_SERVICE.'v1/categories/'.$delId;
		$method			=	'DELETE';		
		$curlResponse	=	curlRequest($url,$method,null, $_SESSION['merchantInfo']['AccessToken']);	
	}
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$successMessage = $curlResponse['notifications'][0];
		if($postType == 3)
		$notification	=	2;
		else 
		$notification	=	1;
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$successMessage	=	$curlResponse['meta']['errorMessage'];
		$notification	=	2;
	} else {
		$successMessage = 	"Bad Request";
		$notification	=	3;
	}
	$message 				= $notification."###".$successMessage."###".$postType ;
	$response['message'] 	= $message;
	echo json_encode($response);
	
}
?>


