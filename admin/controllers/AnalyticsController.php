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
	function getAnalyticsList($fields,$condition,$show)
	{
		if (!isset($this->AnalyticsModelObj))
			$this->loadModel('AnalyticsModel', 'AnalyticsModelObj');
		if ($this->AnalyticsModelObj)
			return $this->AnalyticsModelObj->getAnalyticsList($fields,$condition,$show);
	}	
	function getOrdersDetail($fields,$id,$order_by,$limit){
		if (!isset($this->AnalyticsModelObj))
			$this->loadModel('AnalyticsModel', 'AnalyticsModelObj');
		if ($this->AnalyticsModelObj)
			return $this->AnalyticsModelObj->getOrdersDetail($fields,$id,$order_by,$limit);	
	}
}
?>