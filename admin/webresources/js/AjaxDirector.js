if ("https:" == location.protocol)
    var protocolPath  = 'https://';
else
    var protocolPath  = 'http://';

if (window.location.hostname == '172.21.4.104') {
    var  path       = protocolPath + '172.21.4.104/tuplit/admin/';
    var actionPath	= protocolPath + '172.21.4.104/tuplit/admin/';
}
/*else {
	var  path = protocolPath+''+window.location.hostname+'/';
    var actionPath	= protocolPath+''+window.location.hostname+'/';

}*/


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
	var length		= node.attr("clone");
	var empty		=	0;
	
	$(ref).closest("#"+ref_id).find(".file_input").each(function() {
	 var x=document.getElementById(slide_val+length+"_upload");
		if(x == null)
			empty = 1;
	});
	if(empty == 0)
	{
		if(type == 1){
			var count		=	$("#home_center div").length;
		}
		else
			var count		=	$("#tutorial_center div").length;
		var length		= node.attr("clone");
		var tabindex	= $("#method").attr("tabindex");
		var clonedRow 	= node.clone(true);
		clonedRow.insertAfter(node);
		if(count >= 0) 	{
			count = (+length)+1;
			//var img_count	=	(+length)-1;
			//alert(count);
			if(type == 1){
				$(".Remove_Home_1").attr('style','');
			}
			else if(type == 2){
				$(".Remove_Tutorial_1").attr('style','');
			}
			clonedRow.attr("clone",count);
			clonedRow.find("input").val("");
			clonedRow.find("select").val(0);
			clonedRow.find("#"+slide_name+"_image_name").html(slide_name+ " Image "+count);
			clonedRow.find(".form-control").attr('id','order_'+count);
			clonedRow.find(".form-control").val(count);
			clonedRow.find(".Hidden4").val(count);
			clonedRow.find(".image_disp").attr('id',slide_val+count+'_img');
			clonedRow.find(".image_disp").attr('style','display:none');
			clonedRow.find(".file_input").attr('id',slide_val+count);
			clonedRow.find(".file_input").attr('name',slide_val+count);
			clonedRow.find(".hidd_1").attr('id','empty_'+slide_val+count);
			clonedRow.find(".hidd_1").attr('name','empty_'+slide_val+count);
			clonedRow.find(".hidd_2").attr('id','name_'+slide_val+count);
			clonedRow.find(".hidd_2").attr('name','name_'+slide_val+count);
			clonedRow.find(".hidd_3").attr('id',slide_val+count);
			clonedRow.find(".hidd_3").attr('name',slide_val+count);
			clonedRow.find(".Hidden4").attr('id','hidden_home_count_'+count);
			if(type == 1){
				clonedRow.find(".AddNew").attr('id','AddNew1_'+count);
				var new_img_count	=	count-1;
				$("#AddNew1_"+new_img_count).hide();
			}
			else if(type == 2){
				clonedRow.find(".AddNewSlider").attr('id','AddNew2_'+count);
				var new_img_count	=	count-1;
				$("#AddNew2_"+new_img_count).hide();
				
			}
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
		var new_count	 = $(ref).closest("#"+ref_id).attr("clone");
		var add_id		=	"AddHome_";
	}
	else{
		var count		=	$("#slider_tutorial div").length;
		var new_count	 = $(ref).closest("#"+ref_id).attr("clone");
		var add_id		=	"AddTutorial_";
	}
	if(new_count > 1)	
	{
		var bool	=	confirm("Are you sure to delete ?");
		if(bool){
			
			if(type == 1){
				add_count	=	$(ref).closest("#"+ref_id).attr("clone");
				new_counts	=	add_count-1;
				$("#AddNew1_"+new_counts).show();
			}
			else if(type == 2){
				add_count	=	$(ref).closest("#"+ref_id).attr("clone");
				new_counts	=	add_count-1;
				$("#AddNew2_"+new_counts).show();
			}
			$("#"+add_id+new_counts).show();
			//if(new_counts == 1)
				$(ref).closest("#"+ref_id).attr('style','display:none');
				$(ref).closest(".file_input").attr('id','');
				$(ref).closest(".file_input").attr('value','');
				$(ref).closest(".image_disp").attr('id','');
			//else
				//$(ref).closest("#"+ref_id).remove();
		}
	}
	//else if(new_count == 1){
		/*var bool	=	confirm("Are you sure to delete ?");
		if(bool){
			$(ref).closest("#"+ref_id).attr('style','display:none');
			add_count	=	$(ref).closest("#"+ref_id).attr("clone");
			//alert(add_count );
			new_count	=	add_count-1;
			$("#"+add_id+new_count).show();
		}*/
		
	//}
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
//alert(process_pram);
	/*if(process_pram == '' || process_pram == '')
		path = path+'../'*/
	var loadingIMG  =  '<span class="photo_load load_upimg text-align"><i class="fa fa-spinner fa-spin fa-lg"></i></span>';	
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
					 $("#"+process_pram+"_img").html('');
					
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
					if(process_pram == 'merchant_photo'){						
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
					//alert(process_pram);	
					//alert(img)					
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
		if(type == 1){
		 	var remove_id	=	"exist_id_home_";
			var delete_id	=	"hidden_home_count";
			var add_id		=   "AddHome_";
		 }
		else{
			var remove_id	=	"exist_id_tutorial_";
			var delete_id	=	"hidden_tutorial_count";
			var add_id		=   "AddTutorial_";
		 }
		 
		 var del_count	=	$("#"+delete_id).val() ;
		if(del_count > 1){
			var bool	=	confirm("Are you sure to delete ?");
			if(bool){
				$.ajax({
			        type: "POST",
			        url: actionPath+"models/AjaxAction.php",
			        data: 'action=DELETE_SLIDER&idValue='+id_val,
			        success: function (result){
					
						if(result = 1){							
							//$("#"+remove_id+id_val).animate({height: 'toggle', opacity: 'toggle'}, "slow");
							
							$("#center div").length  = $("#center div").length - 7;	
							
							
							var new_count	=	del_count-1;
							$("#"+delete_id).val(new_count);
							add_count	=	document.getElementById(remove_id+id_val).getAttribute('clone');
							var new_count	=	add_count-1;
							$("#"+add_id+new_count).show();
							//$("#"+remove_id+id_val).hide();
							//if(new_count == 1)
								$("#"+remove_id+id_val).attr('style','display:none');
							//else
								//$("#"+remove_id+id_val).remove();
							//window.location="SliderImages";
						}
						
						//alert(result);
			        }			
			    });
			}
			
	}else{
	
	
	alert("Atleast one row is required");
	
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
function Chkempty(count,type){
if(type == 1)
	var img_val	=	$("#empty_Slider_Image_"+count).val();
else
	var img_val	=	$("#empty_Tutorial_Image_"+count).val();
	if(count == 1 && img_val == ''){
	    if(type == 1)
			alert("Slider images should not be empty");
		else
			alert("Tutorial images should not be empty");
		return false;
	}
else
	return true;

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
	}
	$("#Category").children("option[value ='']").attr('selected', true);
	$('option[disabled="disabled"]').each(function () {
         $(this).attr('style', 'color:#cccccc;font-style:italic');
	});
}
function removeCategory(id) {
	if(confirm('Are you sure to Remove?')) { 		
		$("#cat_id_"+id).css('display','none');
		$("#Category").children("option[value =" +id+ "]").show();
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
 function advanceShow() {	
	$('#asearch').toggle('slow');	
	//<?php if(isset($_SESSION['hideshow'])) unset($_SESSION['hideshow']); else $_SESSION['hideshow']='true';?>	
}
function ChangeImageName(type,vals,idval){
  	setOrderingSlider(vals,0,type);
	//alert(idval);
	if(type == 1)
		var slide_id	=	"Slider_Image_";
	else
		var slide_id	=	"Tutorial_Image_";
	if(idval != '' ){
		var separated_val = idval.split('_');	
		var image_id		=	separated_val[1];
		//alert(image_id);
		$('#'+slide_id+image_id).attr('name',slide_id+vals);
		$('#'+slide_id+image_id).attr('id',slide_id+vals);
		$('#'+slide_id+image_id+'_img').attr('id',slide_id+vals+'_img');
		$('#'+slide_id+image_id+'_upload').attr('name',slide_id+vals+"_upload");
		$('#name_'+slide_id+image_id).attr('name','name_'+slide_id+vals);
		$('#'+slide_id+image_id).attr('name',slide_id+vals);
	}
}

function updateTime(id) {
	from = $('#from_'+id).val();
	to = $('#to_'+id).val();
	//alert(from+'  '+to)
	if(from == '00' && to == '00')
		$('#set_'+id).val('');
	else if(from != '00' || to != '00')
		$('#set_'+id).val('1');
}
function calculateDiscountPrice() {
	var price 			= $('#ItemPrice').val();
	var discount_tier 	= $('#DiscounTtier').val();	
	var discount_val	= parseInt($("input[name=DiscountApplied]:checked").val());
	//iscount_tier 		= discount_tier*10;	
	if(discount_val == '1') {		
		var discount_price = parseFloat(price - ((price/100)*discount_tier));
		$('#DiscountPrice').val(discount_price);		
	}
	else {
		$('#DiscountPrice').val('0');	
	}	 
}
function getProductCategory(merchant_id){
	var discount	=	 $("#Merchant option:selected").attr("discount_val");
	$('#DiscounTtier').val(discount);		
	$('#ItemPrice').val('');
	$('#DiscountPrice').val('');	
	//alert(discount);
	$.ajax({
        type: "POST",
        url: actionPath+"models/AjaxAction.php",
        data: 'action=GET_PRODUCT_CATEGORY&m_id='+merchant_id,
        success: function (result){
			$('#category_box').html(result);
        }			
    });
}

function hideAllDays() {
	var check = $('#showdays').val();	
	if(check == 0) {
		$('.rowHide').fadeOut('slow');
		$('.rowshow').html('Monday to Sunday : ');
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
		$('.rowshow').html('Monday : ');
	}
}

function setTime(id) {	
	from = $('#from1_'+id).val();
	to = $('#to1_'+id).val();
	//alert(from+'  '+to)
	if(from == '' && to == '')
		$('#row_'+id).val('');
	else if(from != '' || to != '')
		$('#row_'+id).val('1');
}



