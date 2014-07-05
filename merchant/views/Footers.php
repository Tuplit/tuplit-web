<?php
	require_once('includes/CommonIncludes.php');
	if(isset($_GET['Page']) && !empty($_GET['Page'])) {
		$url					=	WEB_SERVICE.'v1/contents/webpagecontent/'.$_GET['Page'];
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
		<?php 
			top_header(); 
			if(isset($webpagecontent) && !empty($webpagecontent)) {
				//echo "<pre>"; echo print_r($webpagecontent); echo "</pre>";
				echo $webpagecontent['Content'];
			}
			footerLogin(); 
			commonFooter(); 
		?>
</html>