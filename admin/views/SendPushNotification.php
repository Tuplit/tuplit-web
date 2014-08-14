<?php 

require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/UserController.php');
$userObj   =   new userController();
$msg = $message   =  $class 	=  $tot_user = '';
//$userArray = $userObj->getUserListPN('id,FirstName,LastName',' Notification = 1 and Status!=3 order by FirstName asc ');
if((isset($_GET['checkdelete']) && is_array($_GET['checkdelete']) && count($_GET['checkdelete']) > 0) || (isset($_GET['cs']) && $_GET['cs'] == 1)) {
	
	if(!isset($_GET['cs'])){
		$userId			=	implode(",",$_GET['checkdelete']);
		$con = "and id in ($userId) ";
	}
	else
		$con = "";		
	$userArray 		= 	$userObj->getUserListPN("id,FirstName,LastName,Email"," and PushNotification = 1 and Status!=3 $con ");
	$index			=	0;
	$userNameDisp   = '';
	if(isset($userArray) && is_array($userArray) && count($userArray) > 0 ){
		if(isset($_GET['cs']))
			$tot_user = count($userArray);
		foreach($userArray as $user_key=>$user) {
			//$userNameArr1[$_GET['checkdelete'][$index++]]	=	$user->FirstName.' '.$user->LastName;
			if($user->FirstName == '' && $user->LastName == '')
				$userDisplayName = $user->Email;
			else
				$userDisplayName = $user->FirstName.' '.$user->LastName;
				
			$userNameArr[$user->id]	=	$userDisplayName ;
			if($user_key <= 49){
				$userNameDisp .= $userDisplayName.', ';
			}
			$userIdArray[] = $user->id;		
			if(isset($_GET['cs']))
				$_GET['checkdelete'][] = $user->id;
		}
		$userName  = implode(",",$userNameArr);	
		$userNameDisp = trim($userNameDisp,', ');
		if($tot_user > 50)
			$userNameDisp .= '<b> ....More</b>';
	}
}
if(isset($_POST['user_id']) && isset($userIdArray) && !isset($_POST['Delete']) ){ 
	$message 		= 	trim($_POST['message_hidden']);
	$row_id 		= 	$userIdArray;	
	$userNameArr	=   $_POST['user_name'];
	//$row_id 		= 	$_POST['user_id'];
	$total_user		=	count($row_id);
	$total_pn		=	0;
	for($r=0;$r < count($row_id);$r++){
		$user_id = $row_id[$r];
		$statusCount[$user_id]['Success']	=	0;
		$statusCount[$user_id]['Failure']	=	0;
		if($user_id !='' ) {
			$condition 		= 	'fkUsersId = '.$user_id.' and Status = 1 ';
			$endpointsArn 	= 	$userObj->getDevicetoken('*',$condition);
			if(isset($endpointsArn) && is_array($endpointsArn) && count($endpointsArn) > 0){
				foreach($endpointsArn as $endkey=>$endvalue){
					if(trim($endvalue->DeviceToken) !=''){
						$status = sendNotificationAWS($message,$endvalue->EndpointARN,'',$endvalue->BadgeCount,1,'','','');
						if($status) {
							$userObj->updateBadge($endvalue->DeviceToken);
							$statusCount[$user_id]['Success']++;
							++$total_pn;
						}
						else {
							$statusCount[$user_id]['Failure']++;
							++$total_pn;
						}
					}
				}
			}
		}
	}
	$msg 			= 	'Message sent successfully';
	$class 			= 	"alert-success";
	$path			=	ABS_PATH."/admin/logs";
	$currentFolder	=	$path."/".date('mY');
	$text_file		=	$currentFolder."/".date('mdY').".txt";
	$date			=	date('m-d-Y');
	$time			=	date('h:i:s A');
	$content		=	"\r\n\r\n".$date."  ".$time."\r\n";
	$content		.=	"Total Users: ".$total_user."\r\n\r\n";
	$index			=	0;
	if(isset($userNameArr) && is_array($userNameArr) && count($userNameArr) > 0 ){
	
	foreach($row_id as $id)
		$content	.=	++$index.". ".$userNameArr[$id]." (".$id.")\r\n";
	}
	$content		.=	"\r\nMessage Sent: \"".$_POST['message_hidden']."\"\r\n";
	$index			=	0;
	$count			=	0;
	$statContent	=	"";
	foreach($row_id as $id) {
		if(isset($statusCount[$id]) && ($statusCount[$id]['Success'] > 0 || $statusCount[$id]['Failure'] > 0)) {
			$statContent	.=	++$index.". ".$userNameArr[$id]." ( ".$statusCount[$id]['Success']." - Success, ".$statusCount[$id]['Failure']." - Failure)\r\n";
			$count++;
		}
		else if(!isset($statusCount[$id])) {
			$statContent	.=	++$index.". ".$userNameArr[$id]." (0 - Success, 0 - Failure)\r\n";
			$count++;
		}
	}
	$content	.=	"Total Number of Users Push Notifications Sent: ".$count."\r\n";
	$content	.=	"Total Push Notifications Sent: ".$total_pn."\r\n\r\n";
	$content	.=	$statContent;
	$content	.=	"\r\n----------------------------------------------------------";
	if(!is_dir($path))
		mkdir(ABS_PATH."/admin/logs",0777);
	if(!is_dir($currentFolder))
		mkdir($currentFolder,0777);
	if(!file_exists($text_file))
		$file = fopen($text_file,'w');
	else
		$file = fopen($text_file,'a');
	fwrite($file,$content);
	fclose($file);
}

