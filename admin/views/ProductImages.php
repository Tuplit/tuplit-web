<?php
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/ProductController.php');
$ProductObj   	=   new ProductController();
$condition		= '';
$startLimit			=   "0";
if(isset($_GET['gettype']) && !empty($_GET['gettype']) && $_GET['gettype'] == 1) {
	
}
if(isset($_GET['gettype']) && !empty($_GET['gettype']) && $_GET['gettype'] == 1) {
	if(isset($_GET['merchantId']) && $_GET['merchantId']!='' && isset($_GET['prosearch']) && $_GET['prosearch']!=''){
		$condition 		.= 	" and p.fkMerchantsId ='".$_GET['merchantId']."' and p.ItemName like '%".addslashes($_GET['prosearch'])."%'";
	} else if(isset($_GET['merchantId']) && $_GET['merchantId']!=''){
		$condition 		.= 	" and p.fkMerchantsId ='".$_GET['merchantId']."'";
	} else if(isset($_GET['prosearch']) && $_GET['prosearch']!=''){
		$condition 		.= 	" and p.ItemName like '%".addslashes($_GET['prosearch'])."%'";
	}
} else {
	if(isset($_GET['prosearch']) && $_GET['prosearch']!='' && isset($_GET['mersearch']) && $_GET['mersearch']!='' ){
		$condition 		.= 	" and p.ItemName like '%".addslashes($_GET['prosearch'])."%' and m.CompanyName like '%".addslashes($_GET['mersearch'])."%'";
	} else {
		if(isset($_GET['mersearch']) && $_GET['mersearch']!='' )
			$condition 		.= 	" and m.CompanyName like '%".addslashes($_GET['mersearch'])."%'";
		if(isset($_GET['prosearch']) && $_GET['prosearch']!='' )
			$condition 		.= 	" and p.ItemName like '%".addslashes($_GET['prosearch'])."%'";
	}
} 
if(isset($_GET['start']) && $_GET['start']!=''){
	$startLimit				=   $_GET['start'];
}
$fields    				= 	" p.*,m.CompanyName,m.Icon,pc.CategoryName,m.DiscountTier as Discount ";
$condition 				.= 	" and p.Status in (1,2)";

$productListResult  	= 	$ProductObj->getProductList($fields,$condition,$startLimit);
$total_rec 		 		= 	$ProductObj->getTotalRecordCount();

if(is_array($productListResult) && count($productListResult)>0){
	foreach($productListResult as $key=>$value){
		$image_path = '';
		$photo = $value->Photo;
		$image_path = SITE_PATH.'/Refer/site_source/no_photo_product1.png';
		if(isset($photo) && $photo != ''){
			if(SERVER){
				if(image_exists(3,$photo))
					$image_path = PRODUCT_IMAGE_PATH.$photo;
			}
			else{
				if(file_exists(PRODUCT_IMAGE_PATH_REL.$photo))
					$image_path = PRODUCT_IMAGE_PATH.$photo;
			}
		}
		if($value->Discount > 0)
			$dis_cost = floatval($value->Price - (($value->Price / 100) * $discountTierArray[$value->Discount]));
		else
			$dis_cost = 0;
		$price = '';
		if($dis_cost >0 ) $price =  price_fomat($dis_cost);
		else{
			 if(isset($value->Price) && $value->Price >= 0) 
			 	$price =   price_fomat($value->Price);
	    } 
		if(isset($_GET['action']) && $_GET['action'] == 'GET_MORE_PRODUCTS'){  
			$resultArray[] = '<img title="'.$value->ItemName.'" width="115" height="110" align="top" src="'.$image_path.'" ><br><span class="name">'.'<b>'.ucfirst($value->ItemName).'</b>'.'</span><span>'.$price.'</span><em>VAT Include</em>';
		}
		else { ?>
			<li>
				<img src="<?php echo $image_path; ?>" width="115" height="110" />
				<span class="name">
					<?php if(isset($value->ItemName) && $value->ItemName != ''){  echo "<b>".ucfirst($value->ItemName)."</b>"; } ?>
					<span>
						<?php echo $price;?>
					</span>
					<em>VAT Include</em>
				</span>
			</li>
		<?php } ?>
		
<?php } 
	if(isset($resultArray)){
		echo json_encode($resultArray);
	}
} 
else{
	echo 'fails';
}
?>
		
