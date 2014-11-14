<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();

require_once('controllers/CommentController.php');
$condition		= "";
$commentObj   	=   new CommentController();
//if(isset($_GET['merchantId']) && !empty($_GET['merchantId'])){
$fields    		= " com.*,u.FirstName,u.LastName,u.Photo ";
if(isset($_GET['merchantId']) && !empty($_GET['merchantId'])){
	$condition .= " and m.id = ".$_GET['merchantId'];
}
$condition 		.= " and com.Status = 1";
if(isset($_GET['start']) && $_GET['start'] !=''){
	$limit		= $_GET['start'];
}else{
	 $limit = 0;
} 
$i = $limit;
$commentlist 	= $commentObj->getCommentList($fields,$condition,$limit);
$total_record 	= $commentObj->getTotalRecordCount();
//}
	if(isset($commentlist) && is_array($commentlist) && count($commentlist) > 0 ) { 
		foreach($commentlist as $key=>$value){	
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
<div class="list-comment col-md-12">
<img src="<?php echo $image_path; ?>" width="100" height="100" />
<p>
	<span><?php  echo  (!empty($value->FirstName)?ucfirst($value->FirstName):'').' '.(!empty($value->LastName)?ucfirst($value->LastName):'');?></span>
	<?php  echo getCommentTextEmoji('web',$value->CommentsText,$value->Platform) ; ?>
</p>
</div>
<?php $i++;} 
}else { ?><div class="alert alert-danger alert-dismissable col-sm-5 col-xs-11 clear"><i class="fa fa-warning"></i> <?php echo "No comments"; ?></div><?php } ?>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<script type="text/javascript">
				var startLimit	= '<?php if(isset($_GET['start']) && $_GET['start'] !='') echo $_GET['start']; else echo '0';?>';
				$("#comments_total_count").val('<?php echo $total_record;?>');	
				$('#comments_result_count').val('<?php if(isset($_GET['start']) && $_GET['start'] !='')  echo $_GET['start']+5; else echo "5";?>');
				var total = $('#comments_total_count').val();
				var resultCount = ($('#comments_result_count').val())-1;
				if(startLimit == 0 && total>4){
					$('#seeMoreCommentsLink').show();
				}
				if(resultCount >= total ||  Number(resultCount)+1 == total ) {
					$('#seeMoreCommentsLink').hide();
				}
</script>
