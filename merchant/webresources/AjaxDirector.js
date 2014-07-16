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
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200"  />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					else if(process_pram == 'merchant_photo'){			
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200"  />\n\
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

function calculateDiscountPrice() {
	var price = $('#Price').val();
	var discount_val = $('#Discount').val();	
	var discount_tier = $('#discounttier').html();	
	discount_tier = discount_tier.substring(0, discount_tier.length - 1);	
	if(discount_val == '1') {		
		var discount_price = parseFloat(price - ((price/100)*discount_tier));
		//alert(price+' '+discount_price+'  '+discount_tier)
		$('#DiscountPrice').val(discount_price);
		$('#discount_price').html('$'+discount_price);		
	}
	else {
		$('#DiscountPrice').val(price);
		$('#discount_price').html('$'+price);	
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
	if(check == 0) {
		$('.rowHide').fadeOut('slow');
		$('.rowshow').html('Monday to Sunday');
		$('#showdays').val('1');
		for(i=1;i<=6;i++) {
			$('#from1_'+i).val('');
			$('#to1_'+i).val('');
			$('#row_'+i).val('');
			$('#error_'+i).html('');
		}
	}else {
		$('.rowHide').fadeIn('slow');
		$('#showdays').val('0');
		$('.rowshow').html('Monday ');
	}
}

function setTime(id) {	
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
		from		=	fromhr+':'+frommin+' '+fromampm;
		to			=	tohr+':'+tomin+' '+toampm;
		$("#from1_"+id).val(from)
		$("#to1_"+id).val(to)
	}
}

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

function productCategoryHideShow(id) {
	$('#showmessage').slideUp('slow');
	$('#rowHide'+id).slideToggle('slow');
	
	if($('#rowHidden'+id).val() == 1){		
		$('#rowHidden'+id).val('0');
		$("#plusMinus"+id).removeClass('fa-caret-down');	
		$("#plusMinus"+id).addClass('fa-caret-up');	  
	}
	else {
		$('#rowHidden'+id).val('1');
		$("#plusMinus"+id).removeClass('fa-caret-up');	
		$("#plusMinus"+id).addClass('fa-caret-down');
	}
}

function hideShowOrders(id,name,photo,price,disprice) {
	$('#showmessage').slideUp('slow');
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
			text = '<tr id="orderrow'+id+'"><td><i class="fa fa-minus"   style="cursor:pointer" onclick="return addRemoveQuantity('+id+',2);"></i>&nbsp;<input type="text" id="quantity'+id+'" maxlength="3" name="quantity'+id+'" style="width:50px;text-align:center;" value="1" onkeypress="return isNumberKeyQuantity(event);" onkeyup="return addRemoveQuantity('+id+',3);">&nbsp;<i  style="cursor:pointer" class="fa fa-plus" onclick="return addRemoveQuantity('+id+',1);"></i><input type="hidden" id="imagePath'+id+'" name="imagePath'+id+'" value="'+photo+'" /></td>';
			text = text+'<td><img width="25" height="25" alt="" src="'+photo+'">&nbsp;&nbsp;'+name+'</td><td align="right">$<span id="orderPrice'+id+'">'+numberWithCommas(setprice)+'</span><input type="hidden" id="discountprice'+id+'" name="discountprice'+id+'" value="'+setprice+'"/><input type="hidden" id="originalprice'+id+'" name="originalprice'+id+'" value="'+price+'"/><input type="hidden" id="originalTotalprice'+id+'" name="originalTotalprice'+id+'" value="'+setprice+'"/><input type="hidden" id="orderItemName'+id+'" name="orderItemName'+id+'" value="'+name+'"/></td></tr>';
			$('#tbl1 tr').last().after(text);
			idsArray = prodIds.split(',');		
			for(i=0;i<idsArray.length;i++) {
				ordertot = ordertot + parseFloat($('#originalTotalprice'+idsArray[i]).val());
			}
			ordertot= ordertot.toFixed(2);
		}
		//ordertot= ordertot.toFixed(2);
		$('#Oders_Merchant').slideDown('slow');	
		$('#OrderTotal').val(ordertot);
		$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
		$('.OrderTotalShow').html(numberWithCommas(ordertot));  
		$("html, body").animate({ scrollTop: '0px' },{duration: 1000});
	//}
}

