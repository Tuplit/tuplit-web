<?php 
require_once('includes/CommonIncludes.php');
admin_login_check();
require_once('controllers/ContentController.php');
$contentObj   =   new ContentController();
$value  	  = 1;
$contentDetail		=	$contentObj->selectContentDetail($value);
	if(isset($contentDetail) && is_array($contentDetail)){
		foreach($contentDetail as $key=>$value){
			$ContentData[$value->PageUrl]	=	unEscapeSpecialCharacters($value->Content);
			$hidden[$value->PageUrl]		=   $value->id."#".$value->PageName."#".$value->PageUrl;
		}
	}
	//print_r($ContentData);
/*if(isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'Save') {
	$_POST     		= 	unEscapeSpecialCharacters($_POST);
    $_POST     		= 	escapeSpecialCharacters($_POST);
	//echo "<pre>"; echo print_r($_POST); echo "</pre>";die();	
	//Edit Content
	$contentObj->updateContent($_POST);	
	die();
}*/
//echo '<pre>';print_r($contentList);echo '</pre>';
commonHead(); 
?>
<body class="skin-blue">
<?php top_header(); ?>
	<!-- Content Header (Page header) -->
	<section class="content-header">
		<div class="row">
			<h1 class="col-md-12 col-lg-6">Manage Content</h1>
		</div>
	</section>
	<section class="content">
		<div class="row">
		<!-- left column -->
			<div class="col-md-12 cms-page"><!--  col-lg-6 -->
				<div class="box box-primary">
					<h2 class="tabsection" id="legal">Legal Content Management</h2>
					<div id="legal_block" style="display:none" class="row"> 
						<div class="col-xs-12" id="tabs">		
						
							<div class="table-responsive clear">
								<table class="table  table-bordered">
									<tr>
										<td class="active"><a  title="" onclick="showTag(this,'1')" class="listsection" id="TermsofService"  >Terms of Service</a></td>
										<td class=""><a  title="" onclick="showTag(this,'2')" class="listsection" id="PrivacyPolicy" >Privacy Policy</a></td>
										<td class=""><a  title="" onclick="showTag(this,'3')" class="listsection" id="CookiePolicy"  >Cookie Policy</a></td>
										<td class=""><a  title="" onclick="showTag(this,'4')" class="listsection" id="MerchantAgreement">Merchant Agreement</a></td>
										<td class=""><a  title="" onclick="showTag(this,'5')" class="listsection" id="UserAgreement">User Agreement</a></td>
									</tr>
								</table>
							</div>
							<div id="TermsofService_div" class="tabdata col-sm-12 clear" style="display:block">
								<textarea  class="form-control TermsofService fancy " name="content" id="content_1"><?php if(!empty($ContentData['terms_of_use'])) echo $ContentData['terms_of_use']; else if(!empty($ContentData['terms_of_service'])) echo $ContentData['terms_of_service']; ?></textarea>								
							</div>
							<input type='hidden' name='content_1_id' id="content_1_id" value="<?php if(!empty($hidden['terms_of_use'])) echo $hidden['terms_of_use']; else if(!empty($hidden['terms_of_service'])) echo $hidden['terms_of_service'];?>">
							<div id="content_1_hidden" style="display:none;">
								<?php if(!empty($ContentData['terms_of_use'])) echo $ContentData['terms_of_use']; else if(!empty($ContentData['terms_of_service'])) echo $ContentData['terms_of_service']; ?>
							</div>
							<div id="PrivacyPolicy_div" class="tabdata col-sm-12 clear" style="display:none">
								<textarea  class="form-control PrivacyPolicy" name="content" id="content_2"><?php if(!empty($ContentData['privacy_policy'])) echo $ContentData['privacy_policy'];?></textarea>								
							</div>
							<input type='hidden' name='content_2_id' id="content_2_id" value="<?php if(!empty($hidden['privacy_policy'])) echo $hidden['privacy_policy'];?>">
							<div id="content_2_hidden" style="display:none;">
								<?php if(!empty($ContentData['privacy_policy'])) echo $ContentData['privacy_policy'];?>
							</div>
							<div id="CookiePolicy_div" class="tabdata col-sm-12 clear" style="display:none">
								<textarea  class="form-control CookiePolicy" name="content" id="content_3"><?php if(!empty($ContentData['cookie_policy'])) echo $ContentData['cookie_policy'];?></textarea>								
							</div>
							<input type='hidden' name='content_3_id' id="content_3_id" value="<?php  if(!empty($hidden['cookie_policy'])) echo $hidden['cookie_policy'];?>">
							<div id="content_3_hidden" style="display:none;">
								<?php if(!empty($ContentData['cookie_policy'])) echo $ContentData['cookie_policy'];?>
							</div>
							<div id="MerchantAgreement_div" class="tabdata col-sm-12 clear" style="display:none">
								<textarea  class="form-control MerchantAgreement" name="content" id="content_4"><?php if(!empty($ContentData['merchant_agreement'])) echo $ContentData['merchant_agreement'];?></textarea>								
							</div>
							<input type='hidden' name='content_4_id' id="content_4_id" value="<?php  if(!empty($hidden['merchant_agreement'])) echo $hidden['merchant_agreement'];?>">
							<div id="content_4_hidden" style="display:none;">
									<?php if(!empty($ContentData['merchant_agreement'])) echo $ContentData['merchant_agreement'];?>
							</div>
							<div id="UserAgreement_div" class="tabdata col-sm-12 clear" style="display:none">
								<textarea  class="form-control " name="content" id="content_5"><?php if(!empty($ContentData['user_agreement'])) echo $ContentData['user_agreement'];?></textarea>									
							</div>
							<input type='hidden' name='content_5_id' id="content_5_id" value='<?php  if(!empty($hidden['user_agreement'])) echo $hidden['user_agreement'];?>' />
							<div id="content_5_hidden" style="display:none;">
									<?php if(!empty($ContentData['user_agreement'])) echo $ContentData['user_agreement'];?>
							</div>
							<div class="legal-cms clear">
								<div class="col-xs-12 col-sm-6 talign-left">
									<a class="trans-button fancybox" title="PREVIEW" href="#fancy-div" onclick="fancybox(this.id)" id="content">PREVIEW </a>
								</div>
								<div align="right" class="col-xs-12 col-sm-6 talign-right">
									<a class="add-button mR-button" title="SAVE" id="save_content" onclick="save(this.id);">SAVE </a>
									<a class="cancel-button" title="CANCEL" id="content_cancel" onclick="cancel(this.id)">CANCEL</a>
								</div>
								<div class="clear"></div>
							</div>
							
						</div>
					</div>
				</div>
				<div class="box box-primary ">
					<h2  class="tabsection" id="about_cms" >About</h2>
					<div class="row" id='about_cms_block' style="display:none;">
						<div  class='col-sm-12'>	
							<div  class='col-sm-12' id="fancy12">
								<textarea  class="form-control" name="content" id="text-about"><?php if(!empty($ContentData['about_tuplit'])) echo $ContentData['about_tuplit'];?></textarea>								
								</div>
								<input type='hidden' name='about_id' id="about_id" value="<?php  if(!empty($hidden['about_tuplit'])) echo $hidden['about_tuplit'];?>">
								<div id="about_hidden" style="display:none;">
									<?php  if(!empty($ContentData['about_tuplit'])) echo $ContentData['about_tuplit'];?>
								</div>
							<div class="legal-cms clear" id="about_container">
								<div class="col-xs-12 col-sm-6 talign-left">
									<a class="trans-button fancybox" id="about" title="PREVIEW" href="#fancy-div" onclick="fancybox(this.id)">PREVIEW </a>
								</div>
								<div align="right" class="col-xs-12 col-sm-6 talign-right">
									<a class="add-button mR-button" title="SAVE"  id="about-save" onclick="save(this.id)">SAVE </a>
									<a class="cancel-button" title="CANCEL" id="about_cancel" onclick="cancel(this.id)">CANCEL</a>
								</div>
								<div class="clear"></div>
							</div>	
						</div>
					</div>
				</div>
				<div class="box box-primary">
					<h2  class="tabsection" id="custom_cms" >Custom Content</h2>
					<div id='custom_cms_block' class="row" style="display:none;">
						<div  class='col-sm-12'>
							<div  class='col-sm-12'>
								<textarea  class="form-control" name="content" id="text-custom"><?php if(!empty($ContentData['custom_content'])) echo $ContentData['custom_content'];?></textarea>
								
							</div>
							<input type='hidden' name='custom_id' id="custom_id" value="<?php  if(!empty($hidden['custom_content'])) echo $hidden['custom_content'];?>">
							<div id="custom_hidden" style="display:none;">
								<?php  if(!empty($ContentData['custom_content'])) echo $ContentData['custom_content'];?>
							</div>							
							<div class="legal-cms clear">
								<div class="col-xs-12 col-sm-6 talign-left">
									<a class="trans-button fancybox" id="custom" title="PREVIEW" href="#fancy-div" onclick="fancybox(this.id)">PREVIEW </a>
								</div>
								<div  class="col-xs-12 col-sm-6 talign-right">
									<a class="add-button mR-button" title="SAVE" id="custom-save" onclick="save(this.id)">SAVE </a>
									<a class="cancel-button" title="CANCEL" id="custom_cancel" onclick="cancel(this.id)">CANCEL</a>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="box box-primary">
					<h2 class="tabsection" id="landing_cms">Landing Page</h2>
					<div id="landing_cms_block" style="display:none;" class="row">
						<div  class='col-sm-12'>
							<div  class='col-sm-12'>
								<textarea  class="form-control " name="content" id="text-landing"><?php if(!empty($ContentData['landing_page'])) echo $ContentData['landing_page'];?></textarea>							
								
							</div>	
							<input type='hidden' name='landing_id' id="landing_id" value="<?php  if(!empty($hidden['landing_page'])) echo $hidden['landing_page'];?>">
							<div id="landing_hidden" style="display:none;">
								<?php  if(!empty($ContentData['landing_page'])) echo $ContentData['landing_page'];?>
							</div>
							<div class="legal-cms clear">
								<div class="col-xs-12 col-sm-6 talign-left">
									<a class="trans-button fancybox" id="landing" title="PREVIEW" href="#fancy-div" onclick="fancybox(this.id)">PREVIEW </a>
								</div>
								<div  class="col-xs-12 col-sm-6 talign-right">
									<a class="add-button mR-button" title="SAVE"  id="landing-save" onclick="save(this.id)">SAVE </a>
									<a class="cancel-button" title="CANCEL" id="landing_cancel" onclick="cancel(this.id)">CANCEL</a>
					  			</div>
								<div class="clear"></div>
							</div>
						</div>							
					</div>
				</div>
				<div class="box box-primary">
					<h2 class="tabsection" id="help_cms">Help</h2>
					<div id="help_cms_block" style="display:none;" class="row">
						<div  class='col-sm-12'>
							<div  class='col-sm-12'>
								<textarea  class="form-control " name="content" id="text-help"><?php if(!empty($ContentData['help'])) echo $ContentData['help'];?></textarea>
								
							</div>	
							<input type='hidden' name='help_id' id="help_id" value="<?php  if(!empty($hidden['help'])) echo $hidden['help'];?>">
							<div id="help_hidden" style="display:none;">
								<?php  if(!empty($ContentData['help'])) echo $ContentData['help'];?>
							</div>
							<div class="legal-cms clear">
								<div class="col-xs-12 col-sm-6 talign-left">
									<a class="trans-button fancybox" id="help" title="PREVIEW" href="#fancy-div" onclick="fancybox(this.id)">PREVIEW </a>
								</div>
								<div  class="col-xs-12 col-sm-6 talign-right">
									<a class="add-button mR-button" title="SAVE"  id="help-save" onclick="save(this.id)">SAVE </a>
									<a class="cancel-button" title="CANCEL" id="help_cancel" onclick="cancel(this.id)">CANCEL</a>
								</div>
								<div class="clear"></div>
							</div>
						</div>	
					</div>
				</div>
				<div class="box box-primary">
					<h2 class="tabsection" id="contact_cms">Contact Us</h2>
					<div id="contact_cms_block" style="display:none;" class="row">
						<div  class='col-sm-12'>
							<div  class='col-sm-12'>
								<textarea  class="form-control fanc " name="content" id="text-contact" value=""><?php if(!empty($ContentData['contact_us'])) echo $ContentData['contact_us'];?></textarea>								
				                
							</div>	
							<div id="contact_hidden" style="display:none;">
								<?php  if(!empty($ContentData['contact_us'])) echo $ContentData['contact_us'];?>
							</div>
							<input type='hidden' name='contact_id' id="contact_id" value='<?php   if(!empty($hidden['contact_us'])) echo $hidden['contact_us'];?>'>
								
							<div class="legal-cms clear">
								<div class="col-xs-12 col-sm-6  talign-left"><a class="trans-button fancybox" id="contact" title="PREVIEW" href="#fancy-div"  onclick="fancybox(this.id)">PREVIEW </a></div>
								<div  class="col-xs-12 col-sm-6 talign-right">
									<a class="add-button mR-button" title="SAVE"  id="contact-save" onclick="save(this.id)">SAVE </a>
									<a class="cancel-button" title="CANCEL" id="contact_cancel" onclick="cancel(this.id)">CANCEL</a>
								</div>
								<div class="clear"></div>	
							</div>
						</div>	
					</div>
				</div><!-- /.box -->
			</div>
			<div id="fancy-div" class="only-fcycontent" style="display:none;list-style:desc;"><!--width:700px;height:450px;background-color:#fff;-->
				<h4 id="title" class="title"></h4>
				<div id="preview" class="preview"></div>				
			</div>
		</div><!-- /.row -->
	</section><!-- /.content -->
