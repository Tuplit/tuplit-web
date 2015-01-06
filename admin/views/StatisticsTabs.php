<?php
require_once('includes/CommonIncludes.php');
?>

<section class="content-header">
	<!-- <div class="col-sm-6 col-xs-12 no-padding">
		<h1>Statistics/Tracking</h1>
	</div> -->
	<?php 	

	/*
	if(isset($latlng) && is_array($latlng)){
		echo "<pre>";print_r($latlng);echo "</pre>";
	}
*/

	?>
	<div class="col-sm-6 col-xs-12 header-margin no-padding">
	</div>
</section>	

<section class="content ">
	<div class="row">
		<div class=" col-xs-12 no-padding">
					<div class="box box-primary box-padding gray_bg statistics_tabs">
						<div class="box-body no-padding" >	
							<div class="col-sm-2 col-xs-6 form-group " >
								<label><a href="Statistics?cs=1" id="sel_1">Statistics</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="LogTracking?cs=1" id="sel_2">Log Tracking</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="Transfer?cs=1" id="sel_3">Transfer Tracking</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="ServiceList?cs=1" id="sel_4">Service List</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="Versions?cs=1" id="sel_5">App Versions</a></label>
							</div>
							<!--<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="MangoPayTracking?cs=1" id="sel_6">MangoPay API</a></label>
							</div>-->
						</div>	
					</div>		
			</div>
		</div>	
</section>


<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<?php if(isset($activeTab) && $activeTab != 0){  ?>
	<script>
		$('#sel_<?php echo $activeTab?>').addClass('sel');
	</script>
<?} ?>

		