<?php
ob_start();
require_once('includes/CommonIncludes.php');
merchant_login_check();

if(isset($_SESSION['tuplit_merchant_user_name'])){
	//header('location:UserList?cs=1');
	//die();
}
//echo "<pre>"; echo print_r($_SESSION['merchantDetailsInfo']); echo "</pre>";


$error 		= 	'';
$subuser	=	0;
$class_val	=  "col-md-3 col-sm-3 col-xs-12 paddingrg0";
if(isset($_SESSION['merchantSubuser']) && !empty($_SESSION['merchantSubuser']) && $_SESSION['merchantSubuser'] == 1) {
	$subuser	=	1;
	$class_val	=  "col-md-6 col-sm-6 col-xs-12 paddingrg0";
}

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

<body class="skin-blue fixed body_height home">
		<?php top_header(); ?>
		<section class="content dashboard">
		<div class="row ">
			<div class="col-lg-8 col-md-10 box-center"> 
				
				<div class="col-md-6 col-sm-6 col-xs-12 paddingrg0" onclick="location.href='CreateOrder?cs=1'" style="cursor:pointer" title="Create Order">
                   <!-- small box -->
                   <div class="small-box bg-teal height250">
                       <div class="inner">
                           <h3 class="text-center">
								<i class="fa fa-plus"></i>  <br>                  
								Create Order
                           </h3>
                       </div>
                   </div>
               </div><!-- ./col -->			
				<div class="col-md-6 col-sm-6 col-xs-12 paddingrg0" style="cursor:pointer" onclick="location.href='Orders?cs=1'" title=" Manage Orders">
                   <!-- small box -->
                   <div class="small-box bg-teal height250">
                       <div class="inner">
                           <h3 class="text-center">
								<i class="fa fa-file-text-o"></i> <br>
								Manage Orders
                           </h3>
                       </div>
                   </div>
               </div><!-- ./col  -->
				  
			 </div>
			 <div class="col-lg-8  col-md-10 box-center white_box"> 
				<?php if($subuser != 1) { $Analyticshref = 'CustomerAnalyticsOverview'; ?>
					<div class="col-md-3 col-sm-3 col-xs-12 paddingrg0">
					<div class="col-xs-12 no-padding" style="cursor:pointer" <?php if(!empty($Analyticshref)) echo 'onclick="location.href=\''.$Analyticshref.'\'"'; ?>  title="Analytics">
					   <!-- small box -->
					   <div class="small-box bg-teal">
						   <div class="inner">
							   <h3 class="text-center">
									<i class="fa fa-bar-chart-o"></i> <br>
									Analytics
							   </h3>
						   </div>
					   </div>
				   </div><!-- ./col -->	
					</div>
				<?php } ?>
				<div class="<?php echo $class_val;?>">
				<div class="col-xs-12 no-padding" onclick="location.href='ProductList'" style="cursor:pointer"  title="Products">
                   <!-- small box -->
                   <div class="small-box bg-teal" >
                       <div class="inner">
                           <h3 class="text-center">
								<i class="fa fa-cubes  "></i> <br>
                               Products
                           </h3>
                       </div>
                   </div>
               </div><!-- ./col -->
			   </div>
			   
			   <?php if($subuser != 1) {  $MyStorehref = 'MyStore'; ?>
			   <div class="col-md-3 col-sm-3 col-xs-12 paddingrg0">
               <div class="col-xs-12 no-padding" style="cursor:pointer" <?php if(!empty($MyStorehref)) echo 'onclick="location.href=\''.$MyStorehref.'\'"'; ?> title=" My Store">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3 class="text-center">
						   	<i class="fa fa-shopping-cart"></i><br>
                               My Store
                           </h3>
                       </div>
                   </div>
               </div><!-- ./col -->
			 	</div>
				<?php } ?>
			   <div class="<?php echo $class_val;?>">
               <div class="col-xs-12 no-padding" style="cursor:pointer" onclick="location.href='CustomerTransaction?cs=1'" title="Transactions">
                   <!-- small box -->
                   <div class="small-box bg-teal">
                       <div class="inner">
                           <h3 class="text-center">
						   	<i class="fa fa-transcations"></i><br>
                              Transactions
                           </h3>
                       </div>
                   </div>
               </div><!-- ./col -->
			 </div>
			  </div>
           </div><!-- /.row -->
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>