if ("https:" == location.protocol)
    var protocolPath  = 'https://';
else
    var protocolPath  = 'http://';

if (window.location.hostname == '172.21.4.104') {
    var  path       = protocolPath + '172.21.4.104/tuplit/merchant/';
    var actionPath	= protocolPath + '172.21.4.104/tuplit/merchant/';
}
else {
	var  path = protocolPath+''+window.location.hostname+'/merchant/';
    var actionPath	= protocolPath+''+window.location.hostname+'/merchant/';

}


function addRow(ref,id_val,ref_id,type)
{
	if(type == 1){
	 var slide_val	=	"Slider_Image_";
	 var slide_name	=	'Slider';
	}
	else {
	 var slide_val	=	"Tutorial_Image_";
	 var slide_name	=	'Tutorial';
	}
	var field_name 	= "field_name_clone_";
	var sample_data = "sample_data_clone_";
	var explanation	= "explanation_clone_";
	var count 		= 0;
	var node 		= $(ref).closest("#"+ref_id);
	var empty		=	0;
	$(ref).closest("table").find("tr").each(function() {
		var text 	=	$(this).find("input").eq(0).val();
		if(text == "")
			empty = 1;
	});
	if(empty == 0)
	{
		if(type == 1){
			var count		=	$("#home_center div").length;
		}
		else
			var count		=	$("#tutorial_center div").length;
		var new_count	 = Math.round((count/6)) + 1;
		var length		= node.attr("clone");
		var tabindex	= $("#method").attr("tabindex");
		var clonedRow 	= node.clone(true);
		clonedRow.insertAfter(node);
		if(count >= 0) 	{
			count = (+length)+1;
			
			clonedRow.attr("clone",count);
			clonedRow.find("input").val("");
			clonedRow.find("select").val(0);
			clonedRow.find("#"+slide_name+"_image_name").html(slide_name+ " Image "+new_count);
			clonedRow.find(".form-control").attr('id','order_'+new_count);
			clonedRow.find(".form-control").val(new_count);
			clonedRow.find(".Hidden4").val(new_count);
			clonedRow.find(".image_disp").attr('id',slide_val+new_count+'_img');
			clonedRow.find(".image_disp").attr('style','display:none');
			clonedRow.find(".file_input").attr('id',slide_val+new_count);
			clonedRow.find(".file_input").attr('name',slide_val+new_count);
			clonedRow.find(".hidd_1").attr('id','empty_'+slide_val+new_count);
			clonedRow.find(".hidd_1").attr('name','empty_'+slide_val+new_count);
			clonedRow.find(".hidd_2").attr('id','name_'+slide_val+new_count);
			clonedRow.find(".hidd_2").attr('name','name_'+slide_val+new_count);
			clonedRow.find(".hidd_3").attr('id',slide_val+new_count);
			clonedRow.find(".hidd_3").attr('name',slide_val+new_count);
			clonedRow.find(".Hidden4").attr('id','hidden_home_count_'+new_count);
			settabindex(tabindex);
		}
	}
	else
		alert("Please fill the row to add new row");
}

function delRow(ref,id_val,ref_id,type)
{	
	if(type == 1){
		var count		=	$("#slider_home div").length;
	}
	else
		var count		=	$("#tutorial_center div").length;
	var new_count	 = Math.round((count/7)) + 1;
	if(new_count > 2)	
	{
		var bool	=	confirm("Are you sure to delete ?");
		if(bool)
			//$("#slider_home_"+new_val).closest("div").remove();
			$(ref).closest("#"+ref_id).remove();
	}
	else
		alert("Atleast one row is required");
}
/*function delRow(ref)
{	
	var count	=	$("#inputParam tr").length;
	if(count > 2)	
	{
		var bool	=	confirm("Are you sure to delete ?");
		if(bool)
			$(ref).closest("tr").remove();
	}
	else
		alert("Atleast one row is required");
}*/
$(document).ready(function() {
	var tabindex = $("#method").attr("tabindex");
	settabindex(+tabindex+1);
	showHideInputParam();
	//For Method Change Event
	$("#method").change(function() {
		showHideInputParam();
	});
});

function showHideInputParam() {
	var value = $("#method").val();
		if(value == "GET" || value == "POST") {
			$(".inputParamDefault").hide();
			$(".inputParamMultiple").show();
		}
		else {
			$(".inputParamDefault").show();
			$(".inputParamMultiple").hide();
		}
}
function settabindex(index) {
	$("#inputParam").find("tr").not(":eq(0)").each(function() {
		$(this).find("input").eq(0).attr("tabindex",index++);
		$(this).find("input").eq(1).attr("tabindex",index++);
		$(this).find("select").eq(0).attr("tabindex",index++);
		$(this).find("textarea").eq(0).attr("tabindex",index++);
		$("#output_param").attr("tabindex",index++);
		$("#Save,#Add").attr("tabindex",index++);
		$("#Back").attr("tabindex",index++);
		
	});
}

/*function ajaxAdminFileUploadProcess(process_pram)
{
	var loadingIMG  =  '<span class="photo_load load_upimg"><i class="fa fa-circle-o-notch fa-spin fa-lg text-olive "></i></span>';	
	$(loadingIMG).insertAfter($("#"+process_pram+"_img"));
    $("#"+process_pram+"_img").hide();	
	var hiddenVal = $("#empty_"+process_pram).val();
	if(process_pram == 'myStore') {
		
		//total image uploaded
		oldtotalImage	=	parseInt($('#oldtotalImage').val());
		
		//new images
		imagecount		=	$('#imagecount').val();
		if(imagecount != '') {
			imagecountarray	=	imagecount.split(',');
			newimagelength	=	imagecountarray.length;	
		} else {
			newimagelength	=	0;
		}
		
		DeleteIds		=	$('#DeleteIds').val();
		if(DeleteIds != '') {
			DeleteIdsarray	=	DeleteIds.split(',');
			DeleteIdslength	=	DeleteIdsarray.length;
			oldtotalImage	=	oldtotalImage - DeleteIdslength;
		}
		
		totalimagesin	=	oldtotalImage + newimagelength + 1;
		orgtotalImage	=	parseInt($('#totalImage').val());
		totalImage		=	orgtotalImage + 1;
		url 			= 	actionPath+'models/DoAjaxAdminFileUpload.php?imagetot='+totalImage;
		
		if(imagecount != '') 
			$('#imagecount').val(imagecount+','+totalImage)
		else
			$('#imagecount').val(totalImage)
	}
	else
		url = actionPath+'models/DoAjaxAdminFileUpload.php';
    $.ajaxFileUpload
    ({
        url:url,
        secureuri:false,
        fileElementId:process_pram,
        dataType: 'json',
        data:{			
            process:process_pram
        },
		success: function (data)
        {
           	if(typeof(data.error) != 'undefined')
            {
			    if(data.error != '')
                {
                    alert(data.error);
					if($('#'+process_pram+'_upload').val() == '')
						$("#empty_"+process_pram).val(hiddenVal);
					
					 $("#empty_"+process_pram).val(hiddenVal);
					
                }else
                {
					if(hiddenVal=='') {
						$("#empty_"+process_pram).val(1);
					}
					var result	=	data.msg.split("####");					
					if(process_pram == 'cover_photo'){
						var img	='<img  src="'+path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200"  />\n\
                                    <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					else if(process_pram == 'merchant_photo'){			
						var img	='<img  src="'+path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200"  />\n\
                                    <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />'; 	
					}
					else if(process_pram == 'icon_photo'){
						var img	='<img  src="'+path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="75" height="75" />\n\
                                    <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					else if(process_pram == 'myStore'){
						var img	='<img  src="'+path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="75" height="75" />\n\
                                    <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
									
						// Push the data URI into an array		
						$('#totalImage').val(totalImage);
						imgcount	=	totalimagesin + 1;					
						
						ImageExt	=	$('#ImageExt').val();
						if(ImageExt != '') 
							$('#ImageExt').val(ImageExt+','+result[1]);
						else
							$('#ImageExt').val(result[1]);
						
						totalImageNow	=	parseInt($('#totalImageNow').val());
						totalImageNow	=	totalImageNow + 1;
						$('#totalImageNow').val(totalImageNow)
						
						imgcontent	=	'<div class="col-sm-6 col-xs-12 form-group" id="temp'+totalImage+'"><div class="col-xs-1 no-padding" id="imgStore_'+totalImage+'">'+totalimagesin+'.</div><div class="col-xs-11 no-padding" align="center">';
						imgcontent	+=	'<div  class="photo_gray_bg"><img style="vertical-align:top" class="" width="330" src="'+path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" height="160" alt=""></div></div>';
						if(totalimagesin == 10) {
							imgcontent	+=	'<div class="col-xs-12">&nbsp;</div><div class="col-xs-2 col-md-1 clear">&nbsp;</div>';
							imgcontent	+=	'<div class="col-xs-10 col-md-11" align="center"><input type="button" name="Delete" id="Delete" class="box-center btn btn-danger  col-xs-10" value="Delete" title="Delete" onclick="return deleteBefore('+totalImage+',\''+ result[1]+'\');"></div></div>';
							$(imgcontent).insertBefore('#temp0');
							$('#temp0').hide();
						} else {
							imgcontent	+=	'<div class="col-xs-12">&nbsp;</div><div class="col-xs-2 col-md-1 clear">&nbsp;</div>';
							imgcontent	+=	'<div class="col-xs-10 col-md-11" align="center"><input type="button" name="Delete" id="Delete" class="box-center btn btn-danger  col-xs-10" value="Delete" title="Delete" onclick="return deleteBefore('+totalImage+',\''+ result[1]+'\');"></div></div>';
							$(imgcontent).insertBefore('#temp0');	
							$('#imgcount').html(imgcount+'.');
							$('#imgdrag').attr('src',path+'webresources/images/no_photo_my_store.png');
						}
					}
					else{
						var img	='<img  src="'+path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="75" height="75" />\n\
                                    <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					//alert(img)
					if(process_pram != 'myStore')
						$("#"+process_pram+"_img").html(img);
	                $("#no_"+process_pram).remove();
				}
                $(".photo_load").remove();
				if(process_pram != 'myStore')
					$("#"+process_pram+"_img").show();
            }
        },
        error: function (data, status, e)
        {
           alert(e);
        }		
    });
    return false;
}*/
function deleteAll(del_name){
	var flag       = 0;
	var hash_flag  = 0;
	$("input[name='checkdelete[]']").each(function(){		
		if($(this).attr('checked')){
			flag = 1;	
			if(del_name == 'hashtag' || del_name == 'Users'){
				if($(this).attr('hashCount') > 0)				
					hash_flag = 1;
			}
		}
	});
	if(flag == 0){
		alert('Select atleast a single record');
		return false;
	}	
	if(hash_flag == 1){
		if(del_name == 'hashtag')
			alert('Sorry! you can not delete this selected hash tags since it is used by some other user\'s.');
		if(del_name == 'Users')
			alert('Sorry! you can not delete this selected users');
		return false;
	}
	else if(hash_flag == 0 && flag == 1 ){
		if(confirm('Are you sure to delete?'))
			return true;
		else
			return false;
	}
}
function setOrdering(ordering_value,company_id){
		//alert('order--------'+ordering_value+'-------company--------'+company_id)
		$.ajax({
	        type: "GET",
	        url: actionPath+"models/AjaxAction.php",
	        data: 'action=SET_ORDERING&orderValue='+ordering_value+'&companyId='+company_id,
	        success: function (result){
				//alert(result);
	        }			
	    });
}
function setOrderingWebService(ordering_value,service_id){
		$.ajax({
	        type: "GET",
	        url: actionPath+"models/AjaxAction.php",
	        data: 'action=SET_ORDERING_WEBSERVICE&orderValue='+ordering_value+'&serviceId='+service_id,
	        success: function (result){
				//alert("--"+result);
				if(result == 1){
					alert('This Order already assigned for some other Service');
					return false;
				}
	        }			
	    });
}
sendNotification = function(frmname)
{
	flag=0;
	var message = $('#message').val();
	if(message == ''){
		alert('Enter the message');
		return false;
	}
	else if(frmname.user_id.length>1)
	{
		for (var i = 0; i < frmname.user_id.length; i++)
		{
		  if(frmname.user_id[i].selected){
				flag = 1;
				break;
		  }
		}
	}
	else if (frmname.user_id.selected) {
		flag = 1;
	}
	if(flag==0) {
		alert('Please select atleast a user to send notification');
		return false;
	}
	if(flag==1){
		$('#message_hidden').val(message);
		if(confirm('Are you sure to send notification?')) {
			frmname.submit();
			// parent.$.colorbox.close();
		}
			  
	}
}