function addRemoveQuantity(id,type) {
	$('#showmessage').slideUp('slow');
	tot 	= $('#quantity'+id).val();
	price 	= parseFloat($('#discountprice'+id).val());
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
		$('#OrderTotal').val(ordertot);
		$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
		$('.OrderTotalShow').html(numberWithCommas(ordertot)); 
	}
	else if(tot < 1 && type == 2) {
		if(confirm('Are you sure to remove this product from order?')) {
			$('table#tbl1 tr#orderrow'+id).remove();
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
				$('#OrderTotal').val(ordertot);
				$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
				$('.OrderTotalShow').html(numberWithCommas(ordertot));
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
			$('#OrderTotal').val(ordertot);
			$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
			$('.OrderTotalShow').html(numberWithCommas(ordertot)); 
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
		$('#OrderTotal').val(ordertot);
		$('#order_submit').val('Charge $'+numberWithCommas(ordertot));
		$('.OrderTotalShow').html(numberWithCommas(ordertot));;
	}
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function hideShowUsers(id,photo,name,CurrentBalance) {
	$('#showmessage').slideUp('slow');
	current = $("#CurrentUserId").val();
	if($('#OrderProductIds').val() == '') {
		alert('Please select the products and then choose the customer.');
		return false;
	}
	
	if (current == id) {
		alert('This customer was already added.');
		return false;
	} else {	
		//if(confirm('Are you sure to make order for this user?')) {
			$('#Oders_Merchant').slideDown('slow');
			$("#userImage").attr('src', photo);
			$("#username").html(name);
			$("#bottom").html('Change customer');
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

function clearOrders() {
	$('#showmessage').slideUp('slow');
	if(confirm('Are you sure to clear orders?')) {		
		var prodIds = $('#OrderProductIds').val();
		idsArray = prodIds.split(',');
		for(i=0;i<idsArray.length;i++) {
			$('table#tbl1 tr#orderrow'+idsArray[i]).remove();
		}
		$('#OrderProductIds').val('');				
		$('#OrderTotal').val('');
		$('#order_submit').val('');
		$('.OrderTotalShow').html('');
		photo = $("#userDefaultImage").val();
		$("#userImage").attr('src', photo);
		$("#username").html('No Customer Selected');
		$("#bottom").html('Select Customer');
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
			}
		});	
	}
}

function checkBalance() {
	$('#showmessage').slideUp('slow');
	var prodIds = $('#OrderProductIds').val();
	idsArray = prodIds.split(',');
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
	}
	else if(order == 'asc'){
		$("#ordertype").val('desc');
	}
	else{
		$("#ordertype").val('desc');
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
	if(graghType=='3') {
		var chart_url = 'AjaxLineChart';//AjaxPieChart
	}else if(graghType=='2') {
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
        var loaderBlock = $('<div id="preloaderImage" style="filter:alpha(opacity=40);display:none;position:fixed;left:0px;top:0px;background-color:#000;layer-background-color:#ffffff;height:100%;width:100%;z-index:1000;"></div>');
    }
    else
    {
        var loaderBlock = $('<div id="preloaderImage" style="opacity:0.4;display:none;position:fixed;left:0px;top:0px;background-color:#000;layer-background-color:#ffffff;height:100%;width:100%;z-index:1000;"></div>');
    }
    loaderBlock.append('<div id="sub" style="text-align:center;margin-top:20%"><i class="fa fa-circle-o-notch fa-spin fa-lg text-olive "></i></div>');
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
	
	var searchPath = './Search?search=1';
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
	var searchPath  = './ajax_orders?orders=1';
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

function newSpecialRow(type,ele) {	
	currentid			=	$(ele).attr("id");
	var rowId 			= 	currentid.substr(currentid.length - 1);	
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
			if(confirm('Are you sure to Add another product?')) {	
				Totalrows 	= 	parseInt($('#Totalrows').val());
				Totalrows	=	Totalrows	+	1;
				var $clone 	= 	$("#copydiv").clone();
				$clone.attr({
					id		: "Row_" + Totalrows,
					name	: "Row_" + Totalrows,
					style	: ""
				});
				$clone.find("input,select,i").each(function(){
					$(this).attr({
						id		: $(this).attr("id") + Totalrows,
						name	: $(this).attr("name") + Totalrows,
						value	: ''
					});
				});
				//$clone = $clone+'<div class="col-xs-12 pad"></div>';
				$("#specialProducts").append($clone);
				//$("#specialProducts").append('<div class="col-xs-12 pad"></div>');
				
				hide1		=	Totalrows - 1;
				$("#plus"+hide1).hide();			
				$("#minus"+hide1).show();			
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
			}
		}
		else {
			alert("Please fill the current product.")
		}
	}
	else if(type == 2){
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
	rowId 			= 	currentid.substr(currentid.length - 1);
		
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
	Totalprice	=	$('#TotalPrice').val();
	price		=	$('#Price').val();
	if(Totalprice < price) {
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