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
	/*------APP VERSION-----*/
	function getAppversionList()
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->getAppversionList();
	}
	function checkAppVersionExists($device_type,$app_type)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->checkAppVersionExists($device_type,$app_type);
	}
	function addAppversion($postArray)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->addAppversion($postArray);
	}
	function updateAppversion($postArray,$id)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->updateAppversion($postArray,$id);
	}
	function deleteAppversion($id)
	{
		if (!isset($this->StatisticsModelObj))
			$this->loadModel('StatisticsModel', 'StatisticsModelObj');
		if ($this->StatisticsModelObj)
			return $this->StatisticsModelObj->deleteAppversion($id);
	}
}
?>