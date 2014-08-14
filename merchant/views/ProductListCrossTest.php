<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

$dealsArray			=	array();
$totalPro			=	0;
if(isset($_GET['Ajax']) && $_GET['Ajax'] == 1) {
	if(isset($_POST['CatID']) && (!empty($_POST['CatID']) || $_POST['CatID'] == 0) && isset($_POST['idsarray']) && !empty($_POST['idsarray'])) {
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
//echo "<pre>"; echo print_r($merchantInfo); echo "</pre>";
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
	//echo "<pre>"; echo print_r($specialArray); echo "</pre>";
	//echo "<pre>"; echo print_r($productList); echo "</pre>";
	foreach($productList as $key=>$value) {
		$totalPro		=	$totalPro + count($value);
	}	
}
commonHead();
?>

<body class="skin-blue fixed drag_box" onload="fieldfocus('ItemName');">
		<?php top_header(); ?>
		<section class="content">
		<div class="product_list">
		<div class="col-lg-12 box-center">	
			<?php if(empty($merchantInfo['MangoPayUniqueId'])){?>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12"><i class="fa fa-warning"></i>&nbsp;&nbsp;Please connect with MangoPay in My Account to add products.</div>
			<?php } else if(isset($hide) && $hide == 1) { ?> <br/><br/><br/>
				<div align="center" class="alert alert-danger alert-dismissable  col-lg-5 col-sm-7  col-md-5 col-xs-12"><i class="fa fa-warning"></i>&nbsp;&nbsp;Cannot add products until Price Scheme is selected in Settings</div>
			<?php } else { ?>
			<section class="content-header">
                <h1>Product List</h1>
            </section>
			
			<div class="box box-primary no-padding">
				<div class="box-body">
					<div class="col-xs-8 no-padding">
						<h4><strong>Categories</strong> <?php if(isset($showcat) && $showcat == 1) echo 'Drag & drop items to reorder them'; ?></h4>
					</div>
					<div class="col-xs-4 text-right pad"><a  href="Category?show=0" class="cateWindow"><i class="fa fa-plus"></i> Add Category</a></div>
					
					<!-- start product List -->
					<div class="col-xs-12 no-padding"><hr></div>
					
					<div  class="col-xs-8 no-padding">
						<h4><strong>Deals</strong>&nbsp;&nbsp;This Category can't be renamed</h4>
					</div>				
					<div class="col-xs-4 text-right pad"><a href="Product?show=0&add=deals" class="newWindow"><i class="fa fa-plus"></i> Add Item</a></div>
					<div class="row col-xs-12 clear draggableList" id="dragList_deals">										
						<?php 
							if(isset($dealsArray) && count($dealsArray) > 0) {
								$dealsArray = subval_sort($dealsArray,'Ordering');
								foreach($dealsArray as $key1=>$value1 ) {								
									if($value1["ProductId"]!= ''){ ?>	
							<div class="col-xs-11  col-md-3 col-sm-4 col-lg-2 <?php if($value1['Status'] == 2) echo "inactive";?> paneldragging" id="<?php echo $value1["ProductId"];?>">
								<div id="<?php echo $value1["ProductId"];?>" class="small-box panel-heading">
									<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
										<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br/>
									</a>
									<a class="edit newWindow" href="Product?show=0&edit=<?php echo $value1['ProductId']; ?>&add=deals"><i class="fa fa-pencil fa-lg"></i></a>
									<div class="product_price">
									<span class="title_product" style="" title="<?php echo ucfirst($value1['ItemName']); ?>"><?php echo displayText(ucfirst($value1['ItemName']),10);?></span>
									<?php 	
										echo "<div class='cal actual_price pull-right' style='color:gray;'>$".$value1['Price']."</div> "; 
									?>
									</div>
								</div>
							</div> 
						<?php }  }  }?>										
					</div><!-- /row -->
					<div class="col-xs-12 no-padding"><hr></div> <!-- sep line -->
					
					<div  class="col-xs-8 no-padding">
						<h4><strong>Specials</strong>&nbsp;&nbsp;This Category can't be renamed</h4>
					</div>				
					<div class="col-xs-4 text-right pad"><a href="Product?show=0&add=specials" <?php if($totalPro != 0) echo 'class="specialsnewWindow"'; else echo 'onclick="return noProducts();"'; ?>><i class="fa fa-plus"></i> Add Item</a></div>
					<div class="row col-xs-12 clear draggableListspecial" id="dragList_special">										
						<?php 
							if(isset($specialArray) && count($specialArray) > 0) {
								$specialArray = subval_sort($specialArray,'Ordering');
								foreach($specialArray as $key1=>$value1 ) {								
									if($value1["ProductId"]!= ''){ ?>	
										<div class="col-xs-11  col-md-3 col-sm-4 col-lg-2 <?php if($value1['Status'] == 2) echo "inactive";?> paneldragging" id="<?php echo $value1["ProductId"];?>">
											<div id="<?php echo $value1["ProductId"];?>" class="small-box panel-heading">
												<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
													<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br/>
												</a>
												<a class="edit specialsnewWindow" href="Product?show=0&edit=<?php echo $value1['ProductId']; ?>&add=specials"><i class="fa fa-pencil fa-lg"></i></a>
												<div class="product_price">
												<span class="title_product" style="" title="<?php echo ucfirst($value1['ItemName']); ?>"><?php echo displayText(ucfirst($value1['ItemName']),10);?></span>
												<?php 
													if($value1['OriginalPrice'] != '0.00') {
														echo "<div class='cal pull-right'><strong>$".floatval($value1['Price'])."</strong></div>";  
														echo "<div class='cal actual_price pull-right' style='color:gray;'>$".floatval($value1['OriginalPrice'])."</div> "; 
													} else {
														echo "<div class='cal actual_price pull-right' style='color:gray;'>$".floatval($value1['Price'])."</div> "; 
													}
												?>
												</div>
											</div>
										</div> 
						<?php }  }  }?>										
					</div><!-- /row -->
					<div class="col-xs-12 no-padding"><hr></div> <!-- sep line -->
					
					<?php if(isset($productList) && !empty($productList)) {	 
							foreach($productList as $key=>$value) {  ?>																		
							<div  class="col-xs-8 no-padding">
								<h4><strong><?php echo ucfirst($value[0]['CategoryName']); ?></strong>
									&nbsp;&nbsp;<a class="cateWindow" href="Category?show=0&edit=<?php echo $value[0]['fkCategoryId']; ?>&categoryName=<?php echo base64_encode($value[0]['CategoryName']); ?>&delStatus=<?php echo $value[0]['ProductId']; ?>"><i class="fa fa-edit"></i></a> 
								</h4>
							</div>
							
							<div class="col-xs-4 text-right pad"><a href="Product?show=0&add=<?php echo $key; ?>" class="newWindow"><i class="fa fa-plus"></i> Add Item</a></div>
							<div class="row col-xs-12 clear draggableList" id="dragList_<?php echo $key; ?>">										
								<?php 
									$value = subval_sort($value,'Ordering');
									foreach($value as $key1=>$value1 ) { 
										if($value1["ProductId"]!= ''){ ?>	
											<div class="col-xs-11  col-md-3 col-sm-4 col-lg-2 <?php if($value1['Status'] == 2) echo "inactive";?> paneldragging" id="<?php echo $value1["ProductId"];?>">
												<div id="<?php echo $value1["ProductId"];?>" class="small-box panel-heading">
													<a href="<?php echo $value1['Photo'];?>" class="Product_fancybox" title="<?php echo ucfirst($value1['ItemName']);?>">
														<img height="100" width="100" src="<?php echo $value1['Photo']; ?>" alt=""><br/>
													</a>
													<a class="edit newWindow" href="Product?show=0&edit=<?php echo $value1['ProductId']; ?>&add=<?php if($value1['fkCategoryId'] == 1) echo "deals"; ?>"><i class="fa fa-pencil fa-lg"></i></a>
													<div class="product_price">
													<span class="title_product" style="" title="<?php echo ucfirst($value1['ItemName']); ?>"><?php echo displayText(ucfirst($value1['ItemName']),10);?></span>
													<?php 											
														if($value1['DiscountPrice'] > 0)
															echo "<div class='cal pull-right'><strong>$".floatval($value1['DiscountPrice'])."</strong></div>";  
														echo "<div class='cal actual_price pull-right' style='color:gray;'>$".$value1['Price']."</div> "; 
													?>
													</div>
												</div>
											</div> 
								<?php }  } ?>										
							</div><!-- /row -->
							<div class="col-xs-12 no-padding"><hr></div> <!-- sep line -->
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
			$('.Product_fancybox').fancybox();			
			$(".newWindow").fancybox({
					scrolling	: 'none',			
					type		: 'iframe',
					width		: '380',
					position	:'fixed',
					maxWidth	: '100%',  // for respossive width set					
					fitToView	: false, 
					afterClose 	: function() {
										location.reload();
										return;
										 //$.fancybox.resize();
									}
			});
			$(".specialsnewWindow").fancybox({
					scrolling	: 'true',			
					type		: 'iframe',
					width		: '450',
					position	:'fixed',
					maxWidth	: '100%',  // for respossive width set					
					fitToView	: false, 
					afterClose 	: function() {
										location.reload();
										return;
										 //$.fancybox.resize();
									}
			});
				
			$(".cateWindow").fancybox({
					scrolling: 'none',			
					type: 'iframe',
					width: '380',
					//height: '200',
					maxWidth: '100%',  // for respossive width set					
					fitToView: false,
					minHeight : 135,
					afterClose : function() {
									location.reload();
									return;
								}
			});
		});
		
		<?php if ($_SERVER['HTTP_HOST'] == '172.21.4.104') {
			if(isset($productList) && count($productList) > 0) { foreach($productList as $key=>$value) {  ?>	
				$("#dragList_<?php echo $key; ?>").sortable({
					connectWith: ".draggableList",
					update: function() {idsarray	=	new Array();
							i			=	0;
							cat_id		=	'';
							$('.paneldragging', "#dragList_<?php echo $key; ?>").each(function(index, elem) {
								 var $listItem 	= $(elem),
								 newIndex 		= $listItem.index();
								 //console.log($listItem[0].id);
								 idsarray[i]	=	$listItem[0].id;
								i++;						 
							});
						
							$.ajax({
							type: "POST",
							url: "./ProductListCrossTest?Ajax=1",
							data: 'idsarray='+idsarray+'&CatID='+<?php echo $key; ?>,			
							success: function (result){
								
							}			
						});
					}
				});
		<?php } } ?>				
		<?php } ?>
		$("#dragList_special").sortable({
			connectWith: ".draggableListspecial",
			update: function() {idsarray	=	new Array();
					i			=	0;
					cat_id		=	'';
					$('.paneldragging', "#dragList_special").each(function(index, elem) {
						 var $listItem 	= $(elem),
						 newIndex 		= $listItem.index();
						 //console.log($listItem[0].id);
						 idsarray[i]	=	$listItem[0].id;
						i++;						 
					});
				
					$.ajax({
					type: "POST",
					url: "./ProductListCrossTest?Ajax=1",
					data: 'idsarray='+idsarray+'&CatID=0',			
					success: function (result){
						
					}			
				});
			}
		});
	</script>	 
</html>
