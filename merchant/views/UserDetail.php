<?php 
require_once('includes/CommonIncludes.php');
merchant_login_check();

//getting merchant details

if(isset($_SESSION['merchantDetailsInfo']) && is_array($_SESSION['merchantDetailsInfo'])){
	$merchantInfo  =	$_SESSION['merchantDetailsInfo'];	
}
else{
	$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
	$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId;
	$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);	
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
	 {
		$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
		$merchantInfo  =	$_SESSION['merchantDetailsInfo'];
	}
}
if(isset($_GET['viewId']) && $_GET['viewId'] != '' ){
	$UserId						=	base64_decode($_GET['viewId']);
	//getting user details
	$url						=	WEB_SERVICE.'v1/users/'.$UserId.'?Type=basic';
	$curlUserResponse 			= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlUserResponse) && is_array($curlUserResponse) && $curlUserResponse['meta']['code'] == 200 && is_array($curlUserResponse['userDetails']) ) {
		if(isset($curlUserResponse['userDetails'])){
			$userDetails 	  = $curlUserResponse['userDetails']['Details'];	
		}
	} else if(isset($curlUserResponse['meta']['errorMessage']) && $curlUserResponse['meta']['errorMessage'] != '') {
			$errorMessage	=	$curlUserResponse['meta']['errorMessage'];
	} else {
			$errorMessage	= 	"Bad Request";
	} 
}
if(isset($userDetails) && is_array($userDetails) && count($userDetails) > 0){
	$FirstName     				= $userDetails["FirstName"];
	$LastName     				= $userDetails["LastName"];
	$Email      				= $userDetails["Email"];
	$Location 					= $userDetails["Location"];
	$Country  					= $userDetails["Country"];
	$ZipCode  					= $userDetails["ZipCode"];
	$CellNumber					= $userDetails["CellNumber"];
	$original_image_path =	$image_path = MERCHANT_IMAGE_PATH.'no_user.jpeg'; 
	$original_image_path = '';
	if(isset($userDetails["Photo"]) && $userDetails["Photo"] != ''){
		$image_path = $userDetails["Photo"];
	}
	if(isset($userDetails["OriginalPhoto"]) && $userDetails["OriginalPhoto"] != ''){
		$original_image_path = $userDetails["OriginalPhoto"];
	}
}
commonHead();
?>
<body class="skin-blue fixed popup_bg">
	<div class="popup_white">
	<!-- <section class="content"> -->
		<div class="row">	
					<div class="col-xs-12 user_details" style="padding-top:12px;">
						<?php if(isset($image_path) && $image_path != ''){ ?>
						<div class="col-xs-4 col-sm-4 col-md-5">
							<a onclick="return loaded;"  <?php if(isset($image_path) && basename($image_path) != "no_user.jpeg") { ?>href="<?php echo $original_image_path; ?>" class="fancybox" title="<?php echo  ucfirst($FirstName).' '.ucfirst($LastName);?>" <?php } ?> > 
								<img width="80" height="80" align="top" class="img_border" src="<?php echo $image_path;?>" >
							</a>
						</div>
					<?php } ?>
						<div class="col-xs-8 col-sm-8 col-md-5" style="line-height:20px"> 								
							<i class="fa fa-fw fa-user"></i> <?php if(isset($FirstName) && $FirstName != ''){ echo ucfirst($FirstName).' '; } ?>								
						
							<?php if(isset($LastName) && $LastName != ''){ echo ucfirst($LastName); } ?><br>
							<i class="fa fa-fw fa-envelope"></i> <?php if(isset($Email) && $Email != '' ){ echo $Email;} ?><br>
							<?php if(isset($CellNumber) && $CellNumber != ''){?><i class="fa fa-fw fa-phone"></i>  <?php echo $CellNumber; echo '</br>'; } ?>
							<?php if(isset($Country) && $Country != '' ) {?><i class="fa fa-fw fa-map-marker"></i><?php echo ucfirst($Country); echo ','; }?>
							<?php if(isset($ZipCode) && $ZipCode != '' ) echo $ZipCode;  ?>
						</div>
			</div>		
		</div><!-- /.row -->
		</div>
	<!-- </section> --><!-- /.content -->				  	
<?php commonFooter(); ?>
<script type="text/javascript">	
	$(document).ready(function() {		
		$('.fancybox').fancybox();	
	});	
	
</script>
</html>