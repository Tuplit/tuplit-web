<?php 
ob_start();
require_once('../includes/AjaxCommonIncludes.php');

// get currency from country name
if( isset( $_GET['action'] )  && $_GET['action'] == 'GET_CURRENCY_FROM_COUNTRY') {
	if(isset($_GET['country_name']) && $_GET['country_name']!=''){
		$country_name	= strtolower($_GET['country_name']);
		$currency_array = array_change_key_case($country_currency_array);
		if(isset($currency_array[$country_name]))
			echo $currency_array[$country_name];
		else
			echo 0;
	}
}
if( isset( $_GET['action'] )  && $_GET['action'] == 'GET_BANK_TYPE') {
	
	if(isset($_GET['bank']) && $_GET['bank']!=''){
		$bank_name	= $_GET['bank'];
		if($bank_name == 'Iban'){ ?>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>IBAN</label>
				<input class="form-control" type="text" name="IBAN"  id="IBAN" value="" required="" maxlength="" placeholder="IBAN Format expected">
				<span for="IBAN" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>BIC</label>
				<input class="form-control" type="text" name="BIC"  id="BIC" value="" required="" maxlength="" placeholder="BIC Format expected">
				<span for="BIC" generated="true" class="error">&nbsp;</span>
			</div>
		
		<?php }
		else if($bank_name == 'Gb'){ ?>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Account Number</label>
				<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="" required="" maxlength="8" onkeypress="return isNumberKey(event);">
				<span for="BankAccount" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>SortCode</label>
				<input class="form-control" type="text" name="SortCode"  id="SortCode" value="" required="" maxlength="6" onkeypress="return isNumberKey(event);">
				<span for="SortCode" generated="true" class="error">&nbsp;</span>
			</div>
		<?php }
		else if($bank_name == 'Us'){ ?>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Account Number</label>
				<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="" required="" maxlength="20" onkeypress="return isNumberKey(event);">
				<span for="BankAccount" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>ABA</label>
				<input class="form-control" type="text" name="ABA"  id="ABA" value="" required="" maxlength="9" onkeypress="return isNumberKey(event);">
				<span for="ABA" generated="true" class="error">&nbsp;</span>
			</div>
		<?php }
		else if($bank_name == 'Ca'){ ?>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Bank Name</label>
				<input class="form-control" type="text" name="BankName"  id="BankName" value="" required="" maxlength="50" onkeypress="">
				<span for="BankName" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Institution Number</label>
				<input class="form-control" type="text" name="InstitutionNumber"  id="InstitutionNumber" value="" required="" maxlength="4" onkeypress="return isNumberKey(event);">
				<span for="InstitutionNumber" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Branch Code</label>
				<input class="form-control" type="text" name="BranchCode"  id="BranchCode" value="" required="" maxlength="5" onkeypress="return isNumberKey(event);">
				<span for="BranchCode" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Account Number</label>
				<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="" required="" maxlength="20" onkeypress="return isNumberKey(event);">
				<span for="BankAccount" generated="true" class="error">&nbsp;</span>
			</div>
		<?php }
		else if($bank_name == 'Other'){ ?>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Country</label>
				<input class="form-control" type="text" name="Country"  id="Country" value="" required="" maxlength="" placeholder="ISO 3166-1 alpha-2 format is expected">
				<span for="Country" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>BIC</label>
				<input class="form-control" type="text" name="BIC"  id="BIC" value="" required="" maxlength="" placeholder="BIC Format expected">
				<span for="BIC" generated="true" class="error">&nbsp;</span>
			</div>
			<div class="form-group  col-xs-12 no-padding LH68">
				<label>Account Number</label>
				<input class="form-control" type="text" name="BankAccount"  id="BankAccount" value="" required="" maxlength="20" onkeypress="return isNumberKey(event);">
				<span for="BankAccount" generated="true" class="error">&nbsp;</span>
			</div>
		<?php }
	}
}
?>