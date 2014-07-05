<?php
class ProductModel extends Model
{
   function getProductList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' p.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['item_sess_name']) && $_SESSION['item_sess_name'] != '')
			$condition .= " and p.ItemName  LIKE '%".trim($_SESSION['item_sess_name'])."%' ";
		if(isset($_SESSION['item_sess_cost']) && $_SESSION['item_sess_cost'] != '')
			$condition .= " and p.Price LIKE '".$_SESSION['item_sess_cost']."%' ";
		if(isset($_SESSION['item_sess_mername']) && $_SESSION['item_sess_mername'] != '')
			$condition .= " and m.CompanyName LIKE '%".trim($_SESSION['item_sess_mername'])."%' ";
		if(isset($_SESSION['item_sess_product_discount']) && $_SESSION['item_sess_product_discount'] != '')
			$condition .= " and p.DiscountApplied = '".$_SESSION['item_sess_product_discount']."' ";
		if(isset($_SESSION['item_sess_product_category']) && $_SESSION['item_sess_product_category'] != '')
			$condition .= " and p.fkCategoryId = '".$_SESSION['item_sess_product_category']."' ";
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->productTable} as p	
				left join merchants as m on m.id = p.fkMerchantsId
				left join productcategories as pc on pc.id = p.fkCategoryId
				WHERE 1".$condition." group by p.id ORDER BY ".$sorting_clause." ".$limit_clause;
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
	
	function selectProductDetail($id){
		$sql	 =	"select p.*,p.Status as ProductStat ,m.CompanyName,m.DiscountTier as Discount,m.Icon,m.CompanyName,pc.CategoryName from {$this->productTable} p 
					left join merchants as m on m.id = p.fkMerchantsId 
					left join productcategories as pc on pc.id = p.fkCategoryId where p.id=".$id;
		//echo "<br/>======".$sql;
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>";   print_r($result);   echo "</pre>";		
		if($result) return $result;
		else false;
	}
	function updateProductDetails($update_string,$condition){
		$sql	 =	"update {$this->productTable}  set ".$update_string." where ".$condition;
		//echo "<br/>======".$sql;
		$this->updateInto($sql);
	}
	function insertProductDetails($post_values){
																 
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
	function selectProductDetails($field,$condition){
		$sql	 =	"select ".$field." from {$this->productTable} where ".$condition;
		//echo "<br/>======".$sql;
		$result = 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
	function getProductNamesList($id){
		$sql	 	=	"select id, ItemName from {$this->productTable} where fkMerchantsId = ".$id;
		//echo "<br/>======".$sql;
		$result 	= 	$this->sqlQueryArray($sql);
			if($result) return $result;
			else false;
	}
		
}
?>