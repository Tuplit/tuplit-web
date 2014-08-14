<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/ContentController.php');
$contentObj   =   new ContentController();

$id	=	$ContentName	=	$ContentData	=	'';

if(isset($_GET['action']) && !empty($_GET['action'])) {
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
    $_POST     		= 	escapeSpecialCharacters($_POST);
	//echo "<pre>"; echo print_r($_POST); echo "</pre>";die();
	//New Content
	if($_GET['action'] == 'Save')
		$contentObj->insertContent($_POST);
	
	//Edit Content
	if($_GET['action'] == 'Update')
		$contentObj->updateContentDetail($_POST);
	
	die();
}

if(isset($_GET['editId']) && !empty($_GET['editId'])) {
	$contentDetail	=	$contentObj->selectContentDetail($_GET['editId']);
	$id				=	$contentDetail[0]->id;
	$ContentName	=	$contentDetail[0]->PageName;
	$ContentUrl		=	$contentDetail[0]->PageUrl;
	$ContentData	=	$contentDetail[0]->Content;
}

if(!empty($id))
	$type = 'Update';
else
	$type =	'Save';
commonHead(); ?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row col-md-12"> 
			<h1><i class="fa fa-plus-circle "></i> <?php if(isset($_GET['editId']) && !empty($_GET['editId']))  echo "Edit"; else echo "Add"; ?> Content page</h1>
		</div>
	</section>
	<!-- Main content -->
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12">
				<form name="content_form" id="content_form" action="" method="post" onsubmit="return saveContent(<?php if(!empty($id)) echo $id; else echo "'Save'"; ?>);">
				<div class="box box-primary">
					<div class="box-body">	
						<div id="alreadyExists" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-11 text-center" style="display:none;">
							<i class="fa fa-warning"></i>
							Content URL already exists
						</div>
						<div id="idNotExists" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-11 text-center" style="display:none;">
							<i class="fa fa-warning"></i>
							Content with this id not exists
						</div>
						<div class="form-group">
							<label>Content Name</label>
							<input type="text" class="form-control" id="ContentName" name="ContentName" maxlength="100" style="width:40%;" placeholder="ContentName" value="<?php if(!empty($ContentName)) echo $ContentName; ?>" ><br>
							<label>Content URL</label>
							<input type="text" class="form-control" id="ContentUrl" name="ContentUrl" maxlength="100" style="width:40%;" placeholder="ContentUrl" value="<?php if(!empty($ContentUrl)) echo $ContentUrl; ?>" ><br>
							<textarea  class="form-control" name="content" id="content"><?php if(!empty($ContentData)) echo $ContentData;?></textarea>
						</div>							
					</div><!-- /.box-body -->
					<div class="box-footer" align="center">
						<input type="submit" class="btn btn-success" name="content_submit" id="content_submit" value="<?php echo $type; ?>" title="<?php echo $type; ?>" alt="<?php echo $type; ?>" />
					</div>		
				</div><!-- /.box -->
				</form>	
			</div><!--/.col (left) -->
		</div><!-- /.box -->
	</section><!-- /.content -->	
<?php commonFooter(); ?>
</html>
<script type="text/javascript">
tinymce.init({
	height 	: "310",							
	selector: "textarea", statusbar: false,
	plugins	: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
				],
	toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code"
	});		
</script>
