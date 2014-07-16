<?php
class AnalyticsController extends Controller
{
   function getTotalRecordCount()
	{
		if (!isset($this->AnalyticsModelObj))
			$this->loadModel('AnalyticsModel', 'AnalyticsModelObj');
		if ($this->AnalyticsModelObj)
			return $this->AnalyticsModelObj->getTotalRecordCount();
	}
	function getAnalyticsList($fields,$condition)
	{
		if (!isset($this->AnalyticsModelObj))
			$this->loadModel('AnalyticsModel', 'AnalyticsModelObj');
		if ($this->AnalyticsModelObj)
			return $this->AnalyticsModelObj->getAnalyticsList($fields,$condition);
	}	
	function getOrdersDetail($fields,$id,$order_by,$limit){
		if (!isset($this->AnalyticsModelObj))
			$this->loadModel('AnalyticsModel', 'AnalyticsModelObj');
		if ($this->AnalyticsModelObj)
			return $this->AnalyticsModelObj->getOrdersDetail($fields,$id,$order_by,$limit);	
	}
}
?>