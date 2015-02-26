<?php
class ContentModel extends Model
{
	function insertContent($post_values){
		$sql	=	"select * from {$this->webContentTable} where PageUrl='".strtolower($post_values['filename'])."' and Status=1";
		$result	=	$this->sqlQueryArray($sql);
		if($result) {
			echo "2";
		}
		else {
		$sql	 =	"insert into  {$this->webContentTable}  set PageName	    = 	'".$post_values['filename']."',
															PageUrl				=	'".strtolower($post_values['ContentUrl'])."',															
															Content				=	'".$post_values['Content']."',															
															Status				=	'1',															
														  	DateCreated 		=	'".date('Y-m-d H:i:s')."',
															DateModified		=	'".date('Y-m-d H:i:s')."'";
		$this->result = $this->insertInto($sql);
		$insertId = $this->sqlInsertId();
        if($insertId)
			echo "1";
		}
	}
	
	function getContentList($condition)
	{
		$limit_clause='';
		$sorting_clause = ' id desc';
		if(!empty($_SESSION['ordertype'])) 
			$sorting_clause = $_SESSION['orderby'] . ' ' . $_SESSION['ordertype'];
		if(isset($_SESSION['sortBy']) && isset($_SESSION['orderType']))
			$sorting_clause	= $_SESSION['sortBy']. ' ' .$_SESSION['orderType'];
		if(isset($_SESSION['curpage']))
			$limit_clause = ' LIMIT '.(($_SESSION['curpage'] - 1) * ($_SESSION['perpage'])) . ', '. $_SESSION['perpage'];
		$sql 		= 	"select SQL_CALC_FOUND_ROWS * from {$this->webContentTable} WHERE 1 ".$condition." and Status = 1 ORDER BY id desc".$limit_clause;
		$result		=	$this->sqlQueryArray($sql);
		if(count($result) == 0) return false;
		else return $result;		
	}

   function getTotalRecordCount() {
		$result 	= $this->sqlCalcFoundRows();
		return $result;
	}

	function selectContentDetail($name){
		$sql	 	=	"select * from {$this->webContentTable}  where PageUrl in (".trim($name).")";
		//echo "============>".$sql;
		$result		=	$this->sqlQueryArray($sql);
		if($result) return $result;
		else false;
	}
	
	function deleteContent($ids){
		$sql	 	=	"update  {$this->webContentTable}  set 	Status	    	= 	'2',
																DateModified	=	'".date('Y-m-d H:i:s')."' 
																where id in (".$ids.")";
		$this->updateInto($sql);
		echo "5";
	}
	
	function updateContentDetail($data){

		$sql	=	"update  {$this->webContentTable}  set 	PageName	    = 	'".$data['filename']."',
															PageUrl			=	'".strtolower($data['ContentUrl'])."',															
															Content			=	'".trim($data['Content'])."',															
															DateModified	=	'".date('Y-m-d H:i:s')."' where id='".$data['id']."'";
		//echo $sql; 
		$this->updateInto($sql);
			//echo "3";
	}
	/*
	function updateContent($data){

		$sql	=	"update  {$this->webContentTable}  set	Content			=	'".$data['Content'].													"',															
															DateModified	=	'".date('Y-m-d H:i:s')."' where id='".$data['id']."'";
		//echo $sql;die();
		$this->updateInto($sql);
			echo "3";
	}
*/
}
?>