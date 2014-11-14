<?php
	require_once('includes/CommonIncludes.php');
	if(isset($_GET['page_id']) && $_GET['page_id']!='') {
		$url					=	WEB_SERVICE.'v1/contents/webpagecontent/'.$_GET['page_id'];
		//echo $url;
		$curlCategoryResponse 	= 	curlRequest($url, 'GET', null, null);
		if(isset($curlCategoryResponse) && is_array($curlCategoryResponse) && $curlCategoryResponse['meta']['code'] == 200) {
			if(isset($curlCategoryResponse['webPageContent'])) {
				$webpagecontent	= $curlCategoryResponse['webPageContent'];
			}
		} 
	}
	commonHead();
?>
<body class="skin-blue fixed">
	<?php top_header_before_login(); ?>
	
	<div class="col-lg-8 box-center">	
		<section class="content-header">
			<h1><?php if(isset($webpagecontent) && !empty($webpagecontent)) echo $webpagecontent['PageName']; ?></h1>
		</section>
		<div class="row">
			<div class="col-xs-12">				 
				<div class="clear box box-primary">
					<div class="col-xs-12 static_content" style="min-height:250px;">
					<?php if(isset($webpagecontent) && !empty($webpagecontent)) echo $webpagecontent['Content']; ?>
					</div>
				</div>					
			</div>	
		</div>
	</div>
	</div>
	<?php footerLogin(); 	commonFooter();  ?>
</html>