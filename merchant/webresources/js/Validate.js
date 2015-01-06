/*if ("https:" == location.protocol)
    var protocolPath  = 'https://';
else
    var protocolPath  = 'http://';

if (window.location.hostname == '172.21.4.104') {
    var  path = protocolPath + '172.21.4.104/tuplit/admin/';
    var actionPath	= protocolPath + '172.21.4.104/tuplit/admin/';
}
else {
	var  path = protocolPath+''+window.location.hostname+'/admin/';
    var actionPath	= protocolPath+''+window.location.hostname+'/admin/';

}*/
var email_f		=	/^((\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*)*([,])*)*$/;
$(document).ready(function(){

 $.validator.addMethod("nameRegexp", function(value, element) {
		return this.optional(element) || !(/:|\?|\\|\*|\"|<|>|\||%/g.test(value));
    });
	
$.validator.addMethod("time24", function(value, element) { 
    return /^([01]?[0-9]|2[0-3])(:[0-5][0-9]){2}$/.test(value);
}, "Invalid 24 hours time format.");
	
$.validator.addMethod("time", function(value, element) { 
    return  /^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/.test(value);
}, "Invalid 12 hours time format.");

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
//-------------- My Store---------------start
/*$("#mystore_form").validate({	
		rules:{
			ShopName          			:	{ required:true },			
            ShopDescription			  	:	{ required:true },			
			categorySelected			:	{ required:true },		
			PriceRange		  			:	{ required:true },		
			MoreInfo		  			:	{ required:true },	
			Street		  				:	{ required:true },	
			City		  				:	{ required:true },	
			State		  				:	{ required:true },	
			Country		  				:	{ required:true },	
			Phone		  				:	{ required:true },	
			Email		  				:	{ required:true },	
			Website		  				:	{ required:true,url:true },	
			//Facebook		  			:	{ required:true,url:true },	
			//Twitter			  			:	{ required:true,url:true },	
			ZipCode			  			:	{ required:true },	
			priceValidation				:	{ required:true,min_val:true},
			empty_icon_photo			: 	{ required:true},
			empty_merchant_photo		: 	{ required:true},
			empty_background_photo		: 	{ required:true}
		},
		messages:{
			ShopName      	 			:	{ required:'Shop Name is required'},
			ShopDescription			    :   { required:'Shop Description is required'},			
			categorySelected			:	{ required:'Category is required' },		
			MoreInfo		  			:	{ required:'More Info is required' },			
			Street		  				:	{ required:'Street is required' },			
			City		  				:	{ required:'City is required' },			
			State		  				:	{ required:'State is required' },			
			Country		  				:	{ required:'Country is required' },			
			Phone		  				:	{ required:'Phone is required' },			
			Email		  				:	{ required:'Email is required' },			
			Website		  				:	{ required:'Website is required',url:'Please enter valid website url'  },			
			//Facebook		  			:	{ required:'Facebook is required',url:'Please enter valid facebook url' },			
			//Twitter			  			:	{ required:'Twitter is required',url:'Please enter valid twitter url' },		
			ZipCode			  			:	{ required:'ZipCode is required' },		
			priceValidation				:	{ required:'Price Range is required',min_val:'Please enter valid price range'},
			empty_icon_photo			: 	{ required:'Logo is required'},
			empty_merchant_photo		: 	{ required:'Image is required'},
			empty_background_photo		: 	{ required:'Background is required'}
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
	});*/
//--------------My Store----------------End

//--------------------My store - Opening Hours ------------------Start
/*$("#mystore_form").submit(function() {
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
				$('#error_'+i).html('From time is required');
				validatetime = '0';
			}
			else if(!$("#to1_"+i).val().match(/^(0?[1-9]|1[012])(:[0-5]\d) [APap][mM]$/)) {			
				$('#error_'+i).html('To time is required');
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
					/*Start time
					var startsplit 		= start.split(" ");
					var startarray 		= startsplit[0].split(":").map(Number);
					if(startsplit[1] == 'pm') {
						if(startarray[0] < 12)
							startarray[0] = startarray[0] + 12;
					}
					//End Time
					var endsplit 		= end.split(" ");
					var endarray 		= endsplit[0].split(":").map(Number);	
					if(endsplit[1] == 'pm') {
						if(endarray[0] < 12)
							endarray[0] = endarray[0] + 12;
					}
					//Checking
					if((startarray[0] > endarray[0]) || ((startarray[0] == endarray[0]) && (startarray[1] > endarray[1]))) {
						$('#error_'+i).html('To time must be greater then From time');
						validatetime = '0';
					}	*/				
				/*}				
			}
		}
	}	
	
	if(validatetime == '1')
		return true;
	else
		return false;
});*/
//--------------------My store - Opening Hours ------------------end

//--------------Add Mangopay---------------start
$("#add_mangopay_account").validate({
		rules:{
			CompanyName        :	{ required:true},
			Email		       :	{ required:true,email:true},
			FirstName          :	{ required:true},
			LastName           :	{ required:true},
			Address	       	   :    { required:true},
            Country			   :	{ required:true},
			Currency		   :	{ required:true },
			DOB				   :	{ required:true }
		},
		messages:{
			CompanyName       	:	{ required:'Company Name is required'},
			Email		       	:	{ required:'Email is required',email:'Please enter a valid email address.'},
			FirstName       	:	{ required:'First Name is required'},
			LastName       		:	{ required:'Last Name is required'},
			Address				:	{ required:'Address is required'},
			Country				:	{ required:'Country is required'},
			Currency		    :   { required:'Currency is required'},
			DOB				    :   { required:'Birth Date is required'}
		}
	});
//--------------Add Mangopay-----------End

//--------------Add Merchant---------------start
$("#add_merchant_form_new").validate({
		rules:{
			FirstName	    :	{ required:true},
			LastName	    :	{ required:true},
			BusinessName    :	{ required:true},
			Email       	:	{ required:true,email:true},
			MobileNumber	:	{ required:true },
			Password		:   { required:true,minlength:6},
			CPassword		:   { required:true,minlength:6,equalTo:'#Password'},
			Terms			:	{ required:true },
			Website			:	{ url:true }	
		},
		messages:{
			FirstName    	:	{ required:'First Name is required'},
			LastName    	:	{ required:'Last Name is required'},
			BusinessName	:	{ required:'Business Name is required'},
			Email			:	{ required:'Email is required',email:'Please enter a valid email address.'},
			MobileNumber	:	{ required:'Mobile Number is required'},
			Password		:	{ required:'Password is required',minlength:'Password should have atleast 6 characters'},
			CPassword		:	{ required:'Confirm Password is required',minlength:'Password should have atleast 6 characters',equalTo:'Password mismatch'},
			Terms			:	{ required:'Terms & Conditions is required' },
			Website			:	{ url:'Please enter valid website url'}
		}
	});
//--------------Add Merchant---------------End

//-------------- Merchant Settings ---------------start
/*$("#merchant_setting_form").validate({
		rules:{
			FirstName          :	{ required:true},
			LastName           :	{ required:true},
			Email        	   :	{ required:true,email:true},
			PhoneNumber	   		:	{ required:true,minlength:10 },
			BusinessName	   :	{ required:true },
			BusinessType	   :	{ required:true },
			CompanyNumber	   :	{ required:true },
			Address	   			:	{ required:true },
			Country	   			:	{ required:true },
			PostCode   			:	{ required:true },
			Currency   			:	{ required:true },
			DiscountTier		:	{ required:true },
			CompanyName		   :	{ required:true }
		},
		messages:{
			FirstName       	:	{ required:'First Name is required'},
			LastName       		:	{ required:'Last Name is required'},
			Email				:	{ required:'Email is required',email:'Please enter a valid email address.'},
			CompanyName			:	{ required:'Company Name is required' },
			PhoneNumber			:	{ required:'Mobile Number is required',minlength:'Mobile Number should have atleast 10 numbers' },
			BusinessName		:	{ required:'Business Name is required' },
			BusinessType		:	{ required:'Business Type is required' },
			CompanyNumber		:	{ required:'Registered Company Number is required' },
			Address				:	{ required:'Address is required' },
			Country				:	{ required:'Country is required' },
			PostCode			:	{ required:'Postcode is required' },
			Currency			:	{ required:'Currency is required' },
			DiscountTier		:	{ required:'Discount Scheme is required' },
		}
	});*/
//-------------- Merchant Settings ---------------End

//--------------Add Sub user---------------start
$("#subuser_form").validate({
		rules:{
			FirstName          :	{ required:true},
			LastName           :	{ required:true},
			empty_salesperson  :	{ required:true},
			Email        	   :	{ required:true,email:true},
			Password	       :    { required:true,minlength:6},			
            C_Password		   :	{ required:true,minlength:6, equalTo:'#Password'}
		},
		messages:{
			FirstName       	:	{ required:'First Name is required'},
			LastName       		:	{ required:'Last Name is required'},
			empty_salesperson	:	{ required:'Salesperson image is required'},
			Email				:	{ required:'Email is required',email:'Please enter a valid email address.'},
			Password			:	{ required:'Password is required',minlength:'Password should have atleast 6 characters'},
			C_Password		    :   { required:'Confirm Password is required',minlength:'Confirm Password should have atleast 6 characters',equalTo:'Password mismatch' }
		}
	});
//--------------Add Sub user---------------End

//--------------edit Sub user---------------start
$("#edit_subuser_form").validate({
		rules:{
			FirstName          :	{ required:true},
			LastName           :	{ required:true},
			empty_salesperson  :	{ required:true},
			Email        	   :	{ required:true,email:true}
		},
		messages:{
			FirstName       	:	{ required:'First Name is required'},
			LastName       		:	{ required:'Last Name is required'},
			empty_salesperson	:	{ required:'Salesperson image is required'},
			Email				:	{ required:'Email is required',email:'Please enter a valid email address.'}
		}
	});
//--------------edit Sub user---------------End

//-------------- Settings ---------------start
$("#Settings_form").validate({	
		rules:{
			ShopName          			:	{ required:true },			
            ShopDescription			  	:	{ required:true },			
			categorySelected			:	{ required:true },		
			PriceRange		  			:	{ required:true },		
			MoreInfo		  			:	{ required:true },	
			Street		  				:	{ required:true },	
			City		  				:	{ required:true },	
			State		  				:	{ required:true },	
			Country		  				:	{ required:true },	
			Phone		  				:	{ required:true },	
			Email		  				:	{ required:true },	
			Website		  				:	{ required:true,url:true },	
			ZipCode			  			:	{ required:true },	
			priceValidation				:	{ required:true, min_val:true},
			empty_icon_photo			: 	{ required:true},
			empty_merchant_photo		: 	{ required:true},
			empty_background_photo		: 	{ required:true},
			Security					: 	{ required:true, email:true},
			//AutoLock					: 	{ required:true},
			PriceScheme					: 	{ required:true},
			ProductVAT					: 	{ required:true},
			PanelFeatures				: 	{ required:true},
			Emails						: 	{ required:true,email_format:true},
			Pincode	     				:   { Pincode_check:true},
			CPincode	     			:   { equalTo:'#Pincode'},
			CPassword	     			:   { equalTo:'#Password'}
		},
		messages:{
			ShopName      	 			:	{ required:'Shop Name is required'},
			ShopDescription			    :   { required:'Shop Description is required'},			
			categorySelected			:	{ required:'Category is required' },		
			MoreInfo		  			:	{ required:'More Info is required' },			
			Street		  				:	{ required:'Street is required' },			
			City		  				:	{ required:'City is required' },			
			State		  				:	{ required:'State is required' },			
			Country		  				:	{ required:'Country is required' },			
			Phone		  				:	{ required:'Phone is required' },			
			Email		  				:	{ required:'Email is required' },			
			Website		  				:	{ required:'Website is required',url:'Please enter valid website url'  },			
			ZipCode			  			:	{ required:'ZipCode is required' },		
			priceValidation				:	{ required:'Price Range is required',min_val:'Please enter valid price range'},
			empty_icon_photo			: 	{ required:'Logo is required'},
			empty_merchant_photo		: 	{ required:'Image is required'},
			empty_background_photo		: 	{ required:'Background is required'},
			Security					: 	{ required:'Security is required', email:'enter valid email'},
			//AutoLock					: 	{ required:'Auto-Lock is required'},
			PriceScheme					: 	{ required:'PriceScheme is required'},
			ProductVAT					: 	{ required:'ProductVAT is required'},
			PanelFeatures				: 	{ required:'PanelFeatures is required'},
			Emails						: 	{ required:'Emails is required', email_format:'enter valid email'},
			Pincode	     				:   { Pincode_check:'Pincode is required'},
			CPincode	     			:   { equalTo:'Pincode mismatch'},
			CPassword	     			:   { equalTo:'Password mismatch'}
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
		  	return true;
	});
	$.validator.addMethod("email_format", function(value, element) {
		 if($('#Emails').val().match(email_f))  
	   		 return true;
		 else
		  	return false;
	});
	$.validator.addMethod("Pincode_check", function(value, element) {
		 if($('#AutoLock').val() == '')
	   		 return true;
		 else if(($('#AutoLock').val() > 0) && ($('#hidden_pin').val() == '') && ($('#Pincode').val() == ''))
	   		 return false;
		 else
		  	return true;
	});
//-------------- Settings ----------------End

$("#ask_pin_popup").validate({
		rules:{
			Pincode   	:	{ required:true}
		},
		messages:{
			Pincode		:	{ required:'Pincode is required'}
		}
	});
//--------------Add Mangopay Bank---------------start
$("#add_mangopay_bank_account").validate({
		rules:{
			FirstName          	:	{ required:true},
			LastName           	:	{ required:true},
			Address	       	   	:   { required:true},
            BankAccount		   	:	{ required:true},
			SortCode		   	:	{ required:true,minlength:6},
			BankName		   	:	{ required:true},
			InstitutionNumber  	:	{ required:true},
			BranchCode		   	:	{ required:true},
			ABA		   			:	{ required:true,minlength:9},
			IBAN		   		:	{ required:true},
			BIC		  			:	{ required:true},
			Country		   		:	{ required:true},
		},
		messages:{
			FirstName       	:	{ required:'First Name is required'},
			LastName       		:	{ required:'Last Name is required'},
			Address				:	{ required:'Address is required'},
			BankAccount			:	{ required:'Account Number is required'},
			SortCode		    :   { required:'Sort Code is required',minlength:'Sort Code should contain 6 numbers'},
			BankName			:	{ required:'Bank Name is required'},
			InstitutionNumber	:	{ required:'Institution Number is required'},
			BranchCode			:	{ required:'Branch Code is required'},
			ABA					:	{ required:'ABA is required',minlength:'ABA should contain 9 numbers'},
			IBAN				:	{ required:'IBAN is required'},
			BIC					:	{ required:'BIC is required'},
			Country				:	{ required:'Country is required'},
		}
	});
//--------------Add Mangopay Bank-----------End
//--------------Transfer Money to Bank---------------start
$("#transfer_to_bank").validate({
		rules:{
			BankAccountId    :	{ required:true},
			Amount           :	{ required:true}
		},
		messages:{
			BankAccountId     	:	{ required:'Bank account is required'},
			Amount       		:	{ required:'Amount is required'}
		}
	});
//--------------Transfer Money to Bank-----------End