<?php commonFooter(); ?>
</html>

<script type="text/javascript"> 

tinymce.init({
	height 	: "300",
	//width	: "500",
	mode 	: "textareas", statusbar: false,
	/* for font-family change
	setup : function(ed) {
        ed.on('NodeChange',function(ed) {
            tinymce.execCommand("fontName", false, "Arial");
            tinymce.execCommand("fontSize", false, "2");
        });
    },
	font_formats: "Courier New=courier new,courier,monospace;AkrutiKndPadmini=Akpdmi-n",*/
	plugins	: [
				"advlist autolink lists link image charmap print preview anchor",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste textcolor"
				],
	toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code | forecolor backcolor",
	font_formats: "Arial=arial,helvetica,sans-serif;Courier New=courier new,courier,monospace;AkrutiKndPadmini=Akpdmi-n"
});
function fancybox(id){
	$.fancybox({
		'fitToView'	: false,
		'autoSize'	: false,
		'type'		: 'iframe',
		'beforeLoad': function(){
			if(id.search('content')=='0'){
				//alert(id);
				content_id	= $(".fancy").attr('id')
				var content	= tinymce.get(content_id).getContent({format : 'raw'});
				var title   = $("td.active a").text();				
			}else{
				var content = tinymce.get('text-'+id).getContent();
				var title   = $("#"+id+"_cms").text();
			}
			$(".title").html(title);			
			$(".preview").html(content);
			
		}
	});
	
	

}


function cancel(id_val){
	id_attr = id_val.substr(0,id_val.indexOf('_'));
	if(id_attr.search('content')=='0'){
		content_id	= $(".fancy").attr('id')
		//alert(content_id);
		value = $("#"+content_id+"_hidden").html();	
		tinymce.get(content_id).setContent(value)
	}else{
		value = $("#"+id_attr+"_hidden").html();
		tinymce.get('text-'+id_attr).setContent(value)
	}	
}

	


</script>
