<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/CommentController.php');
$commentObj   =   new CommentController();
require_once('controllers/UserController.php');
$UserObj   =   new UserController();
require_once('controllers/MerchantController.php');
$MerchantObj   =   new MerchantController();

$userimage = $username = $merchantimage = $merchantname = $condition = '';
$show = 0; 
$fields = "com.*";
$condition = ''; 

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();	
}

if(isset($_GET['mer_id']) && !empty($_GET['mer_id'])) {
	$condition .= ' and m.id='.$_GET['mer_id'];
	$cond  = ' and fkMerchantsId ='.$_GET['mer_id'];
	$merchantwhere = ' id ='.$_GET['mer_id'];	
	$merchantdetailarray = $MerchantObj->selectMerchantDetails("CompanyName,Icon",$merchantwhere);
if(isset($merchantdetailarray) && is_array($merchantdetailarray) && count($merchantdetailarray) > 0){
	$merchantname = $merchantdetailarray[0]->CompanyName;
	$icon_image_path = '';
	$merchant_image = $merchantdetailarray[0]->Icon;
	$icon_image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
	if(isset($merchant_image) && $merchant_image != ''){
		if(SERVER){
			if(image_exists(6,$merchant_image))
				$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
		}
		else{
			if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchant_image))
				$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
		}
	}
	if(isset($icon_image_path) && $icon_image_path != ''){
			$merchantimage =  "<img  width='50' height='50' align='top' class='img_border' src='".$icon_image_path."' >";		
	}	
}
	$show = 1;
	$mer_id = $_GET['mer_id'];
	$fields = "com.*,concat(u.FirstName , ' ',u.LastName) as UserName,u.Photo";
	
}


if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
	$condition .= ' and u.id='.$_GET['user_id'];
	$cond  = ' and fkUsersId ='.$_GET['user_id'];
	$userwhere = ' id ='.$_GET['user_id'];
	$userfind = "concat(FirstName , ' ',LastName) as UserName,Photo ";
	$userdetailarray = $UserObj->selectUserDetails($userfind,$userwhere);
	if(isset($userdetailarray) && is_array($userdetailarray) && count($userdetailarray) > 0){
		$image_path = '';
		$photo = $userdetailarray[0]->Photo;
		$original_path = $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
		
		if(isset($photo) && $photo != ''){
			$user_image = $photo;
			if(SERVER){
				if(image_exists(2,$user_image))
					$image_path = USER_THUMB_IMAGE_PATH.$user_image;
				if(image_exists(1,$user_image))
					$original_path = USER_IMAGE_PATH.$user_image;
				
			}else{
				if(file_exists(USER_IMAGE_PATH_REL.$user_image))
					$original_path = USER_IMAGE_PATH.$user_image;
				if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
					$image_path = USER_THUMB_IMAGE_PATH.$user_image;
			}
		}
		$username = $userdetailarray[0]->UserName;
		if(isset($image_path) && $image_path != ''){
			$userimage =  "<img  width='50' height='50'  class='img_border' src='".$image_path."' >";		
			}	
	}		
	$show = 2;
	$user_id = $_GET['user_id'];
	$fields = "com.*,m.CompanyName,m.Icon";
}


setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);

$commentlist = $commentObj->getCommentList($fields,$condition);
$tot_rec 	= $commentObj->getTotalRecordCount();

