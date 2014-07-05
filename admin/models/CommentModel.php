<?php
class CommentModel extends Model
{
   function getCommentList($fields,$condition)
	{
		$limit_clause='';
		$sorting_clause = ' com.id desc';	
		$sql = "select SQL_CALC_FOUND_ROWS ".$fields." from {$this->commentTable} as com 
		left join users as u on  (u.id	= com.fkUsersId )
		left join merchants as m on (m.id = com.fkMerchantsId)
		WHERE 1".$condition."  ORDER BY ".$sorting_clause." ".$limit_clause;
		$result	=	$this->sqlQueryArray($sql);		
		//echo "Query==>".$sql;
		if(count($result) == 0) return false;
		else return $result;	
	}
	
   function getTotalRecordCount()
	{
		$result = $this->sqlCalcFoundRows();
        return $result;
	}	
}
?>