<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/AdminController.php');
$msg = '';
$adminLoginObj   =   new AdminController();
$fields = '*';
$where  = '1';
$static_details  = $adminLoginObj->getCMS($fields,$where);
if(isset($_POST['cms_submit']) && $_POST['cms_submit'] == 'Submit' ){
		$_POST          =   unEscapeSpecialCharacters($_POST);
   		$_POST          =   escapeSpecialCharacters($_POST);
		$updateString   =   " Content  = '".$_POST['cms_about']."' ";
		$condition      =   " id = 1 ";
		$adminLoginObj->updateCMSDetails($updateString,$condition);
		$updateString   =   " Content  = '".$_POST['cms_privacy']."' ";
		$condition      =   " id = 2 ";
		$adminLoginObj->updateCMSDetails($updateString,$condition);
		$updateString   =   " Content  = '".$_POST['cms_terms']."' ";
		$condition      =   " id = 3 ";
		$adminLoginObj->updateCMSDetails($updateString,$condition);
		$updateString   =   " Content  = '".$_POST['cms_faq']."' ";
		$condition      =   " id = 4 ";
		$adminLoginObj->updateCMSDetails($updateString,$condition);
		
		header('location:StaticPages?msg=1');
}
if(isset($_GET['msg']) && $_GET['msg'] != '')
	$msg = "CMS updated successfully";
commonHead(); ?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-md-12"> 
			<h1><i class="fa fa-pencil-square-o"></i> CMS</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12">
				<form name="cms_form" id="cms_form" action="" method="post">
				<div class="box box-primary">
						<div class="box-body">
					<?php if($msg !='') { ?>
						<div class="alert alert-success alert-dismissable col-sm-5" align="center"><span><i class="fa fa-check"></i> <?php echo $msg;?></span></div>
					<?php  } ?>
					
					<?php if(isset($static_details) && is_array($static_details) && count($static_details)>0 ) { ?>
							<div class="form-group">
								<label><?php if(isset($static_details[0]->PageName) && $static_details[0]->PageName != '' ) echo $static_details[0]->PageName;?></label>
								<textarea  class="form-control" name="cms_about" id="cms_about" rows="8" cols="80"><?php if(isset($static_details[0]->Content) && $static_details[0]->Content != '' ) echo $static_details[0]->Content;?></textarea>
							</div>
							<div class="form-group">
								<label><?php if(isset($static_details[1]->PageName) && $static_details[1]->PageName != '' ) echo $static_details[1]->PageName;?></label>
								<textarea  class="form-control" name="cms_privacy" id="cms_privacy" rows="8" cols="80"><?php if(isset($static_details[1]->Content) && $static_details[1]->Content != '' ) echo $static_details[1]->Content;?></textarea>
							</div>
							<div class="form-group">
								<label><?php if(isset($static_details[2]->PageName) && $static_details[2]->PageName != '' ) echo $static_details[2]->PageName;?></label>
								<textarea  class="form-control" name="cms_terms" id="cms_terms" rows="8" cols="80"><?php if(isset($static_details[2]->Content) && $static_details[2]->Content != '' ) echo $static_details[2]->Content;?></textarea>
							</div>
							
							<div class="form-group">
								<label><?php if(isset($static_details[3]->PageName) && $static_details[3]->PageName != '' ) echo strtoupper($static_details[3]->PageName);?></label>
								<textarea  class="form-control" name="cms_faq" id="cms_faq" rows="2" cols="80"><?php if(isset($static_details[3]->Content) && $static_details[3]->Content != '' ) echo $static_details[3]->Content;?></textarea>
							</div>
						</div><!-- /.box-body -->
					
						<div class="box-footer" align="center">
						<input type="submit" class="btn btn-success" name="cms_submit" id="cms_submit" value="Submit" title="Submit" alt="Submit" />
						</div>
					
					<?php  } else { ?>
						<div class="alert alert-danger alert-dismissable col-sm-5" align="center"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php echo "No Static Content Found";?></div>
					<?php } ?>
				</div><!-- /.box -->
				</form>	
			</div><!--/.col (left) -->
		</div><!-- /.box -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>