?>
<body class="skin-blue">
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-6">
			<h1><i class="fa fa-list"></i> <?php if($show == 0) echo "Comments ";  else if($show == 1) echo "Comments" ; else  if($show == 2) echo "Comments";  ?></h1>
		</div>	
		<div class="col-xs-6  text-right">	
			 <h3><?php if($show == 0) echo "";  else if($show == 1) echo "".$merchantimage." ".$merchantname ; else  if($show == 2) echo "".$userimage." ".$username;  ?></h3>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
				<?php if(isset($commentlist) && is_array($commentlist) && count($commentlist) > 0 ) { ?>
				<div class="col-xs-12 no-padding">
					No. of Comment(s)&nbsp:&nbsp;<strong><?php echo $tot_rec; ?></strong>
				</div>
		<?php } ?>
		
		<div class="row">
               <div class="col-xs-12">
			   	<?php if(isset($commentlist) && is_array($commentlist) && count($commentlist) > 0 ) { ?>
				<form action="CommentList" class="l_form" name="CommentListForm" id="CommentForm"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding no-margin">
                           <table class="table table-hover">
                               <tr>                                  	
									
									<?php if($show != 2) {?><th width="25%">User Details</th><?}?>	
									<?php if($show != 1) {?><th width="20%">Merchant Details</th><?}?>										
									<th width="25%">Comment</th>
									<th width="10%">Posted Date</th>	
															
                               </tr>
                              <?php  foreach($commentlist as $key=>$value){							  
							  
									    $gmt_current_created_time = convertIntocheckinGmtSite($value->CommentDate);
										$CommentDate	=  displayDate($gmt_current_created_time,$_SESSION['tuplit_ses_from_timeZone']);
							 ?>									
							<tr id="test_id_<?php echo $value->id;?>">	
								
					<?php if($show != 2) {
										$image_path = '';
										$photo = $value->Photo;
										$original_path = $image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
										
										if(isset($photo) && $photo != ''){
											$user_image = $photo;
											if(SERVER){
												if(image_exists(2,$user_image))
													$image_path = USER_THUMB_IMAGE_PATH.$user_image;
												if(image_exists(1,$user_image))
													$original_path = USER_IMAGE_PATH.$user_image;
												
											}else{
												if(file_exists(USER_IMAGE_PATH_REL.$user_image))
													$original_path = USER_IMAGE_PATH.$user_image;
												if(file_exists(USER_THUMB_IMAGE_PATH_REL.$user_image))
													$image_path = USER_THUMB_IMAGE_PATH.$user_image;
											}
										}
					
					?>
							<td>					
							<?php if(isset($image_path) && $image_path != ''){ ?>
									<div class="col-sm-2 no-padding">
											<img id="<?php echo $value->id ;?>"  width="36" height="36" align="top" class="img_border" src="<?php echo $image_path;?>" >
									</div>
							<?php } ?>
									<div class="col-xs-9 col-sm-8 no-padding"> 								
										<?php if(isset($value->UserName) && $value->UserName != ''){ echo ucfirst($value->UserName); } ?>								
									</div>
							</td>
					<?php }?>
					<?php if($show != 1) {?>
						<td>
							    <?
										$icon_image_path = '';
										$merchant_image = $value->Icon;
										$icon_image_path = ADMIN_IMAGE_PATH.'no_user.jpeg';
										if(isset($merchant_image) && $merchant_image != ''){
											if(SERVER){
												if(image_exists(6,$merchant_image))
													$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
											}
											else{
												if(file_exists(MERCHANT_ICONS_IMAGE_PATH_REL.$merchant_image))
													$icon_image_path = MERCHANT_ICONS_IMAGE_PATH.$merchant_image;
											}
										}
									?>
									<div> 
										<img width="50" height="50"align="top" class="img_border" src="<?php echo $icon_image_path;?>" >	
											<?php if(isset($value->CompanyName) && $value->CompanyName != ''){ ?>
											<span title="Company Name">
												<?php echo "<b>".$value->CompanyName."</b>";  ?>
											</span><br>   
										   <?php }?>
									</div>
						</td>
					<?php }?>
								<td><?php  echo getCommentTextEmoji('web',$value->CommentsText,$value->Platform) ; ?></td>
								<td><?php  echo $CommentDate; ?></td>								
							</tr>
							<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div><!-- /.box -->				    
					</form>
					
					<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-xs-4 "><i class="fa fa-warning"></i> No Comments Found</div> 
					<?php } ?>	
               </div>
           </div>
		
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>