function deleteRow(id_val,type)
{	
		if(type == 1)
		 var remove_id	=	"exist_id_home_";
		else
		 var remove_id	=	"exist_id_tutorial_";
		var bool	=	confirm("Are you sure to delete ?");
		if(bool){
			$.ajax({
		        type: "POST",
		        url: actionPath+"models/AjaxAction.php",
		        data: 'action=DELETE_SLIDER&idValue='+id_val,
		        success: function (result){
					if(result = 1){
						$("#"+remove_id+id_val).animate({
						height: 'toggle', opacity: 'toggle'
					}, "slow");
						$("#center div").length  = $("#center div").length - 7;
					}
					//alert(result);
		        }			
		    });
		}
	
}
function setOrderingSlider(ordering_value,id_val,type){
		$.ajax({
	        type: "GET",
	        url: actionPath+"models/AjaxAction.php",
	        data: 'action=SET_ORDERING_SLIDER&orderValue='+ordering_value+'&SliderId='+id_val+'&slide_type='+type,
	        success: function (result){
				//alert("--"+result);
				if(result == 1){
					alert('This Order already assigned for some other Slider');
					return false;
				}
	        }			
	    });
}

function openImage(img_id){
	$(".popup").show();
	var content = '<img width="250" height="250" align="top" class="" src="'+img_id+'" >';
	$(".popup_content").html(content);
	popupProperties();
}
function popupProperties(){
	var id = '.popup_content';
	var winH = $(window).height();
   	var winW = $(window).width();
	$(id).css({
	  //  'position': 'fixed',
	    'top': parseInt((winH / 2) - ($(id).height() / 2), 10),
	    'left': parseInt((winW / 2) - ($(id).width() / 2), 10),
	});
}
function openIframe(src){
	$(".popup").show();
	$(".popup_content").html($("<iframe id='listing' />").attr("src", src).attr("allowfullscreen", true).attr("allowTransparency", true));
	$("#listing").css({
		'height':'100%',
		'width':'100%',
	});
	popupProperties();
}
$('.popup .popup_close').click(function(){
  	disablePopup();
});
$(document).keydown(function(e) {
    // ESCAPE key pressed
    if (e.keyCode == 27) {
       disablePopup();
    }
});
function loading() {
 	$("div.loader").show();
}
function closeloading() {
     $("div.loader").fadeOut('normal');
}
$("div#backgroundPopup").click(function() {
	$(".popup").hide(); // function close pop up
});

function disablePopup(){
	$(".popup").hide();
}

function showCategory(id_val) {
	$('.cats .error').hide();
	if(id_val != ''){
		var res = id_val.split(",");
		for(var i = 0; i < res.length; i++) {	
		var sel = res[i];
		if(sel != '') {
			$("#cat_id_"+sel).css('display','inline-block');
			$("#Category").children("option[value =" +sel+ "]").attr('disabled','true');
			var sel_val = $("#categorySelected").val();
			if(sel_val == '') {
				sel_val = sel;
				$("#categorySelected").val(sel_val);
			} else {
				sel_val = sel_val+','+sel;
				$("#categorySelected").val(sel_val);
			}
		}
		$("#Category").children("option[value ='']").attr('selected', true);
		}		
	}
	else{
		var sel = $( "#Category option:selected" ).val();
		//$("#cat_id_"+sel).show();
		if(sel != '') {
			$("#cat_id_"+sel).css('display','inline-block');
			$("#Category").children("option[value =" +sel+ "]").attr('disabled','true');
			var sel_val = $("#categorySelected").val();
			if(sel_val == '') {
				sel_val = sel;
				$("#categorySelected").val(sel_val);
			} else {
				sel_val = sel_val+','+sel;
				$("#categorySelected").val(sel_val);
			}
		}
		$("#Category").children("option[value ='']").attr('selected', true);
	}
	$('option[disabled="disabled"]').each(function () {
         $(this).attr('style', 'color:#cccccc;font-style:italic');
	});
}
function removeCategory(id,image) {
	if(confirm('Are you sure to Remove?')) { 		
		$("#cat_id_"+id).css('display','none');
		$("#Category").children("option[value =" +id+ "]").removeAttr('disabled');
		$("#Category").children("option[value =" +id+ "]").attr('style', 'color:#0000;background-image:url('+image+');');
		/*$("#Category").children("option[value =" +id+ "]").attr('disabled',false);
		$("#Category").children("option[value =" +id+ "]").show();*/
		var sel_val = $("#categorySelected").val();		
		var separated_val = sel_val.split(',');	
		var newValue = '';		
		for(var i = 0; i < separated_val.length; i++) {			
			if(id != separated_val[i])
				newValue += separated_val[i]+',';							
		}	
		newValue = newValue.substring(0, newValue.length - 1);
		$("#categorySelected").val(newValue);
	}		
	else
		return false;
}

function calculateDiscountPrice(type) {
	var price = $('#Price').val();
	var discount_val = parseInt($('#Discount_val').val());
	if(type != '0') {
		if(discount_val == 1) {
			$('#Discount_val').val('0');
			discount_val	=	0;
		}
		else if(discount_val == 0) {
			$('#Discount_val').val('1');
			discount_val	=	1;
		}			
	}
	var discount_tier = $('#discounttier').html();	
	discount_tier = discount_tier.substring(0, discount_tier.length - 1);	
	if(discount_val == 1) {		
		var discount_price = parseFloat(price - ((price/100)*discount_tier));
		//alert(price+' '+discount_price+'  '+discount_tier)		
		if(discount_price == '')
			discount_price	=	'0.00';
		$('#DiscountPrice').val(discount_price);
		$('#discount_price').html('&pound;&nbsp;'+discount_price);		
	}
	else {
		
		if(price == '')
			price	=	'0.00';
		$('#DiscountPrice').val(price);
		$('#discount_price').html('&pound;&nbsp;'+price);	
	}	 
}

/*function hideShowPrice() {
	var type = $('#ItemType').val();
	$('#DiscountPrice').val('');
	$("#DiscountTier").children("option[value ='']").attr('selected', true);
	$("#Special_product_1").children("option[value ='']").attr('selected', true);
	$('#Product_Quantity_1').val('');
	$('#Product_Price_1').val('');
	$('#Product_Total_1').val('');
	$('#SpecialPrice').val('');
	$('#dup_SpecialPrice').val('');
	$('#SpecialDiscountPrice').val('');
	$('#Price').val('');
	var total = $('#totalrow').val();
	if(total > 1) {
		for (i=2;i<=total;i++) {
			$('#'+i).html('');
		}
		$('#totalrow').val('1');
	}
	if(type == 2) {
		$('#PriceShow').slideDown();
		$('#Deal').slideDown();
		$('#Deal1').slideDown();
		$('#Special').slideUp();
		$("#Special_product_1").children("option[value ='']").attr('selected', true);
	}
	else if(type == 3) {
		$('#Special').slideDown();
		$('#PriceShow').slideUp();
		$('#Deal').slideUp();
		$('#Deal1').slideUp();			
	}
	else {
		$('#PriceShow').slideDown();
		$('#Deal').slideUp();
		$('#Deal1').slideUp();
		$('#Special').slideUp();		
	}
}

function getPrice(arg) {
	var id = arg.getAttribute('id');
	id = id.substring(id.length - 1, id.length);
	var selected_val = $('#Special_product_'+id).val();
	if(selected_val != '') {
		var price = $('#price_'+selected_val).val();			
		$('#Product_Price_'+id).val(price);
		$('#Product_data_'+id).val('1');
		var quantity = $('#Product_Quantity_'+id).val();
		var price = $('#Product_Price_'+id).val();
		if(quantity != '' && price!='') {
			var total = quantity * price;
			$('#Product_Total_'+id).val(total);			
			totalrow = $('#totalrow').val();
			totalamount = 0;
			for(i=1;i<=totalrow;i++) {
				if ($('#Product_Total_'+i).length > 0) { 
					productval =  parseFloat($('#Product_Total_'+i).val());
				}
				else
					productval =  '';
				if(productval != '')
					totalamount = totalamount + productval;					
			}
			$('#SpecialPrice').val(totalamount);
			$('#dup_SpecialPrice').val(totalamount);
			$('#SpecialDiscountPrice').val(totalamount);
		}		
	} 
	else {
		$('#Product_Price_'+id).val('');
		$('#Product_data_'+id).val('');
	}
}

function calculateSpecialPrice(arg) {
	var id = arg.getAttribute('id');
	id = id.substring(id.length - 1, id.length);
	if(id != '') {
		var quantity = $('#Product_Quantity_'+id).val();
		var price = $('#Product_Price_'+id).val();
		var selected_val = $('#Special_product_'+id).val();
		var total = quantity * price;
		if(quantity != '' && selected_val != '') {
			$('#Product_Total_'+id).val(total);	
			totalrow = $('#totalrow').val();
			totalamount = 0;
			for(i=1;i<=totalrow;i++) {
				if ($('#Product_Total_'+i).length > 0) { 
					productval =  parseFloat($('#Product_Total_'+i).val());
				}
				else
					productval =  '';
				if(productval != '')
					totalamount = totalamount + productval;	
			}
			$('#SpecialPrice').val(totalamount);
			$('#dup_SpecialPrice').val(totalamount);
			$('#SpecialDiscountPrice').val(totalamount);
		} 		
	}
	else
		alert('select product');
}*/

