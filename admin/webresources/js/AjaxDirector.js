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


function showHideInputParam() {
	var value = $("#method").val();
		if(value == "GET" || value == "POST") {
			$("#inputParamDefault").hide();
			$("#inputParamMultiple").show();
		}
		else {
			$("#inputParamDefault").show();
			$("#inputParamMultiple").hide();
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
					/*if($('#'+process_pram+'_upload').val() == '')
						$("#empty_"+process_pram).val(hiddenVal);
					
					 $("#empty_"+process_pram).val(hiddenVal);
					 $("#"+process_pram+"_img").html('');*/
					
                }else
                {
					if(hiddenVal=='') {
						$("#empty_"+process_pram).val(1);
					}
					var result	=	data.msg.split("####");
					var image_name = process_pram.substr(0,16);
					if(process_pram == 'cover_photo'){
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200" />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					if(process_pram == 'merchant_photo'){						
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="200" />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />'; 	
					}
					else if(process_pram == 'icon_photo'){
						var img	='<img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="75" height="75" />\n\
                                        <input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';
					}
					else if(image_name == 'Slider_Image_Old') {
						var img	='<a href "'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" class="fancybox" ><img  src="'+path+'/webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="145" height="145" />\n\<input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" /></a>';
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
/*sendNotification = function(frmname)
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
}*/
sendNotification = function(frmname)
{
	flag=0;
	var message = $('#message').val();
	if(message == ''){
		alert('Enter the message');
		return false;
	}
	else {
		$('#message_hidden').val(message);
		if(confirm('Are you sure to send notification?')) {
			frmname.submit();
			//$.fancybox.close(); 	
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
		$('#DiscountPrice').val(price);	
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


function selectPrice(product_value) {
	var form_name 	 = document.getElementById('Products_List');
	for (i=0;i<form_name.options.length;i++) {
			form_name.options[i].selected = false;
	}
}
jQuery(function() {
	jQuery("div.row-actions a").hide();
	jQuery('table.table-hover tr[id^=test_id_]').hover(function() {
		jQuery(this).find("div.row-actions a").css("visibility","visible");
   	}, function() {
       	jQuery(this).find("div.row-actions a").css("visibility","hidden");
   	});
});

 function requestchartData(date1,date2){
   
    $.ajax({
      type: "GET",
     // dataType: 'json',
     // url: sitePath+"./api", // This is the URL to the API
	  url: actionPath+"models/AjaxAction.php",
	  data: 'action=DRAW_CHART&start_date='+date1+'&end_date='+date2
      //data: { action:chart }
    })
    .done(function( data ) {
      // When the response to the AJAX request comes back render the chart with new data
      //chart.setData(data);
	  alert(data);
    })
    .fail(function() {
      // If there is no communication between the server, show an error
      alert( "error occured" );
    });
  }
  function pushNotificationCheck(ref) {
	var flag       = 0;
	var Title_flag = 0;
	$('.pnclass').attr('href','');
	$('.pnclass').removeClass('notification_popup');
		
	$("input[name='checkdelete[]']").each(function(){		
		if($(this).attr('checked')){
			flag = 1;
		}
	});
	if(flag == 1) {
		/*$(".dapskip_pop_up").colorbox({
			data:$("#UserListForm").serialize(),
			width:"75%",
			height:"55%",
			open:true
		});
		return true;*/
		$('.pnclass').attr('href','SendPushNotification?'+$("#UserListForm").serialize());
		$('.pnclass').addClass('notification_popup');
		$(".notification_popup").fancybox({
			type:"iframe", 
			width:"700", 
			height:"100%",
			 onClosed: function () {
            	$('.pnclass').attr('href','');
				$('.pnclass').removeClass('notification_popup');
				window.location.href = actionPath+'UserList#sendPush';
        	}
		});
		/*$(".dapskip_pop_up").onClosed({
			$('.pnclass').attr('href','');
		});*/
		//$(".user_photo_pop_up").colorbox();
		
	}
	else {
		alert('Select atleast a single record');
		return false;
	}
}

function saveContent(id) {
	var content 	=  escape(tinyMCE.activeEditor.getContent());
	var filename 	=  $('#ContentName').val();
	var ContentUrl 	=  $('#ContentUrl').val();
	//alert(content);
	if(id == 'Save'){
		dataurl		=	"ContentManage?action=Save";
		datapost	=	"Content="+content+"&filename="+filename+"&ContentUrl="+ContentUrl;
	}
	else {
		dataurl		=	"ContentManage?action=Update";
		datapost	=	"Content="+content+"&filename="+filename+"&id="+id+"&ContentUrl="+ContentUrl;
	}
	if(content != '' && filename != '' && ContentUrl != '') {
		$.ajax({
			type	: 	"POST",
			url		: 	dataurl,
			data	: 	datapost,
			success	: 	function (result){
				if(result == 1) {
					$('#alreadyExists').hide();
					window.location.href = "ContentList?msg=1";
				}
				else if(result == 2) {
					$('#alreadyExists').show();
				}
				else if(result == 3) {
					window.location.href = "ContentList?msg=2";
				}
				else if(result == 4) {
					$('#idNotExists').show();
				}
			}			
		});
	}
	return false;
}

function deleteAllContent(){
	var flag       = 0;
	$("input[name='checkdelete[]']").each(function(){		
		if($(this).attr('checked')){
			flag = 1;			
		}
	});
	if(flag == 0){
		alert('Select atleast a single record');
		return false;
	}	
	if(flag == 1 ){
		if(confirm('Are you sure to delete?'))
			return true;
		else
			return false;
	}
}

function locationAlreadyExist() {
	code	=	$('#LocationCode').val();
	name	= 	$('#LocationName').val();
	id 		=	$('#location_id').val();
	ajaxval	=	$('#ajax').val();
	if(ajaxval != '') {
		return true;
	} else {
		if(code != '' && name != '') {
			if(id != '')
				datapost	=	"LocationCode="+code+"&LocationName="+name+"&idedit="+id;
			else
				datapost	=	"LocationCode="+code+"&LocationName="+name;
			$.ajax({
				type	: 	"POST",
				url		: 	'LocationManage?ajax=1',
				data	: 	datapost,
				success	: 	function (result){
					if(result == 1) {
						$('#ajax').val('1');
						$('#add_location_form').submit();
					}
					else if(result == 2) {
						$('#error2').html(' Location code already exists')
						$('#error1').show();
						$('#success1').hide();
					}
					else if(result == 3) {
						$('#error2').html(' Location name already exists')
						$('#error1').show();
						$('#success1').hide();
					}
				}			
			});
		}
	}	
	return false;
}
function currencyAlreadyExist() {
	Location	=	$('#Location').val();
	code		=	$('#CurrencyCode').val();
	name		= 	$('#CurrencyName').val();
	id 			=	$('#Currency_id').val();
	ajaxval		=	$('#ajax').val();
	delStatus	= 	$('#deleteStatus').val();
	if(delStatus == 1){
		window.location.href = actionPath+'CurrencyManage?curDelId='+id;
		return false;
	} else{
		if(ajaxval != '') {
			return true;
		} else {
			if(code != '' && name != '' && Location != '') {
				if(id != '')
					datapost	=	"CurrencyCode="+code+"&CurrencyName="+name+"&Location="+Location+"&Currency_id="+id;
				else
					datapost	=	"CurrencyCode="+code+"&CurrencyName="+name+"&Location="+Location;
				$.ajax({
					type	: 	"POST",
					url		: 	'CurrencyManage?ajax=1',
					data	: 	datapost,
					success	: 	function (result){
						if(result == 1) {
							$('#ajax').val('1');
							$('#add_currency_form').submit();
						}
						else if(result == 2) {
							$('#error2').html(' Currency already added for this location')
							$('#error1').show();
							$('#success1').hide();
						}
						else if(result == 3) {
							$('#error2').html(' Currency code already exists')
							$('#error1').show();
							$('#success1').hide();
						}
						else if(result == 4) {
							$('#error2').html(' Currency name already exists')
							$('#error1').show();
							$('#success1').hide();
						}
					}			
				});
			}
		}	
		return false;
	}
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
				//alert(result);
                $('.graph').html(result);
        }
    });
    return false;
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
function getCurrency() {
	var country		=	$('#Country').val();
	$.ajax({
		 url: actionPath+"models/AjaxAction.php",
        data: 'action=GET_CURRENCY_FROM_COUNTRY&country_name='+country,
        success: function (result){
			$('#Currency').val(result);
        }			
				
	});
}	

function typeSubmit() {
	$('#type').val('1');
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
function addRowWeb(ref)
{
	var field_name 	= "field_name_clone_";
	var sample_data = "sample_data_clone_";
	var explanation	= "explanation_clone_";
	var count 		= 0;
	var node 		= $(ref).closest("tr");
	var empty		=	0;
	$(ref).closest("table").find("tr").each(function() {
		var text 	=	$(this).find("input").eq(0).val();
		if(text == "")
			empty = 1;
	});
	if(empty == 0)
	{
		var length		= node.attr("clone");
		var tabindex	= $("#method").attr("tabindex");
		var clonedRow 	= node.clone(true);
		clonedRow.insertAfter(node);
		if(length >= 0) 	{
			count = (+length)+1;
			clonedRow.attr("clone",count);
			clonedRow.find("input").val("");
			clonedRow.find("select").val(0);
			clonedRow.find("textarea").text("");
			settabindex(tabindex);
		}
	}
	else
		alert("Please fill the row to add new row");
}
function delRowWeb(ref)
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
}

/*-------category list-------------*/
function seeMoreCategory()
{
	var start = $("#result_count").val();
	$.ajax({
	        type: "GET",
	        url: actionPath+"models/AjaxAction.php",
	        data: 'action=GET_CATEGORY_LIST&start='+start,
	        success: function (result){
				//alert(result);
				$("#category_list").append(result);
				
	        }			
	    });
}
/*-------comment list-------------*/
function seeMoreComments(commentsNo)
{
	var start = '';
	if(commentsNo == 1){
		start = 0;
	}else{
		start = $("#comments_result_count").val();
	}
	var merchantId	= $("#merchantId").val();
	//alert(merchantId);
	$.ajax({
	        type: "GET",
	        url: actionPath+"CommentList",
			 global: false,
	        data: 'action=GET_COMMENTS_LIST&merchantId='+merchantId+'&start='+start,
	        success: function (result){
				if(commentsNo == 1){
					$("#commentsList").html(result);
				}else{
					$("#commentsList").append(result);
					$('html, body').animate({scrollTop:$(document).height()}, 2000);
					return false;
				}
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
function callProductSlider(type){
	var productSlider = $("#productSlider").sudoSlider({ 
        effect: "slide",
        speed: 1500,
        customLink: false,
        controlsShow: true,
        controlsFadeSpeed: 400,
        controlsFade: true,
        insertAfter: true,
		ease: "swing",
        vertical: false,//slider effect 
		numeric:true,
		responsive:true,
		slideCount: 5,
        moveCount: 1,
        startSlide: 1,
		prevNext: true,//speed: 100,
		afterAnimation: function(slide){
			var total_products = $('#product_total_count').val();
			var display_products = $('#product_display_count').val();
            var totalSlides = productSlider.getValue('totalSlides');
			var currentslides = productSlider.getValue('currentSlide');
			var merchantId	= $("#merchantId").val();
			var total_pager = $('#productImages .numericControls li').size();
			/*console.log('--------currentslides-------------'+currentslides);
			console.log('---------total_pager------------'+total_pager);
			console.log('----------totalSlides-----------'+totalSlides);
			console.log('----------total_products-----------'+total_products);*/
			
			if(currentslides == total_pager && totalSlides < total_products && total_products > 5){
				var start = display_products;
				$.ajax({
			        type: "GET",
			        url: actionPath+"ProductImages",
			        data: 'action=GET_MORE_PRODUCTS&start='+start+'&merchantId='+merchantId,
			        success: function (result){
						//alert(result);
						if($.trim(result) != 'fails'){
							var obj = jQuery.parseJSON(result);
							$.each(obj, function(i, objects) {
								productSlider.insertSlide(objects, totalSlides, '');
								totalSlides =  (+totalSlides) + (+1);
							});
							display_products = (+display_products) + (+30);
							$('#product_display_count').val(display_products);
						}
						else{
							return false;
						}
			        },
					beforeSend: function(){
						// Code to display spinner
						$('.loader-merchant').show();
					},
					complete: function(){
						setTimeout( function() {
							$('.loader-merchant').hide();
						},100);
					}			
			    });
			}
         },
      });
	 return productSlider;
}
/*-------Merchant images list-------------*/
function callSlider(type){
	//alert(type);
	var sudoSlider = $("#slider").sudoSlider({ 
        effect: "slide",
        speed: 1500,
        customLink: false,
        controlsShow: true,
        controlsFadeSpeed: 400,
        controlsFade: true,
        insertAfter: true,
		ease: "swing",
        vertical: false,//new
		numeric:true,
		responsive:true,
		slideCount: 5,
        moveCount: 1,
        startSlide: 1,
		prevNext: true,
		afterAnimation: function(slide){
			var total_merchant = $('#image_total_count').val();
			var display_merchant = $('#image_display_count').val();
            var totalSlides = sudoSlider.getValue('totalSlides');
			var currentslides = sudoSlider.getValue('currentSlide');
			var curr = $('.current').attr('data-target');
			var total_pager = $('#merchantImages .numericControls li').size();
			
			/*console.log('--------currentslides-------------'+currentslides);
			console.log('---------total_pager------------'+total_pager);
			console.log('----------totalSlides-----------'+totalSlides);
			console.log('----------total_merchant-----------'+total_merchant);*/
			if(currentslides == total_pager && totalSlides < total_merchant && total_merchant > 5){
				var start = display_merchant;
				var search = $('#merchantsearch').val();
				var countValue = 1;
				//alert("===="+start);
				$.ajax({
			        type: "GET",
			        url: actionPath+"MerchantsImages",
			        data: 'action=GET_MORE_MERCHANTS&start='+start+'&search='+search+'&type='+countValue,
			        success: function (result){
						//alert(result);
						//alert(display_merchant);
						if($.trim(result) != 'fails'){
							var obj = jQuery.parseJSON(result);
							$.each(obj, function(i, objects) {
								sudoSlider.insertSlide(objects, totalSlides, '');
								totalSlides =  (+totalSlides) + (+1);
							});
							display_merchant = (+display_merchant) + (+30);
							$('#image_display_count').val(display_merchant);
						}
						else{
							return false;
						}
			        },
					beforeSend: function(){
						// Code to display spinner
						$('.loader-merchant').show();
					},
					complete: function(){
						setTimeout( function() {
							$('.loader-merchant').hide();
						},100);
					}		
			    });
			}
         },
      });
	 return sudoSlider;
}
/*-------Edit Merchant-------------*/
$( "#editMerchant" ).click(function() {
		var redirect = $("#delStatus").val();
		if($(".merchantImage").closest("li").hasClass("select")){
			var mer_id = $("#merchantId").val();
			window.location.href = actionPath+"MerchantManage?editId="+mer_id+"&back="+redirect;
		}else{
			alert("Please select merchant and then choose edit");
		}
});
/*-------Delete Merchant-------------*/
$( "#deleteMerchant" ).click(function() {
		//alert($("#merchantId").val());
		var redirect = $("#delStatus").val(); 
		if($(".merchantImage").closest("li").hasClass("select")){
			var mer_id = $("#merchantId").val();
			if(confirm('Are you sure to cancel membership?')){
				window.location.href = actionPath+"Merchants?delId="+mer_id+"&redirect="+redirect;}
			//return true;
				else
			return false;
		}else{
			alert("Please select merchant and then choose delete");
		}
});
/*-------Customer images list-------------*/
/*-------Edit Customers-------------*/
$( "#editCustomer" ).click(function() {
		if($(".customerImage").closest("li").hasClass("select")){
			var cust_id = $("#customerId").val();
			//url = actionPath+"UserManage?editId="+cust_id;
			//alert(url);
			//window.open(url, '_blank');
			window.location.href = actionPath+"CustomerManage?editId="+cust_id;
		}else{
			alert("Please select customer and then choose edit");
		}
});
/*-------Delete Customers-------------*/
$( "#deleteCustomer" ).click(function() {
		if($(".customerImage").closest("li").hasClass("select")){
			var cust_id = $("#customerId").val();
			if(confirm('Are you sure to suspend user?')){
				window.location.href = actionPath+"Customers?delId="+cust_id;}
			//return true;
				else
			return false;
		}else{
			alert("Please select customer and then choose delete");
		}
});
/*-------Customers transaction list-------------*/
function getCustomerTransaction(cust_id){
	var customer = "";
	customer		= cust_id;
	$.ajax({
	        type: "GET",
	        url: actionPath+"CustomerTransactionList",
	        data: 'action=GET_CUSTOMER_TRANSACTION&customerId='+customer,
	        success: function (result){
				//alert(result);
				$("#customerTransactions").html(result);
	        },
			beforeSend: function(){
				//alert('----');
				// Code to display spinner
				$('.loader-merchant').show();
			},
			complete: function(){
			// Code to hide spinner.
				/*alert('--hi--');
				$('.loader-merchant').hide();*/
				setTimeout( function() {
							$('.loader-merchant').hide();
				},100);
			}								
	});
	//return false;
}
function callCustomerSlider(type){
	//alert(type);
	var sudoSlider = $("#sliderC").sudoSlider({ 
        effect: "slide",
        speed: 2500,
        customLink: false,
        controlsShow: true,
        controlsFadeSpeed: 400,
        controlsFade: true,
        insertAfter: true,
		ease: "swing",
        vertical: false,//new
		numeric:true,
		responsive:true,
		slideCount: 5,
        moveCount: 1,
        startSlide: 1,
		prevNext: true,//speed: 500,
		afterAnimation: function(slide){
			var total_merchant = $('#customer_total_count').val();
			var display_merchant = $('#customer_display_count').val();
            var totalSlides = sudoSlider.getValue('totalSlides');
			var currentslides = sudoSlider.getValue('currentSlide');
			var curr = $('.current').attr('data-target');
			var total_pager = $('#Customers .numericControls li').size();
			if(currentslides == total_pager && totalSlides < total_merchant && total_merchant > 5){
				var start = display_merchant;
				var search = $('#customersearch').val();
				var countValue = 1;
				$.ajax({
			        type: "GET",
			        url: actionPath+"CustomerList",
			        data: 'action=GET_MORE_CUSTOMERS&start='+start+'&search='+search+'&type='+countValue,
			        success: function (result){
						if($.trim(result) != 'fails'){
							var obj = jQuery.parseJSON(result);
							$.each(obj, function(i, objects) {
								sudoSlider.insertSlide(objects, totalSlides, '');
								totalSlides =  (+totalSlides) + (+1);
							});
							display_merchant = (+display_merchant) + (+30);
							$('#customer_display_count').val(display_merchant);
						}
						else{
							return false;
						}
			        },
					beforeSend: function(){
						// Code to display spinner
						$('.loader-merchant').show();
					},
					complete: function(){
						setTimeout( function() {
							$('.loader-merchant').hide();
						},100);
					}			
			    });
			}
         },
      });
	 return sudoSlider;
}
/*-------Customers end-------------*/
/*-------Transaction list-------------*/
function getTransactionList(merchantId){
	var merchant = merchantId;
	$.ajax({
	        type: "GET",
	        url: actionPath+"MerchantTransactionList",
			global: false,
	        data: 'action=GET_TRANSACTION_LIST&merchantId='+merchant,
	        success: function (result){
				//alert(result);
				$("#merchantTransactions").html(result);
	        },
			beforeSend: function(){
				$('.loader-merchant').show();
			},
			complete: function(){
				seeMoreComments('1');
				//$('.loader-merchant').hide();
			}				
	});
}
/*-------Load transaction chart-------------*/
function loadTransactionChart()
{
   $.ajax({
        type: "POST",
        url : actionPath+"views/TransactionChart.php",
		data : "action=GET_CHART",
        success: function(result){
            result	=	$.trim(result);
            $('#transactionChart').html(result);
        }
    });
}
function uploadFiles(event){
	var process_pram = event.data.name;
	if(process_pram.search("Slider_Image")=='0'){
		var save_class = 'save';
		var delete_class = 'delete';
	}else if(process_pram.search("Tutorial_Image")=='0'){
		var save_class		= 'tutor_save'
		var delete_class	= 'tutor_delete';
	}
	files = event.target.files;
	event.stopPropagation();
	event.preventDefault();
	
	// Create a formdata object and add the files
	var data = new FormData();
	$.each(files, function(key, value)
	{
		data.append(key, value);
	});
	
	var loadingIMG  =  '<span class="photo_load load_upimg text-align" ><i class="fa fa-spinner fa-spin fa-lg"></i></span>';	
	$(loadingIMG).insertAfter($("#"+process_pram+"_img"));
	$("#"+process_pram+"_img").hide();	
	var hiddenVal = $("#empty_"+process_pram).val();
	image_name = document.getElementById(process_pram).value;
	image_name = image_name.replace(/C:\\fakepath\\/i,'');
	
	$.ajax({
		url: 'models/AjaxAdminFileUploadScript.php?files&filename='+process_pram,
		type: 'POST',
		data: data,
		cache: false,
		processData: false,
		contentType: false,
		success: function(data)
		{
			if(hiddenVal=='') {
				$("#empty_"+process_pram).val(1);
			}
			var json = JSON.parse(data);					
			if(json['error'] != false) {
				alert(json['error']);
				$('#error_cover_image').html(data.error);
				if($('#'+process_pram+'_upload').val() == '')
					$("#empty_"+process_pram).val(hiddenVal);
				
				 $("#empty_"+process_pram).val(hiddenVal);
				 $("#"+process_pram+"_img").html('');
				 $("#image_value").val('');
				 $("#image_value").html('');
			}				
			if(json['msg'] != '') {
				msg	=	json['msg'];
				var result	=	msg.split("####");
				if(process_pram.search("Slider_Image")=='0'){
					//var newdiv = $("#"+id+"_img").clone();
					//$('#image_body').append("<div class='"+process_pram+" up-img unsortable ' ><div id='"+process_pram+"_img' class='pad "+process_pram+"_url'></div></div>");	
					//$('#image_body').append("<div class='"+process_pram+" up-img unsortable ' ><div id='"+process_pram+"_img' class='pad "+process_pram+"_url'></div></div>");	
					$("<div class='"+process_pram+" up-img unsortable ' ><div id='"+process_pram+"_img' class='pad "+process_pram+"_url'></div></div>").insertBefore('#welcome_Slider_upload');
				}else if(process_pram.search("Tutorial_Image")=='0'){
					//var newdiv = $("#"+id+"_img").clone();
					$("<div class='up-img unsortable "+process_pram+"'><div id='"+process_pram+"_img' class='pad "+process_pram+"_url '></div></div>").insertBefore('#welcome_Tutorial_upload');	
				}
				 img ='<a href = "./webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" class="fancybox" ><img  src="./webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'" width="145" height="145" class="img_border" />\n\<input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />\n\<input type="hidden" name="delete_'+process_pram+'" id="delete_'+process_pram+'" class="delete_'+process_pram+'" value="" /></a><div  width="200" height="50" class="'+save_class+'" id="'+process_pram+'_save"><i class="fa fa-plus-circle  fa-lg"></i></div><div  width="200" height="50" class="'+delete_class+'" style="display:none;" id="'+process_pram+'_delete"><i class="fa fa-minus-circle fa-lg"></i></div>';
				/*img		=	'<img width="145" height="145" src="./webresources/uploads/temp/'+result[0] +'.'+ result[1]+'?rnd='+Math.random()+'">'
						+'<input type="hidden" name="'+process_pram+'_upload" id="'+process_pram+'_upload" value="'+result[0] +'.'+ result[1]+'" />';*/						
				$("#"+process_pram+"_img").html(img);
				$("#no_"+process_pram).remove();
				$("#image_value").val(image_name);
			}
			 $(".photo_load").remove();
			$("#"+process_pram+"_img").show();
			return true;
		}
	});
}
function delete_image($image,$remove_id){
	data ={image:$image},
	$.ajax({
            url		: actionPath+"models/AjaxAction.php?action=Delete_Image",
			data 	: data,
           	type	: 'POST',
            success: function(response) {
				if(response == 'Deleted'){
					$(remove_id).css('display','none');
				}
			}
		});
 }
function save_image($image_id,$imgorder,$image,$old_image,my_class,id){
	$.ajax({
            url	:actionPath+"models/AjaxAction.php",
			data:{action:'Save_Image',old_img:$old_image,save_image_name:$image_id,imgorder:$imgorder,image:$image},
           	type: 'POST',
			success:function(response){
				var msg = $.parseJSON(response); 
				$("."+my_class).attr('id','Slider_Image_Old_'+msg.insertid);
				$("."+my_class).removeClass('unsortable');
				$("#delete_"+my_class).val(msg.insertid);
				$(id).attr('href',msg.url);
				$(id+" img").attr('src',msg.url);				
			}
				
		});
}
$(document).ready(function() {
	$('.fancybox').fancybox();
	$(".sortable").sortable({
		cancel	: ".unsortable",
		cursor	: "move",
		grid	: [ 20, 10 ],
		helper	: "clone",
		opacity	: 0.4,
		revert	: 10,
		update	:function(event,ui){
			var data = $(this).sortable('serialize');
			alert(data);
			$.ajax({
            data:{"my_order": data},
            type: 'POST',
            url: actionPath+"models/AjaxAction.php"
        });
		}
	});
	$( ".sortable" ).disableSelection();
	$(".file_input_old,.tutorial_input_old").live('change',function(event){
		my_id = $(this).attr('name'),
		event.data = {name : my_id},
		uploadFiles(event);
		document.getElementById(my_id).style.visibility = 'hidden';
		var id_value = my_id.substr(my_id.lastIndexOf('_')+1);
		++id_value;
		if(my_id.search("Slider_Image")=='0'){
			var id_name		= 'Slider_Image_Old_'+id_value;
			$('#file_input').append("<input type='file'  class='file_input_old'  name='"+id_name+"' id='"+id_name+"' title='Home Slider' />");
		}else if(my_id.search("Tutorial_Image")=='0'){
			var id_name		= 'Tutorial_Image_Old_'+id_value;
			$('#tutorial_input').append("<input type='file'  class='tutorial_input_old'  name='"+id_name+"' id='"+id_name+"' title='Home Slider' onclick=''/>");
		}
	});
	$(".save,.tutor_save").live('click',function(){
		var old_image	= '';
		value		= $(this).attr('id');
		extract		= value.substr(0,$(this).attr('id').lastIndexOf('_'));
		number		= extract.substr(extract.lastIndexOf('_')+1);
		id = "."+extract+"_url a";
		hidden_id 	= '#'+extract+"_upload";
		if(document.getElementById("name_"+extract)){
			old_image	= $("#name_"+extract).val();
		}
		add_id			= "#"+extract+"_delete";		
		$image_id 		= $(hidden_id).val();		
		$imgorder		= number;
		extract1	= extract.substr(0,extract.lastIndexOf('_'));
		$image		 	= $image_id.substr(extract1.length+1); 
		save_image($image_id,$imgorder,$image,old_image,extract,id);
		document.getElementById(value).style.visibility = 'hidden';
		$(add_id).css('display','block');
	});
	$(".delete,.tutor_delete").live('click',function(){
		value		= $(this).attr('id');
		extract		= value.substr(0,$(this).attr('id').lastIndexOf('_'));		
		$image_id 	= $("#delete_"+extract).val();
		remove_id 	= "#"+extract+"_img";
		//extract1	= extract.substr(0,extract.lastIndexOf('_'));
		//$image		= $image_id.substr(extract1.length+1);
		if(confirm('Are you sure to delete this image?')){
			delete_image($image_id,remove_id);
		}
	});
});
function showTag(ele,val){	
	$(".tabdata textarea").removeClass('fancy');
	var currenttab = $(ele).attr('id');
	$('#tabs td').removeClass('active');
	$(ele).parent().addClass('active');
	$(".tabdata").not('#'+currenttab+'_div').hide("fast");
	$('#'+currenttab+'_div textarea').addClass('fancy');
	if($('#'+currenttab+'_div').is(':hidden')){
		$('#'+currenttab+'_div').show("fast");
	}
}



//$('.tabsection').children().hide();
$('.tabsection').click(function(){
	var id_val = $(this).attr('id');
	//$(this).children().slideToggle().toggleClass("h2-active");
	//alert(id_val);
	$('#'+id_val+'_block').slideToggle(function(){
		if($('#'+id_val+'_block').is(':hidden')){
			$('#'+id_val).removeClass('active');
			
		}
		else{
			$('#'+id_val).addClass('active');
			
		}
	});
	
	
});
/*var parentDivs = $('.tabsection').next();
$('.tabsection').children().hide();
$('.tabsection').click(function(){
	$('.tabsection').children().hide();
	var $this	= $(this);
	var h2_chilren	= $this.children();
	parentDivs.slideUp();
	if(h2_chilren.is(':hidden')){
		h2_chilren.show();
	}
	else{
		h2_chilren.hide();
	}
	if($this.next().is(':hidden')){
		 $this.next().slideDown();
		}else{
		 $this.next().slideUp();
		}
})*/


/*function save(id){
	if(id.search('save_content')=='0'){
		content_id		= 	$(".fancy").attr('id')	
		var content		= 	tinymce.get(content_id).getContent();
		alert($("#"+content_id+"_id").val());
		var	data_id     = 	$("#"+content_id+"_id").val().split("#");
	}else{
		my_id 			= 	id.split("-");
		var content 	= 	tinymce.get('text-'+my_id[0]).getContent();
		alert($("#"+my_id[0]+"_id").val());
		var data_id		= 	$("#"+my_id[0]+"_id").val().split("#");
	}
	//alert(data_id);
	//alert(data_id[0]);
	//alert(data_id[1]);
	//alert(data_id[2]);
	url					=	actionPath+"Models/AjaxAction.php?action=Save-Content";
	//data				=   "Content="+content+"&id="+data_id[0]+"&filename="+data_id[1]+"&ContentUrl="+data[2];
	//alert(data);
	$.ajax({
		type	: 	"POST",
		url		: 	url,
		data	: 	data,
		success : function(result){
		 //alert(result);
		}
	});
}*/
function save(id){
	if(id.search('save_content')=='0'){
		content_id		= 	$(".fancy").attr('id')	
		var content		= 	tinymce.get(content_id).getContent();
		var	data_id     = 	$("#"+content_id+"_id").val().split("#");
		$("#"+content_id+"_hidden").html(content);
	}else{
		my_id 			= 	id.split("-");
		var content 	= 	tinymce.get('text-'+my_id[0]).getContent();
		var data_id		= 	$("#"+my_id[0]+"_id").val().split("#");
		$("#"+my_id[0]+"_hidden").html(content);
	}
	url					=	actionPath+"models/AjaxAction.php?action=Save-Content";
	if($.isArray(data_id) && data_id.length>1){
		data				=   "Content="+escape(content)+"&id="+data_id[0]+"&filename="+data_id[1]+"&ContentUrl="+data_id[2];
		$.ajax({
			type	: 	"POST",
			url		: 	url,
			data	: 	data,
			success : function(result){
				//alert(result);
			}
		});
	}
}

/*-------Merchant/Customer list-------------*/
function showMoreMerchantCustomer(idVal)
{
	var type = $("#define_search_type").val();
	var display = '';
	//alert(idVal);
	//alert(type);
	if(type == 2){
		display = 'Merchant';
	}else if(type == 3){
		display	= 'Customer';
	}
	var start = $("#"+display+"_result_count_"+idVal).val();
	//alert(start);
	$.ajax({
	        type: "GET",
	        url: actionPath+"models/AjaxAction.php",
	        data: 'action=MERCHANT_CUSTOMER_TRANSACTION&start='+start+'&type='+type+'&idVal='+idVal,
	        success: function (result){
				//alert(result);
				$("#transaction_list_"+idVal).append(result);
	        },
			beforeSend: function(){
				// Code to display spinner
				$('.loader').show();
			},
			complete: function(){
			// Code to hide spinner.
				$('.loader').hide();
			}				
	    });
}
function approve(value){
	if(confirm('Do you want to approve this merchant?')){
		var id	  = $("#"+value+"_value").val();
		//alert(id);
		var num = parseInt($('#merchant_approve').text().match(/\d+/)[0], 10);
		--num;
		$.ajax({
			type	: "POST",
			url		: actionPath+"models/AjaxAction.php?action=APPROVE_MERCHANT",
			data	: {"id":id},
			success	: function(response){
				$("#"+value).hide();
				if(num!=0)
					$('#merchant_approve').text($('#merchant_approve').text().replace(/-?[0-9]*\.?[0-9]+/, num)); 
				else
					$('#merchant_approve').text('');
			}
		});
	}
}

/*function getMerchantLocation(merchant_id){
	$.ajax({
        type: "POST",
        url: actionPath+"models/AjaxAction.php",
        data: 'action=GET_MERCHANT_LOCATION&id='+merchant_id,
        success: function (result){
			$('#category_box').html(result);
        }			
    });
}*/

