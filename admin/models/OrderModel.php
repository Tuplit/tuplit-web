<?php
class OrderModel extends Model
{
   function getOrderList($fields,$leftjoin,$condition,$sortby,$limit)
	{
		if(isset($limit) && $limit != ''){
			$limit_clause = 'limit '.$limit.',10';
		}else{ 
			$limit_clause='limit 0,10';
		}
		/*if(isset($_SESSION['loc_mer_name']) && $_SESSION['loc_mer_name'] != '')
			$condition .= " and m.id = ".trim($_SESSION['loc_mer_name']); 
		if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '')
			$condition .= " and o.TotalPrice = ".trim($_SESSION['loc_mer_price']); 
		if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] != '')
			$condition .= " and m.City LIKE '%".trim($_SESSION['loc_mer_city'])."%'"; 
		if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] != ''){
			$condition .= " and c.id  = ".trim($_SESSION['loc_mer_category']);
			$groupby	.= " group by fkCartId ";
		}else{
			$groupby	= " ";
		}*/
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->orderTable} as o
				".$leftjoin."
				WHERE 1".$condition." and o.Status in (1,2) ORDER BY ".$sortby." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);
		//echo "====>".$sql;
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
							//echo '-->'. $sql .'<br>';
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
		//echo $sql;
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
	/*-----merchants - transaction list-----*/
	function getTotalRevenue($field,$condition)
	{
		$sql 	= 	"SELECT  count(id) as TotalOrders,SUM(TotalPrice) as TotalPrice ".$field." from orders as o 
								where 1 and  o.OrderStatus IN (1) ".$condition;	
		//	echo $sql."<br>";
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;	
	}
	function getProductList($field,$condition)
	{
		$limit_clause = '';
		$sorting_clause = ' c.id desc';
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		$sql 	= "select SQL_CALC_FOUND_ROWS ".$field." from {$this->cartTable} as c
					left join {$this->productTable} as p on  (p.id	= c.fkProductsId)
					WHERE 1".$condition." ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
	function MerchantTransactionList($fields,$condition,$searchCond,$limit)
	{
		if(isset($limit) && $limit != ''){
			$limit_clause = 'limit '.$limit.',10';
		}else{ 
			$limit_clause='limit 0,10';
		}
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->orderTable} As o1 
				LEFT JOIN {$this->orderTable} AS o2
				ON (".$condition." AND o1.OrderDate < o2.OrderDate) 
				LEFT JOIN users as u on (u.id = o1.fkUsersId)
				Left join merchants as m on (m.id = o1.fkMerchantsId)
				WHERE o2.OrderDate IS NULL  ".$searchCond."and o1.Status in (1,2) and u.Status = 1 and m.Status = 1 ".$limit_clause;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
	function getPopularProducts($fields,$condition)
	{
		$sql 	= "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->cartTable} as c
					left join products p on (c.fkProductsId = p.id) 
					WHERE 1 and p.Status=1 ".$condition." group by c.fkProductsId ORDER BY TotalPrice desc LIMIT 0,5";
		//echo $sql;
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
	function merchantCustomerList($fields,$condition,$groupby)
	{
		
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->orderTable} as o
				WHERE 1".$condition." and o.Status in (1,2) ".$groupby;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}
	function getTransactionDetails($fields,$leftjoin,$condition,$sortby,$limit,$type)
	{
		$groupby	= "";
		if($type == 1){
			if(isset($limit) && $limit != ''){
				$limit_clause	= 'limit '.$limit.',10';
			}else{ 
				$limit_clause	='limit 0,10';
			}
		}else if($type == 2){
			$limit_clause	='limit 0,3';
		}
		if(isset($_SESSION['loc_mer_name']) && $_SESSION['loc_mer_name'] != '')
			$condition .= " and m.id = ".trim($_SESSION['loc_mer_name']); 
		if(isset($_SESSION['loc_mer_price']) && $_SESSION['loc_mer_price'] != '')
			$condition .= " and o.TotalPrice  <= '".trim($_SESSION['loc_mer_price'])."'"; 
		if(isset($_SESSION['loc_mer_city']) && $_SESSION['loc_mer_city'] != '')
			$condition .= " and m.City LIKE '%".trim($_SESSION['loc_mer_city'])."%'"; 
		if(isset($_SESSION['loc_mer_category']) && $_SESSION['loc_mer_category'] != ''){
			$condition  .= " and c.id  = ".trim($_SESSION['loc_mer_category']);
			$groupby	.= " group by fkCartId ";
		}else{
			$groupby	= " ";
		}
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->orderTable} as o
				".$leftjoin."
				WHERE 1".$condition." and o.Status in (1,2) ".$groupby." ORDER BY ".$sortby." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);
		//echo "====>".$sql;
		if(count($result) == 0) return false;
		else return $result;		
	}

}
?>