<?php
require_once('includes/CommonIncludes.php');
admin_login_check();

require_once("includes/mangopay/functions.php");
require_once('controllers/MerchantController.php');
$merchantObj   		=   new MerchantController();

require_once('controllers/OrderController.php');
$orderObj   		=   new OrderController();

if(isset($_GET['cs']) && $_GET['cs']=='1') { 
	$_SESSION['tuplit_sess_merchant_id']		=	'';
}
$inputarr			= array();
$field				=	' id,CompanyName';
$condition       	= "  Status =1 and MangoPayUniqueId != '' order by CompanyName asc";
$merchantList		= 	$merchantObj->selectMerchantDetails($field,$condition);
if((isset($_POST['Search']) && !empty($_POST['Search'])) || (isset($_POST['export-excel']) && $_POST['export-excel'] == 1)) {
	if(isset($_POST['Merchant']) && $_POST['Merchant'] != ''){
		$from_date	=	$_POST['from_date'];
		$to_date	=	$_POST['to_date'];
		if(isset($_POST['Merchant']) && $_POST['Merchant'] != ''){
			$_SESSION['tuplit_sess_merchant_id']	=	$_POST['Merchant'];
			$merchantListResult  					= 	$merchantObj->selectMerchantDetail($_POST['Merchant']);
		}
		$inputarr['Id']				=	$merchantListResult[0]->WalletId;
		if(isset($_POST['from_date']) && !empty($_POST['from_date']))
			 $inputarr['Start']	=	strtotime($_POST['from_date']);
		if(isset($_POST['to_date']) && !empty($_POST['to_date']))
			$to_date   = $inputarr['End']		=	strtotime($_POST['to_date']);
		if(isset($_POST['Status']) && !empty($_POST['Status'])) {
			if($_POST['Status'] == 1)
				$inputarr['Status']		=	'SUCCEEDED';
			else if($_POST['Status'] == 2)
				$inputarr['Status']		=	'FAILED';
		}
			
		$result					=	GetTransactionsNew($inputarr);		
		$fields					=	'';
		$conditions				=	"o.fkMerchantsId =".$_POST['Merchant']." ";	
		$userList				=	$orderObj->getUserTransactions($fields,$conditions);
		if($userList) {
			foreach($userList as $key=>$val) {
				$nameArray[$val->MangoPayUniqueId]	=	ucfirst($val->FirstName).' '.ucfirst($val->LastName);
			}
		}
			
			//forming result 
			foreach($result as $key=>$val){
				$TransactionsList[$key]					= 	(array)$val;
				
				//user uniqueId
				$id							=	$val->AuthorId;
				if(array_key_exists($id,$nameArray))
					$TransactionsList[$key]['Customer']	=	$nameArray[$id];
				else
					$TransactionsList[$key]['Customer']	=	'';
			}
	}
}

/* FOR CSV export */
if(isset($_POST['export-excel']) && $_POST['export-excel'] == 1)
{
	if(is_array($TransactionsList)){
		$TransactionsExport		=	array();
		foreach($TransactionsList as $key=>$value){	
			$TransactionsExport[$key]['Transaction ID']		=	$value['Id'];
			$TransactionsExport[$key]['Date']				=	date("m/d/Y", $value['CreationDate']);
			$TransactionsExport[$key]['Time']				=	date("H:i:s", $value['CreationDate']);
			
			if(!empty($value['Customer'])) 
				$TransactionsExport[$key]['Customer Name']	=	$value['Customer']; 
			else 
				$TransactionsExport[$key]['Customer Name']	=	"Test Transaction";
			
			$TransactionsExport[$key]['Amount Debited']		=	price_fomat($value['DebitedFunds']->Amount);
			$TransactionsExport[$key]['Amount Credited']	=	price_fomat($value['CreditedFunds']->Amount);
			$TransactionsExport[$key]['Transaction Fee']	=	price_fomat($value['Fees']->Amount);
			$TransactionsExport[$key]['Nature']				=	$value['Nature'];
			$TransactionsExport[$key]['Status']				=	$value['Status'];
			$TransactionsExport[$key]['Status Message']		=	$value['ResultMessage'];
		}
		$TransactionsExport = subval_sort($TransactionsExport,'Transaction ID',1);
		csvDownload($TransactionsExport,'TuplitTransactionList.csv');
	}
}
commonHead();
?>

