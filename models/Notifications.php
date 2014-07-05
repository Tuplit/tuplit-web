<?php
/**
 * Description of Notifications
 *
 * @author eventurers
 */
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
	* Constructor
	*/
    public function __construct() {

    }

	/**
	* @param array of notification params
	*/
    public function sendNotification($type){
		$log_content 	= 	'';
		$blockIds 		= 	$condition = '';
		 if($type == 1){//contact //$actionId is user Id
			 $users 	= 	R::find("users", " id = ? or id =? ",[$this->userId,$this->toUserId]);
			 $fromName 	= 	$toId 	= 	'';
			 $log_content = "\r\n TRANSFER AMOUNT (".date('H:i:s A').")\r\n";
			 if($users){
			 	foreach ($users as $val) {
					if($val['id'] == $this->userId ){
						$fromName 	= 	$val['FirstName'].' '.$val['LastName'];
					}
					if($val['id'] == $this->toUserId && $val['PushNotification'] == 1){
						$toId 		= 	$val['id'];
					}					
					//$userArray[$thing->id] = $thing->UserName;
			 	}				
				if($fromName != '' && $toId != ''){
					$message 		= 	ucfirst($fromName).' has transfered $'.$this->Amount.' to your balance';					
					$endpointsArn 	= 	R::find('devicetokens','fkUsersId = ? and Status = 1',[$toId]);
					if($endpointsArn){
						foreach($endpointsArn as $key=>$value){
							$this->updateBadgeForToken($value['DeviceToken'],1);
							$success = sendNotificationAWS($message,$value['EndpointARN'],$value['Platform'],$value['BadgeCount'],$type,$this->toUserId,$this->userId);
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
    }
	
	/**
	* @param array of update badge params
	*/
    public function updateBadgeForToken($token,$process){
		
		if($process == 2){
			$rules 	= 	[
							'required' => [
								['DeviceToken']
							],
						];
			$this->DeviceToken	= 	$token;
	        $v 					= 	new Validator($this);
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