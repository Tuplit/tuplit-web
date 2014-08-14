<?php
class LocationController extends Controller
{
    function getTotalRecordCount()
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->getTotalRecordCount();
	}
	function getLocationList()
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->getLocationList();
	}
	function getLocationDetails($id)
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->getLocationDetails($id);
	}
	function insertLocationDetails($post_values)
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->insertLocationDetails($post_values);
	}
	function updateLocationDetails($post_values)
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->updateLocationDetails($post_values);
	}
	function deleteLocation($ids)
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->deleteLocation($ids);
	}	
	function checkExist($post_values)
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->checkExist($post_values);
	}	
	function updateDetails($update_string,$condition)
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->updateDetails($update_string,$condition);
	}
	function getLocationArray($field,$condition)
	{
		if (!isset($this->LocationModelObj))
			$this->loadModel('LocationModel', 'LocationModelObj');
		if ($this->LocationModelObj)
			return $this->LocationModelObj->getLocationArray($field,$condition);
	}
}
?>