<?php

/**
 * Description of Friends
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
require_once	'Orders.php';

class Friends extends RedBean_SimpleModel implements ModelBaseInterface {

	/**
	* Constructor
	*/
    public function __construct() {

    }
	
	/**
	* Insert friends
	*/
    public function create(){ 
		/**
		* Get the bean
		* @var $bean friends
		*/
        $bean 	= 	$this->bean;
		
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
		$bean 	= 	$this->bean;
		$rules 	= 	[
						'required' => [
							['UsersId'],['FriendUsersId']
						]
					];
        $v 		= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
            throw new ApiException("Please check the friends properties." ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/*
	* validate create
	*/
	public function validateCreate(){
	
		/**
		* Get the bean
		* @var $bean friends
		*/
        $bean 	= 	$this->bean;
		
	}
	
	/**
	* Create an user account
	* Validation for email,fbId,GooglePlusId
	*/
    public function checkInviteFriends(){ // Tuplit user creating
		/**
		* Get the bean
		* @var $bean Friends
		*/
        $bean 						= 	$this->bean;
        $bean->DateCreated 			= 	date('Y-m-d H:i:s');
        $bean->DateModified 		= 	$bean->DateCreated;
		$response					=  	array();
		if($bean->GoogleFriends){
			$fbids = '';
			foreach($bean->GoogleFriends as $key=>$value){
				$fbids .= $value.',';
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
			foreach($bean->GoogleFriends as $fkey=>$value){
				$response['GoogleFriends'][$fkey]['id'] = $value;
				if(in_array($value,$invitedIdArray))
					$response['GoogleFriends'][$fkey]['AlreadyInvited'] = 1;
				else
					$response['GoogleFriends'][$fkey]['AlreadyInvited'] = 0;
			}
		}
		if($bean->ContactFriends){
			$contactids = '';
			foreach($bean->ContactFriends as $key=>$value){
				$contactids .= $value.',';
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
				$response['ContactFriends'][$ckey]['id'] = $value;
				if(in_array($value,$invitedContactArray))
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
    public function usersFriendsList($type = ''){ // Tuplit user friendlist
		/**
		* Get the bean
		* @var $bean Friends
		*/
        $bean 				= 	$this->bean;
		if(isset($bean->Start))
			$start				=	$bean->Start;
		else
			$start			=	0;
		$friendsId	=	$search = '';
		$friendsListArray	= 	$friendsIdArray	=	array();
		
		$condition			=	" and WalletId != '' ";
		if(isset($bean->Search) && !empty($bean->Search)){
			$search			=	$bean->Search;
			$condition		.=	" and (FirstName like '%".trim($search)."%' || LastName like '%".trim($search)."%') ";
		}
		if($bean->UserId != '') {
			$userId			=	$bean->UserId;
			$sql 			= 	"SELECT  *  FROM friends 
									where  (fkUsersId = ".$userId." or fkFriendsId = ".$userId.") and Status = 1 ";
	   		$friends		= 	R::getAll($sql);			
			if($friends){
				foreach($friends as $val) {
					if($val['fkFriendsId'] != $userId) {
						if(!in_array($val['fkFriendsId'],$friendsIdArray))							
							$friendsIdArray[]	=	$val['fkFriendsId'];
					}
					if($val['fkUsersId'] != $userId) {
						if(!in_array($val['fkUsersId'],$friendsIdArray))						
							$friendsIdArray[]	=	$val['fkUsersId'];
					}		
				}
				$friendsId	=	implode(',',$friendsIdArray);
					//echo "=======>".$friendsId;
				if(!empty($friendsId)){
					//Getting user Info
					$dis_user 				= 	R::dispense('orders');
					$dis_user->friendsId	=	$friendsId;
					$dis_user->userId		=	$userId;
					$dis_user->condition	=	$condition;	
					$dis_user->start		=	$start;	
					// have to remove after friends model implementation
					$usersFriendsList		=	$dis_user->getFriendsInfo($type);
					return $usersFriendsList;
				}
			}
			else if($search	!= ''){// have to remove after friends model implementation
					$dis_user 				= 	R::dispense('orders');
					$dis_user->userId		=	$userId;
					$dis_user->condition	=	$condition;	
					$dis_user->start		=	$start;	
					// have to remove after friends model implementation
					$dis_user->search		=	$search;
					$usersFriendsList		=	$dis_user->getFriendsInfo($type);
					return $usersFriendsList;
			}
		}
	}
	
	/**
	* @param  user invitation
	*/
    public function inviteFriend(){
	
		/**
		* Get the bean
		* @var $bean Friends
		*/
		$bean 				= 	$this->bean;
		$userId				=	$bean->UserId;
		$fbId				=	$bean->GoogleId;
		$inviteType			=	$bean->InviteType;
		$cellNumber			=	$bean->CellNumber;

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
		if($bean->GoogleId == '' && $bean->CellNumber == '')
			$this->validateParams();
			
		$this->validateInvite();
		
		$invite 						= 	R::dispense('invites');
		$invite->fkUsersId 				=	$userId;
		$invite->inviteType 			=	$inviteType;	
		$invite->OtherUserUniquevalue  	=   $uniqueValue;	
		$inviteId 						=  	R::store($invite);
		return $inviteId;
	}
	
	/**
	* validate FbId and invite type
	* @throws ApiException if the models fails to validate
	*/
	public function validateParams()
    {
		$bean 		= 	$this->bean;
		if($bean->GoogleId == ''){
		  	$rules 	= 	[
							'required' => [
								 ['GoogleId']
							],
							
						];
		}
		else if($bean->CellNumber == ''){
			$rules 	= 	[
							'required' => [
								 ['CellNumber']
							],
							
						];
		}
		
        $v 			= 	new Validator($this->bean);
        $v->rules($rules);
        if (!$v->validate()) {
            $errors = $v->errors();
			// the action was not found
            throw new ApiException("Please check the invite properties. Fill FbId,CellNumber with correct values" ,  ErrorCodeType::SomeFieldsRequired, $errors);
        }
    }
	
	/**
	* Validate the modification
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
		* @var $bean Friends
		*/
        $bean = $this->bean;
		if($bean->GoogleId != '')
	        $existingAccount = R::findOne('invites', 'OtherUserUniquevalue = ? and fkUsersId = ?  and inviteType = 1', array($bean->GoogleId,$bean->UserId));
		else if($bean->CellNumber != '')
	        $existingAccount = R::findOne('invites', 'OtherUserUniquevalue = ? and fkUsersId = ? and inviteType = 2', array($bean->CellNumber,$bean->UserId));
        if ($existingAccount) {
            // an account with that FacebookId or CellNumber already exists in the system - don't create invite
            throw new ApiException("Already invitation sent", ErrorCodeType::AlreadyInvited);
		}
	}
	
	/**
	* @param  insert facebook friend
	*/
    public function insertFBFriends(){
	
		/**
		* Get the bean
		* @var $bean Friends
		*/
		$bean 				= 	$this->bean;
		$userId				=	$bean->UserId;
		$fbId				=	$bean->OtherUserUniquevalue;
		if($fbId != '' && $userId != ''){
			$inviteType		=	 1;
			$sql 			= 	"select fkUsersId as friendsId from invites where OtherUserUniquevalue = ".$fbId." and inviteType = 1 ";
			$inviteResult 	= 	R::getAll($sql);
			if(isset($inviteResult) && count($inviteResult) > 0){
				foreach($inviteResult as $key=>$value){
					$friends 						= 	R::dispense('friends');
					$friends->fkUsersId 			=	$userId;
					$friends->fkFriendsId 			=	$value['friendsId'];	
					$friends->Status  				=   1;	
					$friends->DateCreated			=   date('Y-m-d H:i:s');
					$friendsId 						=  	R::store($friends);
				}
			}
		}
	}
}
