<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();
global $discountTierArray;
global $itemTypeArray;
$specialProductsArray = array();
$photoPath= '';

if(isset($_SESSION['merchantInfo']['MerchantId']) && !empty($_SESSION['merchantInfo']['MerchantId'])) {
	
	//To get regular products list
	$merchantId		= 	$_SESSION['merchantInfo']['MerchantId'];
	$url			=	WEB_SERVICE.'v1/products/regularProducts/'.$merchantId;	
	$curlMerchantResponse  = 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
	if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201) {
		$specialProductsArray   =	$curlMerchantResponse['specialproductDetails'];		
	}	
	//echo'<pre>';print_r();echo'</pre>';
	//New Product POST
	if(isset($_POST['merchant_product_submit']) && $_POST['merchant_product_submit'] == 'SAVE'){		
		if(!empty($selectedProductsID))
			$selectedProductsID = rtrim($selectedProductsID,',');
		$data	=	array(
					'ItemName' 			=> $_POST['ItemName'],
					'ItemDescription' 	=> $_POST['ItemDescription'],
					'Price' 			=> $_POST['Price'],
					'ItemType' 			=> $_POST['ItemType'],
					'DiscountTier' 		=> $_POST['DiscountTier'],
					'DiscountPrice' 	=> $_POST['DiscountPrice'],
					'Photo' 			=> $_POST['product_photo_upload'],				
					'SpecialProductsIds'	=> $_POST['Special_products'],
					'Quantity'			=> $_POST['Quantity']
				);
		$url	=	WEB_SERVICE.'v1/products/';
		$method	=	'POST';
		//echo'<pre>';print_r($data);echo'</pre>';
		$curlResponse	=	curlRequest($url,$method,json_encode($data), $_SESSION['merchantInfo']['AccessToken']);
		//echo'<pre>';print_r($curlResponse);echo'</pre>';
		if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
			
		} else if(isset($curlResponse['meta']['errorMessage']) && $curlResponse['meta']['errorMessage'] != '') {
			$responseMessage	=	$curlResponse['meta']['errorMessage'];
		} else {
			$responseMessage 	= 	"Bad Request";
		}
	}
}
commonHead();
?>

