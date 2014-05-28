<?php

/**
 * Description of Account Types
 *
 * @author 
 */

namespace Enumerations;


class ErrorCodeType {
	const SomeFieldsMissing = 1000;
	const EmailAlreadyExists = 1001;
    const FbIdAlreadyExists = 1002;
	const GooglePlusIdAlreadyExists = 1003;
	const UserNameAlreadyExists = 1004;
	const NoAuthoriseToRequestForPassword = 1005;
	const ErrorInUpdateForgetPassword = 1006;
	const NoUserNameExists = 1007;
	const PincodeAlreadyExists = 1008;
	const NotAccessToDoProcess = 1009;
	
	const UserNotFound = 1010;
	const UserNotInActiveStatus = 1011;
	const userNotAllowedToDoProcess = 1012;
	const userNotAllowedToAddOrBlockOwn = 1013;
	const userNotAllowedToMessageOwn = 1014;
	
	const ErrorInProcessing = 1015;
	const ErrorInSaving = 1016;
	
	const ProblemInImage = 1017;
	const ProblemInVIdeo = 1018;
	const ProblemInAudio = 1019;
	const CellNumberAlreadyExists = 1020;
	const NoEmailExists	 =	1021;
	const PasswordResetNotValid	 =	1022;
	
	const NoResultFound = 2000;
	const SomeFieldsRequired = 3000;
	
	const MerchantsNotInActiveStatus = 2001;
}