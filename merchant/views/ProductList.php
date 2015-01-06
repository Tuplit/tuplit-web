<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$dealsArray			=	$specialArray = array();
$totalPro			=	0;
if(isset($_GET['Ajax']) && $_GET['Ajax'] == 1) {
	if(isset($_POST['CatID']) && (!empty($_POST['CatID'])  || $_POST['CatID'] == 0) && isset($_POST['idsarray']) && !empty($_POST['idsarray'])) {
		$ProductIds		=	explode(",",$_POST['idsarray']);
		$data			=	array(
								'CatId'				=> $_POST['CatID'],
								'ProductIds'		=> $ProductIds
							);
		$method			=	'PUT';
		$url			=	WEB_SERVICE.'v1/products/';
		$curlResponse	=	curlRequest($url,$method,json_encode($data),$_SESSION['merchantInfo']['AccessToken']);
		if(isset($curlResponse) && is_array($curlResponse) && $curlResponse['meta']['code'] == 201) {
			echo "1";
		}
		else
			echo "0";
	}
	die();
}

//getting merchant details
$merchantId					= 	$_SESSION['merchantInfo']['MerchantId'];
$url						=	WEB_SERVICE.'v1/merchants/'.$merchantId."?From=0";
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && $curlMerchantResponse['merchant']['MerchantId'] != '' ) 
 {
	$merchantInfo  			= 	$_SESSION['merchantDetailsInfo']   =	$curlMerchantResponse['merchant'];
	$newCategory			=	$merchantInfo['Category'];
	if(!empty($merchantInfo['DiscountTier']) || $merchantInfo['DiscountTier'] != 0 ) {
	}
	else {
		$hide	= 	1;	
	}
}

//getting product counts details
$Totalproducts	=	$TotalDiscountApplied	=	$Discounted	=	0;
$url						=	WEB_SERVICE.'v1/merchants/discount/?Type=3';
$curlMerchantResponse 		= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlMerchantResponse) && is_array($curlMerchantResponse) && $curlMerchantResponse['meta']['code'] == 201 && isset($curlMerchantResponse['ProductCounts'])) 
{
	$ProductCounts	=	$curlMerchantResponse['ProductCounts'];
	if(isset($ProductCounts['Totalproducts']) && !empty($ProductCounts['Totalproducts']) && $ProductCounts['Totalproducts'] > 0)
		$Totalproducts	=	$ProductCounts['Totalproducts'];
		
	if(isset($ProductCounts['TotalDiscountApplied']) && !empty($ProductCounts['TotalDiscountApplied']) && $ProductCounts['TotalDiscountApplied'] > 0)
		$TotalDiscountApplied	=	$ProductCounts['TotalDiscountApplied'];
		
	if(isset($ProductCounts['Discounted']) && !empty($ProductCounts['Discounted']) && $ProductCounts['Discounted'] > 0)
		$Discounted	=	$ProductCounts['Discounted'];
}

//getting product List
$url					=	WEB_SERVICE.'v1/products/';
$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, $_SESSION['merchantInfo']['AccessToken']);
if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 201 && is_array($curlCategoryResponse['ProductList']) ) {
	if(isset($curlCategoryResponse['ProductList']))
	$productList = $curlCategoryResponse['ProductList'];	
} else if(isset($curlCategoryResponse['meta']['errorMessage']) && $curlCategoryResponse['meta']['errorMessage'] != '') {
		$errorMessage	=	$curlCategoryResponse['meta']['errorMessage'];
} else {
		$errorMessage	= 	"Bad Request";
} 
if(isset($productList) && !empty($productList)) { 
	foreach($productList as $key=>$value) {
		if(!empty($value[0]['ProductId'])) {
			$showcat	=	1;
			break;
		}		
	}
	
	if(isset($productList[0]) && count($productList[0]) > 0) {		
		foreach($productList[0] as $data) {
			if($data['ItemType']	==	2) 
				$dealsArray[]	=	$data;
			if($data['ItemType']	==	3) 
				$specialArray[]	=	$data;
		}
		unset($productList[0]);
	}
	foreach($productList as $key=>$value) {
		if($value[0]['ProductId'] != '')
			$totalPro		=	$totalPro + count($value);
	}
}
commonHead();
?>

