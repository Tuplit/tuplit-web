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
	
	
}
?>