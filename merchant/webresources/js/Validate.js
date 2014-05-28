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
//--------------Add User---------------start
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
			priceValidation		:	{ required:'Price range is required',min_val:'Please enter valid price range'}
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
	
//--------------Add user----------------End

//--------------Add Product---------------start
$("#add_product_form").validate({	
		rules:{
			ItemName          	:	{ required:true},
			ItemDescription   	:	{ required:true},			
			DiscountTier      	: 	{ discount_tier:true},
            Price			  	:	{ price:true},
			DiscountPrice	  	:	{ discount_price:true },
			Quantity	  	  	:	{ required:true },
			empty_product_photo	:	{ required:true },
			dup_SpecialPrice	:	{ specialprice:true },
			ItemType		  	:	{ required:true },
			SpecialDiscountPrice:	{ specialdiscountprice:true }			
		},
		messages:{
			ItemName      	 	:	{ required:'Item Name is required'},
			ItemDescription		:	{ required:'Item Description is required'},			
			DiscountTier		:	{ discount_tier:'Discount Tier is required'},
			Price			    :   { price:'Price is required'},
			DiscountPrice		:	{ discount_price:'Discount Price is required' },
			Quantity			:	{ required:'Quantity is required' },
			empty_product_photo	:	{ required:'Product Image is required' },
			dup_SpecialPrice	  	:	{ specialprice:'Add atleast one product data' },
			ItemType	        :	{ required:'Item Type is required' },
			SpecialDiscountPrice:	{ specialdiscountprice:'DiscountPrice should be less then the original price'}	
		}		
	});
	$.validator.addMethod("price", function(value, element) {
		 if($('#ItemType').val() == 3 && $('#ItemType').val() == '') 
			return true;
		 else {
			if($('#Price').val() == '') 
				return false;
			else	
				return true;
		}
	});
	$.validator.addMethod("discount_tier", function(value, element) {
		 if($('#ItemType').val() == 2 && $('#DiscountTier').val() == '') 
			return false;
		 else
			return true;  		 
	});
	$.validator.addMethod("discount_price", function(value, element) {
		 if($('#ItemType').val() == 2 && $('#DiscountPrice').val() == '') 
			return false;
		 else
			return true;	   		 
	});
	$.validator.addMethod("specialprice", function(value, element) {
		 if($('#ItemType').val() == 3 && $('#specialProductsCount').val() > 0 && $('#SpecialPrice').val() == '') 
			return false;
		 else
			return true;	   		 
	});
	$.validator.addMethod("specialdiscountprice", function(value, element) {
		 if($('#ItemType').val() == 3 && $('#specialProductsCount').val() > 0 && $('#SpecialPrice').val() != '') {
			if(parseInt($('#SpecialDiscountPrice').val()) < parseInt($('#SpecialPrice').val()))
				return true;
			else
				return false;
		 } else
			return true;	   		 
	});
//--------------Add Product----------------End

//--------------Change Password---------------start
$("#change_password_form").validate({
		rules:{		
			OldPassword	       :    { required:true},
			Password	       :    { required:true,minlength:6},
            C_Password		   :	{ required:true,minlength:6, equalTo:'#Password'}			
		},
		messages:{	
			OldPassword			:	{ required:'Old password is required'},
			Password			:	{ required:'New password is required',minlength:'New Password should have atleast 6 characters'},
			C_Password		    :   { required:'Confirm Password is required',minlength:'Confirm Password should have atleast 6 characters',equalTo:'Password mismatch' }			
		}
	});
//--------------Change Password----------------End
});


