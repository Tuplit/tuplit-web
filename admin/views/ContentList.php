<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
commonHead();
require_once('controllers/ContentController.php');
$contentObj   =   new ContentController();
$condition	=	'';
if(isset($_GET['cs']) && $_GET['cs']=='1') {
	destroyPagingControlsVariables();	
}
if(isset($_GET['delId']) && !empty($_GET['delId'])) {
	$contentObj->deleteContent($_GET['delId']);
	header("location:ContentList?msg=3");
	die();
}
if(isset($_POST['Delete']) && $_POST['Delete'] == 'Delete') {
	$ids	=	'';
	$ids	=	implode(',',$_POST['checkdelete']);
	if(!empty($ids)) {
		$contentObj->deleteContent($ids);
		header("location:ContentList?msg=4");
		die();
	}
}
if(isset($_GET['msg'])){
	if($_GET['msg'] == 1)
		$msg 		= 	"Content added successfully";
	else if($_GET['msg'] == 2)
		$msg 		= 	"Content updated successfully";
	else if($_GET['msg'] == 3)
		$msg 		= 	"Content deleted successfully";
	else if($_GET['msg'] == 4)
		$msg 		= 	"Selected contents deleted successfully";
		
	$display		=	"block";
	$class 			= 	"alert-success";
	$class_icon 	= 	"fa-check";
}

setPagingControlValues('id',ADMIN_PER_PAGE_LIMIT);
$contentList  	= $contentObj->getContentList($condition);
$tot_rec 		= $contentObj->getTotalRecordCount();
if($tot_rec!=0 && !is_array($contentList)) {
	$_SESSION['curpage'] = 1;
	$contentList  = $contentObj->getContentList($condition);
}
?>
<body class="skin-blue" onload="">
	<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa fa-list"></i> Content List</h1>
		</div>
		<div class="col-sm-5 col-xs-12"><h3><a href="ContentManage" title="Add Content"><i class="fa fa-plus-circle"></i> Add Content</a></h3></div>
	</section>	
	 <!-- Main content -->
	<section class="content">
		<div class="row paging">
			<div class="col-xs-12 col-sm-2">
				<?php if(isset($contentList) && is_array($contentList) && count($contentList) > 0){ ?>
				<div class="dataTables_info">No. of Content(s)&nbsp;:&nbsp;<strong><?php echo $tot_rec; ?></strong> </div>
				<?php } ?>
			</div>
			<div class="col-xs-12 col-sm-10">
				<div class="dataTables_paginate paging_bootstrap row">
				<?php if(is_array($contentList) && count($contentList) > 0 ) {
						pagingControlLatest($tot_rec,'contentList'); ?>
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
			   	<?php if(isset($contentList) && is_array($contentList) && count($contentList) > 0 ) { ?>
				<form action="contentList" class="l_form" name="contentList" id="contentList"  method="post">
                   <div class="box">
                       <div class="box-body table-responsive no-padding">
                           <table class="table table-hover" id="myTable">
                               <tr>
                                  	<th align="center" width="1%" class="text-center"><input onclick="checkAllDelete('contentList');" type="Checkbox" name="checkAll"/></th>
									<th align="center" width="3%" class="text-center">#</th>												
									<th width="15%">Page Name</th>
									<th width="15%">Page URL</th>
									<th width="51%"  align="center">Content</th>
									<th width="15%" align="center">Date Created</th>		
								</tr> 
								<?php
									foreach($contentList as $key=>$value){
								 ?>	
								<tr id="test_id_<?php echo $value->id;?>">
									<td align="center"><input id="checkdelete" name="checkdelete[]" value="<?php if(isset($value->id) && $value->id != '') echo $value->id  ?>" type="checkbox"/></td>
									<td align="center" nowrap><?php echo (($_SESSION['curpage'] - 1) * ($_SESSION['perpage']))+$key+1;?></td>												
									<td>
										<?php echo ucfirst($value->PageName);?>
										<div style="float:left;display:inline-block;" class="row-actions col-xs-12">
											<a href="ContentManage?editId=<?php if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="Edit"  class="edit"><i class="fa fa-edit "></i></a>
											<!--<a href="UserDetail?viewId=<?php //if(isset($value->id) && $value->id != '') echo $value->id; ?>" title="View"  class="view"><i class="fa fa-search "></i></a>	<!-- data-toggle="tooltip" -->
											<a onclick="javascript:return confirm('Are you sure to delete?')" href="ContentList?delId=<?php if(isset($value->id) && $value->id != '') echo $value->id;?>" title="Delete" class="delete"><i class="fa fa-trash-o "></i></a>
										</div>
									</td>
									<td><?php echo $value->PageUrl;?></td>
									<td><?php echo displayText($value->Content,250); ?></td>
									<td><?php echo date('m/d/Y',strtotime($value->DateCreated)); ?></td>
								</tr>
								<?php } //end for ?>	
                           </table>
                       </div><!-- /.box-body -->
                   </div><!-- /.box -->
					<?php if(isset($contentList) && is_array($contentList) && count($contentList) > 0){ ?>
						<button type="submit" onclick="return deleteAllContent();" class="btn btn-danger" name="Delete" id="Delete" value="Delete" title="Delete" alt="Delete"><i class="fa fa-trash-o"></i>&nbsp;Delete</button>
					<?php } ?>
				</form>					
				<?php } else { ?>	
					<div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11"><i class="fa fa-warning"></i> No Contents found</div> 
				<?php } ?>	
            </div>
        </div>
	</section><!-- /.content -->	
						  	
<?php commonFooter(); ?>
</html>