function selectProduct(product_value) {
	var form_name 		= document.getElementById('Products_List');
	var discount_tier 	= document.getElementById('DiscountTier');
	for (i=0;i<discount_tier.options.length;i++) {
		if(discount_tier.options[i].value == '3')
			discount_tier.options[i].selected = true;
	}
	if(product_value == 'all'){		
		for (i=0;i<form_name.options.length;i++) {
			if(form_name.options[i].value != 'all'){
				form_name.options[i].selected = true;
			}
		}
	}
}

function selectPrice(product_value,type) {
	if(type == 1) {
		var form_name 	 = document.getElementById('Products_List');
		for (i=0;i<form_name.options.length;i++) {
				form_name.options[i].selected = false;
		}
	}
}


function hideAllDays() {
	var check = $('#showdays').val();
		
	if(check == 1) {
		$('.rowHide').fadeIn('slow');
		$('.rowshow').html('Monday ');
		$('#showdays').val('0');
		$('#samehours').addClass('active');
		
	}else {
		$('#showdays').val('1');
		$('#samehours').removeClass('active');
		$('.rowHide').fadeOut('slow');
		$('.rowshow').html('Monday - Sunday');
		for(i=1;i<=6;i++) {
			$('#from1_'+i).val('');
			$('#to1_'+i).val('');
			$('#row_'+i).val('');
			$('#error_'+i).html('');
		}
	}
	
}

/*function setTime(id) {	
	fromhr = $('#fromhours_list'+id).val();
	frommin = $('#fromminute_list'+id).val();
	fromampm = $('#fromampm_list'+id).val();
	tohr = $('#tohours_list'+id).val();
	tomin = $('#tominute_list'+id).val();
	toampm = $('#toampm_list'+id).val();
	if(fromhr == '' && frommin == '' && fromampm == '' && tohr == '' && tomin == '' && toampm == '') {
		$('#row_'+id).val('');
		$("#from1_"+id).val('')
		$("#to1_"+id).val('')
	}
	else {
		$('#row_'+id).val('1');
		from	=	fromhr+':'+frommin+' '+fromampm;
		to		=	tohr+':'+tomin+' '+toampm;
		$("#from1_"+id).val(from)
		$("#to1_"+id).val(to)
	}
}*/

function showAllItems(tag) {
	if($('#link'+tag).html() == 'Show all items'){
		$('.otherItems'+tag).fadeIn('slow');
		$('#link'+tag).html('Hide items');
	}
	else {
		$('.otherItems'+tag).fadeOut('slow');
		$('#link'+tag).html('Show all items');
	}
}

function productCategoryHideShow(id,type) {
	$('#showmessage').slideUp('slow');	
	$('#rowHide'+id).slideToggle('slow');
	if($('#rowHidden'+id).val() == 1){		
		$('#rowHidden'+id).val('0');
		$("#plusMinus"+id).removeClass('fa-caret-down');	
		$("#plusMinus"+id).addClass('fa-caret-up');	 
		$('#hideSep'+id).removeClass('blockdown');
		$('#hideSep'+id).addClass('blockup'); 
		$('#rowHide'+id).removeClass('blockdown');
		$('#rowHide'+id).addClass('blockup'); 
	}
	else {
		$('#rowHidden'+id).val('1');
		$("#plusMinus"+id).removeClass('fa-caret-up');	
		$("#plusMinus"+id).addClass('fa-caret-down');
		$('#hideSep'+id).removeClass('blockup');
		$('#hideSep'+id).addClass('blockdown');		
		$('#rowHide'+id).removeClass('blockup');
		$('#rowHide'+id).addClass('blockdown');
	}
	//rowHide
	if(type == '') {
		var categoryIds	=	$('#categoryIds_val').val();
		if(categoryIds != '') {
			categoryIdsArray	=	[];
			categoryIdsArray	=	categoryIds.split(',');
			categoryIdsArray[categoryIdsArray.length] = '0_1';
			for(i=0;i<categoryIdsArray.length;i++) {
				if(categoryIdsArray[i] != id) {
					cat_id = categoryIdsArray[i];
					$('#rowHide'+cat_id).slideUp('slow');
					$('#rowHidden'+cat_id).val('1');
					$("#plusMinus"+cat_id).removeClass('fa-caret-up');	
					$("#plusMinus"+cat_id).addClass('fa-caret-down');
					$('#hideSep'+cat_id).removeClass('blockup');
					$('#hideSep'+cat_id).addClass('blockdown');		
					$('#rowHide'+cat_id).removeClass('blockup');
					$('#rowHide'+cat_id).addClass('blockdown');
				}
			}
		}
	}
}

function hideShowOrders(id,name,photo,price,disprice) {
	var vat = '';
	$('.clearorder').show();
	$('#showProductPrice').show();
	$('#orderPrint').show();
	
	$('#showmessage').slideUp('slow');
	if(	$('#ProductVAT').val() != ''){
		vat		= parseFloat($('#ProductVAT').val());
	}else{	
		vat 	= 0;
	}
		
	//if(confirm('Are you sure to add this product?')) {
		if(disprice == 0)
			setprice = price;
		else
			setprice = disprice;
		var prodIds = $('#OrderProductIds').val();
		show = 0;
		ordertot = 0;
		if(prodIds == ''){
			prodIds = id;
			$('#OrderProductIds').val(prodIds);
			show = 1;
		}
		else {
			idsArray = prodIds.split(',');		
			if(jQuery.inArray( id, idsArray ) !== -1) {
				if(confirm('Product is already in your cart. Do you need to increment the quantity'))
				{
					addRemoveQuantity(id,1)
				}
				else
					return false;
			}
			else {
				prodIds = prodIds+','+id;
				$('#OrderProductIds').val(prodIds);
				show = 1;
			}				
		}
		
		
		
		if(show == 1) {
			text = '<div id="orderrow'+id+'" class="clear"><div class="order-iname no-padding"><img width="50" height="50" alt="" src="'+photo+'"><div class="new_oreder_name">'+name+'</div></div><div class="dotted-line"><span>&nbsp;</span></div>';

			text = text +'<div align="right" class="order-price"><div align="right" class="col-xs-6 col-sm-6 col-md-6 col-lg-7 no-padding"><input type="text" id="quantity'+id+'" maxlength="3" name="quantity'+id+'" style="width:26px;text-align:center;border:0px solid #fff;" value="1" onkeypress="return isNumberKeyQuantity(event);" onkeyup="return addRemoveQuantity('+id+',3);">&nbsp;&nbsp;<i class="fa fa-plus fa-plus-bgcolor" onclick="return addRemoveQuantity('+id+',1);"></i><i class="fa fa-minus fa-minus-bgcolor" onclick="return addRemoveQuantity('+id+',2);"></i><input type="hidden" id="imagePath'+id+'" name="imagePath'+id+'" value="'+photo+'" /></div><strong>\u00A3<span id="orderPrice'+id+'">'+numberWithCommas(setprice)+'</span></strong><input type="hidden" id="discountprice'+id+'" name="discountprice'+id+'" value="'+setprice+'"/><input type="hidden" id="originalprice'+id+'" name="originalprice'+id+'" value="'+price+'"/><input type="hidden" id="originalTotalprice'+id+'" name="originalTotalprice'+id+'" value="'+setprice+'"/><input type="hidden" id="orderItemName'+id+'" name="orderItemName'+id+'" value="'+name+'"/></div></div>';
			$('#tbl1 tr').last().after(text);
			idsArray = prodIds.split(',');		
			for(i=0;i<idsArray.length;i++) {
				var otp = $('#originalTotalprice'+idsArray[i]).val();
				if(otp != ''){
					ordertot = ordertot + parseFloat($('#originalTotalprice'+idsArray[i]).val());
				}else{	
					ordertot = ordertot + 0;
				}
			}
			ordertot= ordertot.toFixed(2);
		}
		
		//ordertot= ordertot.toFixed(2);
		
		showVat			=	parseFloat(ordertot)*parseFloat(vat);
		showVat			= 	showVat.toFixed(2);
		showPrice		=	parseFloat(ordertot)+parseFloat(showVat);
		showPrice		= 	showPrice.toFixed(2);
		
		$('#Oders_Merchant').slideDown('slow');	
		$('#OrderTotal').val(showPrice);
		$('#SubTotal').val(ordertot);
		$('#VatTotal').val(showVat);
		$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
		$('.SubTotalShow').html(numberWithCommas(ordertot)); 
		$('.OrderTotalShow').html(numberWithCommas(showPrice)); 
		$('.VatTotalShow').html(numberWithCommas(showVat));  
		//$("html, body").animate({ scrollTop: '0px' },{duration: 1000});

	//}
}