if(!isset($userNameArr) ||  count($userNameArr) < 0 ){
	$msg = "Selected users not having notification in ON status";
	$class 		= 	"alert-success";
}

?>
<body class="skin-blue" onload="">
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-8 col-sm-7">
			<h1><i class="fa fa-list"></i> Send Push Notification</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row paging form-group">
			<div class="col-xs-12 col-sm-2">
				<?php if($tot_user != '') { ?>
				<div class="dataTables_info">No. Of Users : <?php echo $tot_user; ?></div>
				<?php } ?>
			</div>
		</div>
	<?php if(isset($msg) && $msg != '') { ?>
	 <div class="row">
              <div align="center" class="alert <?php  echo $class;  ?> alert-dismissable col-xs-11  col-sm-4"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
	 </div>	
	
	<?php } ?>
	
	<?php if(isset($userNameArr) && is_array($userNameArr) && count($userNameArr) > 0 ){
	if(isset($userArray) && count($userArray)>0) { ?>

	<div class="row">
		<div class="col-xs-12">
		<form name="pushNotificationForm" id="pushNotificationForm" action="" method="post">
			<input type="hidden" value="" id="message_hidden" name="message_hidden"/>
			<?php 
			 if(isset($userNameArr) && is_array($userNameArr) && count($userNameArr) > 0 ){
			 if(isset($_GET['checkdelete']) && is_array($_GET['checkdelete'])) {
				foreach($_GET['checkdelete'] as $id) {?>
			<input type="hidden" value="<?php  echo $id; ?>" name="user_id[]"> 
			<?php } }?>
			<?php  if(isset($_GET['checkdelete']) && is_array($_GET['checkdelete'])) {
					foreach($userNameArr as $id=>$user) {?>
			<input type="hidden" value="<?php echo $user; ?>" name="user_name[<?php  echo $id;?>]">
			</form>
			<?php } } } ?>
		<div class="box box-primary">
			<div class="box-body no-padding" >	
			<div class="row col-sm-12 form-group">
				<div class="col-xs-2">
					<label>User</label>
				</div>
				<div class="col-xs-1" style="padding-right:0px;">:</div>
				<div class="col-xs-9 no-padding">
				<?php if(isset($userNameDisp)) echo $userNameDisp;?>
				</div>
			</div>
			<div class="row col-sm-12 form-group">
				<div class="col-xs-2">
					<?php } ?>
					<label>Message</label>
				</div>
				<div class="col-xs-1" style="padding-right:0px;">:</div>
				<div class="col-xs-9 no-padding">
					<textarea id="message" class="form-control" name="message" rows="4" cols="25" ></textarea>
				</div>
			</div>
			</div>
				<div class="box-footer col-sm-12" align="center">
						<input type="button" onclick="sendNotification(document.forms.pushNotificationForm);" class="btn btn-success" value="Send" name="search" id="search" title="Send" alt="Send">
				</div>
		</div>
		</form>
	</div>

	<?php } ?>

 </div>
 </section><!-- /.content -->
 <?php commonFooter(); ?>