<body class="skin-blue fixed drag_box body_height bproduct_list" onclick="hideAlertMsg();">
		<?php top_header(); ?>
		<section class="content">		
		<div class="item_discount">
			<!-- Total products -->
			<div class="pull-left white_area">
				<?php if($Totalproducts > 0)
						echo "<big id='toptotpro'>".$Totalproducts."</big><span> items<br>Added</span>";
					else
						echo "<span>No items added</span>";
				?>
			</div>
			<!-- Total products -->
			
			<!-- Total Discount Applied -->
			<div class="nodiscount" <?php if($Discounted == 1) echo ''?>>
				<div class="pull-left">
				<?php if($TotalDiscountApplied > 0)
						echo "<big id='topdispro'>".$TotalDiscountApplied."</big><p>items<br> discounted</p>";
					else
						echo "<p>No items discounted</p>";
				?>
				</div>
			</div>
			<!-- Total Discount Applied -->
		
			<!-- Comment Text -->
			<span class="comment_txt">
				<?php if($Totalproducts <= 20 )  
						echo "Your business isn't being published if you add less then 20 items";
					else if($Discounted == 1)
						echo "Your business is set perfectly with number of added and discounted items. Enjoy!";
					else 
						echo "Your business isn't going to be published unless 1/3 off added items to be discounted";
				?>			
			</span>
			<!-- Comment Text -->
		</div>
		
		<div class="product_list">
		<div class="col-lg-12 box-center">		
			<?php if(empty($merchantInfo['MangoPayUniqueId'])){?>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12 clear"><i class="fa fa-warning"></i>&nbsp;&nbsp;Please connect with MangoPay in Settings to add products.</div>
			<?php } else if(isset($hide) && $hide == 1) { ?> <br/><br/><br/>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12 clear"><i class="fa fa-warning"></i>&nbsp;&nbsp;Cannot add products until Price Scheme is selected in Settings</div>
			<?php } else { ?>
			<section class="content-header">
				<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 no-padding">
						<h1 class="no-padding no-margin">Products / Categories</h1>
						<p class="sub_tit"><?php if(isset($showcat) && $showcat == 1) echo 'Drag & drop items to reorder them'; ?></p>
					</div>
					<div class="col-xs-12 col-sm-4 col-md-4 col-lg-4  text-right pad"><a onclick="return loaded;" href="Category?show=0" class="cateWindow add_cat categoryBox"><i class="fa fa-plus"></i> Add Category</a></div>
            </section>
			
				<div class="box box-primary no-padding clear" style="margin-bottom:20px;">
					<div class="box-body">
					<!-- <div class="col-xs-12 no-padding"><hr></div> --> <!-- sep line -->
					
					<div  class="col-xs-11 col-sm-4 col-md-4 col-lg-4">
						<h2 style="margin-bottom:0px;">Specials</h2>
						<P>This Category can't be renamed</P>
					</div>
					<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 text-right pad resp-text-center">
						<a href="javascript:void(0)" class="delete_items" onclick="return selectAllProductDelete('dragList_special','3');" <?php if(isset($specialArray) && count($specialArray) == 0) echo 'style="display:none;"'; ?> ><i class="fa fa-trash"></i> Delete</a>
						<a href="javascript:void(0)" class="select_all" <?php if(isset($specialArray) && count($specialArray) == 0) echo 'style="display:none;"'; ?> ><i class="fa fa-check"></i> Select All</a>
						<a onclick="return loaded;" href="Product?show=0&add=specials" <?php if($totalPro != 0) echo 'class="specialsnewWindow add_item"'; else echo 'onclick="return noProducts();"'; ?>><i class="fa fa-plus"></i> Add Specials</a>
					</div>
					<div class="col-xs-12">
						<div align="center" id="alert_dragList_special" class="alert alert-success alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12 hideshowalert" style="display:none;"><i class="fa fa-check"></i>&nbsp;&nbsp;Selected products deleted successfully.</div>
					</div>	
					<div class="row no-margin col-xs-12 clear draggableListspecial" id="dragList_special" style="margin-bottom:20px;">	
						<?php 
							if(isset($specialArray) && count($specialArray) > 0) {
								foreach($specialArray as $key1=>$value1 ) {								
									if($value1["ProductId"]!= ''){ ?>	
										<div class="col-xs-6 col-md-2 col-sm-2 <?php //if($value1['Status'] == 2) echo "inactive";?> paneldragging" id="<?php echo $value1["ProductId"];?>">
											<div id="<?php echo $value1["ProductId"];?>" class="small-box panel-heading" onclick="cls(this);">
												<div class="list_box" style="background-image:url(<?php echo $value1['Photo']; ?>);">
													<a onclick="return loaded;" href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
														<!-- <img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""> --><br/>
													</a>
													<a onclick="return loaded;" class="edit specialsnewWindow" href="Product?show=0&edit=<?php echo $value1['ProductId']; ?>&add=specials"><i class="fa fa-pencil-lt fa-lg"></i></a>
												</div>
												<div class="product_price">
													<span class="title_product" style="" title="<?php echo ucfirst($value1['ItemName']); ?>"><?php echo ucfirst($value1['ItemName']);?></span>
													<?php 
														if($value1['OriginalPrice'] != '0.00') {
															echo "<div class='cal pull-center'><strong>".price_fomat($value1['Price'])."</strong></div>";  
															echo "<div class='cal actual_price pull-center' style='color:gray;'>".price_fomat($value1['OriginalPrice'])."</div> ";
														?> <input type="hidden" id="product_discount_type_<?php echo $value1["ProductId"]; ?>" value="1" /><?php
														} else {
															echo "<div class='cal actual_price' style='color:gray;'>".price_fomat($value1['Price'])."</div> "; 
														?> <input type="hidden" id="product_discount_type_<?php echo $value1["ProductId"]; ?>" value="" /><?php
														}
													?>
												</div>
											</div>
										</div> 
						<?php }  }  }?>	
					</div><!-- /row -->
					</div>
				</div>
				<div class="box box-primary no-padding clear" style="margin-bottom:20px;">
					<div class="box-body">
					<!-- <div class="col-xs-12 no-padding"><hr></div> --> <!-- sep line -->
					
					<?php if(isset($productList) && !empty($productList)) {								
							foreach($productList as $key=>$value) {  
					?>																		
							<div  class="col-xs-11 col-sm-4 col-md-4 col-lg-4">
								<h2><?php echo ucfirst($value[0]['CategoryName']); ?></h2>
							</div>							
							<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8 text-right pad resp-text-center">
								<?php 
									if(count($value) > 0 && !empty($value[0]['ProductId']))
										$count	= 	count($value);
									else
										$count	=	0;
								?>							
								
								<a href="javascript:void(0)" class="delete_items" id="delete_items_<?php echo $value[0]['fkCategoryId']; ?>" onclick="return selectAllProductDelete('dragList_<?php echo $key; ?>','1','<?php echo $value[0]['fkCategoryId']; ?>');" <?php if($count == 0) echo 'style="display:none;"'; ?>><i class="fa fa-trash"></i> Delete</a>
								<a onclick="return loaded;" id="edit_cat_<?php echo $value[0]['fkCategoryId']; ?>" href="Category?show=0&edit=<?php echo $value[0]['fkCategoryId']; ?>&categoryName=<?php echo base64_encode($value[0]['CategoryName']); ?>&delStatus=<?php echo $value[0]['ProductId']; ?>&cat_pro=<?php echo base64_encode($count); ?>" class="edit_specials categoryBox"><i class="fa fa-pencil"></i>Edit <?php echo ucfirst($value[0]['CategoryName']); ?></a>
								<a href="javascript:void(0)" class="select_all" id="select_all_<?php echo $value[0]['fkCategoryId']; ?>" <?php if($count == 0) echo 'style="display:none;"'; ?>><i class="fa fa-check"></i> Select All</a>
								<a onclick="return loaded;" href="Product?show=0&add=<?php echo $key; ?>" class="newWindow add_item"><i class="fa fa-plus"></i> Add Item</a>
							</div>
							<div class="col-xs-12">
								<div align="center" id="alert_dragList_<?php echo $key; ?>" class="alert alert-success alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12 hideshowalert" style="display:none;"><i class="fa fa-check"></i>&nbsp;&nbsp;Selected products deleted successfully.</div>
							</div>	
							<div class="row no-margin col-xs-12 clear draggableList" id="dragList_<?php echo $key; ?>" style="margin-bottom:20px;">									
								<?php 
									$value = subval_sort($value,'Ordering');
									foreach($value as $key1=>$value1 ) {
										if($value1["ProductId"]!= ''){ ?>	
											<div class="col-xs-6 col-md-2 col-sm-2 <?php //if($value1['Status'] == 2) echo "inactive";?> paneldragging" id="<?php echo $value1["ProductId"];?>" onclick="clss(this);">
												<div id="<?php echo $value1["ProductId"]."_pro";?>" class="small-box panel-heading" onclick="cls(this);">
													<div class="list_box" style="background-image:url(<?php echo $value1['Photo']; ?>);">
														<a onclick="return loaded;" href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
															<!-- <img height="100" width="100" src="<?php  // echo $value1['Photo']; ?>" alt=""><br/> -->
														</a>
														<a onclick="return loaded;" class="edit newWindow" href="Product?show=0&edit=<?php echo $value1['ProductId']; ?>&add=<?php if($value1['fkCategoryId'] == 1) echo "deals"; ?>"><i class="fa fa-pencil-lt fa-lg"></i></a>
													</div>
													<div class="product_price">
													<span class="title_product" style="" title="<?php echo ucfirst($value1['ItemName']); ?>"><?php echo ucfirst($value1['ItemName']);?></span>
													<?php 
														if($value1['DiscountPrice'] > 0){
															echo "<div class='cal pull-center'><strong>".price_fomat($value1['DiscountPrice'])."</strong></div>";  
															echo "<div class='cal actual_price pull-center' style='color:gray;'>".price_fomat($value1['Price'])."</div> "; 
														?> <input type="hidden" id="product_discount_type_<?php echo $value1["ProductId"]; ?>" value="1" /><?php
														} else {
															echo "<div class='cal actual_price' style='color:gray;'><strong>".price_fomat($value1['Price'])."</strong></div> "; 	
														?> <input type="hidden" id="product_discount_type_<?php echo $value1["ProductId"]; ?>" value="" /><?php
														}
													?>
													</div>
												</div>
											</div> 
								<?php }  } ?>										
							</div><!-- /row -->
					</div> <!-- Box body -->
				</div>
				<div class="box box-primary no-padding clear" style="margin-bottom:20px;">
					<div class="padding-left10">
							<!-- <div class="col-xs-12 no-padding"><hr></div> --> <!-- sep line -->
					<?php  }  ?>
						<!-- End product List -->						 
					<?php } else { ?>
						<div class="row clear">		
							 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i>  No items found. Please add items</div>							
						</div>							
					<?php } ?>						
					</div><!-- /.box-body -->
			</div>					
		</div>	
			<?php } ?>
		 </div>
		</section>
		<?php footerLogin(); ?>
	<?php commonFooter(); ?>
	<script type="text/javascript">
		$(document).ready(function() {			
			$("#deleteFancy").fancybox(); 
			$('.Product_fancybox').fancybox();	
			
			$(".newWindow").fancybox({
					scrolling	: 'none',			
					type		: 'iframe',
					width		: '600',
					position	:'fixed',
					maxWidth	: '100%',  // for respossive width set					
					fitToView	: false,
					//wrapCSS 	: 'photo-lightbox-class',		
					afterClose 	: function() {
										location.reload();
										return;
									}
			});
			$(".specialsnewWindow").fancybox({
					scrolling	: 'true',			
					type		: 'iframe',
					width		: '600',
					position	:'fixed',
					maxWidth	: '100%',  // for respossive width set					
					fitToView	: false, 
					afterClose 	: function() {
										location.reload();
										return;
									}
			});
			$(".categoryBox").fancybox({
					scrolling: 'none',			
					type: 'iframe',
					width: '380',
					maxWidth: '100%',  // for respossive width set					
					fitToView: false,
					minHeight : 175,
					maxHeight : 275,
					afterClose : function() {
									location.reload();
									return;
								}
			});
		});
		
		<?php	if(isset($productList) && count($productList) > 0) { foreach($productList as $key=>$value) {  ?>	
				$("#dragList_<?php echo $key; ?>").sortable({
					connectWith: ".draggableList",
					update: function() {idsarray	=	new Array();
							i	=	0;
							$('.paneldragging', "#dragList_<?php echo $key; ?>").each(function(index, elem) {
								 var $listItem 	= $(elem);
								 idsarray[i]	=	$listItem[0].id;
								i++;						 
							});
							if(i == 0) {
								$('#delete_items_<?php echo $key; ?>').hide();
								$('#select_all_<?php echo $key; ?>').hide();
							} else {
								$('#delete_items_<?php echo $key; ?>').show();
								$('#select_all_<?php echo $key; ?>').show();
							}
							$.ajax({
							type: "POST",
							url: "./ProductList?Ajax=1",
							data: 'idsarray='+idsarray+'&CatID='+<?php echo $key; ?>,			
							success: function (result){
								
							}			
						});
					}
				});
		<?php } } ?>
		$("#dragList_special").sortable({
			connectWith: ".draggableListspecial",
			update: function() {idsarray	=	new Array();
					i	=	0;
					$('.paneldragging', "#dragList_special").each(function(index, elem) {
						 var $listItem 	= 	$(elem);
						 idsarray[i]	=	$listItem[0].id;
						i++;						 
					});
				
					$.ajax({
					type: "POST",
					url: "./ProductList?Ajax=1",
					data: 'idsarray='+idsarray+'&CatID=0',			
					success: function (result){
						
					}			
				});
			}
		});
			
		//select all product and unselect
		$( ".select_all" ).click(function() {	
			if($(this).hasClass('select')){
				$(this).closest("div.box").find(".small-box,.panel-heading").removeClass("select_active");
				$(this).removeClass('select');
				$(this).html('<i class="fa fa-check"></i> Select All');
			}
			else{
				$(this).addClass('select');
				$(this).html('<i class="fa fa-check"></i> Unselect All');
				$(this).closest("div.box").find(".small-box,.panel-heading").addClass("select_active");
			}			
		});	
		
		//select all and drag
	</script>
</html>