function addRemoveQuantity(id,type) {
	var vat = '';
	var price = '';
	$('#showmessage').slideUp('slow');
	tot 	= $('#quantity'+id).val();
	if(	$('#ProductVAT').val() != ''){
		vat		= parseFloat($('#ProductVAT').val());
	}else{	
		vat 	= 0;
	}
	//vat		= parseFloat($('#ProductVAT').val());
	if(	$('#discountprice'+id).val() != ''){
		price 	= parseFloat($('#discountprice'+id).val());
	}else{	
		price 	= 0;
	}
	
	if(tot == 'NaN' || tot == '')
		tot = 0;
	
	if(type == 1)
		tot = parseInt(tot) + 1;
	else if(type == 2) {
		if(tot == 0)
			tot = tot;
		else	
			tot = tot - 1;
	}
	else if(type == 3)
		tot = tot;
	
	if(tot >= 1) {				
		price	= parseFloat(price * tot);
		price	= price.toFixed(2);
	
		$('#quantity'+id).val(tot);
		$('#originalTotalprice'+id).val(price);
		$('#orderPrice'+id).html(numberWithCommas(price));
		
		var prodIds = $('#OrderProductIds').val();
		ordertot = 0;
		idsArray = prodIds.split(',');		
		for(i=0;i<idsArray.length;i++) {
			ordertot = ordertot + parseFloat($('#originalTotalprice'+idsArray[i]).val());
		}
		ordertot= ordertot.toFixed(2);
		showVat			=	parseFloat(ordertot)*parseFloat(vat);
		showVat			= 	showVat.toFixed(2);
		showPrice		=	parseFloat(ordertot)+parseFloat(showVat);
		showPrice		= 	showPrice.toFixed(2);
		//$('#OrderTotal').val(ordertot);
		$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
		$('#OrderTotal').val(showPrice);
		$('#SubTotal').val(ordertot);
		$('#VatTotal').val(showVat);
		$('.OrderTotalShow').html(numberWithCommas(showPrice)); 
		$('.VatTotalShow').html(numberWithCommas(showVat)); 
		$('.SubTotalShow').html(numberWithCommas(ordertot)); 
	}
	else if(tot < 1 && type == 2) {
		if(confirm('Are you sure to remove this product from order?')) {
			$('#orderrow'+id).remove();
			var prodIds = $('#OrderProductIds').val();
			idsArray = prodIds.split(',');
			newProductIds = '';
			for(i=0;i<idsArray.length;i++) {
				if(idsArray[i] != id) {
					if(newProductIds == '')
						newProductIds = idsArray[i];
					else
						newProductIds = newProductIds+','+idsArray[i];
				}
			}
			$('#OrderProductIds').val(newProductIds);
			
			ordertot = 0;
			idsArray = newProductIds.split(',');		
			for(i=0;i<idsArray.length;i++) {
				ordertot = ordertot + parseFloat($('#originalTotalprice'+idsArray[i]).val());
			}
			ordertot= ordertot.toFixed(2);
			if(ordertot != 'NaN') {
				showVat			=	parseFloat(ordertot)*parseFloat(vat);
				showVat			= 	showVat.toFixed(2);
				showPrice		=	parseFloat(ordertot)+parseFloat(showVat);
				showPrice		= 	showPrice.toFixed(2);
				
				//$('#OrderTotal').val(ordertot);
				$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
				$('#OrderTotal').val(showPrice);
				$('#SubTotal').val(ordertot);
				$('#VatTotal').val(showVat);
				$('.VatTotalShow').html(numberWithCommas(showVat)); 
				$('.OrderTotalShow').html(numberWithCommas(showPrice));
				$('.SubTotalShow').html(numberWithCommas(ordertot)); 
			}
			else {
				$('#Oders_Merchant').slideUp('slow');
			}
		}
		else {
			tot		= 1;
			price	= parseFloat(price * tot);
			price	= price.toFixed(2);
		
			$('#quantity'+id).val(tot);
			$('#originalTotalprice'+id).val(price);
			$('#orderPrice'+id).html(numberWithCommas(price));
			
			var prodIds = $('#OrderProductIds').val();
			ordertot = 0;
			idsArray = prodIds.split(',');		
			for(i=0;i<idsArray.length;i++) {
				ordertot = ordertot + parseFloat($('#originalTotalprice'+idsArray[i]).val());
			}
			ordertot= ordertot.toFixed(2);
			showVat			=	parseFloat(ordertot)*parseFloat(vat);
			showVat			= 	showVat.toFixed(2);
			showPrice		=	parseFloat(ordertot)+parseFloat(showVat);
			showPrice		= 	showPrice.toFixed(2);
			
			//$('#OrderTotal').val(ordertot);
			$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
			$('#OrderTotal').val(showPrice);
			$('#SubTotal').val(ordertot);
			$('#VatTotal').val(showVat);
			$('.OrderTotalShow').html(numberWithCommas(showPrice));
			$('.VatTotalShow').html(numberWithCommas(showVat));
			$('.SubTotalShow').html(numberWithCommas(ordertot)); 
		}
	}
	else {
		price	= parseFloat(price * tot);
		price	= price.toFixed(2);
	
		$('#quantity'+id).val('');
		$('#originalTotalprice'+id).val(price);
		$('#orderPrice'+id).html(numberWithCommas(price));
		
		var prodIds = $('#OrderProductIds').val();
		ordertot = 0;
		idsArray = prodIds.split(',');		
		for(i=0;i<idsArray.length;i++) {
			ordertot = ordertot + parseFloat($('#originalTotalprice'+idsArray[i]).val());
		}
		ordertot= ordertot.toFixed(2);
		showVat			=	parseFloat(ordertot)*parseFloat(vat);
		showVat			= 	showVat.toFixed(2);
		showPrice		=	parseFloat(ordertot)+parseFloat(showVat);
		showPrice		= 	showPrice.toFixed(2);
		$('#OrderTotal').val(showPrice);
		$('#SubTotal').val(ordertot);
		$('#VatTotal').val(showVat);
		$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
		$('.OrderTotalShow').html(numberWithCommas(showPrice));
		$('.VatTotalShow').html(numberWithCommas(showVat));
		$('.SubTotalShow').html(numberWithCommas(ordertot)); 
	}
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function hideShowUsers(id,photo,name,CurrentBalance) {
	$('#showmessage').slideUp('slow');
	current = $("#CurrentUserId").val();
	if($('#OrderProductIds').val() == '') {
		$('.clearorder').hide();
		$('#showProductPrice').hide();
		$('#orderPrint').hide();
	}
	
	if (current == id) {
		alert('This customer was already added.');
		return false;
	} else {	
		//if(confirm('Are you sure to make order for this user?')) {
			$('#Oders_Merchant').slideDown('slow');
			$("#userImage").attr('src', photo);
			$("#username").html(name);
			$("#bottom").html('Change user');
			$("#userImageValue").val('1');
			$("#CurrentUserId").val(id);
			$("#OrderUserImage").val(photo);
			$("#OrderUserName").val(name);
			$("html, body").animate({ scrollTop: '0px'}, {duration: $("#userinstore").offset().top});
			$("#user"+id).addClass('userselected');
			if(current != '')
				$("#user"+current).removeClass('userselected');
		//}
	}
}

function clearOrders(ctype) {
	$('#showmessage').slideUp('slow');
	var flag = 0;
	if(ctype == 1)
		flag = 1;
	else {
		if(confirm('Are you sure to clear order?'))
			flag = 1;
	}
	if(flag == 1) {
		currentUserID =	$('#CurrentUserId').val();
		var prodIds = $('#OrderProductIds').val();
		idsArray = prodIds.split(',');
		for(i=0;i<idsArray.length;i++) {
			$('#orderrow'+idsArray[i]).remove();
		}
		$('#OrderProductIds').val('');				
		$('#OrderTotal').val('');
		$('#order_submit').val('');
		$('.OrderTotalShow').html('');
		photo = $("#userDefaultImage").val();
		$("#userImage").attr('src', photo);
		$("#username").html('No User Selected');
		$("#bottom").html('Select user');
		$("#userImageValue").val('');
		$("#CurrentUserId").val('');
		$("#OrderUserImage").val(photo);
		$("#OrderUserName").val('');
		$("#showmessage").hide();
		$('#Oders_Merchant').slideUp('slow');
		
		 $.ajax({
			type: "GET",
			url : "./CreateOrder?cs=1&ajax=true",	
			success: function(result){
				$('#user'+currentUserID).removeClass('userselected');
			}
		});	
	}
}

function checkBalance() {
	
	$('#showmessage').slideUp('slow');
	var prodIds = $('#OrderProductIds').val();
	idsArray = prodIds.split(',');
	if($('#OrderProductIds').val() == '') {
		alert('Please select the products to place the order.');
		return false;
	}
	quantity = 1;
	for(i=0;i<idsArray.length;i++) {
		if(($('#quantity'+idsArray[i]).val() == 0 ) || ($('#quantity'+idsArray[i]).val() == '' ))
			quantity = 0;
	}

	if(quantity	==	0) {
		alert('Please select products with quantity to place the order.')
		return false;
	} else {
		if($("#userImageValue").val() != 1) {
			alert('Please select the customer to place the order.')
			return false;
		} else {
			if(confirm('Are you sure to place this order?')) {
				printFunction('2');
				$('#accOrRej').click();
				
				//$('#OrderForm').submit();
				return true;
			} else {
				return false;
			}
		}		
	}	
}
function sortTable(idval){

    $("#orderby").val(idval);
	var order = $("#ordertype").val();
	if( order == 'desc'){
		$("#ordertype").val('asc');
		if(idval == 'Name')
			$("#name_bg").attr('src', path+'webresources/images/asc.gif');
		else if(idval == 'TotalPrice')
			$("#sales_bg").attr('src', path+'webresources/images/asc.gif');
		else if(idval == 'TotalQuantity')
			$("#quantity_bg").attr('src', path+'webresources/images/asc.gif');
	}
	else if(order == 'asc'){
		$("#ordertype").val('desc');
		if(idval == 'Name')
			$("#name_bg").attr('src', path+'webresources/images/desc.gif');
		else if(idval == 'TotalPrice')
			$("#sales_bg").attr('src', path+'webresources/images/desc.gif');
		else if(idval == 'TotalQuantity')
			$("#quantity_bg").attr('src', path+'webresources/images/desc.gif');
	}
	else{
		$("#ordertype").val('desc');
		if(idval == 'Name')
			$("#name_bg").attr('src', path+'webresources/images/desc.gif');
		else if(idval == 'TotalPrice')
			$("#sales_bg").attr('src', path+'webresources/images/desc.gif');
		else if(idval == 'TotalQuantity')
			$("#quantity_bg").attr('src', path+'webresources/images/desc.gif');
	}
	var fieldVal	=  $("#orderby").val();
	var sortVal 	=  $("#ordertype").val();
	loadGraph(1,2,sortVal,fieldVal);
}
function loadGraph(search,graghType,sortVal,fieldVal)
{
	$("#sort_val").val(sortVal);
	$("#sort_field").val(fieldVal);
    var queryString		=	'';
	//$('.chart').html('<img align="absmiddle" src="'+actionPath+'webresources/images/no_datas.png">');
    if(search=='1')
	{
		queryString		=	$("div.dashboard_filter #dashboard_list_search").serialize();
	}
     else
        queryString		=	"cs=1";
	if(graghType=='2') {
		var chart_url = 'ProductChart';//AjaxBarChart
	} 
	else {
		var chart_url = 'AjaxBarChart';
	}
   $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_CHART&"+queryString,
        success: function(result){
            result	=	$.trim(result);
                $('.graph').html(result);
        }
    });
    return false;
}
function showLoader()
{
    if($.browser.msie)
    {
        var loaderBlock = $('<div id="preloaderImage" style="display:none;position:fixed;left:0px;top:0px;background-color:rgba(0,0,0,0.4);layer-background-color:#ffffff;height:100%;width:100%;z-index:1000;"></div>');
    }
    else
    {
        var loaderBlock = $('<div id="preloaderImage" style="display:none;position:fixed;left:0px;top:0px;background-color:rgba(0,0,0,0.4);layer-background-color:#ffffff;height:100%;width:100%;z-index:1000;"></div>');
    }
    loaderBlock.append('<div  id="sub" class="load-icon-bg"><div><i class="fa fa-spinner fa-spin fa-lg"></i></div></div>');
    $('body').append(loaderBlock);
    $('#preloaderImage').show();
}

