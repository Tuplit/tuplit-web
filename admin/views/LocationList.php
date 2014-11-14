<?php 	
	require_once('includes/CommonIncludes.php');
		if(is_array($LocationListResult)){?>
			<ul class="col-xs-12">
		<?	foreach($LocationListResult as $key=>$value){ 
			?>
				<li class="col-xs-6 col-sm-3">
					<input type="radio" name="rGroup" value="1" id="<?php echo $value->id;?>" checked="checked" />
					<label class="radio" for="<?php echo $value->id;?>"><?php echo $value->Location;?></label>
				</li>				
							
		<?php $i++; } ?>
			</ul>
		<?php }  ?>
