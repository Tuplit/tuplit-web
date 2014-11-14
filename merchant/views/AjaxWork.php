<?php
require_once('includes/CommonIncludes.php');
//add/edit/delete category
if(isset($_GET['action']) && $_GET['action'] == 'ADD_EDIT_DELETE_CATEGORY'){
	$categoryName 	= 	$_GET['CategoryName'];
	$categoryId 	= 	$_GET['CategoryId'];
	$postType		= 	$_GET['type'];
	$html			= 	'';
	$data			=	array(
							'CategoryName' 			=> $_GET['CategoryName'],
							'CategoryId'			=> $categoryId
						);
	$url			=	WEB_SERVICE.'v1/categories/products';
	if($postType == 1){
		$curlResponse	=	curlRequest($url,'POST',$data, $_SESSION['merchantInfo']['AccessToken']);	
	}
	else if($postType == 2){
		$curlResponse	=	curlRequest($url,'PUT',json_encode($data), $_SESSION['merchantInfo']['AccessToken']);	
	}
	else if($postType == 3){
		$delId 			= 	$categoryId;
		$url			=	WEB_SERVICE.'v1/categories/productscount/'.$categoryId;
		$curlResponse	=	curlRequest($url,'GET',null, $_SESSION['merchantInfo']['AccessToken']);	 
		if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
			
			$items	=	'item';
			if($curlResponse['meta']['TotalCount'] > 0) {
				if($curlResponse['meta']['TotalCount'] > 1)
					$items .= 's';
				$html 	= 	'<div class="form-group col-xs-12" id="secondDiv" >
								<div class="col-xs-12 popup_title text-center" style="padding-bottom:20px;"><h1>Delete category?</h1></div>
								<div class="col-xs-12 delete_cat">
									<p align="center" style="margin-bottom:0px;">There are '.$curlResponse['meta']['TotalCount'].' '.$items.' in this category.</p>
									<p align="center" style="padding-bottom:15px;">Are you sure you want to delete all '.$curlResponse['meta']['TotalCount'].' '.$items.'?</p>
									<input class="form-control" type="hidden" maxlength="30" name="CategoryId" id="CategoryId" value="'.$categoryId.'" />
									<!-- <input id="Yes" type="checkbox" name="Yes" style="display: none;" checked="checked">
									<div id="#Yes" class="tog on" style="display: block;" align="center"></div> -->
								</div>
								<div class="col-xs-12">
								<span class="email_notification category_notification">
									<input checked="checked" style="display: none;" id="EmailNotification" name="EmailNotification" type="checkbox">
									<div id="#EmailNotification" class="tog on del_category"></div>
									<input type="hidden" id="" name="" value="">
								</span>
								</div>
							</div>	
							<div class="footer col-md-12 text-center clear"> 
								<br>
								<a href="#" class="link col-xs-3 cancel" onclick="parent.jQuery.fancybox.close();">CANCEL</a>
								<input type="button" name="delete_category_submit" id="delete_category_submit" value="DELETE CATEGORY & ALL ITEMS" onclick="category_operation(4);" class="btn btn-success col-xs-9 " style="background:#f56954;">
							</div>';				
			} else {
				$html 			= 	'';
				$url			=	WEB_SERVICE.'v1/categories/'.$delId;
				$method			=	'DELETE';		
				$curlResponse	=	curlRequest($url,$method,null, $_SESSION['merchantInfo']['AccessToken']);
			}
		}
	}
	else if($postType == 4){
		$html 			= 	'';
		$url			=	WEB_SERVICE.'v1/categories/'.$categoryId;
		$method			=	'DELETE';		
		$curlResponse	=	curlRequest($url,$method,null, $_SESSION['merchantInfo']['AccessToken']);
	}
	if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
		$successMessage = $curlResponse['notifications'][0];
		/*if($postType == 3)
			$notification	=	2;
		else*/
			$notification	=	1;
	} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
		$successMessage		=	$curlResponse['meta']['errorMessage'];
		$notification		=	2;
	} else {
		$successMessage 	= 	"Bad Request";
		$notification		=	3;
	}
	$message 				= $notification."###".$successMessage."###".$postType."###".$html ;
	$response['message'] 	= $message;
	echo json_encode($response);
	
}

if(isset($_GET['action']) && $_GET['action'] == 'CHECKPIN'){
	$Pincode	=	$_GET['Pincode'];
	if($_SESSION['merchantDetailsInfo']['Pincode'] == $Pincode) {
		$_SESSION['MerchantPortalAccessTime'] = time();
		echo "1";
	} else {
		echo "2";
	}
}
if(isset($_GET['action']) && $_GET['action'] == 'UPDATEPIN'){
	if(isset($_GET['type']) && $_GET['type'] == 1) {
		$_SESSION['MerchantPortalAskPin']   	=	0;
		$_SESSION['MerchantPortalAccessTime']   =	time();
		echo "2";
	} else {
		merchant_login_check();
		if($_SESSION['MerchantPortalAskPin'] == 1) {		
			echo "1";
		} else {
			echo "2";
		}
	}
}
?>