function removeLoader()
{
    $('#preloaderImage').remove();
}
$('body').ajaxStart(function() {
  showLoader();
}).ajaxStop(function() {
  removeLoader();
});

function approveReject(type) {
	if(confirm('Are you sure to '+type+' this order?')) {
		return true;
	}
	else
		return false;
}

function loadMoreUser() {
	var start					= parseInt($('#UserStart').val());
	var total					= parseInt($('#userTotalhide').val());
	var usersearch				= $('#usersearch').val();
	var perviousUserSearch		= $('#perviousUserSearch').val();
	if(perviousUserSearch == '') {
		perviousUserSearch		=	usersearch;
		$('#perviousUserSearch').val(usersearch)
	}
	if(usersearch == perviousUserSearch) {
		var dataparams	=	'load=1&usersearch='+usersearch;
	}
	else {
		$('#perviousUserSearch').val(usersearch)
		if(usersearch != '')
			var dataparams	=	'load=1&usersearch='+usersearch;
		else
			var dataparams	=	'load=1';
	}
	
	var searchPath = actionPath+'Search?search=1';
	 $.ajax({
		type: "GET",
		url: searchPath,
		data: dataparams,			
		success: function (result){
			$('#users_block').append(result);					
			start	= start + 12;
			$('#UserStart').val(start);
			if((total-start) <= 0)
				$('#loadmore').hide();
			$('#loadmorehome').remove()
		}			
	});
   return false;		  
}

function loadMoreNewOrders() {
	var start		= parseInt($('#newOrderStart').val());
	var total		= parseInt($('#newOrderTotalhide').val());
	var searchPath  = actionPath+'ajax_orders?orders=1';
	 $.ajax({
		type: "GET",
		url: searchPath,
		data: '',			
		success: function (result){
			$('#NewOrderListHtml').append(result);					
			start	= start + 12;
			$('#newOrderStart').val(start);
			distot	=	(start + 12);
			if(distot > total)
				distot	= total;
			dis	=	'Orders Displayed - '+distot;
			$('#OrdersDisplayed').html(dis);
			$('#loadmorehome').remove();
		}			
	});
   return false;		  
}

function newSpecialRow(type,ele,idrow) {	
	currentid			=	$(ele).attr("id");
	var numberPattern 	= /\d+/g;
	//var rowId 	= 	currentid.substr(currentid.length - 1);	
	rowId 			= 	currentid.match( numberPattern );
	if(type	==	1) {
	
		//Check for current row filled or not
		TotalrowIds		=	$('#TotalRowIds').val();
		var res 		= 	TotalrowIds.split(",");
		checkid			=	res[res.length - 1];
		
		Productid		=	$('#Products'+rowId).val();
		qty				=	$('#quantity'+rowId).val();
		price			=	$('#price'+Productid).val();
					
		if(Productid != '' && qty != '') {
		
			//adding new row
			//if(confirm('Are you sure to Add another product?')) {	
				Totalrows 	= 	parseInt($('#Totalrows').val());
				Totalrows	=	Totalrows	+	1;
				var clone 	= 	$("#copydiv").clone();
				clone.attr({
					id		: "Row_" + Totalrows,
					name	: "Row_" + Totalrows,
					style	: ""
				});
				clone.find("input,select,i").each(function(){
					$(this).attr({
						id		: $(this).attr("id") + Totalrows,
						name	: $(this).attr("name") + Totalrows,
						value	: ''
					});
				});
				
				clone.find("i.fa-plus").attr("onclick","return newSpecialRow(1,this,"+Totalrows+");");
				
				
				//$clone = $clone+'<div class="col-xs-12 pad"></div>';
				$("#specialProducts").append(clone);
				//$("#specialProducts").append('<div class="col-xs-12 pad"></div>');
				
				hide1		=	Totalrows - 1;
				$("#plus"+hide1).hide();			
				$("#minus"+hide1).show();			
				$("#plus"+idrow).hide();			
				$("#minus"+idrow).show();			
				$("#plus"+Totalrows).show();
				$("#minus"+Totalrows).show();
				
				//Total Price
				tprice			=	0;
				for(var i = 0; i < res.length; i++) {
					cprice		=	'';
					if(res[i] == 0) 
						cprice	=	$('#quantityTotalPrice').val();
					else
						cprice	=	$('#quantityTotalPrice'+res[i]).val();
					if(cprice != '')
					tprice		=	tprice	+	parseFloat(cprice);	
				}
				$('#TotalPrice').val(tprice);
				
				//updating row ids
				TotalrowIds		=	$('#TotalRowIds').val();
				if(TotalrowIds != '')
					TotalrowIds	=   TotalrowIds+','+Totalrows;
				$('#TotalRowIds').val(TotalrowIds);	
				
				//updating total row				
				$('#Totalrows').val(Totalrows);
			//}
		}
		else {
			alert("Please fill the current product.")
		}
	}
	else if(type == 2){// && rowId > 1
		if(confirm('Are you sure to remove this product?')) {	
			
			//removing the table row
			$('#Row_'+rowId).remove();
			
			//removing the row id from the TotalIds
			TotalrowIds			=	$('#TotalRowIds').val();
			var res 			= 	TotalrowIds.split(",");
			tprice				=	0;
			newIds				=	'';
			for(var i = 0; i < res.length; i++) {
				if(res[i] != rowId) {					
					//form new ids
					if(newIds == '')
						newIds	=	res[i];
					else
						newIds	=	newIds+','+res[i];
					
					//Total price
					cprice	=	'';
					if(res[i] == 0) 
						cprice	=	$('#quantityTotalPrice').val();
					else
						cprice	=	$('#quantityTotalPrice'+res[i]).val();
					if(cprice != '')
						tprice	=	tprice	+	parseFloat(cprice);	
				}
			}
			$('#TotalPrice').val(tprice);
			$('#TotalRowIds').val(newIds);
			
			//showing plus/minus for row while removing
			var res 		= 	newIds.split(",");
			showplus		=	res[res.length - 1];
			if(res.length > 1) {
				if(showplus != 0)
					$("#plus"+showplus).show();
				else
					$("#plus").show();
			} else {
				if(showplus != 0) {
					$("#plus"+showplus).show();
					$("#minus"+showplus).hide();
				}
				else {
					$("#plus").show();
					$("#minus").hide();
				}
			}			
		}
	}
}

function calculateSpecialPrice(type,ele) {
	currentid		=	$(ele).attr("id");
	var numberPattern = /\d+/g;
	//rowId 			= 	currentid.substr(currentid.length - 1);
	rowId 			= 	currentid.match( numberPattern );
	//Getting Value for current event
	Productid		=	$('#Products'+rowId).val();
	qty				=	$('#quantity'+rowId).val();
	price			=	$('#price'+Productid).val();		
	
	//check for product id
	if(Productid == '' && type == 2) {
		alert('Please select the product first.');		
		$('#quantity'+rowId).val('');	
		$('#quantityTotalPrice'+rowId).val('');	
		return false;
	} else {
		if(Productid == '') {
			qty			=	'0';
			price		=	'0';
		}
	}
	
	//check for qty
	if(qty == '' && type == 2) {
		qty		=	'0';		
	}
	
	//Calculating TotalPrice from quantity
	if(qty == '') {
		qty			=	'1';
		totPrice	=	parseInt(qty) * price;
	}
	else 
		totPrice	=	parseInt(qty) * price;
	
	//restoring the calculated data's
	if(qty == 0 || totPrice == 0) {
		$('#quantity'+rowId).val('');
		$('#quantityTotalPrice'+rowId).val('');
	} else {
		$('#quantity'+rowId).val(qty);
		$('#quantityTotalPrice'+rowId).val(totPrice);	
	}
	
	//Total Price
	TotalrowIds		=	$('#TotalRowIds').val();
	var res 		= 	TotalrowIds.split(",");
	tprice			=	0;
	for(var i = 0; i < res.length; i++) {
		cprice	=	'';
		if(res[i] == 0) 
			cprice	=	$('#quantityTotalPrice').val();
		else
			cprice	=	$('#quantityTotalPrice'+res[i]).val();
		if(cprice != '')
		tprice	=	tprice	+	parseFloat(cprice);	
	}
	$('#TotalPrice').val(tprice);
}

