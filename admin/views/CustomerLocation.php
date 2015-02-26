<?php
require_once('controllers/MerchantController.php');
$merchantObj   	=   new MerchantController();
require_once('controllers/ManagementController.php');
$managementObj  =   new ManagementController();
/*--------------Merchant Array----------------------*/
$condition       	= "  Status =1 order by CompanyName asc";
$field				=	' id,CompanyName';
$MerchantList		= 	$merchantObj->selectMerchantDetails($field,$condition);
/*---------------Category Array--------------------------*/
$condition       	= 1;
$field				='id,CategoryName';
$CategoryList  		= $managementObj->selectCategoryDetails($field,$condition);

/*--------------City Array-----------------------------*/
$condition       	= " City<>''  group by City order by City asc";
$field				=	' City';
$CityList		= 	$merchantObj->selectMerchantDetails($field,$condition);
?>

<section class="content-header">

	<?php 	

	/*
	if(isset($latlng) && is_array($latlng)){
		echo "<pre>";print_r($latlng);echo "</pre>";
	}
*/

	?>
		<div class="col-sm-7 col-xs-12 col-lg-5 header-margin" style="align:right;">
			<div class="search-box">
				<input type="text" placeholder="Search" value="<?php if(!empty($_SESSION['merchantSearch'])) echo $_SESSION['merchantSearch']; ?>" name="merchantsearch" id="merchantsearch">
				<input type="submit" name="Search" id="merchant_search" value="Search" class="search_icon" title="Search">  
			</div>
		</div>
</section>	
<section class="content">
	<div class="row">
		<div class=" col-xs-12">
			<form name="Location_breakdown" action="<?php if(isset($formStatus) && $formStatus == 1) echo "LocationBreakdown"; else if (isset($formStatus) && $formStatus == 2) echo "TransactionHistory"; else if (isset($formStatus) && $formStatus == 3) echo "CustomerReport";?>" method="post">
				<div class="box box-primary box-padding">
					<div class="box-body no-padding" >	
						<div class="form-group col-sm-3 ">
							<label>Merchant</label>
							<select class="form-control " name="Merchant_Name" id="Merchant_Name" >
								<option value="" >Select</option>								
								<?php if(isset($MerchantList) && !empty($MerchantList)) {
									foreach($MerchantList as $m_key=>$m_val) {								
								?>
								<option value="<?php echo $m_val->id;?>" <?php if(isset($_SESSION['loc_mer_name']) && $_SESSION['loc_mer_name'] == $m_val->id){echo "selected";}else{echo '';} ?>><?php echo ucfirst($m_val->CompanyName);?></option>
								<?php } } ?>								
							</select>
						</div>
						<div class="col-sm-3 form-group">
							<label>Category</label>
							<select class="form-control " name="Merchant_Category" id="Merchant_Category" >
								<option value="" >Select</option>								
								<?php if(isset($CategoryList) && !empty($CategoryList)) {
										foreach($CategoryList as $m_key=>$m_val) { ?>
								<option value="<?php echo $m_val->id;?>" <?php if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] == $m_val->id) echo "selected"; ?>><?php echo ucfirst($m_val->CategoryName);?></option>
								<?php } } ?>								
							</select>
						</div>
						<div class="col-sm-2 col-xs-6 form-group">
							<label>Price</label>
							<input type="text" class="form-control" name="Merchant_Price" id="Vistit"  value="<?php  if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '') echo $_SESSION['loc_mer_price'];  ?>" >
						</div>
						<div class="col-sm-2 col-xs-6 form-group">
							<label>City</label>
							<select class="form-control " name="Merchant_city" id="Merchant" >
								<option value="" >Select</option>								
								<?php if(isset($CityList) && !empty($CityList)) {
									foreach($CityList as $m_key=>$m_val) {								
								?>
								<option value="<?php echo $m_val->City;?>" <?php if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] == $m_val->City) echo "selected"; ?>><?php echo ucfirst($m_val->City);?></option>
								<?php } } ?>								
							</select>
						</div>
						<div class="col-xs-12 box-footer clear" align="center">
							<label>&nbsp;</label>
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" >
						</div>	
					</div>	
				</div>	
			</form>		
		</div>
	</div>	
</section>
<section class="content">
	<div class="row">
		<div class=" col-xs-12">
					<div class="box box-primary box-padding">
						<div class="box-body no-padding" >	
							<div class="col-sm-2 form-group ">
								<label>Overview</label>
							</div>
							<div class="col-sm-2 form-group">
								<label>Transaction history</label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group">
								<label>Performance</label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group">
								<label>Demographics</label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group">
								<label>Customer list</label>
							</div>
							<div class="col-sm-2 col-xs-6 form-group">
								<label>Location breakdown</label>
							</div>
						</div>	
					</div>		
			</div>
		</div>	
</section>
		