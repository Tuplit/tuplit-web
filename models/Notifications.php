<?php
/**
 * Description of Notifications
 *
 * @author 
 */
ini_set('default_encoding','utf-8');
use RedBean_Facade as R;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
use Exceptions\ApiException as ApiException;
use Valitron\Validator as Validator;


class Notifications extends RedBean_SimpleModel implements ModelBaseInterface {

	
	
	/**
     * @var int
     */
    public $userId;
	/**
     * @var int
     */
    public $merchantId;
	/**
     * @var int
     */
    public $toUserId;
	/**
     * @var int
     */
	 public $orderId;
	/**
     * @var int
     */
    public $Amount;
	/**
     * @var string
     */
    public $DeviceToken;
	/**
     * @var string
     */
    public $Notes;

	/**
	* Constructor
	*/
    public function __construct() {

    }

	/**
	* @param array of notification params
	*/
    public function sendNotification($type){
	
		$bean 			= 	$this->bean;
		$log_content 	= 	'';
		$blockIds 		= 	$condition = '';
		$sounds			=	'1';
		 if($type == 1)//transfer
		 {
			 $users 	= 	R::find("users", " id = ? or id =? ",[$bean->userId,$bean->toUserId]);
			 $fromName 	= 	$toId 	= 	'';
			 $log_content = "\r\n TRANSFER AMOUNT (".date('H:i:s A').")\r\n";
			
			 if($users)
			 {
			 	foreach ($users as $val) 
				{
					if($val['id'] == $bean->userId ){
						$fromName 	= 	$val['FirstName'].' '.$val['LastName'];
					}
					if($val['id'] == $bean->toUserId && $val['PushNotification'] == 1){
						$toId 		= 	$val['id'];
					}
					$sounds	=	$val['Sounds'];
					//$userArray[$thing->id] = $thing->UserName;
			 	}				
				if($fromName != '' && $toId != '')
				{
					$notes	=	'';
					if(!empty($bean->Notes))
						$notes 	= 	'Message : '.$bean->Notes;
					$amount			=	number_format($bean->Amount,2,'.',',');
					$message 		= 	ucfirst($fromName).' has transferred '.utf8_encode('£').$amount.' to your account balance';
					$endpointsArn 	= 	R::find('devicetokens','fkUsersId = ? and Status = 1',[$toId]);
					if($endpointsArn)
					{
						foreach($endpointsArn as $key=>$value){
							$this->updateBadgeForToken($value['DeviceToken'],1);
							$success = sendNotificationAWS($message,$value['EndpointARN'],$value['Platform'],$value['BadgeCount'],$type,$bean->toUserId,$bean->userId,$sounds,'','',$notes);
							if($success == '1')
								$log_content .= "\r\n To user(".$toId.") : ".$message." - Success ";
							else
								$log_content .= "\r\n To user(".$toId.") : ".$message." - Failure ";	
						}
						$log_content  .= "\r\n --------------------------------------------------------------------------------------------------------------------------------";		
						
						//log
						logEntryProcess($log_content,date('mdY').".txt"); // write the data to log file
					}
				}
			}
		 }
		 if($type == 2)//create order
		 {		
		 	$companyName 	= '';
			$sql			=	'SELECT  d.*,u.Sounds from users as u
								 left join devicetokens as d on (u.id = d.fkUsersId) where u.id = '.$bean->userId.' and  u.Status = 1  and d.Status = 1 ';// and u.PushNotification = 1
			$user			=	R::getAll($sql);
			
			if($user)
			{
				$sounds		=	$user[0]['Sounds'];
				$merchants 	= 	R::find("merchants", " id = ? ",[$bean->merchantId]);
			 	$log_content = "\r\n CREATE ORDER (".date('H:i:s A').")\r\n";
			 	if($merchants)
			 	{
					foreach ($merchants as $val) 
					{
						$companyName 	= 	ucfirst($val['CompanyName']);
					}
					$message 		= 	'Merchant "'.$companyName.'"  has created a New Order for you.';					
					$endpointsArn 	= 	R::find('devicetokens','fkUsersId = ? and Status = 1',[$bean->userId]);
					if($endpointsArn)
					{
						foreach($endpointsArn as $key=>$value){
							$this->updateBadgeForToken($value['DeviceToken'],1);
							$success = sendNotificationAWS($message,$value['EndpointARN'],$value['Platform'],$value['BadgeCount'],$type,$bean->orderId,$bean->userId,$sounds,$bean->merchantId,$companyName,'');
							if($success == '1')
								$log_content .= "\r\n To user(".$bean->userId.") : ".$message." - Success ";
							else
								$log_content .= "\r\n To user(".$bean->userId.") : ".$message." - Failure ";	
						}
						$log_content  .= "\r\n --------------------------------------------------------------------------------------------------------------------------------";		
						//log
						logEntryProcess($log_content,date('mdY').".txt"); // write the data to log file
					}
				}
			}
		 }
		 if($type == 3 || $type == 4)//Approve or reject
		 {	
		 	$companyName 	= '';
		 	$sql			=	'SELECT  d.*,u.Sounds from users as u
								 left join devicetokens as d on (u.id = d.fkUsersId) where u.id = '.$bean->userId.' and  u.Status = 1 and u.PushNotification = 1  and d.Status = 1 ';
			$user			=	R::getAll($sql);
			if($user)
			{
				$sounds		=	$user[0]['Sounds'];
				$merchants 		= 	R::find("merchants", " id = ? ",[$bean->merchantId]);		
			 	if($merchants)
			 	{
					foreach ($merchants as $val) 
					{
						$companyName 	= 	ucfirst($val['CompanyName']);
					}
					if($type == 3) {
						$log_content 	= 	"\r\n APPROVE ORDER (".date('H:i:s A').")\r\n";
						$message 	 	= 	'Merchant "'.$companyName.'"  has approved your order.';
					}
					if($type ==	4) {
						$log_content 	= 	"\r\n REJECT ORDER (".date('H:i:s A').")\r\n";
						$message 		= 	'Merchant "'.$companyName.'"  has rejected your order.';
					}	
					$endpointsArn 		= 	R::find('devicetokens','fkUsersId = ? and Status = 1',[$bean->userId]);
					if($endpointsArn) {
						foreach($endpointsArn as $key=>$value){
							$this->updateBadgeForToken($value['DeviceToken'],1);
							$success = sendNotificationAWS($message,$value['EndpointARN'],$value['Platform'],$value['BadgeCount'],$type,$bean->orderId,$bean->userId,$sounds,$bean->merchantId,$companyName,'');
							if($success == '1')
								$log_content .= "\r\n To user(".$bean->userId.") : ".$message." - Success ";
							else
								$log_content .= "\r\n To user(".$bean->userId.") : ".$message." - Failure ";	
						}
						$log_content  .= "\r\n --------------------------------------------------------------------------------------------------------------------------------";		
						//log
						logEntryProcess($log_content,date('mdY').".txt"); // write the data to log file
					}
				}
			}
		 }
    }
	