function validateSpecialdata() {
	//Total Price
	TotalrowIds		=	$('#TotalRowIds').val();
	var res 		= 	TotalrowIds.split(",");
	tprice			=	0;
	for(var i = 0; i < res.length; i++) {
		Productid	=	$('#Products'+res[i]).val();
		qty			=	$('#quantity'+res[i]).val();
		if(Productid == '' || qty == '') {
			$('#specialerror').html('All Products fields are required');			
			$('#specialerror').show();			
			return false;
		}
	}
	
	//Product Ids unique
	TotalrowIds		=	$('#TotalRowIds').val();
	var res 		= 	TotalrowIds.split(",");
	proids			=	new Array();
	for(var i = 0; i < res.length; i++) {
		ids		=	$('#Products'+res[i]).val();
		if(jQuery.inArray(ids,proids) != '-1') { 
			$('#specialerror').html('Select products uniquely');	
			$('#specialerror').show();	
			return false;
		}
		proids[i]	=	ids;
	}
	$('#specialerror').hide();	
	
	//Price must me less then total Price
	Totalprice	=	parseInt($('#TotalPrice').val());
	price		=	parseInt($('#Price').val());
	if(price >= Totalprice) {
		$('#specialpriceerror').show();
		return false;
	}
	else
		$('#specialpriceerror').hide();
}

function noProducts(){
	alert('Add items to process with specials');
	return false;
}

function refundBox(){
	$("#refund_msg_but").hide();
	$("#refund_msg_box").fadeIn('slow');
	return false;
}

function refundSubmit(type,id){
	if(type == 1)
		$('#RefundForm').submit();
	else {
		$('#RefundProduct'+id).submit();
	}
}
function deleteMyStoreImage(id){	
	if(confirm("Are you sure to delete ?")) {
		$('#image_'+id).hide();
		$('#default_'+id).show();
		$('#upload_'+id).show();
		$('#delete_'+id).hide();
		var del = $('#image_Id_'+id).val();
		if(del != '') {
			$('#deleteimage_'+id).val(del);
		}
	}
}
function deleteBefore(id,ext){
	if(confirm("Are you sure to delete?")) {
		imagecount	=	$('#imagecount').val();
		ImageExt	=	$('#ImageExt').val();
		
		resultimgcount	=	imagecount.split(",");
		resultimgext	=	ImageExt.split(",");
		if(resultimgcount.length > 0){
			var newimagecount	=	newimageext	=	'';
			for(i=0;i<resultimgcount.length;i++) {
				if(id != resultimgcount[i]) {
					if(newimagecount == '' && newimageext == '') {
						newimagecount	=	resultimgcount[i];
						newimageext		=	resultimgext[i];
					}
					else {
						newimagecount	+=	','+resultimgcount[i];
						newimageext		+=	','+resultimgext[i];
					}
				} else {
				
				}
			}
		}
		$('#imagecount').val(newimagecount);
		$('#ImageExt').val(newimageext);
		
		//new images		
		oldtotalImage	=	parseInt($('#oldtotalImage').val());		
		inc			=	oldtotalImage;
		newimagelength	=	resultimgcount.length;		
		totalimagesin	=	oldtotalImage + newimagelength;
		
		deleteImage		=	$('#DeleteIds').val();
		if(deleteImage != '') {
			resultdeleteImage	=	deleteImage.split(",");
			deletelength		=	resultdeleteImage.length;
			oldtotalImage		=	oldtotalImage	-	deletelength;
			inc					=	oldtotalImage + 1;
		} else {
			deletelength		=	0;
		}
		if(oldtotalImage == 0 && inc == 0)
			inc		=	1;
		//reordering the images in the screen
		totalImage		=	$('#totalImage').val();
		for(j = oldtotalImage + 1; j<= totalImage; j++) {
			if ($('#temp'+j).length > 0 && id != j) {
				$('#imgStore_'+j).html(inc+'.');
				inc		=	inc 	+	1;
			}
		}
		$('#imgcount').html(inc+'.');
		
		//unlink image from temp path
		$.ajax({
	        type: "GET",
	        url: actionPath+"MyStore?imgid="+id+"&ext="+ext,
	        success: function (result){
				
	        }			
	    });
		
		totalImageNow	=	parseInt($('#totalImageNow').val());
		totalImageNow	=	totalImageNow - 1;
		$('#totalImageNow').val(totalImageNow);
		
		//show drag/drop
		$('#temp0').show();
		
		//removing selected image
		$('#temp'+id).remove();
		$('.remove'+id).show();
	}
}

function geolocation(type) {
	loc_lat		=	$("#Latitude").val();
	loc_log		=	$("#Longitude").val();
	if(loc_lat	==	'' && loc_log == '') {
		if(geo_position_js.init()){
			if(type == 1)
				$('#fancybox-loading').show();
			geo_position_js.getCurrentPosition(success_callback,error_callback,{enableHighAccuracy:true});
		}
		else{
			alert("Functionality not available");
		}
	}  else {
		return false;
	}
}

function success_callback(p)
{
	$('#Latitude').val(p.coords.latitude);
	$('#Longitude').val(p.coords.longitude);
	$('#fancybox-loading').hide();
	saveAddress(p.coords.latitude,p.coords.longitude);
}

function error_callback(p)
{
	alert('error='+p.message);
}

function saveAddress(loc_lat,loc_log){
	var geocoder 	= 	new google.maps.Geocoder();
	var latitude 	= 	loc_lat;
	var longitude 	= 	loc_log;
	var latLng 		= 	new google.maps.LatLng(latitude,longitude);
	//var latLng = new google.maps.LatLng(38.8833,77.0167);
	geocoder.geocode({      
			latLng: latLng    
			},
			function(responses)
			{    
			   if (responses && responses.length > 0)
			   {        
					//console.log(responses[0].formatted_address); 
					address			=	responses[0].formatted_address;
					addressArray	=	address.split(',');
					t 				=	0;
					if(addressArray.length > 0) {
						$('#Country').val('');
						$('#State').val('');
						$('#ZipCode').val('');
						$('#City').val('');
						$('#Street').val('');
						for(i=addressArray.length;i>0;i--) {
							j	=	i - 1;
							t   = 	t + 1;
							if(t == 1)
								$('#Country').val(addressArray[j])
							else if(t == 2) {
								
								stateArray	=	addressArray[j].split(' ');
								statelen	=	stateArray.length;
								if(statelen > 0) {
									if(isNaN(stateArray[statelen - 1])) {
										$('#State').val(stateArray[statelen - 1])
									}
									else{
										$('#ZipCode').val(stateArray[statelen - 1])
										adata1 = '';
										for(st= 0;st<statelen - 1;st++) {
											if(stateArray[st] != '') {
												adata1	=	$('#State').val()+' '+stateArray[st];
												$('#State').val(adata1);
											}
										}
									}	
								} else {
									$('#State').val(addressArray[j]);
								}
							}
							else if(t == 3)
								$('#City').val(addressArray[j])
							else if(t > 4 ) {
								adata	=	addressArray[j]+', '+$('#Street').val();
								$('#Street').val(adata);
							}
						}
					}
			   }
			   else
			   {      
				 alert('Not getting Any address for given latitude and longitude.');    
			   }  
			   $('#fancybox-loading').hide();
			}
	);	
}


function selectAllProductDelete(category_sel,type,cat_id) {
	var id	=	'';
	idArray	=	[];
	i = 0;
	$('#'+category_sel).find( ".small-box.panel-heading.select_active" ).each(function( index,value ) {
		idtemp		= 	$( this ).attr('id').split('_');
		id 			+= 	idtemp[0]+',';
		idArray[i] 	= 	idtemp[0];
		i++;
	});
	if(id != '') {
		if(confirm('Are you sure to delete?')) { 	
				id = id.substring(0,id.length-1);
				$.ajax({
					type: "GET",
					url: actionPath+"Product",
					data: 'delete='+id+'&from=1&Type='+type,
					success: function (result){
						if(result == 1) {
							for(i=0;i<idArray.length;i++)
								$('#'+idArray[i]).remove();
							$('#alert_'+category_sel).show();
							temp	=	[];
							i = 0;
							$('#'+category_sel).find( ".small-box.panel-heading" ).each(function( index,value ) {
								idtemp		= 	$( this ).attr('id').split('_');
								temp[i] 	= 	idtemp[0];
								i++;
							});
							if(i == 0) {
								$('#select_all_'+cat_id).remove();
								$('#delete_items_'+cat_id).remove();
							}
						} 
					}			
				});
		}
	}
	else
		alert('Please select the products and then choose the delete')
}

function cls(id_val){
	//console.log(id_val);
	if($(id_val).hasClass('select_active'))
		$(id_val).removeClass("select_active");
	else 
		$(id_val).addClass("select_active");
}
function hideAlertMsg() {
	$('.hideshowalert').slideUp();	
}


