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
	unset($_SESSION['tuplit_sess_comment_user_name']);
	unset($_SESSION['tuplit_sess_comment_date']);
	if(isset($_SESSION['tuplit_ses_from_timeZone']))
		unset($_SESSION['tuplit_ses_from_timeZone']);
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['UserName']))
		$_SESSION['tuplit_sess_comment_user_name'] 	= $_POST['UserName'];
	if(isset($_POST['SearchDate']) && $_POST['SearchDate'] != ''){
		$validate_date = dateValidation($_POST['SearchDate']);
		if($validate_date == 1){
			$date = date('Y-m-d',strtotime($_POST['SearchDate']));
			if($date != '' && $date != '1970-01-01' && $date != '0000-00-00' )
				$_SESSION['tuplit_sess_comment_date']	= $date;
			else 
				$_SESSION['tuplit_sess_comment_date']	= '';
		}
		else 
			$_SESSION['tuplit_sess_comment_date']	= '';
	}
	else 
		$_SESSION['tuplit_sess_comment_date']	= '';
}
if(isset($_GET['mer_id']) && !empty($_GET['mer_id'])) {
	$condition .= ' and m.id='.$_GET['mer_id'];
	$cond  = ' and fkMerchantsId ='.$_GET['mer_id'];
	$merchantwhere = ' id ='.$_GET['mer_id'];	
	$merchantdetailarray = $MerchantObj->selectMerchantDetails("CompanyName,Icon",$merchantwhere);
if(isset($merchantdetailarray) && is_array($merchantdetailarray) && count($merchantdetailarray) > 0){
	$merchantname = ucfirst($merchantdetailarray[0]->CompanyName);
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
	$fields = "com.*,u.FirstName,u.LastName,u.Photo,u.id as userId";
	
}


if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
	$condition .= ' and u.id='.$_GET['user_id'];
	$cond  = ' and fkUsersId ='.$_GET['user_id'];
	$userwhere = ' id ='.$_GET['user_id'];
	$userfind = "FirstName,LastName,Photo ";
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
		$username = ucfirst($userdetailarray[0]->FirstName).' '.ucfirst($userdetailarray[0]->LastName);
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
if(!isset($_SESSION['tuplit_ses_from_timeZone']) || $_SESSION['tuplit_ses_from_timeZone'] == ''){
	 $time_zone = getTimeZone();
	 $_SESSION['tuplit_ses_from_timeZone'] = strval($time_zone);	 
}
?>
<body class="skin-blue">
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-8 col-sm-7">
			<h1><i class="fa fa-list"></i> <?php if($show == 0) echo "Comments ";  else if($show == 1) echo "Comments" ; else  if($show == 2) echo "Comments";  ?></h1>
		</div>	
		<div class="col-xs-4 col-sm-5">	
			 <h3><?php if($show == 0) echo "";  else if($show == 1) echo "".$merchantimage." ".$merchantname ; else  if($show == 2) echo "".$userimage." ".$username;  ?></h3>
		</div>
	</section>
	<?php if(isset($_GET['mer_id']) && !empty($_GET['mer_id'])) {?>
		<section class="content-header no-padding">
			<div class="row">
				<div class="col-xs-12">
					<form name="search_category" action="CommentList?mer_id=<?php echo $_GET['mer_id'];?>" method="post">
					<div class="box box-primary">
						<div class="box-body no-padding" >
					
							<div class="col-sm-3 form-group">
								<label>User Name</label>
								<input type="text" class="form-control" name="UserName" id="UserName"  value="<?php  if(isset($_SESSION['tuplit_sess_comment_user_name']) && $_SESSION['tuplit_sess_comment_user_name'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_comment_user_name']);  ?>" >
							</div>
							<div class="col-sm-5 col-xs-12 form-group">
								<label>Comment Date</label>
								<div class="col-xs-6 no-padding"> <input type="text"  maxlength="10" class="form-control  fleft" name="SearchDate" id="SearchDate" title="Select Date" value="<?php if(isset($_SESSION['tuplit_sess_comment_date']) && $_SESSION['tuplit_sess_comment_date'] != '') echo date('m/d/Y',strtotime($_SESSION['tuplit_sess_comment_date'])); else echo '';?>" ></div>
								<div class="col-xs-6 LH30">(mm/dd/yyyy)</div>
							</div>
						</div>
						<div class="box-footer col-xs-12" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
						</div>
						
					</div>
					</form>
				</div>
			</div>
		</section>
	<?php } ?>
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
                   <div class="box height-control">
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
										<a href="UserDetail?viewId=<?php if(isset($value->userId) && $value->userId != '') echo $value->userId; ?>&show=1" title="View"  class="view"><?php  echo  ucfirst($value->FirstName).' '.ucfirst($value->LastName);?></a>				
																		
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
												<?php echo "<b>".ucfirst($value->CompanyName)."</b>";  ?>
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
						<div class="alert alert-danger alert-dismissable col-sm-5 col-lg-3 col-xs-11 "><i class="fa fa-warning"></i> No Comments Found</div> 
					<?php } ?>	
               </div>
           </div>
		
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>
<script type="text/javascript">
$("#SearchDate").datepicker({
	showButtonPanel	:	true,        
    buttonText		:	'',
    buttonImageOnly	:	true,
    buttonImage		:	path+'webresources/images/calender.png',
    dateFormat		:	'mm/dd/yy',
	changeMonth		:	true,
	changeYear		:	true,
	hideIfNoPrevNext:	true,
	showWeek		:	true,
	yearRange		:	"c-30:c",
	maxDate			:	"0",
	closeText		:   "Close"
   });
</script>