<body class="skin-blue">
	<?php top_header(); ?>
	
	<!-- Content Header (Page header) -->
	<section class="content-header no-padding">
		<div class="col-xs-7">
			<h1><i class="fa fa-list"></i> Transaction List</h1>
		</div>
	</section>
	
	 <!-- Main content -->
	<section class="content">
		<div class="row">
			<div class="col-xs-12">
					<form name="search_transaction" id="search_transaction" action="TransactionList?cs=1" method="post">
					<div class="box box-primary">
						<div class="box-body" >		
							<div class="col-sm-4 form-group">
									<label>Merchant</label>
									<select class="form-control " name="Merchant" id="Merchant" onchange="getProductCategory(this.value);">
										<option value="" >Select</option>								
										<?php if(isset($merchantList) && !empty($merchantList)) {
											foreach($merchantList as $m_key=>$m_val) {								
										?>
										<option value="<?php echo $m_val->id;?>" <?php if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id'] == $m_val->id) echo "selected"; ?>><?php echo ucfirst($m_val->CompanyName);?></option>
										<?php } } ?>								
									</select>
							</div>
							<div class="col-sm-4  col-md-2 form-group">
								<label>After Date</label>
								<input  type="text" id = "from_date" class="form-control datepicker" autocomplete="off" title="Select Date" name="from_date" value="<?php if(isset($_POST['from_date']) && $_POST['from_date'] != '') echo $_POST['from_date'];?>" onchange="return emptyDates(this);">
							</div>
							<div class="col-sm-4 col-md-2 form-group">
								<label>Before Date</label>
								<input type="text" id = "to_date" class="form-control datepicker" autocomplete="off"  title="Select Date" name="to_date" value="<?php if(isset($_POST['to_date']) && $_POST['to_date'] != '') echo $_POST['to_date'];?>" onchange="return emptyDates(this);">
							</div>
							<div class="col-sm-4 col-md-2 form-group">
								<label>Status</label>
								<select id="Status" class="form-control" name="Status">
									<option value="">Select</option>
									<option value="1" <?php if(isset($status) && $status == 1) echo "selected"; ?>>Success</option>
									<option value="2" <?php if(isset($status) && $status == 2) echo "selected"; ?>>Failed</option>
								</select>
							</div>
						</div>
						<div class="box-footer col-sm-12" align="center">
							<input type="submit" class="btn btn-success" name="Search" id="Search" value="Search" title="Search">
						</div>					
					</div>
					</form>
				</div>
			</div>
			<?php if(!SERVER){ 
			if(isset($TransactionsList) && !empty($TransactionsList)) { ?>
			<div class="box-footer pull-right" align="right" style="padding-bottom:20px;">
				<input type="Button" class="btn btn-success" name="export_csv" onclick="exportExcelSubmit('search_transaction');" id="export_csv" value="Export CSV" title="Export CSV">
			</div>	
			<?php } }?>
			<div class="row">
			 	  <?php if(isset($TransactionsList) && !empty($TransactionsList)) { 
						$TransactionsList = subval_sort($TransactionsList,'Id',1);
				   ?>
               <div class="col-xs-12">
                   <div class="box">
		               <div class="box-body table-responsive no-padding no-margin">
						<table class="table table-hover">
                               <tr>
									<th align="center" width="3%" class="text-center">#</th>									
									<th width="10%">Transaction ID</th>
									<th width="8%">Date</th>
									<th width="7%">Time</th>
									<!--<th width="10%">Customer Id</th>-->
									<th width="12%">Customer Name</th>
									<th width="5%" class="text-right">Amount Debited</th>
									<th width="5%" class="text-right">Amount Credited</th>
									<th width="5%" class="text-right">Transaction Fee</th>
									<th width="7%" class="">Nature</th>
									<th width="10%" class="">Status</th>
									<th width="">Status Message</th>
								</tr>
                              <?php $count	=	0;
							  	foreach($TransactionsList as $key=>$value){	
									//echo "<pre>"; echo print_r($value); echo "</pre>";
									$count 	+= 1;
								?>
								<tr>
									<td align="center" width="3%" class="text-center"><?php echo $count; ?></td>									
									<td><?php echo $value['Id']; ?></td>
									<td><?php echo date("m/d/Y", $value['CreationDate']); ?></td>
									<td><?php echo date("H:i:s", $value['CreationDate']); ?></td>
									<!--<td><?php echo $value['AuthorId']; ?></td>-->
									<td><?php if(!empty($value['Customer'])) echo $value['Customer']; else echo "Test Transaction"; ?></td>
									<td class="text-right"><?php echo '<b>'.price_fomat($value['DebitedFunds']->Amount)."</b>"; ?></td>
									<td class="text-right"><?php echo '<b>'.price_fomat($value['CreditedFunds']->Amount)."</b>"; ?></td>
									<td class="text-right"><?php echo '<b>'.price_fomat($value['Fees']->Amount)."</b>";//echo $value['DebitedFunds']['Currency'].' '.$value['Fees']['Amount']; ?></td>
									<td class=""><?php echo $value['Nature']; ?></td>
									<td class=""><?php 
										if($value['Status'] == 'SUCCEEDED')
											echo "<b style='color: #01a99a;'>".$value['Status']."</b>"; 
										else if($value['Status'] == 'FAILED')
											echo "<b style='color: #FF4747;'>".$value['Status']."</b>";
										//echo $value['Status'];
										?>
									</td>
									<td><?php echo $value['ResultMessage']; ?></td>
									
								</tr>
							<?php } //end for ?>	
                           </table>
							<!-- End product List -->						 
						<?php } else { ?>
							<div class="row clear">		
								 <div align="center" class="alert alert-danger alert-dismissable col-lg-4 col-sm-5 col-xs-10"><i class="fa fa-warning"></i> No transactions found	</div>							
							</div>							
						<?php } ?>						
					</div><!-- /.box-body -->
				</div>					
			</div>	
		 </div>
		 </section>
		 
		
		
		<?php commonFooter(); ?>
<script type="text/javascript">
	$(".datepicker").datepicker({
			showButtonPanel	:	true,        
		    buttonText		:	'<i class="fa fa-calendar"></i>',
		    buttonImageOnly	:	true,
		    buttonImage		:	path+'webresources/images/calender.png',
		    dateFormat		:	'mm/dd/yy',
			changeMonth		:	true,
			changeYear		:	true,
			hideIfNoPrevNext:	true,
			showWeek		:	true,
			yearRange		:	"c-30:c",
			closeText		:   "Close"
		   });
		 function emptyDates(arg) { 
			var id = arg.getAttribute('name');		
			if(id == 'year' || id == 'month') {
				$('#from_date').val('');
				$('#to_date').val('');		
			}
			else {
				$("#year").children("option[value ='']").attr('selected', true);
				$("#month").children("option[value ='']").attr('selected', true);
			}
		 }
	$(document).ready(function() {
			$(".newWindow").fancybox({
				scrolling: 'auto',			
				type: 'iframe',
				width: '800',
				maxWidth: '100%',	
					title: null,			
				fitToView: false
			});	
		});
</script>
	
</html>
