<?php
require_once('includes/CommonIncludes.php');
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj  =   new ManagementController();
$_SESSION['over'] = 0;
/*--------------Merchant Array----------------------*/
$condition       	= "  Status =1 order by CompanyName asc";
$field				=	' id,CompanyName';
$MerchantList		= 	$merchantObj->selectMerchantDetails($field,$condition);
/*---------------Category Array--------------------------*/
$condition       	= 1;
$field				='id,CategoryName';
$CategoryList  		= $managementObj->selectCategoryDetails($field,$condition);

/*--------------City Array-----------------------------*/
$condition       	= " City<>'' and merchants.Status = 1 group by City order by City asc";
$field				=	' City';
$CityList			= 	$merchantObj->selectMerchantDetails($field,$condition);
?>

<section class="content-header">
	<div class="col-sm-6 col-xs-12 no-padding">
		<h1>Report</h1>
	</div>
	<?php 	

	/*
	if(isset($latlng) && is_array($latlng)){
		echo "<pre>";print_r($latlng);echo "</pre>";
	}
*/

	?>
	<div class="col-sm-6 col-xs-12 header-margin no-padding">
		<!--<div class="search-box">
			<input type="text" placeholder="Search" value="<?php if(!empty($_SESSION['merchantSearch'])) echo $_SESSION['merchantSearch']; ?>" name="merchantsearch" id="merchantsearch">
			<input type="submit" name="Search" id="merchant_search" value="Search" class="search_icon" title="Search">  
		</div>-->
	</div>
</section>	
<section class="content">
	<div class="row">
		<div class=" col-xs-12">
			<form name="Location_breakdown" action="<?php if(isset($formStatus) && $formStatus == 1) echo "LocationBreakdown"; else if (isset($formStatus) && $formStatus == 2) echo "TransactionHistory"; else if (isset($formStatus) && $formStatus == 3) echo "CustomerReport"; else if (isset($formStatus) && $formStatus == 4) echo "ReportOverview"; else if (isset($formStatus) && $formStatus == 5) echo "Performance";?>" method="post">
				<div class="box box-primary box-padding report">
					<div class="box-body no-padding" >	
						<div class="form-group col-sm-12">	
							<h3 style="color: #fff">Filter by Merchant</h3>
						</div>
						<div class="form-group col-sm-3 ">
							<select class="form-control " name="Merchant_Name" id="Merchant_Name" >
								<option value="" >Name</option>								
								<?php if(isset($MerchantList) && !empty($MerchantList)) {
									foreach($MerchantList as $m_key=>$m_val) {								
								?>
								<option value="<?php echo $m_val->id;?>" <?php if(isset($_SESSION['loc_mer_name']) && $_SESSION['loc_mer_name'] == $m_val->id){echo "selected";}else{echo '';} ?>><?php echo ucfirst($m_val->CompanyName);?></option>
								<?php } } ?>								
							</select>
						</div>
						<div class="col-sm-3 form-group">
							
							<select class="form-control " name="Merchant_Category" id="Merchant_Category" >
								<option value="" >Category</option>								
								<?php if(isset($CategoryList) && !empty($CategoryList)) {
										foreach($CategoryList as $m_key=>$m_val) { ?>
								<option value="<?php echo $m_val->id;?>" <?php if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] == $m_val->id) echo "selected"; ?>><?php echo ucfirst($m_val->CategoryName);?></option>
								<?php } } ?>								
							</select>
						</div>
						<div class="col-sm-3 col-xs-6 form-group">
							
							<input type="text" class="form-control" placeholder="Price" onkeyPress = "return isNumberKey_Enter(event)" name="Merchant_Price" id="Vistit"  value="<?php  if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '') echo $_SESSION['loc_mer_price'];  ?>" >
						</div>
						<div class="col-sm-3 col-xs-6 form-group">
						
							<select class="form-control " name="Merchant_city" id="Merchant" >
								<option value="" >City</option>								
								<?php if(isset($CityList) && !empty($CityList)) {
									foreach($CityList as $m_key=>$m_val) {								
								?>
								<option value="<?php echo $m_val->City;?>" <?php if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] == $m_val->City) echo "selected"; ?>><?php echo ucfirst($m_val->City);?></option>
								<?php } } ?>								
							</select>
						</div>
						<div class="col-xs-12 clear" align="center">
							<label>&nbsp;</label>
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" >
						</div>	
					</div>	
				</div>	
			</form>		
		</div>
	</div>	
</section>
<section class="content ">
	<div class="row">
		<div class=" col-xs-12">
					<div class="box box-primary box-padding gray_bg">
						<div class="box-body no-padding" >	
							<div class="col-sm-2 col-xs-6 form-group " >
								<label><a href="ReportOverview?cs=1" id="sel_4">Overview</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="TransactionHistory?cs=1" id="sel_2">Transaction history</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="Performance?cs=1" id="sel_5">Performance</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="Demographics?cs=1" id="sel_6">Demographics</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="CustomerReport?cs=1" id="sel_3">Customer list</a></label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group" >
								<label><a href="LocationBreakdown?cs=1" id="sel_1">Location breakdown</a></label>
							</div>
						</div>	
					</div>		
			</div>
		</div>	
</section>

<?php //commonFooter(); ?>
<script src="<?php echo SITE_PATH;?>/admin/webresources/js/jquery-latest.js" type="text/javascript"></script>
<?php if(isset($formStatus) && $formStatus != 0){  ?>
	<script>
		$('#sel_<?php echo $formStatus?>').addClass('sel');
	</script>
<?} ?>

		