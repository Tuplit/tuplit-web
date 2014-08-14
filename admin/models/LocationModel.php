<?php
class LocationModel extends Model
{
	function getLocationList() {
		$limit_clause	=	$condition	=	'';
		$sorting_clause = ' l.id desc';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		if(isset($_SESSION['tuplit_sess_Location_code']) && $_SESSION['tuplit_sess_Location_code'] != '')
			$condition .= " and l.Code  like '%".$_SESSION['tuplit_sess_Location_code']."%'";
		if(isset($_SESSION['tuplit_sess_Location_name']) && $_SESSION['tuplit_sess_Location_name'] != '')
			$condition .= " and l.Location  like '%".strtolower($_SESSION['tuplit_sess_Location_name'])."%'";
		if(isset($_SESSION['tuplit_sess_Location_status']) && $_SESSION['tuplit_sess_Location_status'] != '')
			$condition .= " and l.Status  = ".$_SESSION['tuplit_sess_Location_status'];
		if(isset($_SESSION['tuplit_sess_Location_registerdate']) && $_SESSION['tuplit_sess_Location_registerdate'] != '')
			$condition .= " and date(l.DateCreated)  = '".$_SESSION['tuplit_sess_Location_registerdate']."'";		
		
		$sql	 	= 	"select SQL_CALC_FOUND_ROWS * from {$this->locationTable} l where 1 ".$condition." and Status in (1,2) ORDER BY ".$sorting_clause." ".$limit_clause;
		//$sql	 	= 	"select SQL_CALC_FOUND_ROWS * from {$this->locationTable} l where 1 ".$condition." and Status in (1,2) ORDER BY l.id desc ".$limit_clause;
		$result		=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}

   function getTotalRecordCount() {
		$result = $this->sqlCalcFoundRows();
		return $result;
	}
	
	
	function getLocationDetails($id){
		$sql	 =	"select * from {$this->locationTable} where id='".$id."'";
		$result	 =	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
	
	function insertLocationDetails($post_values){																 
		$sql	 =	"insert into  {$this->locationTable}  set Code	    	= 	'".trim($post_values['LocationCode'])."',
															Location		=	'".strtolower(trim($post_values['LocationName']))."',
															Status			=	'".$post_values['Status']."',
														  	DateCreated 	=	'".date('Y-m-d H:i:s')."'";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
        return $insertId;
	}
	
	function updateLocationDetails($post_values){																 
		$sql	 =	"update  {$this->locationTable}  set Code	    	= 	'".trim($post_values['LocationCode'])."',
														Location		=	'".strtolower(trim($post_values['LocationName']))."',
														Status			=	'".$post_values['Status']."',
														DateModified 	=	'".date('Y-m-d H:i:s')."' 
														where id		=	'".$post_values['location_id']."'";
		return $this->updateInto($sql);
	}	
	
	function deleteLocation($ids){																 
		$sql	 =	"update  {$this->locationTable}  set Status			=	'3',
														DateModified 	=	'".date('Y-m-d H:i:s')."' 
														where id in (".$ids.")";
		$res	= $this->updateInto($sql);
		if($res) {
			$sql	=	"update  {$this->currencyTable}  set Status			=	'3',
														DateModified 	=	'".date('Y-m-d H:i:s')."' 
														where fkLocationId in (".$ids.")";
			return	$this->updateInto($sql);
		}
	}
	
	function checkExist($post_values){	
		$condition	=	'';
		if(isset($post_values['idedit']) && !empty($post_values['idedit']))
			$condition	=	" and id != '".$post_values['idedit']."'";
		$sql	 =	"select * from {$this->locationTable} where 1 and (Code ='".trim($post_values['LocationCode'])."' || Location='".strtolower(trim($post_values['LocationName']))."') ".$condition;
		$result	 =	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
	function updateDetails($update_string,$condition){
		$sql	 =	"update {$this->locationTable}  set ".$update_string." where ".$condition;
		//echo "<br/>======".$sql;
		$this->updateInto($sql);
	}
	function getLocationArray($field,$condition)
	{
		$sql	 =	"select ".$field." from {$this->locationTable} where ".$condition."";
		$result	 =	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;
	}
}
?>