function uploadFiles(event)
{

//console.log(event);
	/*	uptype = 0 //Normal upload
		uptype = 1 //Tap here upload
	*/
	var process_pram 	= 	event.data.name;
	var uptype 			= 	event.data.type;
	var mid 			= 	event.data.id;
	files 				= 	event.target.files;
	event.stopPropagation();
	event.preventDefault();
	
	// Create a formdata object and add the files
	var data = new FormData();
	$.each(files, function(key, value) {
		data.append(key, value);
	});
	
	var loadingIMG  =  '<span class="photo_load load_upimg text-align" ><i class="fa fa-spinner fa-spin fa-lg"></i></span>';	
	$(loadingIMG).insertAfter($("#"+process_pram+"_img"));
	$("#"+process_pram+"_img").hide();	
	var hiddenVal 	= 	$("#empty_"+process_pram).val();
	image_name 		= 	document.getElementById(process_pram).value;
	image_name 		= 	image_name.replace(/C:\\fakepath\\/i,'');
	
	url = actionPath+'models/AjaxAdminFileUploadScript.php?files&filename='+process_pram;
	
	$.ajax({
		url			: 	url,
		type		: 	'POST',
		data		: 	data,
		cache		: 	false,
		processData	: 	false,
		contentType	: 	false,
		success		: 	function(data)
		{
			
			if(hiddenVal=='')
				$("#empty_"+process_pram).val(1);
			var json = JSON.parse(data);					
			if(json['error'] != false) {
				alert(json['error']);
				//$("#empty_"+process_pram).val('');
				/*if(uptype == 1) {
					$(".upload_info").show();
					$("#"+process_pram+"_image_upload").attr('src','');
					$("#"+process_pram+"_image_upload").hide();
				}*/
			}				
			if(json['msg'] != '') {
				msg			=	json['msg'];
				var result	=	msg.split("####");
				$("#"+process_pram+"_image_upload").attr('src','');
				if (uptype == 1) {
					path		=	actionPath+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random();
					$("#"+process_pram+"_image_upload").attr('src',path);
					$(".upload_info").hide();
					$("#"+process_pram+"_upload").val(result[0] +'.'+ result[1]);
					$("#"+process_pram+"_image_upload").show();
				}
				else {
					height 	=	75;
					width	= 	75;
					imgclass=	'';
					if(process_pram == 'icon_photo'){
						imgclass	=	'img_border';
					}
					if(process_pram == 'merchant_photo') {
						height	=	200;
						width	=	100;
					}
					if(mid != '') {
						var src 	= 	path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random();
						var srcval	=	result[0] +'.'+ result[1];
						$('#image_'+mid+' img').attr('src',src);
						$('#image_'+mid+' img').attr('height','');
						$('#default_'+mid).hide();
						$('#image_'+mid).show();
						$('#uploadimage_'+mid).val(srcval);
					} else {
						var img		=	'<img  src="'+path+'webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="'+height+'" height="'+width+'" class="'+imgclass+'" />';
							img 	+= 	'<input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
						$("#"+process_pram+"_img").html(img);
					}
				}
				$("#no_"+process_pram).remove();
				$("#old_"+process_pram).val('');
			}
			$(".photo_load").remove();
			$("#"+process_pram+"_img").show();
			return true;
		}
	});
}

$('#usersearch').keypress(function(event) {	
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13') {
		var userKey  = this.value;			   
		 UserSearch(userKey);		
	   return false;
    }
});
function UserSearch(userKey){
	 $('#UserStart').val('0');
	 var userKey = $('#usersearch').val();
	var start					= parseInt($('#UserStart').val());
	 $.ajax({
			type: "GET",
			url: actionPath+'/Search?search=1',
			data: 'usersearch='+userKey+"&clear=1",
			success: function (result){
				$('#users_block').html(result);	
				start	= start + 6;
				$('#UserStart').val(start);
				$('#perviousUserSearch').val(userKey)
			}			
		});		
}
//product search
$('#productsearch').keypress(function(event) {	
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13') {
		var productKey  = this.value;			   
		 Search(productKey);		
	   return false;
    }
	
});
function Search(productKey){
	$('#ProductStart').val('0');
 	var productKey = $('#productsearch').val();
	var start		= parseInt($('#ProductStart').val());
	 $.ajax({
			type: "GET",
			url: actionPath+'/Search?search=1',
			data: 'productsearch='+productKey+"&clear=1",
			success: function (result){
				$('#products_block').html(result);	
				start	= start + 6;
				$('#ProductStart').val(start);
				$('#perviousProductSearch').val(productKey)
			}			
		});	
}
function printFunction(showList){
	//showList = 1 - print preview
	//showList = 2 - popup preview 
		var text 		= "";
		var itemName 	= "";
		var itemPrice	= "";
		var vattotal	= "";
		var ordertotal	= $('#OrderTotal').val();
		var subtotal	= $('#SubTotal').val();
		//var vattotal	= $('#VatTotal').val();
		if($('#VatTotal').val() != 'NaN'){
			vattotal	= $('#VatTotal').val();
		}else { vattotal	= '0'; }
		var prodIds 	= $('#OrderProductIds').val();
		prodIds 		= prodIds.split(',');	
			if(showList == 1){
			text = '<table id="printTable" width="300" id="tbl1" align="center" border="0" style="margin-top:10px;border:1px solid #DFDFDF;" class="popup your_order">';
			text = text+'<tr><td style="border-bottom:1px dotted #dbdbdb;" align="center" width="100%" colspan="2"><h1>Your order</h1><p class="help-block col-xs-12 text-center">Items in your cart</p></td></tr>';
			}else{
			text = '<table id="listTable" id="tbl1" align="center" border="0" style="width:300px;border:1px solid #dbdbdb;border-top:0px !important;padding:5px 5px">';
			}
		for(i=0;i<prodIds.length;i++) {
			itemQuantity 	= $("#quantity"+prodIds[i]).val();
			itemName		= $("#orderItemName"+prodIds[i]).val();
			itemPrice		= $('#originalTotalprice'+prodIds[i]).val();
			text 			= text+'<tr id="item'+prodIds[i]+'" style="padding:10px 10px;"><td class="item-name" width="90%" style="line-height:20px;padding:5px 20px;">'+itemQuantity+' x '+itemName+'</td><td class="item-price" width="10%" style="line-height:20px;padding:5px 20px;" align="right"><strong>\u00A3'+itemPrice+'</strong></td></tr>';
		}
			text = text+'<tr id="itemTotal" class="print_subtotal" style="font-size: 11px;line-height:18px;"><td style="border-top:1px dotted #DFDFDF;padding:20px 20px 0px 20px;" align="left" class="item-total LH18" width="90%"><strong>SUB TOTAL</strong></td><td  style="border-top:1px dotted #DFDFDF;padding:20px 20px 0px 20px;"  width="10%"  class="LH18"  align="right"><strong>\u00A3'+subtotal+'</strong></td></tr>';
			text = text+'<tr id=""  class="print_vat LH18" style="font-size: 10px;line-height:18px;"><td align="left" class="item-total LH18" width="90%" style="padding:0px 20px;font-weight:normal;">VAT</td><td width="10%" style="padding:0px 20px;font-weight:normal;" class="LH18" align="right">\u00A3'+vattotal+'</td></tr>';
			text = text+'<tr id="" class="print_total" style="line-height:40px;"><td align="left" class="item-total" style="line-height:45px;padding:0px 20px" width="90%"><strong>TOTAL</strong></td><td width="10%" style="padding:0px 20px"  align="right"><strong>\u00A3'+ordertotal+'</strong></td></tr>';
			text = text+"</table>";
		
		
		$("#displayAcc").html(text);
		if(showList == 1){
			$("#newprint").html(text);	
		}
		else{
			printtext = '<table id="printTable" width="300" id="tbl1" align="center" border="0" style="margin-top:10px;border:1px solid #DFDFDF;" class="popup your_order">';
			printtext = printtext+'<tr><td style="border-bottom:0px dotted #dbdbdb;" align="center" width="100%" colspan="2"><h1>Your order</h1><p class="help-block col-xs-12 text-center">Items in your cart</p></td></tr>';
			printtext = printtext+text;
			$("#newprint").html(printtext);	
		}
		
		
}
/*---- bill popup on order submit----*/
function popupBill(){
		var text 		= "";
		var itemName 	= "";
		var itemPrice	= "";
		var ordertotal	= $('#OrderTotal').val();
		var prodIds 	= $('#OrderProductIds').val();
		prodIds 		= prodIds.split(',');	
		//alert(prodIds);
		text = text+'';
		for(i=0;i<prodIds.length;i++) {
			itemQuantity 	= $("#quantity"+prodIds[i]).val();
			itemName		= $("#orderItemName"+prodIds[i]).val();
			itemPrice		= $('#originalTotalprice'+prodIds[i]).val();
			text 			= text+'<div class="col-xs-1 col-sm-1 col-md-1" style="margin-bottom:8px;"><div class="col-xs-12  no-padding">'+itemQuantity+' x '+'</div></div>';
			text			= text+'<div class="col-xs-4 col-sm-4 col-md-6 no-padding" style="margin-bottom:8px;"><span title="Item Name">'+itemName+'</span></div>';
			text			= text+'<div class="col-xs-7 col-sm-5 col-md-5 text-right" style="margin-bottom:8px;"><strong>\u00A3'+itemPrice+'</strong></div>';
		}
			text = text+'<div class="col-xs-12 col-sm-12 col-md-12" style="border-top:1px solid #dbdbdb;padding-top:5px;padding-bottom:5px;"><div class="col-xs-3 col-sm-6 col-md-6 no-padding"><strong>Total</strong></div><div class="col-xs-9 col-sm-6 col-md-6 text-right no-padding"><strong>\u00A3'+ordertotal+'</strong></div></div>';
		$("#displayAcc").html(text);
		return true;
}
function printBill(divId){
	$("#"+divId).print();
	return false;
}

function getPound(price){
	var priceValue = "\u00A3"+price;
	return priceValue;
}

function dropSlideShow(){

}

