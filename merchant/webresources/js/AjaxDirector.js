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

function ajaxAdminFileUploadProcess(process_pram)
{	
	var loadingIMG  =  '<span class="photo_load load_upimg"><i class="fa fa-circle-o-notch fa-spin fa-lg text-olive "></i></span>';	
    $(loadingIMG).insertAfter($("#"+process_pram+"_img"));
    $("#"+process_pram+"_img").hide();	
	var hiddenVal = $("#empty_"+process_pram).val();
    $.ajaxFileUpload
    ({
        url:actionPath+'models/DoAjaxAdminFileUpload.php',
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
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200" height="75" />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					else if(process_pram == 'merchant_photo'){			
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200" height="100" />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />'; 	
					}
					else if(process_pram == 'icon_photo'){
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="75" height="75" />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					else{
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="75" height="75" />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					
					$("#"+process_pram+"_img").html(img);
	                $("#no_"+process_pram).remove();
				}
                $(".photo_load").remove();
                $("#"+process_pram+"_img").show();
				
				//alert(process_pram);
				/*if(process_pram == 'merchant_photo') {
					var name = $("#merchant_photo_upload").val();
					if(name != 'undefind')
						$("#old_merchant_photo").val(name);
				}
				if(process_pram == 'icon_photo') {
					var name = $("#icon_photo_upload").val();
					if(name != 'undefind')
						$("#old_icon_photo").val(name);
				}*/
            }
        },
        error: function (data, status, e)
        {
           alert(e);
        }
    });

    return false;
}
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
	$(".popup_content").html($("<iframe id='listing' />").attr("src", src).attr("allowfullscreen", true));
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
function removeCategory(id) {
	if(confirm('Are you sure to Remove?')) { 		
		$("#cat_id_"+id).css('display','none');
		$("#Category").children("option[value =" +id+ "]").removeattr('disabled');
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

function Cal_DiscountPrice() {
	var price = $('#Price').val();
	var discount_val = $('#DiscountTier').val();
	var discount = $("#DiscountTier option:selected").text();
	if(price != '' && discount_val != '') {	
		discount = parseInt(discount.substring(0, discount.length - 1));
		var discount_price = 0;			
		discount_price = price - ((price/100)*discount);
		$('#DiscountPrice').val(discount_price);		
	 } else if(price != '' && discount_val == '') {
		$('#DiscountPrice').val('');
	 } else if(price == '') {
		$('#DiscountPrice').val('');
		//$("#DiscountTier").children("option[value ='']").attr('selected', true);
	 }
	 
}

function hideShowPrice() {
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
}

/*function selectProduct(product_value) {
	alert(product_value)
}*/
