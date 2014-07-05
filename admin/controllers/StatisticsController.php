<?php
class StatisticsController extends Controller
{
  	 function getTotalRecordCount()
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getTotalRecordCount();
	}
	function getTopUsersList($fields,$condition)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getTopUsersList($fields,$condition);
	}
	function getTopMerchantsList($fields,$condition)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getTopMerchantsList($fields,$condition);
	}
	function getRegisteredUsers($fields,$condition)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getRegisteredUsers($fields,$condition);
	}
	function getRegisteredMerchants($fields,$condition)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getRegisteredMerchants($fields,$condition);
	}
	function getOverallStatOrderList($condition)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getOverallStatOrderList($condition);
	}
	function getorderlistbydate($fields,$condition)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getorderlistbydate($fields,$condition);
	}
}
?>