	/**
	* @param array of update badge params
	*/
    public function updateBadgeForToken($token,$process){
		
		$bean = $this->bean;
		if($process == 2){
			$rules 	= 	[
							'required' => [
								['DeviceToken']
							],
						];
			$bean->DeviceToken	= 	$token;
	        $v 					= 	new Validator($bean);
	        $v->rules($rules);
	        if (!$v->validate()) {
	            $errors = $v->errors();
	            throw new ApiException("Please check DeviceToken" , HttpStatusCode::BadRequest, $errors);
	        }
			$valueToken1 = ltrim($token,'<');
		    $token = Rtrim( $valueToken1,'>');
		
		}
		if($process == 1){
			R::exec("update devicetokens set BadgeCount = BadgeCount + 1 where DeviceToken = '".$token."'");
		}
		else{
			R::exec("update devicetokens set BadgeCount = 0 where DeviceToken = '".$token."'");
		}
	}
	
	/*
	* create notification
	*/
	public function create(){ 
		/**
		* Get the bean
		*/
        $bean 			= 	$this->bean;
		
		// validate the parameters
        $this->validate();
		
		// validate create
        $this->validateCreate();		
		
    }
	
	/**
	* Validate the model
	* @throws ApiException if the models fails to validate required fields
	*/
    public function validate()
    {
	
	}
	
	/*
	* validate create
	*/
	public function validateCreate(){
	
		/**
		* Get the bean
		*/
        $bean 	= 	$this->bean;
		
	}
}