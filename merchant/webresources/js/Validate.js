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
$("#merchant_login_form").validate({
		rules:{
			Email			:	{ required:true},
			Password		:	{ required:true}
		},
		messages:{
			Email			:	{ required:'Email is required',email:'Please enter a valid email address.' },
			Password		:	{ required:'Password is required'}
		}

	});
//--------------Login----------------End

//--------------Forget Password---------------start
$("#forget_password_form").validate({
		rules:{
			Email       :	{ required:true,email:true }
          },
		messages:{
			Email       :	{ required:'Email is required',email:'Please enter a valid email address.'}
		}
	});
//--------------Forget Password----------------End
//--------------Add User---------------start
$("#add_merchant_form").validate({
		rules:{
			FirstName          :	{ required:true},
			LastName           :	{ required:true},
			Email        	   :	{ required:true,email:true},
			Password	       :    { required:true,minlength:6},
            C_Password		   :	{ required:true,minlength:6, equalTo:'#Password'},
			CompanyName		   :	{ required:true },
			RememberMe		   :	{ required:true }	
		},
		messages:{
			FirstName       	:	{ required:'First Name is required'},
			LastName       		:	{ required:'Last Name is required'},
			Email				:	{ required:'Email is required',email:'Please enter a valid email address.'},
			Password			:	{ required:'Password is required',minlength:'Password should have atleast 6 characters'},
			C_Password		    :   { required:'Confirm Password is required',minlength:'Confirm Password should have atleast 6 characters',equalTo:'Password mismatch' },
			CompanyName			:	{ required:'Company Name is required' },
			RememberMe		   :	{ required:'Terms & Conditions is required' }
		}
	});
