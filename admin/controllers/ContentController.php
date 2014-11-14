<?php
class ContentController extends Controller
{
	function insertContent($post_values)
	{
		if (!isset($this->ContentModelObj))
			$this->loadModel('ContentModel', 'ContentModelObj');
		if ($this->ContentModelObj)
			return $this->ContentModelObj->insertContent($post_values);
	}
	function getContentList($condition)
	{
		if (!isset($this->ContentModelObj))
			$this->loadModel('ContentModel', 'ContentModelObj');
		if ($this->ContentModelObj)
			return $this->ContentModelObj->getContentList($condition);
	}
	function getTotalRecordCount()
	{
		if (!isset($this->ContentModelObj))
			$this->loadModel('ContentModel', 'ContentModelObj');
		if ($this->ContentModelObj)
			return $this->ContentModelObj->getTotalRecordCount();
	}
	function selectContentDetail($id)
	{
		if (!isset($this->ContentModelObj))
			$this->loadModel('ContentModel', 'ContentModelObj');
		if ($this->ContentModelObj)
			return $this->ContentModelObj->selectContentDetail($id);
	}
	function updateContentDetail($data)
	{
		if (!isset($this->ContentModelObj))
			$this->loadModel('ContentModel', 'ContentModelObj');
		if ($this->ContentModelObj)
			return $this->ContentModelObj->updateContentDetail($data);
	}
	function deleteContent($ids)
	{
		if (!isset($this->ContentModelObj))
			$this->loadModel('ContentModel', 'ContentModelObj');
		if ($this->ContentModelObj)
			return $this->ContentModelObj->deleteContent($ids);
	}  
	/*
	function updateContent($data)
	{
		if (!isset($this->ContentModelObj))
			$this->loadModel('ContentModel', 'ContentModelObj');
		if ($this->ContentModelObj)
			return $this->ContentModelObj->updateContent($data);
	}
*/
}
?>