<body class="skin-blue" onload="fieldfocus('ItemName');">
		<?php top_header(); ?>
		<section class="content" align="center">
		<div class="col-md-10" style="margin:auto;float:none" >		
			<section class="content-header">
                <h1>Product</h1>
            </section>			
			<form action="" name="add_product_form" id="add_product_form"  method="post">
				<div class="row">
				<div class="col-md-6">
					<div class="box box-primary no-padding">
						<div class="form-group col-md-12">
							<label>Item Name</label>
							<input class="form-control" type="text" name="ItemName"  id="ItemName" value="">
						</div>
						<div class="form-group col-md-12">
							<label>Item Description</label>
							<textarea class="form-control" id="ItemDescription" name="ItemDescription" cols="5"></textarea>
						</div>
						<div class="form-group col-md-12">
							<label>Product Image</label>
							<div class="row">
						    <div class="col-md-7">	
								<input type="file"  name="product_photo" id="product_photo" onchange="return ajaxAdminFileUploadProcess('product_photo');"  /> 
								<p class="help-block">(Minimum dimension 100x100)</p>
								<span class="error" for="empty_product_photo" generated="true" style="display: none">Product photo is required</span>	
									<div id="product_photo_img">
									</div>	
								<input type="Hidden" name="empty_product_photo" id="empty_product_photo" value="" />
								<input type="Hidden" name="name_product_photo" id="name_product_photo" value="" />				
							</div>
							
						</div>										
						<div class="form-group col-md-12">
							<label>Price</label>
							$ <input class="form-control" type="text" name="Price" style="width:50px;"  id="Price" onkeypress="return isNumberKey(event);" maxlength="10" value="" onchange="return Cal_DiscountPrice();">
						</div>	
						<div class="form-group col-md-12">
							<label>ItemType</label>
							<select class="form-control" id="ItemType" name="ItemType" onchange="return hideShowPrice();">
								<option value="" >Select</option>
								<?php if(isset($itemTypeArray) && is_array($itemTypeArray) && count($itemTypeArray) > 0) {
										foreach($itemTypeArray as $key=>$value){
								 ?>
								<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
								<?php } } ?>
							</select>
						</div>		
						<div id="Deal" class="form-group col-md-12" style="display:none;">
							<label>DiscountTier</label>
							<select class="form-control" id="DiscountTier" name="DiscountTier" onchange="return Cal_DiscountPrice();">
								<option value="" >Select</option>
								<?php if(isset($discountTierArray) && is_array($discountTierArray) && count($discountTierArray) > 0) {
										foreach($discountTierArray as $key=>$value){
								 ?>
								<option value="<?php echo $key; ?>"><?php echo $value.'%'; ?></option>
								<?php } } ?>
							</select>
						</div>
						<div id="Deal1" class="form-group col-md-12" style="display:none;">
							<label>DiscountPrice</label>
							$ <input class="form-control" type="text" name="DiscountPrice" style="width:50px;" id="DiscountPrice" onkeypress="return isNumberKey(event);" maxlength="10" value="" readonly>
						</div>
						<input type="hidden" id="specialProductsCount" name="specialProductsCount">
						<?php if(isset($specialProductsArray) && is_array($specialProductsArray) && count($specialProductsArray) > 0) { ?>						
						<div id="Special" class="form-group col-md-12" style="display:none;">
							<table id='SpecialTable' >
								<tr>
									<th width="15%">Product</th>
									<th width="7%" style="padding-left:30px;">Quantity</th>
									<th width="7%">Price</th>
									<th width="7%">Total Price</th>									
								</tr>
								<tr>
									<td>
										<select class="form-control" id="Special_product_1" name="Special_products_1" onchange="return specialPrice(1);">
										<option value="" >Select</option>
										<?php if(isset($specialProductsArray) && is_array($specialProductsArray) && count($specialProductsArray) > 0) {
												foreach($specialProductsArray as $key=>$value){
										 ?>										
										<option value="<?php echo $value['id']; ?>"><?php echo $value['ItemName']; ?></option>
										<?php } } ?>
									</select>										
									</td>
									<td style="padding-left:30px;">
										<input type="Text" id="Product_Quantity_1" style="width:50px;" onkeypress="return isNumberKey(event);" onchange="return calculateSpecialPrice(1);" maxlength="4" name="Product_Quantity_1" value="0"/>
									</td>
									<td>
										<input type="Text" id="Product_Price_1" style="width:80px;" name="Product_Price_1" value="0" readonly>										
									</td>
									<td>
										<input type="Text"id="Product_Total_1" style="width:80px;" name="Product_Total_1"value="0" readonly>
									<td>
									<td><i class="fa fa-lg fa-plus-circle" id="addrow"></i></td>
								</tr>
							</table>
							<input type="Hidden" id="totalrow" name="totalrow" value="1">
							<?php if(isset($specialProductsArray) && is_array($specialProductsArray) && count($specialProductsArray) > 0) {
									foreach($specialProductsArray as $key=>$value){
							 ?>	
							 <input type="Hidden" id ="price_<?php echo $value['id']; ?>" value="<?php echo $value['Price']; ?>">										
							<?php } } ?>
						</div>	
						<?php } else { ?>
							<span>No Regular Products</span> 
						<?php } ?>
						<div class="form-group col-md-12">
							<label>Quantity</label>
							<input class="form-control" type="text" name="Quantity"  id="Quantity" onkeypress="return isNumberKey(event);" maxlength="15" value="">
						</div>						
					</div>
				</div>	
			</div>
		</div>
				<div class="footer col-md-12" align="center"> 
						<input type="submit" name="merchant_product_submit" id="merchant_product_submit" value="SAVE" class="btn btn-success ">
				</div>
			</form>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
</html>
<script type="text/javascript">
$(document).ready(function() {
	$('.icon_fancybox').fancybox();		
});
$('#addrow').live('click', function(){
   //put jquery this context into a var
   var $btn = $(this);
   //use .closest() to navigate from the buttno to the closest row and clone it
   var $clonedRow = $btn.closest('tr').clone();
   //append the cloned row to end of the table
	var total = parseInt($('#totalrow').val());
	total = total + 1;
	total = total.toString();
	
   //clean ids if you need to
   $clonedRow.find('*').andSelf().filter('[id]').each( function(){
       //clear id or change to something else
	   var id = this.id;
	  
	   id = id.substring(0, id.length - 1);
	   id +=total;
       this.id = id;
	   this.name = id;
   });

   //finally append new row to end of table
   $btn.closest('tbody').append( $clonedRow );
     $('#totalrow').val(total);
});
</script>