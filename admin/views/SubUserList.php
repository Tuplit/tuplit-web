<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/SubUserController.php');
$SubUserObj   	=   new SubUserController();
require_once('controllers/UserController.php');
$UserObj   		=   new UserController();
require_once('controllers/MerchantController.php');
$MerchantObj   	=   new MerchantController();

$condition = $companyname = $user_id =  $mer_id = $cond = '';
$show = 0;
$username = $merchantname  = $userimage = $merchantimage = '';

if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_sub_user_fname']);
	unset($_SESSION['tuplit_sess_sub_user_email']);	
}

if(isset($_GET['mer_id']) && !empty($_GET['mer_id']) && isset($_GET['sub_user']) && !empty($_GET['sub_user']) ) {
	$fields			= "*";
	$condition  	= " MainMerchantId ='".$_GET['mer_id']."' AND UserType = 2 ";
	$SubUserList 	= $SubUserObj->getSubUserList($fields,$condition);
	$show = 1;
}
setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
?>
<body class="skin-blue" onload="">
	<section class="content-header no-padding">
		<div class="col-xs-8 col-sm-7">
			<h1><i class="fa fa-list"></i> Salesperson List</h1>
		</div>
	</section>	
		<div class="row">
            <div class="col-xs-12">
		   	<?php if(isset($SubUserList) && is_array($SubUserList) && count($SubUserList) > 0 ) { ?>
			<form action="" class="l_form" name="SubUserList" id="SubUserList"  method="post">
                  <div class="box "> 
                      <div class="box-body table-responsive no-padding">
                          <table class="table table-hover">
                              <tr>
								<th align="center" width="3%" style="text-align:center">#</th>									
								<th width="24%">Salesperson Name</th>
								<th width="28%">Email</th>
							</tr>
                             <?php
						  	foreach($SubUserList as $key=>$value){
							?><tr>
								<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>	
								<td><?php echo $value->FirstName."&nbsp;".$value->LastName;?></td>
								<td><?php echo $value->Email;?>	</td>	
							</tr>
							<?php } ?>
						</table>
              </div>
          </div>
	<?php	} else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 col-lg-3 col-xs-11"><i class="fa fa-warning"></i>&nbsp;&nbsp;<?php echo "No salesperson found"; ?></div> 
					<?php } ?>	
	</section><!-- /.content -->	
						  	
<?php commonFooter(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$('.fancybox').fancybox();	
	
	$(".newWindow").fancybox({
			scrolling: 'auto',			
			type: 'iframe',
			width: '780',
			maxWidth: '100%',
			
			fitToView: false,
		});
});
</script>
</html>