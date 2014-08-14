<?php
class OrderModel extends Model
{
   function getOrderList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' o.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_order_user_name']) && $_SESSION['tuplit_sess_order_user_name'] != '')
			$condition .= " and ( u.FirstName LIKE '%".trim($_SESSION['tuplit_sess_order_user_name'])."%' || u.LastName LIKE '%".trim($_SESSION['tuplit_sess_order_user_name'])."%' )";
		if(isset($_SESSION['tuplit_sess_order_company_name']) && $_SESSION['tuplit_sess_order_company_name'] != '')
			$condition .= " and ( m.CompanyName LIKE '%".trim($_SESSION['tuplit_sess_order_company_name'])."%')";
		if(isset($_SESSION['tuplit_sess_order_price']) && $_SESSION['tuplit_sess_order_price'] != '')
			$condition .= " and o.TotalPrice = '".$_SESSION['tuplit_sess_order_price']."' ";
		if(isset($_SESSION['tuplit_sess_order_status']) && $_SESSION['tuplit_sess_order_status'] != '')
			$condition .= " and o.orderStatus LIKE '%".trim($_SESSION['tuplit_sess_order_status'])."%' ";
		if(isset($_SESSION['tuplit_sess_trans_id']) && $_SESSION['tuplit_sess_trans_id'] != '')
			$condition .= " and o.TransactionId LIKE '%".$_SESSION['tuplit_sess_trans_id']."%' ";
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->orderTable} as o
				left join users as u on  (u.id	= o.fkUsersId )
				left join merchants as m on (m.id = o.fkMerchantsId)
				WHERE 1".$condition." group by o.id ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>";   print_r($result);   echo "</pre>";
		if(count($result) == 0) return false;
		else return $result;		
	}

   function getTotalRecordCount() {
		$result = $this->sqlCalcFoundRows();
		return $result;
	}
	
	function selectOrderDetail($id){
		$sql	 =	"select p.*,p.Status as OrderStat ,m.CompanyName,m.DiscountTier as Discount,m.Icon,m.CompanyName,pc.CategoryName from {$this->productTable} p 
					left join merchants as m on m.id = p.fkMerchantsId 
					left join productcategories as pc on pc.id = p.fkCategoryId where p.id=".$id;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>";   print_r($result);   echo "</pre>";		
		if($result) return $result;
		else false;
	}
	function updateOrderDetails($update_string,$condition){
		$sql	 =	"update {$this->productTable}  set ".$update_string." where ".$condition;
		//echo "<br/>======".$sql;
		$this->updateInto($sql);
	}
	function insertOrderDetails($post_values){
																 
		$sql	 =	"insert into  {$this->productTable}  set ItemName	    = '".trim($post_values['ItemName'])."',
															fkMerchantsId	=	'".$post_values['Merchant']."',
															fkCategoryId	=	'".$post_values['Category']."',
															DiscountApplied	=	'".$post_values['DiscountApplied']."',
															Price			= 	'".$post_values['ItemPrice']."',
														  	Status 			= 	'".$post_values['Status']."',
														  	DateCreated 	=	'".date('Y-m-d H:i:s')."',
															DateModified	=	'".date('Y-m-d H:i:s')."'";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
        return $insertId;
	}
	function selectOrderDetails($field,$condition){
		$sql	 =	"select ".$field." from {$this->productTable} where ".$condition;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function getCartList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' c.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->cartTable} as c
				left join orders as o on  (o.fkCartId	= c.CartId )
				left join products as p on (p.id = c.fkProductsId)
				WHERE 1".$condition." group by c.id ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>";   print_r($result);   echo "</pre>";
		if(count($result) == 0) return false;
		else return $result;		
	}
	
	function getOverallOrderList($fields = '',$condition = '')
	{
		$fields = "o.*";		
		$sql = "select ".$fields." from {$this->orderTable} as o
				WHERE 1 ".$condition;		
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
	function getTransactionList($field,$condition)
	{
		$search_condition = '';
		if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id'] != '')
			$search_condition .= " and o.fkMerchantsId = ".$_SESSION['tuplit_sess_merchant_id']." ";
			
		$sql 				= 	"SELECT  count(id) as TotalOrders,SUM(TotalPrice) as TotalPrice ".$field." from orders as o 
								where 1 and  o.OrderStatus IN (1)  ".$search_condition." ".$condition."";	
							//	echo '-->'. $sql .'<br>';
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}	
	function getProductTransactions($field,$condition,$sort_condition)
	{
		$search_condition = '';
		if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id'] != ''){
			$search_condition .= " and o.fkMerchantsId = ".$_SESSION['tuplit_sess_merchant_id']." ";
			$search_condition .= " and c.fkMerchantsId  = ".$_SESSION['tuplit_sess_merchant_id']." ";
			$search_condition .= " and p.fkMerchantsId  = ".$_SESSION['tuplit_sess_merchant_id']." ";
		}
			
		$sql 				= 	"SELECT  p.ItemName as Name,p.id as ProductId ,SUM(ProductsQuantity) as TotalQuantity,SUM(c.TotalPrice) as TotalPrice,
									COUNT(o.id) as TotalOrders ".$field." from orders as o 
									left join carts as c ON (c.CartId = o.fkCartId)
		                         	left join products as p on(p.id = c.fkProductsId)
									where 1  and  o.OrderStatus IN (1) ".$search_condition."   ".$condition." ".$sort_condition."";	
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}	
	function getCategoryTransactions($field,$condition,$sort_condition)
	{
		$search_condition = '';
		if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id'] != ''){
			$search_condition .= " and o.fkMerchantsId = ".$_SESSION['tuplit_sess_merchant_id']." ";
		//$search_condition .= " and pc.fkMerchantId IN( ".$_SESSION['tuplit_sess_merchant_id'].",0) ";
			$search_condition .= " and c.fkMerchantsId  = ".$_SESSION['tuplit_sess_merchant_id']." ";
			$search_condition .= " and p.fkMerchantsId  = ".$_SESSION['tuplit_sess_merchant_id']." ";
		}
			
		$sql 				= 	"SELECT  pc.CategoryName as Name,p.ItemType,p.fkCategoryId as CategoryId,SUM(ProductsQuantity) as TotalQuantity,SUM(c.TotalPrice) as TotalPrice,
									COUNT(o.id) as TotalOrders ".$field." from orders as o
									left join carts as c ON (c.CartId = o.fkCartId)
		                         	left join products as p on(p.id = c.fkProductsId)
									left join productcategories as pc on(pc.id = p.fkCategoryId )
									where 1  and  o.OrderStatus IN (1) ".$search_condition."   ".$condition." ".$sort_condition."";	
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}	
	function getPieChart($field,$condition)
	{
		$search_condition = '';
		if(isset($_SESSION['tuplit_sess_merchant_id']) && $_SESSION['tuplit_sess_merchant_id'] != ''){
			$search_condition .= " and c.fkMerchantsId  = ".$_SESSION['tuplit_sess_merchant_id']." ";
		}
			
		$sql 			= 		"select (select sum( c.TotalPrice ) from `carts` AS c
									left join orders as o on (c.CartId = o.fkCartId)
									left join products as p on (p.id = c.fkProductsId)
									where ( (c.`ProductsCost` = c.DiscountPrice and p.ItemType IN(2,3)) or (c.`ProductsCost` > c.DiscountPrice and p.ItemType = 1)) and o.OrderStatus IN (1)  ".$search_condition ."  ".$condition."  
									limit 0,1)  as specialProducts ,
									(select sum( c.TotalPrice ) from `carts` AS c
									left join orders as o on (c.CartId = o.fkCartId)
									left join products as p on (p.id = c.fkProductsId)
									where (c.`ProductsCost` = c.DiscountPrice and p.ItemType IN(1)) and o.OrderStatus IN (1) 	".$search_condition ."  ".$condition." 
									limit 0,1)  as normalProducts
 									from `carts` where 1  limit 0,1" ;	
	   $result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}	
	function getUserTransactions($fields,$conditions)
	{
	
		$sql =			"SELECT DISTINCT o.fkUsersId, u.FirstName, u.LastName, u.MangoPayUniqueId, u.WalletId FROM `orders` o
						LEFT JOIN users u ON ( o.fkUsersId = u.id )
						WHERE 1 AND ".$conditions." AND u.MangoPayUniqueId != '' AND u.WalletId != ''";			
		 $result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}
}
?>