function validateOpenHours() {
	var validatetime = '1';
	var myregex = new RegExp("");
	var checksame = 6;
	if($('#samehours').is(':checked')) {
		checksame = 0;
		$('#row_0').val('1');
		if($("#from1_0").val() == '' || $("#from1_0").val() == ''){
			$('#error_0').html('Enter From time and To time.');
			validatetime = '0';
			return false;
		}
	}/* else {
		$('#row_0').val('');
	}*/
	for(i=0;i<=checksame;i++) {
		$('#error_'+i).html('');
		$('#error_frm_'+i).hide();
		$('#error_to_'+i).hide();
		$('#error_'+i).hide();
		if($('#row_'+i).val() != '') {			
			if(!$("#from1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				//$('#error_'+i).html('From time is required');
				$('#error_frm_'+i).html('From time is required');
				$('#error_frm_'+i).show();
				validatetime = '0';
			}
			else if(!$("#to1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				//$('#error_'+i).html('To time is required');
				$('#error_to_'+i).html('To time is required');
				$('#error_to_'+i).show();
				validatetime = '0';
			}
			else {
				var start = $('#from1_'+i).val();
				start = start.toLowerCase();
                var end = $('#to1_'+i).val();
				end = end.toLowerCase();
				if(start != '' && end != '') {
					if(start == end) {
						$('#error_'+i).html('From time and To time should not be same');
						$('#error_'+i).show();
						validatetime = '0';
					}
				}				
			}
		}
	}	
	
	if(validatetime == '1')
		return true;
	else
		return false;
}


function checkPincode(frm) {	
	
	var pin			=	frm.Pincode.value;// $('#Pincode').val()
	var url 		= 	actionPath+'AjaxWork?action=CHECKPIN';
	var dataparams	=	'Pincode='+pin;
	 $.ajax({
		type	: "GET",
		url		: url,
		data	: dataparams,			
		success	: function (result){
			if(result == 1) {
				var now 	= 	Math.round((new Date()).getTime() / 1000);
				$('#tuplit_merchant_lastaccess').val(now);	
				$('.screen-inner').empty();	
				$('.AskPin_error').hide();
				parent.jQuery.fancybox.close();				
			} else {
				alert('Invalid Pincode.');//location.reload();
			}
		}			
	});
   return false;		  
}

function addPincode(val) {	
	var length = 0;
	//var input = $('.screen-inner');
	//$(input).append(val);
	var input = $('#Pincode_box').val();
	var pin = input+val; 
	//alert('------pin---------------'+pin);
	$('#Pincode_box').val(pin);
	$('.screen-inner').html(pin);
	length	=	pin.length;
	//var pin	=	 $('#screen').html();//kalpana
	//var pin	=	 $('#Pincode').val();
	//length	=	input.text().length;
	//length = $('#screen').text().length;
	if(length == 4)  {
		var url 		= 	actionPath+'AjaxWork?action=CHECKPIN';
		var dataparams	=	'Pincode='+pin;
		 $.ajax({
			type	: "GET",
			url		: url,
			data	: dataparams,			
			success	: function (result){
				if(result == 1) {
					var now 	= 	Math.round((new Date()).getTime() / 1000);
					$('#tuplit_merchant_lastaccess').val(now);		
					//$('.screen-inner').empty();	kalpana		
					$('#Pincode_box').val('');
					$('.screen-inner').html('');
					parent.jQuery.fancybox.close();					
				} else {
					$('.pin_error').html('Invalid Pincode');
					//$('.screen-inner').empty(); kalpana
					$('#Pincode_box').val('');
					$('.AskPin_error').show();
					$('.screen-inner').html('');
				}
			}			
		});
	   return false;	
	 }
	/*var btnVal = this.innerHTML;
	input.innerHTML += btnVal;	  */
}
function clearPin() {	
	//$('.screen-inner').empty();	kalpana
	$('#Pincode_box').val('');
	$('.screen-inner').html('');
	$('.AskPin_error').hide();
}

function updatePin(type) {
	var now 		= 	Math.round((new Date()).getTime() / 1000);	
	var last 		= 	$('#tuplit_merchant_lastaccess').val();
	var autolock 	=  	60;
	var diff 		= 	now - last;
	var txttype		=	'';	
	/*console.log('====>'+now)
	console.log(last)
	console.log(autolock)
	console.log(diff)*/
	if(type == 2) {
		txttype	=	'&type=1';
	}	
	if(diff >= autolock) {	
		var url 	= 	actionPath+'AjaxWork?action=UPDATEPIN'+txttype;
		 $.ajax({
			type	: "GET",
			url		: url,
			data	: '',	
			global	: false,
			success	: function (result){
				if(result == 2) {
					$('#tuplit_merchant_lastaccess').val(now);					
				}
				else  if(result == 1){
					$('.AskPin_error').hide();
					$('#screen').html('');
					var acceptedContent	 =   $('#AskPin').html(); 					
					$.fancybox({
						content		: acceptedContent, 
						width		: '280',
						height		: 'auto',
						autoSize	: false,
						type		: 'iframe',
						closeBtn	: false,
						closeClick  : false,
						helpers 	: { 
										overlay : {closeClick: false}
										},
						keys 		: {
										close  : null
										}
					});
				}
			}			
		});	
	}	
}
function getTopsales(search,graghType,dateTime)
{
	//var search = $( "#dataType option:selected" ).val();
	//$('#dateTypes').html(search);
	var content = $('#'+search).html();
	$('#dateTypes').html(content);
	//alert(content);
	if(graghType == 1)
		var chart_url = 'TopSaleChart';
	if(graghType == 2)
		var chart_url = 'DemographicsChart';
    $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_CHART&dataType="+search+"&timeOfDay="+dateTime,
        success: function(result){
            result	=	$.trim(result);
                $('.graph').html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}		
		
    });
    return false;
}

/*----------Circle chart-------------*/
function circleChart(text1,text2,radius,color,canvasId)
{
	var can 	= document.getElementById(canvasId);
	var context = can.getContext('2d');
	var text 	= text1;//"61%";
	var text1 	= text2;//"New";
	context.fillStyle = color;//"#fc7f09";
	context.beginPath();
	var radius = radius//50	// for example
	var radius1 = 25;
	context.arc(100, 100, radius, 0, Math.PI * 2);
	context.closePath();
	context.fill();
	context.lineWidth = 2;
	context.strokeStyle = '#003300';
	context.stroke();
	context.fillStyle = "white"; // font color to write the text with
	var font = radius1 +"px serif";
	context.font = font;
	  
	// Move it down by half the text height and left by half the text width
	var width = context.measureText(text).width;
	var height = context.measureText("w").width; // this is a GUESS of height
	context.fillText(text, 100 - (width/2) ,90 + (height/2));
	var font1 = 15 +"px serif";
	context.font = font1;
	context.fillText(text1, 100 - (width/2) ,100 + (height));

	// To show where the exact center is:
	context.fillRect(50,50,5,5)
}
function squareChart(text1,text2,color,canvasId)
{
	var can = document.getElementById(canvasId);
	var context = can.getContext('2d');
    context.beginPath();
	context.fillStyle = color;
	context.rect(100, 100, 100, 100);
	context.fill();
	context.lineWidth = 2;
	context.strokeStyle = '#003300';
	context.stroke();
	var font = 20 +"px serif";
	context.font = font;
	context.fillStyle = "white";
	context.fillText(text1, 135, 150);
	var font = 14 +"px serif";
	context.font = font;
	context.fillStyle = "white";
	context.fillText(text2, 110, 170);
}
/*----------Circle chart-------------*/
/*----------get top products-------------*/
function getTopOrders(search,graghType,dateTime)
{
	//var search = $( "#dataType option:selected" ).val();
	var content = $('#'+search).html();
	$('#dateTypes').html(content);
	if(graghType == 1)
		var chart_url = 'TopOrdersChart';
	if(graghType == 2)
		var chart_url = 'DemographicsChart';
    $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_CHART&dataType="+search+"&timeOfDay="+dateTime,
        success: function(result){
            //alert(result);
			result	=	$.trim(result);
                $('.graph').html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}		
		
    });
    return false;
}
/*----------get bar charts-------------*/
function getBarCharts(perc1,perc2,perc3,color1,color2,color3,divid)
{
	//alert(color1+"-"+color2+"-"+color3);
	$.ajax({
        type: "POST",
        url : "HorizontalBarChart",
		data : 'action=GET_BAR_CHART&colour1='+color1+'&colour2='+color2+'&colour3='+color3+'&perc1='+perc1+'&perc2='+perc2+'&perc3='+perc3+'&divid='+divid,
        success: function(result){
            //alert(result);
			result	=	$.trim(result);
                $('.'+divid).html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}		
		
    });
}
/*----------get top sellers-------------*/
function getTopSellers(search,graghType,dateTime)
{
	//var search = $( "#dataType option:selected" ).val();
	var content = $('#'+search).html();
	$('#dateTypes').html(content);
	if(graghType == 1)
		var chart_url = 'TopSellersChart';
	if(graghType == 2)
		var chart_url = 'DemographicsChart';
    $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_CHART&dataType="+search+"&timeOfDay="+dateTime,
        success: function(result){
            //alert(result);
			result	=	$.trim(result);
                $('.graph').html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}		
		
    });
    return false;
}
function getProductCustomers(search,cur_page,per_page)
{
	var content = $('#'+search).html();
	$('#dateTypes').html(content);
	var chart_url = 'ProductCustomersList';
    $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_CUSTOMERS&dataType="+search+"&cur_paging="+cur_page+"&per_paging="+per_page,
        success: function(result){
			result	=	$.trim(result);
                $('#append_id').html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}				
    });
    return false;
}
function getProdComments(search,start)
{
	var content = $('#'+search).html();
	$('#dateTypes').html(content);
	var chart_url = 'ProductCommentsList';
    $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_COMMENTS&dataType="+search+"&Starts="+start,
        success: function(result){
			result	=	$.trim(result);
                $('#comment_append').html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}				
    });
    return false;
}
function getAnalytics(search,graghPage,dateTime)
{
	var content = $('#'+search).html();
	$('#dateTypes').html(content);
	//alert(content);
	if(graghPage == 1)
		var chart_url = 'ProductSaleChart';
	
    $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_CHART&dataType="+search+"&timeOfDay="+dateTime,
        success: function(result){
            result	=	$.trim(result);
                $('.graph').html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}		
		
    });
    return false;
}
/*----------get top sellers-------------*/
function getTransactionDetails(search,graghType,dateTime)
{
	//var search = $( "#dataType option:selected" ).val();
	var content = $('#'+search).html();
	$('#dateTypes').html(content);
	if(graghType == 1)
		var chart_url = 'TransactionOverviewChart';
	if(graghType == 2)
		var chart_url = 'DemographicsChart';
    $.ajax({
        type: "POST",
        url : chart_url,
		data : "action=GET_CHART&dataType="+search+"&timeOfDay="+dateTime,
        success: function(result){
            //alert(result);
			result	=	$.trim(result);
                $('.graph').html(result);
        },
		beforeSend: function(){
			// Code to display spinner
			//alert('l start');
			$('.loader-merchant').show();
		},
		complete: function(){
		// Code to hide spinner.
		//alert('end');
			$('.loader-merchant').hide();
		}		
		
    });
    return false;
}
