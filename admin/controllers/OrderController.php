<?php
class OrderController extends Controller
{
    function getTotalRecordCount()
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getTotalRecordCount();
	}
	function getOrderList($fields,$condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getOrderList($fields,$condition);
	}
	function selectOrderDetail($id)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->selectOrderDetail($id);
	}
	function updateOrderDetails($update_string,$condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->updateOrderDetails($update_string,$condition);
	}
	function insertOrderDetails($post_values)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->insertOrderDetails($post_values);
	}
	function selectOrderDetails($field,$condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->selectOrderDetails($field,$condition);
	}
	function getCartList($fields,$condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getCartList($fields,$condition);
	}
	function getOverallOrderList($fields,$condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getOverallOrderList($fields,$condition);
	}
	function getTransactionList($field,$condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getTransactionList($field,$condition);
	}
	function getProductTransactions($field,$condition,$sort_condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getProductTransactions($field,$condition,$sort_condition);
	}
	function getCategoryTransactions($field,$condition,$sort_condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getCategoryTransactions($field,$condition,$sort_condition);
	}
	function getPieChart($field,$condition)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getPieChart($field,$condition);
	}
	function getUserTransactions($fields,$conditions)
	{
		if (!isset($this->OrderModelObj))
			$this->loadModel('OrderModel', 'OrderModelObj');
		if ($this->OrderModelObj)
			return $this->OrderModelObj->getUserTransactions($fields,$conditions);
	}
}
?>