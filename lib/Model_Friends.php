<?php

/**
 * Description of Model_Friends
 *
 * @author 
 */
use RedBean_Facade as R;
use Helpers\PasswordHelper as PasswordHelper;
use Enumerations\HttpStatusCode as HttpStatusCode;
use Enumerations\AccountType as AccountType;
use Enumerations\StatusType as StatusType;
use Enumerations\ErrorCodeType as ErrorCodeType;
use Exceptions\ApiException as ApiException;
use Valitron\Validator as Validator;
use Helpers\RedBeanHelper as RedBeanHelper;

//Require needed models
class Model_Friends extends RedBean_SimpleModel {

    /**
     * Identifier
     * @var int
     */
    public $id;
	
    /**
     * When the record was created
     * @var int
     */
    public $DateCreated;

    /**
     * When the record was last updated
     * @var int
     */
    public $DateModified;
	/**
     * Person platform
     * @var string
     */
    public $Platform;

    /**
     * Constructor
     */
    public function __construct() {

    }
     /**
     * Create an user account
	 * Validation for email,fbId,GooglePlusId
     */
    public function checkInviteFriends(){ // Tuplit user creating
		 /**
         * Get the bean
         * @var $bean Model_Friends
         */
        $bean 		= $this->bean;
        $bean->DateCreated 			= date('Y-m-d H:i:s');
        $bean->DateModified 		= $bean->DateCreated;
		if($bean->FacebookFriends){
			$fbids = '';
			foreach($bean->FacebookFriends as $key=>$value){
				$fbids .= $value->id.',';
			}
			if($fbids != ''){
				$fbids = trim($fbids,',');
				$sql = "select OtherUserUniquevalue from invites where OtherUserUniquevalue in (".$fbids.") and inviteType = 1 and fkUsersId = ".$bean->UserId;
				$inviteResult = R::getAll($sql);
			}
			
			$invitedIdArray = array();
			if($inviteResult){
				foreach($inviteResult as $key=>$val){
					$invitedIdArray[$key] = $val['OtherUserUniquevalue'];
				}
			}
			foreach($bean->FacebookFriends as $fkey=>$value){
				$response['FacebookFriends'][$fkey]['id'] = $value->id;
				if(in_array($value->id,$invitedIdArray))
					$response['FacebookFriends'][$fkey]['AlreadyInvited'] = 1;
				else
					$response['FacebookFriends'][$fkey]['AlreadyInvited'] = 0;
			}
		}
		if($bean->ContactFriends){
			$contactids = '';
			foreach($bean->ContactFriends as $key=>$value){
				$contactids .= $value->id.',';
			}
			if($contactids != ''){
				$contactids 			= trim($contactids,',');
				$sql 					= "select OtherUserUniquevalue from invites where OtherUserUniquevalue in (".$contactids.") and inviteType = 2 and fkUsersId = ".$bean->UserId;
				$inviteContactResult 	= R::getAll($sql);
			}
			
			$invitedContactArray = array();
			if($inviteContactResult){
				foreach($inviteContactResult as $key=>$val){
					$invitedContactArray[$key] = $val['OtherUserUniquevalue'];
				}
			}
			foreach($bean->ContactFriends as $ckey=>$value){
				$response['ContactFriends'][$ckey]['id'] = $value->id;
				if(in_array($value->id,$invitedContactArray))
					$response['ContactFriends'][$ckey]['AlreadyInvited'] = 1;
				else
					$response['ContactFriends'][$ckey]['AlreadyInvited'] = 0;
			}
		}
		return $response;
    }
	 /**
     * Get user friend list
     */
    public function usersFriendsList(){ // Tuplit user friendlist
		 /**
         * Get the bean
         * @var $bean Model_Friends
         */
        $bean 			= 	$this->bean;
        $start			=	$bean->Start;
		$limit			=	$bean->Limit;
		$friendsListArray= array();
		if($bean->UserId != ''){
			$userId			=	$bean->UserId;
			$sql 			= 	"SELECT  group_concat(`fkFriendsId`,',', fkUsersId) as friendsId  FROM friends 
								where  (fkUsersId = ".$userId." or fkFriendsId = ".$userId.") and Status = 1 ";
	   		$friends		= R::getAll($sql);
			if($friends){
				if($friends[0]['friendsId'] != ''){
					$sql 			=  "SELECT SQL_CALC_FOUND_ROWS id,FirstName,LastName,Photo,Email,FBId FROM users where id IN (".$friends[0]['friendsId'].") and id != ".$userId." and Status = 1 ORDER BY FirstName asc limit $start,$limit";
	   				$friendsArray 	=  R::getAll($sql);
					$totalRec 		=  R::getAll('SELECT FOUND_ROWS() as count');
					$total 			= (integer)$totalRec[0]['count'];
					$listedCount	=  count($friendsArray);
					if($friendsArray){
						foreach($friendsArray as $key => $value){
							$user_image_path = '';
							if(isset($value['Photo']) && $value['Photo'] != ''){
								$user_image_path = USER_IMAGE_PATH.$value['Photo'];
							}
							$value['Photo'] 				= $user_image_path;
							$friendsListArray[]				= $value;
						}
					}
					$usersFriendsList['result'] 		  = $friendsListArray;
					$usersFriendsList['totalCount']   	  = $total;
					$usersFriendsList['listedCount']   	  = count($friendsListArray);
					return $usersFriendsList;
				}
			}
		}
	}
	/**
     * invite friend
	 * Validation for fbId
     */
    /**
     * @param  user invitation
     */
    public function inviteFriend(){
	
		/**
         * Get the bean
         * @var $bean Model_Friends
         */
		 $bean 			= 	$this->bean;
		 $userId		=	$bean->UserId;
		 $fbId			=	$bean->FbId;
		 $inviteType	=	$bean->InviteType;
		 $cellNumber	=	$bean->CellNumber;
		 
		 if($fbId != ''){
		 	$uniqueValue	=	$fbId;
			$inviteType		=	 1;
		}
		else if($cellNumber != ''){
			$uniqueValue	=	$cellNumber;
			$inviteType 	= 	2;
		}
		//validate user
		$this->validateUser($userId);
		//validate FbId and invite type
		if($bean->FbId == '' && $bean->CellNumber == '')
			$this->validateParams();
		$this->validateInvite();
		
		 $invite = R::dispense('invites');
		 $invite->fkUsersId 			=	$userId;
		 $invite->inviteType 			=	$inviteType;	
		 $invite->OtherUserUniquevalue  =   $uniqueValue;	
		 $inviteId =  R::store($invite);
		 return $inviteId;
	}
	/**
     * validate FbId and invite type
     * @throws ApiException if the models fails to validate
     */
	public function validateParams()
    {
		$bean = $this->bean;
		if($bean->FbId == ''){
		  	$rules = [
	            'required' => [
	                 ['FbId']
	            ],
				
	        ];
		}
		else if($bean->CellNumber == ''){
			$rules = [
	            'required' => [
	                 ['CellNumber']
	            ],
				
	        ];
		}
		
        $v = new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the invite properties. Fill FbId,CellNumber with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	/**
     * Validate the modification of an account
     * @throws ApiException if the user being modifyng the account with already exists of email , facebook and linked ids in the database.
     */
	public function validateUser($userId)
    {
        /**
         * Get the identity of the person requesting the details
         */
		$requestedBy = R::findOne('users', 'id = ? and Status = ?', [$userId,StatusType::ActiveStatus]);
		
        if (!$requestedBy) {
            // the User was not found
            throw new ApiException("Your status is not in active state", ErrorCodeType::UserNotInActiveStatus);
        }
    }
	public function validateInvite(){
		  /**
         * Get the bean
         * @var $bean Model_Friends
         */
        $bean = $this->bean;
		if($bean->FbId != '')
	        $existingAccount = R::findOne('invites', 'OtherUserUniquevalue = ? and fkUsersId = ?  and inviteType = 1', array($bean->FbId,$bean->UserId));
		else if($bean->CellNumber != '')
	        $existingAccount = R::findOne('invites', 'OtherUserUniquevalue = ? and fkUsersId = ? and inviteType = 2', array($bean->CellNumber,$bean->UserId));
        if ($existingAccount) {
            // an account with that FacebookId or CellNumber already exists in the system - don't create invite
            throw new ApiException("Already invitation sent", ErrorCodeType::AlreadyInvited);
		}
	}
}
