<?php
require_once('includes/CommonIncludes.php');
merchant_login_check();

commonHead();
?>
<body class="skin-blue fixed body_height">
<?php top_header(); ?>		
<section class="content top-spacing" align="center">
	<div class="col-md-12 col-lg-12 box-center row" >				
		<section class="content-header">
			<h1 class="col-sm-9 col-lg-10 no-padding text-left">New incoming orders </h1>
			<a href="OrderHistory?cs=1" class="col-sm-3  col-lg-2  btn btn-success margin-bottom padding10" title="View Orders History"><i class="fa fa-history"></i> View Orders History</a>
		</section>
		<div class="clear order_list row">
			<div class="no-padding" id="NewOrderListHtml">
				<div class="col-md-4 col-sm-6 col-lg-3 col-xs-12">
					<div class="small-box" style="height:309px;">
						<div id="144" class="orderswipe orderswipeless144">
							<div class="col-md-3 col-lg-4 col-xs-3">
								<a class="fancybox" title="Customer Us" href="http://172.21.4.104/tuplit/admin/webresources/uploads/users/1_1405935748.png">
								<img width="65" height="65" alt="" src="http://172.21.4.104/tuplit/admin/webresources/uploads/users/thumbnail/1_1405935748.png">
								</a>
							</div>
							<div class="col-md-9 col-lg-8 col-xs-9 min-height90 no-padding-left">
								<span class="text-small" data-original-title="Customer Us" title="Customer Us" data-toggle="tooltip">Customer Us</span>
								<span class="help-block no-margin">customer1@mail.com</span>
								<span class="help-block no-margin HelveticaNeueBold">tuplit3971</span>
								<span class="help-block no-margin time_post">5 hrs ago</span>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-12 list_height clear more_item">
								<div class="col-xs-6 no-padding clear">Burger1478 </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$18.00</div>
								
								<div class="col-xs-6 no-padding clear">Cake2 </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$18.00</div>
								<div class="text-center col-xs-12 no-padding ">
									<a id="linkNew0" class="Swipe_up" href="javascript:void(0)" style="cursor:pointer">Swipe up for more</a>
								</div>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended">Total </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended"><strong>$100.20</strong></div>
							<div class="col-xs-12 no-padding min-height40 approveReject">
								<div class="col-xs-4 btn btn-default Rejected_class">
									<a class="text-red" onclick="return approveReject('reject');" href="?Reject=118"><em> </em>	Reject	</a>
								</div>
								<div class="col-xs-8 text-right btn btn-success approve_class">
									<a id="submit" title="Approve" href="javascript:void(0)"><em> </em>Approve	</a>
								</div>
							</div>
						</div>
						<div id="144" class="col-xs-12 list_height clear more_item orderswipe orderswipemore144" style="display:none;overflow-y:auto;">
							<div class="text-center col-xs-12 no-padding ">
								<a id="linkNew0" class="Swipe_up" style="cursor:pointer" href="javascript:void(0)">Swipe down for hide</a>
							</div>
							<div class="col-xs-6 no-padding clear">Burger1478 </div>
							<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
							<div class="col-xs-4 no-padding text-right">$18.00</div>
							
							<div class="col-xs-6 no-padding clear">Cake2 </div>
							<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
							<div class="col-xs-4 no-padding text-right">$18.00</div>
							
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>	
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>	
										
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended">Total </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended"><strong>$100.20</strong></div>
						</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 col-lg-3 col-xs-12">
					<div class="small-box" style="height:309px;">
						<div id="145" class="orderswipe orderswipeless145">
							<div class="col-md-3 col-lg-4 col-xs-3">
								<a class="fancybox" title="Customer Us" href="http://172.21.4.104/tuplit/admin/webresources/uploads/users/1_1405935748.png">
								<img width="65" height="65" alt="" src="http://172.21.4.104/tuplit/admin/webresources/uploads/users/thumbnail/1_1405935748.png">
								</a>
							</div>
							<div class="col-md-9 col-lg-8 col-xs-9 min-height90 no-padding-left">
								<span class="text-small" data-original-title="Customer Us" title="Customer Us" data-toggle="tooltip">Customer Us</span>
								<span class="help-block no-margin">customer1@mail.com</span>
								<span class="help-block no-margin HelveticaNeueBold">tuplit3971</span>
								<span class="help-block no-margin time_post">5 hrs ago</span>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-12 list_height clear more_item">
								<div class="col-xs-6 no-padding clear">Burger1478 </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$18.00</div>
								
								<div class="col-xs-6 no-padding clear">Cake2 </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$18.00</div>
								<div class="text-center col-xs-12 no-padding ">
									<a id="linkNew0" class="Swipe_up" href="javascript:void(0)" style="cursor:pointer">Swipe up for more</a>
								</div>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended">Total </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended"><strong>$100.20</strong></div>
							<div class="col-xs-12 no-padding min-height40 approveReject">
								<div class="col-xs-4 btn btn-default Rejected_class">
									<a class="text-red" onclick="return approveReject('reject');" href="?Reject=118"><em> </em>	Reject	</a>
								</div>
								<div class="col-xs-8 text-right btn btn-success approve_class">
									<a id="submit" title="Approve" href="javascript:void(0)"><em> </em>Approve	</a>
								</div>
							</div>
						</div>
						<div id="145" class="col-xs-12 list_height clear more_item orderswipe orderswipemore145" style="display:none;overflow-y:auto;">
							<div class="text-center col-xs-12 no-padding ">
								<a id="linkNew0" class="Swipe_up" style="cursor:pointer" href="javascript:void(0)">Swipe down for hide</a>
							</div>
							<div class="col-xs-6 no-padding clear">Burger1478 </div>
							<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
							<div class="col-xs-4 no-padding text-right">$18.00</div>
							
							<div class="col-xs-6 no-padding clear">Cake2 </div>
							<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
							<div class="col-xs-4 no-padding text-right">$18.00</div>
							
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>	
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>	
										
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended">Total </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended"><strong>$100.20</strong></div>
						</div>
					</div>
				</div>
				<div class="col-md-4 col-sm-6 col-lg-3 col-xs-12">
					<div class="small-box" style="height:309px;">
						<div id="146" class="orderswipe orderswipeless146">
							<div class="col-md-3 col-lg-4 col-xs-3">
								<a class="fancybox" title="Customer Us" href="http://172.21.4.104/tuplit/admin/webresources/uploads/users/1_1405935748.png">
								<img width="65" height="65" alt="" src="http://172.21.4.104/tuplit/admin/webresources/uploads/users/thumbnail/1_1405935748.png">
								</a>
							</div>
							<div class="col-md-9 col-lg-8 col-xs-9 min-height90 no-padding-left">
								<span class="text-small" data-original-title="Customer Us" title="Customer Us" data-toggle="tooltip">Customer Us</span>
								<span class="help-block no-margin">customer1@mail.com</span>
								<span class="help-block no-margin HelveticaNeueBold">tuplit3971</span>
								<span class="help-block no-margin time_post">5 hrs ago</span>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-12 list_height clear more_item">
								<div class="col-xs-6 no-padding clear">Burger1478 </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$18.00</div>
								
								<div class="col-xs-6 no-padding clear">Cake2 </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$18.00</div>
								<div class="text-center col-xs-12 no-padding ">
									<a id="linkNew0" class="Swipe_up" href="javascript:void(0)" style="cursor:pointer">Swipe up for more</a>
								</div>
							</div>
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended">Total </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended"><strong>$100.20</strong></div>
							<div class="col-xs-12 no-padding min-height40 approveReject">
								<div class="col-xs-4 btn btn-default Rejected_class">
									<a class="text-red" onclick="return approveReject('reject');" href="?Reject=118"><em> </em>	Reject	</a>
								</div>
								<div class="col-xs-8 text-right btn btn-success approve_class">
									<a id="submit" title="Approve" href="javascript:void(0)"><em> </em>Approve	</a>
								</div>
							</div>
						</div>
						<div id="146" class="col-xs-12 list_height clear more_item orderswipe orderswipemore146" style="display:none;overflow-y:auto;">
							<div class="text-center col-xs-12 no-padding ">
								<a id="linkNew0" class="Swipe_up" style="cursor:pointer" href="javascript:void(0)">Swipe down for hide</a>
							</div>
							<div class="col-xs-6 no-padding clear">Burger1478 </div>
							<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
							<div class="col-xs-4 no-padding text-right">$18.00</div>
							
							<div class="col-xs-6 no-padding clear">Cake2 </div>
							<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
							<div class="col-xs-4 no-padding text-right">$18.00</div>
							
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
							<div class="col-xs-12 no-padding otherItemsNew0">
								<div class="col-xs-6 no-padding">Buger </div>
								<div class="col-xs-2 no-padding text-right price_cal"><span>x</span>1</div>
								<div class="col-xs-4 no-padding text-right">$10.80</div>
							</div>
																	
							<div class="col-xs-12 no-padding"><hr></div>
							<div class="col-xs-8 HelveticaNeueBoldExtended">Total </div>
							<div class="col-xs-4 text-right HelveticaNeueBoldExtended"><strong>$100.20</strong></div>
						</div>
					</div>
				</div>
			</div>
			<!-- /.box-body -->
		</div>
 </div>
</section>
<?php footerLogin();  commonFooter(); ?>
<script type="text/javascript">
	$canDrag	=	true;
	$(document).ready(function() {
		if($(".orderswipe").length > 0) {
			$(".orderswipe").draggable({
				axis : "y",
				delay: 200,
				start:function() {
					$(this).data("ui-draggable").options.revert = false;
					if($canDrag)
						return true;
					else
						return false;
				},
				drag:function(event,ui) {
					$pos = ui.position.top;
					var id	=	$(this).attr("id")
					if($pos < 0) {
						$(this).data("ui-draggable").options.revert = true;
						$('.orderswipeless'+id).slideUp();
						$('.orderswipemore'+id).slideDown();
					} 
					else if(Math.abs($pos) > 0) {
						$(this).data("ui-draggable").options.revert = true;
						$('.orderswipeless'+id).slideDown();
						$('.orderswipemore'+id).slideUp();
					} 				
				},
				stop:function(event,ui) {
				}
			});
		}		
	});
</script>
</html>
