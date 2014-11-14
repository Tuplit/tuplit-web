<?php 	
		if(is_array($CategoryListResult)){
			foreach($CategoryListResult as $key=>$value){ 
					$image_path = '';
					$photo = $value->CategoryIcon;
					$original_path = ADMIN_IMAGE_PATH.'no_category.jpeg';
					if(isset($photo) && $photo != ''){
						$Category_image = $photo;
						if(SERVER){
							if(image_exists(3,$Category_image))
								$original_path = CATEGORY_IMAGE_PATH.$Category_image;
						}else{
							if(file_exists(CATEGORY_IMAGE_PATH_REL.$Category_image))
								$original_path = CATEGORY_IMAGE_PATH.$Category_image;
						}
					}
			?>									
			<tr>
				<td align="center"><?php echo $i+1;?></td>	
				<td><?php echo (!empty($value->CategoryName)?$value->CategoryName:"-"); ?></td>
				<td>
				<?php if(isset($original_path) && $original_path != ''){ ?>
						<div class="">
							<a <?php if(isset($original_path) && basename($original_path) != "no_category.jpeg") { ?> href="<?php echo $original_path; ?>" class="Category_image_pop_up" title="View Photo" <?php } ?> ><img width="36" height="36" align="top" class="img_border" src="<?php echo $original_path;?>" ></a>
						</div><!--col-xs-2 col-sm-2 col-lg-1 no-padding-->
					<?php } ?>
				</td>
				<td><b><?php echo (!empty($value->ProductsCount)?$value->ProductsCount:"0"); ?></b></td>
				<td style="text-align: right;padding-right: 15px">
					<a href="CategoryManage?editId=<?php if(isset($value->CategoryId) && $value->CategoryId != '') echo $value->CategoryId; ?>" class="Edit newWindow" id="catEdit" onclick="return showLoaderPopup();" title="Edit" alt="Edit"><i class="fa fa-edit "></i></a>&nbsp;&nbsp;
					<a onclick="javascript:return confirm('Are you sure to delete?') " href="CommonSettings?delId=<?php if(isset($value->CategoryId) && $value->CategoryId != '') echo $value->CategoryId;?>" title="Delete" data-toggle="tooltip" alt="Delete" class="delete"><i class="fa fa-trash-o "></i></a>
				</td>
			</tr>
		<?php $i++; }  }  ?>
