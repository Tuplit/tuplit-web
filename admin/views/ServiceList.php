<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/ServiceController.php');
$serviceObj   =   new ServiceController();
$class =  $msg  = $error  = $error_class = '';
$display = $display_add = 'none';
$jobtype_exists = 0;
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();
	unset($_SESSION['tuplit_sess_search_process']);
	unset($_SESSION['tuplit_sess_search_module']);
}
if(isset($_GET['add']) && $_GET['add'] != ''){
	$display_add = 'block';
}
if(isset($_GET['delId']) && $_GET['delId'] != '' ){
	$condition       = "id = ".$_GET['delId'];
	$serviceObj->deleteServiceDetails($condition);
	//$serviceObj->deleteServiceParamsDetails($_GET['delId']);
	header("location:ServiceList?msg=3");		
}
if(isset($_POST['Search']) && $_POST['Search'] != ''){
	destroyPagingControlsVariables();
	$_POST          = unEscapeSpecialCharacters($_POST);
    $_POST          = escapeSpecialCharacters($_POST);
	if(isset($_POST['search_process']))
		$_SESSION['tuplit_sess_search_process'] 	= $_POST['search_process'];	
	if(isset($_POST['search_module']))
		$_SESSION['tuplit_sess_search_module'] 	= $_POST['search_module'];	
}
$_SESSION['ordertype'] = 'asc';
setPagingControlValues('Ordering',ADMIN_PER_PAGE_LIMIT);
$fields    = "*";
$condition = "";
$serviceResult  = $serviceObj->getServiceList($fields,$condition);
$tot_rec 		 = $serviceObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($serviceResult)) {
	$_SESSION['curpage'] = 1;
	$serviceResult  = $serviceObj->getServiceList($fields,$condition);
}
if(isset($_GET['msg']) && $_GET['msg'] == 1){
	$msg 		= 	"Service added successfully";
	$display	=	"block";
	$class 		= 	"alert-success";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 2){
	$msg 		 = 	"Service updated successfully";
	$display	 =	"block";
	$class 		 = 	"alert-success";
}
else if(isset($_GET['msg']) && $_GET['msg'] == 3){
	$msg 		 = 	"Service deleted successfully";
	$display	 =	"block";
	$class 		 = 	"alert-success";
}

commonHead(); ?>
<body class="skin-blue" onload="">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa fa-list"></i> Service List</h1>
		</div>
		<div class="col-sm-5 col-xs-12"><h3><a href="ServiceManage" title="Add Service"><i class="fa fa-plus-circle"></i> Add Service</a></h3></div>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
				 <form name="search_service" action="ServiceList" method="post">
				 	<div class="box box-primary">
						<div class="box-body no-padding" >				
							<div class="col-sm-4 form-group">
								<label>Module</label>
								<input type="text" class="form-control" name="search_module" id="search_module"  value="<?php  if(isset($_SESSION['tuplit_sess_search_module']) && $_SESSION['tuplit_sess_search_module'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_search_module']);  ?>"  >
							</div>
							<div class="col-sm-4 form-group">
								<label>Purpose</label>
								<input type="text" class="form-control" id="search_process" name="search_process"  value="<?php  if(isset($_SESSION['tuplit_sess_search_process']) && $_SESSION['tuplit_sess_search_process'] != '') echo unEscapeSpecialCharacters($_SESSION['tuplit_sess_search_process']);  ?>" >
							</div>		
						</div>
						<div class="box-footer col-sm-12" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search">
						</div>	
                  	</div>	       
				  </form>	
				  <div class="row paging">
					<div class="col-xs-12 col-sm-2">
						<?php if(isset($serviceResult) && is_array($serviceResult) && count($serviceResult) > 0){ ?>
						<div class="dataTables_info">No. of Service(s)&nbsp;:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
						<?php } ?>
					</div>
					<div class="col-xs-12 col-sm-10">
						<div class="dataTables_paginate paging_bootstrap row">
						<?php if(is_array($serviceResult) && count($serviceResult) > 0 ) {
								pagingControlLatest($tot_rec,'ServiceList'); ?>
						<?php }?>
						</div>
					</div>
				 </div>	
				<?php if(isset($msg) && $msg != '') { ?>
				 <div class="row">
		               <div class="alert <?php  echo $class;  ?> alert-dismissable col-xs-11  col-sm-5  col-lg-3"><i class="fa fa-check"></i>  <?php echo $msg; ?></div>
				 </div>	
				<?php } ?>
		
				<div class="row">
		               <div class="col-xs-12">
					   	<?php if(isset($serviceResult) && is_array($serviceResult) && count($serviceResult) > 0 ) { ?>
						<form action="ServiceList" class="l_form" name="ServiceList" id="ServiceList"  method="post">
		                   <div class="box">
		                       <div class="box-body table-responsive no-padding">
		                           <table class="table table-hover" id="myTable">
		                               <tr>
											<th width="3%" class="text-center">#</th>
											<th width="17%"><?php echo SortColumn('Process','Purpose'); ?></th>
											<th width="10%"><?php echo SortColumn('Module','Module'); ?></th>
											<th><?php echo SortColumn('ServicePath','Endpoint'); ?></th>
											<th width="3%"><?php echo SortColumn('Ordering','Order'); ?></th>													
											 <th  width="5%" colspan="2" align="center">Action</th>			
										</tr>
												 
										<?php foreach($serviceResult as $key=>$value){ ?>									
										<tr>												
											<td align="center"><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>
											<td align="left"><?php if(isset($value->Process) && $value->Process != ''){ echo $value->Process;} else echo '-';?></td>		
											<td align="left"><?php if(isset($value->Module) && $value->Module != ''){ echo $value->Module;} else echo '-';?></td>	
											<td align="left">
												<a href="ServiceDetail?id=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" data-toggle="tooltip" data-original-title="View" class="view"><?php if(isset($value->ServicePath) && $value->ServicePath != ''){ echo SITE_PATH.$value->ServicePath;} else echo '-';?></a>
											</td>													
											<td align="center"><input type="Text" class="form-control" name="ordering" id="ordering" value="<?php if(isset($value->Ordering) && $value->Ordering != '' ) echo $value->Ordering; ?>" class="order" maxlength="5" onkeypress="return isNumberKey(event);" onchange="setOrderingWebService(this.value,<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>);" ></td>
											<td align="center"><a href="ServiceManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" data-toggle="tooltip" data-original-title="Edit" class="edit"><i class="fa fa-edit "></i></a></td>
											<td align="center"><a onclick="javascript:return confirm('Are you sure to delete?')" href="ServiceList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" data-toggle="tooltip" data-original-title="Delete" class="delete"><i class="fa fa-trash-o "></i></a>
										</tr>
										<?php }  ?>	
									</table>		
									 </div><!-- /.box-body -->
					  
                   				</div><!-- /.box -->									
									</form>
									<?php } else { ?>	
						<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-warning"></i> No Services found</div> 
					<?php } ?>	
               </div>
           </div>
	
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>