/*if ("https:" == location.protocol)
    var protocolPath  = 'https://';
else
    var protocolPath  = 'http://';

if (window.location.hostname == '172.21.4.104') {
    var  path = protocolPath + '172.21.4.104/simplyshredded/admin/';
    var actionPath	= protocolPath + '172.21.4.104/simplyshredded/admin/';
}
else {
	var  path = protocolPath+''+window.location.hostname+'/admin/';
    var actionPath	= protocolPath+''+window.location.hostname+'/admin/';

}*/
$(document).ready(function(){

 $.validator.addMethod("nameRegexp", function(value, element) {	
		return this.optional(element) || !(/:|\?|\\|\*|\"|<|>|\||%/g.test(value));
    });
//--------------Login ----------------Start
$("#admin_login_form").validate({
	rules:{
		user_name		:	{ required:true},
		password		:	{ required:true}
	},
	messages:{
		user_name		:	{ required:'Username is required' },
		password		:	{ required:'Password is required'}
	}

});
//--------------Login----------------End

//--------------Forget Password---------------start
$("#forget_password_form").validate({
	rules:{
		email       :	{ required:true,email:true }
		// email          :	{ required:true,email:true },
         },
	messages:{
		email       :	{ required:'Email address is required',email:'Please enter a valid email address.'}
		//email      :	{ required:'Email Address is required',email:'Please enter a valid email address.'},
	}
});
//--------------Forget Password----------------End
//--------------Change Password---------------start
$("#change_password_form").validate({
	rules:{			
		old_password        :	{ required:true},
           new_password     	:   { required:true,minlength:5},
           confirm_password    :	{ required:true,minlength:5, equalTo:'#new_password'}
	},
	messages:{
		old_password		:	{ required:'Old Password is required' },
		new_password		:	{ required:'New Password is required',minlength:'New Password should have atleast 5 characters'},
		confirm_password    :   { required:'Confirm Password is required',minlength:'Confirm Password should have atleast 5 characters',equalTo:'Password mismatch' }
	}
});
//--------------Change Password----------------End
//--------------General Settings---------------start
$("#general_settings_form").validate({
	rules:{			
		email       :	{ required:true,email:true },
		fees       :	{  fees_amount:true }
	},
	messages:{
		email       :	{ required:'Email address is required',email:'Please enter a valid email address.'},
		fees       :	{ fees_amount:'MangoPay fees is required'}
	}
});
$.validator.addMethod("fees_amount", function(value, element) {
	 if($('#fees').val() > 0)  
   		 return true;
	 else
	  	return false
});
//--------------Change Password----------------End
//--------------CMS---------------start
$("#cms_form").validate({
	rules:{			
		cms_about       :	{ required:true},		
		cms_privacy     :	{ required:true},		
		cms_terms       :	{ required:true}			
	},
	messages:{
		cms_about       :	{ required:'About is required'},
		cms_privacy     :	{ required:'Privacy Policy is required'},
		cms_terms       :	{ required:'Terms of Service is required'}
	}
});
//--------------CMS----------------End
//--------------Add User---------------start
$("#add_user_form").validate({
	rules:{
		FirstName          :	{ required:true},
		LastName           :	{ required:true},
		UserName           :	{ required:true },
		Email        	   :	{ required:true,email:true},
		Password	       :    { required:true,minlength:6},
         C_Password		   :	{ required:true,minlength:6,equalTo:'#Password'},
		PinCode			   :	{ required:true,minlength:4},
		CellNumber 		   :    { mobile_format:true}
	},
	messages:{
		FirstName       	:	{ required:'First Name is required'},
		LastName       		:	{ required:'Last Name is required'},
		UserName          	:	{ required:'Username is required'},
		Email				:	{ required:'Email is required' },
		Password			:	{ required:'Password is required',minlength:'Password should have atleast 6 characters'},
		C_Password		    :   { required:'Confirm Password is required',minlength:'Confirm Password should have atleast 6 characters',equalTo:'Password mismatch' },
		PinCode				:	{ required:'PIN Code is required',minlength:'PIN Code must have 4 numbers' },
		CellNumber 		    :    { mobile_format:'Cell Number should have atleast 10 numbers'}
		/*CellNumber			:	{ required:'Cell Number is required' }
		empty_user_photo	:	{ required:'User Image is required'},
		empty_cover_photo	:	{ required:'Cover Image is required'},
		fb_id				:	{ required:'Facebook Id is required' },
		twitter_id    		:   { required:'Twitter Id is required' }*/
	}
});
$.validator.addMethod("mobile_format", function(value, element) {
	 if($('#CellNumber').val() == '')  
   		 return true;
	else if($('#CellNumber').val().length < 10)  
   		 return false;
	 else
	  	return true
});
//--------------Add user----------------End
//--------------Add Hash Tag ---------------start
$("#add_hashtag_form").validate({
	rules:{
		hash_tag_name       :	{ required:true},			
	},
	messages:{
		hash_tag_name       :	{ required:'Hashtag is required'},			
	}
});
//--------------Add Hash Tag----------------End

//--------------Add Service---------------start
$("#add_service_form").validate({
	rules:{
		process			:	{ required:true},			
		service_path	:	{ required:true},       
		method			:	{ required:true},
		module_name		:	{ required:true},
		output_param	:	{ required:true}
	},
	messages:{
		process       	:	{ required:'Purpose is required'},
		service_path	:	{ required:'Endpoint is required'},
		method			:	{ required:'Method is required'},
		module_name		:	{ required:'Module Name is required'},
		output_param	:	{ required:'Output param is required'}
	}
});
//--------------Add Service----------------End
//--------------Add Category ---------------start
$("#add_category_form").validate({
	rules:{
		CategoryName      		 :	{ required:true},			
		empty_category_photo	 :	{ required:true}
	},
	messages:{
		CategoryName       :	{ required:'Category Name is required'},	
		empty_category_photo	 :	{ required:'Category Icon is required'}		
	}
});
//--------------Add Category----------------End
//--------------Add Product Category----------------start
$("#add_product_category_form").validate({
	rules:{
		CategoryName      		 :	{ required:true},			
	},
	messages:{
		CategoryName       :	{ required:'Product Category Name is required'},	
	}
});
//--------------Add Product Category----------------end
//--------------Add User---------------start
$("#merchant_edit_form").validate({
	rules:{
		FirstName          :	{ required:true},
		LastName           :	{ required:true},
		CompanyName        :	{ required:true },
		Email        	   :	{ required:true,email:true},
		PhoneNumber	       :    { required:true,minlength:10},
        WebsiteUrl		   :	{ required:true,url:true},
		Location		   :	{ required:true },		
		Address		   	   :	{ required:true },
		Description		   :	{ required:true },
		ShortDescription   :	{ required:true },
		categorySelected   :	{ required:true },
		DiscountTier	   :	{ required:true },		
		empty_icon_photo	:	{ required:true },
		empty_merchant_photo		:	{ required:true },
		priceValidation		:	{ required:true,min_val:true},
		set_0				: {set_0:true},
		set_1				: {set_1:true},
		set_2				: {set_2:true},
		set_3				: {set_3:true},
		set_4				: {set_4:true},
		set_5				: {set_5:true},
		set_6				: {set_6:true}

	},
	messages:{
		FirstName       	:	{ required:'First Name is required'},
		LastName       		:	{ required:'Last Name is required'},
		CompanyName         :	{ required:'Company Name is required'},
		Email				:	{ required:'Email is required',email:'Please enter a valid Email address.'},
		PhoneNumber			:	{ required:'Phone Number is required',minlength:'Phone Number should have atleast 10 numbers'},
		WebsiteUrl		    :   { required:'Website url is required',url:'Please enter valid url'},
		Location			:	{ required:'Location is required'},		
		Address				:	{ required:'Address is required'},
		Description			: 	{ required:'Description is required'},
		ShortDescription   :	{ required:'Short Description is required' },
		categorySelected   :	{ required:'Category is required' },
		DiscountTier	   :	{ required:'Price Scheme is required' },
		empty_icon_photo		: 	{ required:'Icon is required'},
		empty_merchant_photo		: 	{ required:'Image is required'},
		priceValidation		:	{ required:'Price range is required',min_val:'Please enter valid price range'},
		set_0				: {set_0:'To hour must be greater then from hour'},
		set_1				: {set_1:'To hour must be greater then from hour'},
		set_2				: {set_2:'To hour must be greater then from hour'},
		set_3				: {set_3:'To hour must be greater then from hour'},
		set_4				: {set_4:'To hour must be greater then from hour'},
		set_5				: {set_5:'To hour must be greater then from hour'},
		set_6				: {set_6:'To hour must be greater then from hour'}
	}		
});

$.validator.addMethod("min_val", function(value, element) {	
	 if(parseInt($('#max_price').val()) == 0)
		 return false;
	 else if(parseInt($('#min_price').val()) == 0)
		 return false;
	 else if(parseInt($('#min_price').val()) > parseInt($('#max_price').val()))
		 return false;
	 else
		return true
});
$.validator.addMethod("set_0", function(value, element) {
	if($('#set_0').val() == '' || ($('#set_0').val() != '' && $('#to_0').val() == '00' && $('#from_0').val() == '00'))
		return true;
	else {
		if((parseInt($('#to_0').val()) < parseInt($('#from_0').val())) || (parseInt($('#to_0').val()) == parseInt($('#from_0').val())))
			return false;
		else
			return true;
	}
});	
$.validator.addMethod("set_1", function(value, element) {
	if($('#set_1').val() == '' || ($('#set_1').val() != '' && $('#to_1').val() == '00' && $('#from_1').val() == '00'))
		return true;
	else {
		if((parseInt($('#to_1').val()) < parseInt($('#from_1').val())) || (parseInt($('#to_1').val()) == parseInt($('#from_1').val())))
			return false;
		else
			return true;
	}
});	
$.validator.addMethod("set_2", function(value, element) {
	if($('#set_2').val() == '' || ($('#set_2').val() != '' && $('#to_2').val() == '00' && $('#from_2').val() == '00'))
		return true;
	else {
		if((parseInt($('#to_2').val()) < parseInt($('#from_2').val())) || (parseInt($('#to_2').val()) == parseInt($('#from_2').val())))
			return false;
		else
			return true;
	}
});	
$.validator.addMethod("set_3", function(value, element) {
	if($('#set_3').val() == '' || ($('#set_3').val() != '' && $('#to_3').val() == '00' && $('#from_3').val() == '00'))
		return true;
	else {
		if((parseInt($('#to_3').val()) < parseInt($('#from_3').val())) || (parseInt($('#to_3').val()) == parseInt($('#from_3').val())))
			return false;
		else
			return true;
	}
});	
$.validator.addMethod("set_4", function(value, element) {
	if($('#set_4').val() == '' || ($('#set_4').val() != '' && $('#to_4').val() == '00' && $('#from_4').val() == '00'))
		return true;
	else {
		if((parseInt($('#to_4').val()) < parseInt($('#from_4').val())) || (parseInt($('#to_4').val()) == parseInt($('#from_4').val())))
			return false;
		else
			return true;
	}
});	
$.validator.addMethod("set_5", function(value, element) {
	if($('#set_5').val() == '' || ($('#set_5').val() != '' && $('#to_5').val() == '00' && $('#from_5').val() == '00'))
		return true;
	else {
		if((parseInt($('#to_5').val()) < parseInt($('#from_5').val())) || (parseInt($('#to_5').val()) == parseInt($('#from_5').val())))
			return false;
		else
			return true;
	}
});	
$.validator.addMethod("set_6", function(value, element) {
	if($('#set_6').val() == '' || ($('#set_6').val() != '' && $('#to_6').val() == '00' && $('#from_6').val() == '00'))
		return true;
	else {
		if((parseInt($('#to_6').val()) < parseInt($('#from_6').val())) || (parseInt($('#to_6').val()) == parseInt($('#from_6').val())))
			return false;
		else
			return true;
	}
});	
		
//--------------Add user----------------End

//--------------Forget Password---------------start
$("#forget_password_form1").validate({
	rules:{
		Password	       :    { required:true,minlength:6},
           C_Password		   :	{ required:true, minlength:6,equalTo:'#Password'}
         },
	messages:{
		Password	       :    { required:'Password is required',minlength:'Password should have atleast 6 characters'},
           C_Password		   :	{ required:'Confirm Password is required',minlength:'Confirm Password should have atleast 6 characters', equalTo:'Password mismatch'},
	}
});
//--------------Forget Password----------------End
//--------------Add Product---------------start
$("#add_product_form").validate({	
		rules:{
			ItemName          	:	{ required:true},			
            ItemPrice		  	:	{ required:true},			
			empty_product_photo	:	{ required:true },			
			Category		  	:	{ required:true }			
		},
		messages:{
			ItemName      	 	:	{ required:'Item Name is required'},
			ItemPrice		    :   { required:'Price is required'},
			empty_product_photo	:	{ required:'Product Image is required' },
			Category		  	:	{ required:'Category is required' }			
		}		
	});	
//--------------Add Product----------------End

});
//--------------------merchant - Opening Hours ------------------Start
$("#merchant_edit_form").submit(function() {
	var validatetime = '1';
	var myregex = new RegExp("");
	var checksame = 6;
	if($('#samehours').is(':checked')) {
		checksame = 0;
		$('#row_0').val('1');
		if($("#from1_0").val() == '' || $("#from1_0").val() == ''){
			$('#error_0').html('Enter From time and To time');
			validatetime = '0';
			return false;
		}
	} else {
		if($("#from1_0").val() == '' || $("#from1_0").val() == ''){
			$('#row_0').val('');
		}
	}
	for(i=0;i<=checksame;i++) {	
		$('#error_'+i).html('');
		if($('#row_'+i).val() != '') {
			if(!$("#from1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				$('#error_'+i).html('Invalid From time. Please select HH, MM and AM/PM');
				validatetime = '0';
			}
			else if(!$("#to1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				$('#error_'+i).html('Invalid To time. Please select HH, MM and AM/PM');
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
						validatetime = '0';
					}
					//Start time
					var startsplit 		= start.split(" ");
					var startarray 		= startsplit[0].split(":").map(Number);
					if(startsplit[1] == 'pm') {
						startarray[0] = startarray[0] + 12;
					}
					//End Time
					var endsplit 		= end.split(" ");
					var endarray 		= endsplit[0].split(":").map(Number);	
					if(endsplit[1] == 'pm') {
						endarray[0] = endarray[0] + 12;
					}
					//Checking
					if((startarray[0] > endarray[0]) || ((startarray[0] == endarray[0]) && (startarray[1] > endarray[1]))) {
						$('#error_'+i).html('To time must be greater then From time');
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
});
//--------------------merchant - Opening Hours ------------------end

//--------------Web content---------------start
$("#content_form").validate({
	rules:{			
		ContentName       :	{ required:true},		
		ContentUrl       :	{ required:true},		
		content			  :	{ content:true}		
	},
	messages:{
		ContentName     :	{ required:'Content name is required'},
		ContentUrl     :	{ required:'Content URL is required'},
		content     	:	{ content:'Please enter content details'}
	}
});
$.validator.addMethod("content", function(value, element) {	
	 var content 	=  tinyMCE.activeEditor.getContent();
	 if(content != '')
		return true;
	else
		return false;
});
//--------------Web content----------------End