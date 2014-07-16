<?php
class TransferModel extends Model
{
   function getTransferList($condition)
	{
		
		$limit_clause	=	'';
		$left			=	'';
		if(!empty($_SESSION['ordertype']))
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		
		if(isset($_SESSION['tuplit_sess_DebitedName']) && $_SESSION['tuplit_sess_DebitedName'] != '') {
			$left 			= 	" left join users u on (t.fkUsersId = u.id || t.fkTransferUsersId = u.id) ";
			$condition		.=	" and (u.FirstName like '%".$_SESSION['tuplit_sess_DebitedName']."%' || u.LastName like '%".$_SESSION['tuplit_sess_DebitedName']."%') ";
		}/* else 	if(isset($_SESSION['tuplit_sess_CreditedName']) && $_SESSION['tuplit_sess_CreditedName'] != '') {
			$left 			= 	" left join users u on (t.fkUsersId = u.id || t.fkTransferUsersId = u.id) ";
			$condition		.=	" and (u.FirstName like '%".$_SESSION['tuplit_sess_CreditedName']."%' || u.LastName like '%".$_SESSION['tuplit_sess_CreditedName']."%') ";
		}*/
		if(isset($_SESSION['tuplit_sess_TransferAmount']) && $_SESSION['tuplit_sess_TransferAmount'] != '')
			$condition 		.= 	" and t.Amount >='".$_SESSION['tuplit_sess_TransferAmount']."'";

		if(isset($_SESSION['tuplit_sess_TransferDateFrom']) && $_SESSION['tuplit_sess_TransferDateFrom'] != '' && isset($_SESSION['tuplit_sess_TransferDateTo']) && $_SESSION['tuplit_sess_TransferDateTo'] != '')
			$condition 		.= 	" AND ((date(t.TransferDate) >=  '".date('Y-m-d',strtotime($_SESSION['tuplit_sess_TransferDateFrom']))."' and date(t.TransferDate) <= '".date('Y-m-d',strtotime($_SESSION['tuplit_sess_TransferDateTo']))."') ) ";
		else if(isset($_SESSION['tuplit_sess_TransferDateFrom']) && $_SESSION['tuplit_sess_TransferDateFrom'] != '')
			$condition 		.= 	" AND date(t.TransferDate) >=  '".date('Y-m-d',strtotime($_SESSION['tuplit_sess_TransferDateFrom']))."'";
		else if(isset($_SESSION['tuplit_sess_TransferDateTo']) && $_SESSION['tuplit_sess_TransferDateTo'] != '')
			$condition 		.= 	" AND date(t.TransferDate) ==  '".date('Y-m-d',strtotime($_SESSION['tuplit_sess_TransferDateTo']))."'";
			
			
		$sql = "select SQL_CALC_FOUND_ROWS t.*  from {$this->transferTable} as t ".$left." 	
				WHERE 1".$condition."  ORDER BY ".$sorting_clause." ".$limit_clause;
		//echo "	<br/><br/><br/><br/>======".$sql;		
		$result	=	$this->sqlQueryArray($sql);
		//echo "<pre>"; echo print_r($result); echo "</pre>";
		if(count($result) == 0) return false;
		else return $result;
	}
   function getTotalRecordCount()
	{
		$result = $this->sqlCalcFoundRows();
        return $result;
	}
	
	function getUserDetail(){
		$sql	 =	"SELECT distinct u.id,u.`FirstName`,u.`LastName` FROM {$this->transferTable} t 
						left join users u on (t.fkUsersId = u.id) where 1 and u.Status=1";
		$result = 	$this->sqlQueryArray($sql);
		if($result) {
			$userIds	=	'';
			foreach($result as $key=>$value) {
				if(!empty($userIds))
					$userIds	.=	','.$value->id;
				else 
					$userIds	=	$value->id;
			}
			
			if(!empty($userIds)) {
				$sql2	 	=	"SELECT distinct u.id,u.`FirstName`,u.`LastName` FROM {$this->transferTable} t 
									left join users u on (t.fkTransferUsersId = u.id) where 1 and u.Status=1 and t.fkTransferUsersId not in (".$userIds.")";
				$result2 	= 	$this->sqlQueryArray($sql2);
				if($result2) {
					$out	=	array_merge($result,$result2);
					return  $out;
				} 	else {
					return $result;
				}
			}
		}		
		else false;
	}
}
?>