<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/UserController.php');
$userObj   =   new userController();
$original_image_path =  $original_cover_image_path = $actualPassword = '';
unset($_SESSION['orderby']);
unset($_SESSION['ordertype']);
if(isset($_GET['viewId']) && $_GET['viewId'] != '' ){
	$condition       = " id = ".$_GET['viewId']." and Status in (1,2) LIMIT 1 ";	
	$field				=	' id,FirstName,LastName,Email,FBId,GooglePlusId,Photo,PinCode,Location,Country,ZipCode,DateCreated,PushNotification,Platform,SendCredit,
							 RecieveCredit,BuySomething,CellNumber,MangoPayUniqueId,WalletId,DealsOffers,Sounds,Passcode,PaymentPreference,RememberMe';
	$userDetailsResult  = $userObj->selectUserDetails($field,$condition);
	if(isset($userDetailsResult) && is_array($userDetailsResult) && count($userDetailsResult) > 0){
		$_GET['viewId']				= $userDetailsResult[0]->id;
		$FirstName     				= $userDetailsResult[0]->FirstName;
		$LastName     				= $userDetailsResult[0]->LastName;
		$Email      				= $userDetailsResult[0]->Email;
		$FbId       				= $userDetailsResult[0]->FBId;
		$GooglePlusId  				= $userDetailsResult[0]->GooglePlusId;
		$Location 					= $userDetailsResult[0]->Location;
		$Country  					= $userDetailsResult[0]->Country;
		$PinCode  					= $userDetailsResult[0]->PinCode;
		$ZipCode  					= $userDetailsResult[0]->ZipCode;
		$dateCreated    			= $userDetailsResult[0]->DateCreated;
		$PushNotification   		= $userDetailsResult[0]->PushNotification;
		$SendCredit   				= $userDetailsResult[0]->SendCredit;
		$RecieveCredit   			= $userDetailsResult[0]->RecieveCredit;
		$BuySomething   			= $userDetailsResult[0]->BuySomething;
		$Platform					= $userDetailsResult[0]->Platform;
		$CellNumber					= $userDetailsResult[0]->CellNumber;
		$MangoPayId					= $userDetailsResult[0]->MangoPayUniqueId;
		$WalletId					= $userDetailsResult[0]->WalletId;
		$DealsOffers   				= $userDetailsResult[0]->DealsOffers;
		$Sounds   					= $userDetailsResult[0]->Sounds;
		$Passcode   				= $userDetailsResult[0]->Passcode;
		$PaymentPreference   		= $userDetailsResult[0]->PaymentPreference;
		$RememberMe   				= $userDetailsResult[0]->RememberMe;
		$image_path = ADMIN_IMAGE_PATH.'no_user.jpeg'; 
		$original_image_path = '';
 		if(isset($userDetailsResult[0]->Photo) && $userDetailsResult[0]->Photo != ''){
			$user_image = $userDetailsResult[0]->Photo;
			if (!SERVER){
				if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
					$image_path = USER_THUMB_IMAGE_PATH.$user_image;
				if(file_exists(USER_IMAGE_PATH_REL.$user_image))
					$original_image_path = USER_IMAGE_PATH.$user_image;
			}
			else{
				if(image_exists(2,$user_image))
					$image_path = USER_THUMB_IMAGE_PATH.$user_image;
				if(image_exists(1,$user_image))
					$original_image_path = USER_IMAGE_PATH.$user_image;
			}
		}
	}
}
?>
<body class="skin-blue">
	<?php if(!isset($_GET['show'])) top_header(); ?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div <?php if(!isset($_GET['show'])) { ?> class="col-xs-12 col-sm-6" <?php } ?>> 
			<h1><i class="fa fa-search"></i> View User</h1>
		</div>
	</section>
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-md-12 view-page"> 
				<div class="box box-primary"> 
			<!--	<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5"  class="col-xs-6  col-sm-5" >Username</label>
					<div  class="col-xs-6  col-sm-7"> <?php //if(isset($UserName) && $UserName !='') echo ucfirst($UserName); else echo '-'; ?></div>
				</div>
			-->
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >First Name</label>
					<div  class="col-xs-6  col-sm-7">
					<?php if(isset($FirstName) && $FirstName != '') echo ucfirst($FirstName); else echo '-'; ?>	</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">									
					<label class="col-xs-6  col-sm-5" >Last Name</label>
					<div  class="col-xs-6  col-sm-7">										
					<?php if(isset($LastName) && $LastName != '') echo ucfirst($LastName);  else echo '-'; ?>	</div>									
				</div>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Email</label>
					<div  class="col-xs-6  col-sm-7"><?php if(isset($Email) && $Email != '' ) echo $Email; else echo '-'; ?>	</div>	
				</div>					
			
									
				<?php 
				//if($_SERVER['HTTP_HOST']=='172.21.4.104' || (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ($_SERVER['HTTP_X_FORWARDED_FOR'] == '125.19.192.66' || $_SERVER['HTTP_X_FORWARDED_FOR'] == '27.124.58.85')) ) { ?>
				
			<!--	<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Password</label>
					<div  class="col-xs-6  col-sm-7">
					<?php //if(isset($actualPassword) && $actualPassword != '' ) echo $actualPassword; else echo '-'; ?>		</div>					
					
				</div>
			-->
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >PIN Code</label>
					<div  class="col-xs-6  col-sm-7">
					<?php if(isset($PinCode) && $PinCode !='') echo $PinCode; else echo '-'; ?></div>
				</div>	
													
				
				<?php //} ?>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Facebook Id</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($FbId) && $FbId != '' ) echo $FbId; else echo '-';  ?>
					</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Google Plus Id</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($GooglePlusId) && $GooglePlusId != '' ) echo $GooglePlusId; else echo '-'; ?>
					</div>
					
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Location</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($Location) && $Location !='') echo ucfirst($Location); else echo '-'; ?>
					</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Country</label>
					<div  class="col-xs-6  col-sm-7">
					<?php if(isset($Country) && $Country != '' ) echo ucfirst($Country); else echo '-'; ?></div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Zip Code</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($ZipCode) && $ZipCode != '' ) echo $ZipCode; else echo '-'; ?>
					</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Cell Number</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($CellNumber) && $CellNumber != '') echo $CellNumber; else echo '-'; ?>
					</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Registered Date</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($dateCreated) && $dateCreated != '' ) echo date('m/d/Y',strtotime($dateCreated)); else echo '-'; ?>
					</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Platform</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($Platform) && $Platform != '' ) echo $platformArray[$Platform]; else echo '-'; ?>
					</div>
				</div>			
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >MangoPay Id</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($MangoPayId) && $MangoPayId != '' ) echo $MangoPayId; else echo '-'; ?>
					</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Wallet Id</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($WalletId) && $WalletId != '' ) echo $WalletId; else echo '-'; ?>
					</div>
				</div>											
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-6  col-sm-5" >Photo</label>
					<div  class="col-xs-6  col-sm-7">
						<?php if(isset($original_image_path) && $original_image_path != '') {  ?> 
						<a href="<?php echo $original_image_path; ?>" class="fancybox"  title="Click here" alt="Click here" ><?php if(isset($image_path) && $image_path != '') { ?> <img width="75" height="75" src="<?php echo $image_path;?>"><?php } ?></a>
						<?php } else { ?>
						<div class="no_photo img75"><i class="fa fa-user"></i></div>
						<?php } ?>
					</div>
				</div>
				
				<div class="col-xs-12"> <h3 >Notifications</h3></div>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Push Notification</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($PushNotification) && $PushNotification == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				</div>	
				<div class="form-group col-xs-12 col-sm-6 row">
							
					<label class="col-xs-7 col-sm-5"  class="notification">Send Credit</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($SendCredit) && $SendCredit == '1') echo 'On'; else echo 'Off'; ?>
					</div>
															
				</div>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Buy Something</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($BuySomething) && $BuySomething == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				
				</div>					
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Receive Credit</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($RecieveCredit) && $RecieveCredit == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				</div>					
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Deals &amp; Offers</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($DealsOffers) && $DealsOffers == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				
				</div>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Sounds</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($Sounds) && $Sounds == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				
				</div>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Passcode</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($Passcode) && $Passcode == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				
				</div>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Payment Preference</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($PaymentPreference) && $PaymentPreference == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				
				</div>
				<div class="form-group col-xs-12 col-sm-6 row">
					<label class="col-xs-7 col-sm-5"  class="notification">Remember Me</label>
					<div  class="col-xs-4 col-sm-7">
						<?php if(isset($RememberMe) && $RememberMe == '1') echo 'On'; else echo 'Off'; ?>
					</div>
				
				</div>
			<?php	if(!isset($_GET['show'])){?>
				<div class="box-footer col-xs-12" align="center">
						<?php 
						if(isset($_GET['bk']) && $_GET['bk'] == 1)
							$href_page = "CustomerAnalytics";
						else
						 	$href_page = "UserList"; ?>	
						<a href="UserManage?editId=<?php if(isset($_GET['viewId']) && $_GET['viewId'] != '') echo $_GET['viewId']; ?>" title="Edit" alt="Edit" class="btn btn-success">Edit</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<a href="<?php if(isset($href_page) && $href_page != '' ) echo $href_page; else echo 'UserList';?>" class="btn btn-default" name="Back" id="Back" title="Back" alt="Back" >Back </a>
				</div>
				<?php } ?>
			</div>		
			</div>		
		</div><!-- /.row -->
	</section><!-- /.content -->				  	
<?php commonFooter(); ?>
<script type="text/javascript">	
	$(document).ready(function() {		
		$('.fancybox').fancybox();	
	});	
	
</script>
<script>
$(document).ready(function() {		
	/*$(".pop_up").colorbox(
		{
			iframe:true,
			width:"30%", 
			height:"60%",
			title:true,
			opacity:0.7
	});*/
});
</script>
</html>
