<?php
class TransferController extends Controller
{
   function getTotalRecordCount()
	{
		if (!isset($this->TransferModelObj))
			$this->loadModel('TransferModel', 'TransferModelObj');
		if ($this->TransferModelObj)
			return $this->TransferModelObj->getTotalRecordCount();
	}
	function getTransferList($condition)
	{
		if (!isset($this->TransferModelObj))
			$this->loadModel('TransferModel', 'TransferModelObj');
		if ($this->TransferModelObj)
			return $this->TransferModelObj->getTransferList($condition);
	}	
	function getUserDetail(){
		if (!isset($this->TransferModelObj))
			$this->loadModel('TransferModel', 'TransferModelObj');
		if ($this->TransferModelObj)
			return $this->TransferModelObj->getUserDetail();	
	}
}
?>