<?php
ob_start();
require_once('includes/CommonIncludes.php');
merchant_login_check();
if(isset($_SESSION['tuplit_merchant_user_name'])){
	//header('location:UserList?cs=1');
	//die();
}
$error = '';
if(isset($_POST['merchant_login_submit']) && $_POST['merchant_login_submit'] == 'Submit'){
	$_POST          =   unEscapeSpecialCharacters($_POST);
   	$_POST          =   escapeSpecialCharacters($_POST);
    $md5Pass        =   $_POST['password'];	
    $condition  	=   " UserName = '{$_POST['user_name']}' AND Password = '{$md5Pass}'";
	$result 		=   $adminLoginObj->checkAdminLogin($condition);
	if($result)
    {
		$_SESSION['tuplit_merchant_user_id'] 		= $result[0]->id;
		$_SESSION['tuplit_merchant_user_name'] 	    = $result[0]->UserName;
		$_SESSION['tuplit_merchant_user_email'] 	= $result[0]->EmailAddress;
		$fields     = " LastLoginDate = '".date('Y-m-d H:i:s')."'";
		$condition  = " Id = ".$result[0]->id;
		$result     =   $adminLoginObj->updateAdminDetails($fields,$condition);
		header('location:UserList?cs=1');
		die();
	}
	else{
		$error = "Invalid Username or Password";
	}
}
commonHead();
?>

<body class="skin-blue">
		<?php top_header(); ?>
		<section class="content">
		 <div class="row">
		 	<div class="col-xs-10" style="margin:auto;float:none"> 
               <div class="col-xs-6">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3>
                               Create Orders
                           </h3>
                       </div>
                       <div class="icon">
                           <i class="ion ion-bag"></i>
                       </div>
                       <a class="small-box-footer" href="#">
                           More info <i class="fa fa-arrow-circle-right"></i>
                       </a>
                   </div>
               </div><!-- ./col -->
               <div class="col-xs-6">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3>
                                Manage Orders
                           </h3>
                       </div>
                       <div class="icon">
                           <i class="ion ion-stats-bars"></i>
                       </div>
                       <a class="small-box-footer" href="#">
                           More info <i class="fa fa-arrow-circle-right"></i>
                       </a>
                   </div>
               </div><!-- ./col -->
			  </div>
			</div>
				
			  <div class="row">
			  
			  <div class="col-xs-10" style="margin:auto;float:none"> 
               <div class="col-lg-3 col-xs-6">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3>
                              Statistics
                           </h3>
                       </div>
                       <div class="icon">
                           <i class="ion ion-person-add"></i>
                       </div>
                       <a class="small-box-footer" href="#">
                           More info <i class="fa fa-arrow-circle-right"></i>
                       </a>
                   </div>
               </div><!-- ./col -->
               <div class="col-lg-3 col-xs-6">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3>
                               Menu
                           </h3>
                       </div>
                       <div class="icon">
                           <i class="ion ion-pie-graph"></i>
                       </div>
                       <a class="small-box-footer" href="#">
                           More info <i class="fa fa-arrow-circle-right"></i>
                       </a>
                   </div>
               </div><!-- ./col -->
			   
               <div class="col-lg-3 col-xs-6">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3>
                               Manage Users
                           </h3>
                       </div>
                       <div class="icon">
                           <i class="ion ion-pie-graph"></i>
                       </div>
                       <a class="small-box-footer" href="#">
                           More info <i class="fa fa-arrow-circle-right"></i>
                       </a>
                   </div>
               </div><!-- ./col -->
			   
               <div class="col-lg-3 col-xs-6">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3>
                              Global Settings
                           </h3>
                       </div>
                       <div class="icon">
                           <i class="ion ion-pie-graph"></i>
                       </div>
                       <a class="small-box-footer" href="#">
                           More info <i class="fa fa-arrow-circle-right"></i>
                       </a>
                   </div>
               </div><!-- ./col -->
			   </div>
           </div><!-- /.row -->
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>