//--------------Add user----------------End
//--------------Add Merchant---------------start
$("#add_account_form").validate({
		rules:{
			CompanyName		   :	{ required:true },
			Address			   :	{ required:true },
			PhoneNumber		   :	{ required:true,minlength:10 },
			categorySelected	:	{ required:true},
			ShortDescription	:	{ required:true},
			Description			:	{ required:true},
			OpeningHours		:	{ required:true},
			Website        	   :	{ required:true,url:true},
			DiscountTier   	   :	{ required:true},
			empty_icon_photo	: 	{ required:true},
			empty_merchant_photo	: 	{ required:true},
			priceValidation		:	{ required:true,min_val:true}
			/*max_price			:	{ required:true,max_val:true}*/
		},
		messages:{
			CompanyName			:	{ required:'Company Name is required' },
			Address				:	{ required:'Address is required' },
			PhoneNumber			:	{ required:'Phone Number is required',minlength:'Phone Number should have atleast 10 numbers'},
			categorySelected	:	{ required:'Category is required'},
			ShortDescription	:	{ required:'Short Description is required'},
			Description			:	{ required:'Description is required'},
			OpeningHours		:	{ required:'Opening Hours is required'},
			Website				:	{ required:'Website is required',url:'Please enter valid url' },
			DiscountTier		:	{ required:'Price Scheme is required'},
			empty_icon_photo		: 	{ required:'Icon is required'},
			empty_merchant_photo	: 	{ required:'Image is required'},
			priceValidation		:	{ required:'Price Range is required',min_val:'Please enter valid price range'}
			/*max_price			:	{ required:'Max price is required',max_val:'Max price sould be greater than Min price'}*/
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
	
//--------------Add Merchant----------------End

//--------------Add Product---------------start
$("#add_product_form").validate({	
		rules:{
			ItemName          	:	{ required:true},			
            Price			  	:	{ required:true},			
			empty_product_photo	:	{ required:true },			
			Category		  	:	{ required:true }			
		},
		messages:{
			ItemName      	 	:	{ required:'Item Name is required'},
			Price			    :   { required:'Price is required'},
			empty_product_photo	:	{ required:'Product Image is required' },
			Category		  	:	{ required:'Category is required' }			
		}		
	});	
//--------------Add Product----------------End

//--------------Edit Product---------------start
$("#edit_product_form").validate({	
		rules:{
			ItemName          	:	{ required:true},			
            Price			  	:	{ required:true},			
			Category		  	:	{ required:true }			
		},
		messages:{
			ItemName      	 	:	{ required:'Item Name is required'},
			Price			    :   { required:'Price is required'},			
			Category		  	:	{ required:'Category is required' }			
		}		
	});	
//--------------Edit Product----------------End

//--------------Change Password---------------start
$("#change_password_form").validate({
		rules:{		
			OldPassword	       :    { required:true},
			Password	       :    { required:true,minlength:6},
            C_Password		   :	{ required:true,minlength:6, equalTo:'#Password'}			
		},
		messages:{	
			OldPassword			:	{ required:'Old Password is required'},
			Password			:	{ required:'New Password is required',minlength:'New Password should have atleast 6 characters'},
			C_Password		    :   { required:'Confirm Password is required',minlength:'Confirm Password should have atleast 6 characters',equalTo:'Password mismatch' }			
		}
	});
//--------------Change Password----------------End
//--------------Add Category----------------Start
$("#add_category_form").validate({	
		rules:{
			CategoryName          	:	{ required:true}			
		},
		messages:{
			CategoryName      	 	:	{ required:'Category name is required'}
		}		
	});
//--------------Add Category----------------End
});

//--------------------My Account - Opening Hours ------------------Start
$("#add_account_form").submit(function() {
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
	} else {
		$('#row_0').val('');
	}
	for(i=0;i<=checksame;i++) {
		$('#error_'+i).html('');
		if($('#row_'+i).val() != '') {
			if(!$("#from1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				$('#error_'+i).html('Invalid From time. It should be in HH:MM AM or HH:MM PM');
				validatetime = '0';
			}
			else if(!$("#to1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				$('#error_'+i).html('Invalid To time. It should be in HH:MM AM or HH:MM PM');
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
//--------------------My Account - Opening Hours ------------------end

//-------------- My Store---------------start
$("#mystore_form").validate({	
		rules:{
			ShopName          			:	{ required:true},			
            ShopDescription			  	:	{ required:true},			
			Category		  			:	{ required:true },		
			PriceRange		  			:	{ required:true },		
			MoreInfo		  			:	{ required:true },	
			Street		  				:	{ required:true },	
			City		  				:	{ required:true },	
			State		  				:	{ required:true },	
			Country		  				:	{ required:true },	
			Phone		  				:	{ required:true },	
			Email		  				:	{ required:true },	
			Website		  				:	{ required:true },	
			Facebook		  			:	{ required:true },	
			Twiter			  			:	{ required:true },	
			ZipCode			  			:	{ required:true },	
		},
		messages:{
			ShopName      	 			:	{ required:'Shop name is required'},
			ShopDescription			    :   { required:'ShopDescription is required'},			
			Category		  			:	{ required:'Category is required' },		
			MoreInfo		  			:	{ required:'MoreInfo is required' },			
			Street		  				:	{ required:'Street is required' },			
			City		  				:	{ required:'City is required' },			
			State		  				:	{ required:'State is required' },			
			Country		  				:	{ required:'Country is required' },			
			Phone		  				:	{ required:'Phone is required' },			
			Email		  				:	{ required:'Email is required' },			
			Website		  				:	{ required:'Website is required' },			
			Facebook		  			:	{ required:'Facebook is required' },			
			Twiter			  			:	{ required:'Twiter is required' },		
			ZipCode			  			:	{ required:'ZipCode is required' },		
		}		
	});	
//--------------My Store----------------End

//--------------------My store - Opening Hours ------------------Start
$("#mystore_form").submit(function() {
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
	} else {
		$('#row_0').val('');
	}
	for(i=0;i<=checksame;i++) {
		$('#error_'+i).html('');
		if($('#row_'+i).val() != '') {
			if(!$("#from1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				$('#error_'+i).html('Invalid From time. It should be in HH:MM AM or HH:MM PM');
				validatetime = '0';
			}
			else if(!$("#to1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				$('#error_'+i).html('Invalid To time. It should be in HH:MM AM or HH:MM PM');
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
//--------------------My store - Opening Hours ------------------end

//--------------Add Mangopay---------------start
$("#add_mangopay_account").validate({
		rules:{
			FirstName          :	{ required:true},
			LastName           :	{ required:true},
			Address	       	   :    { required:true},
            Country			   :	{ required:true},
			Currency		   :	{ required:true },
			DOB				   :	{ required:true }
		},
		messages:{
			FirstName       	:	{ required:'First Name is required'},
			LastName       		:	{ required:'Last Name is required'},
			Address				:	{ required:'Address is required'},
			Country				:	{ required:'Country is required'},
			Currency		    :   { required:'Currency is required'},
			DOB				    :   { required:'Birth date is required'}
		}
	});
//--------------Add Mangopay-----------End