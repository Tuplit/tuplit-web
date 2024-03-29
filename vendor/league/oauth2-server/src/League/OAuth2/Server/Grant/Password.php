<?php
/**
 * OAuth 2.0 Password grant
 *
 * @package     php-loep/oauth2-server
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) 2013 PHP League of Extraordinary Packages
 * @license     http://mit-license.org/
 * @link        http://github.com/php-loep/oauth2-server
 */

namespace League\OAuth2\Server\Grant;

use League\OAuth2\Server\Request;
use League\OAuth2\Server\Authorization;
use League\OAuth2\Server\Exception;
use League\OAuth2\Server\Util\SecureKey;
use League\OAuth2\Server\Storage\SessionInterface;
use League\OAuth2\Server\Storage\ClientInterface;
use League\OAuth2\Server\Storage\ScopeInterface;

/**
 * Password grant class
 */
class Password implements GrantTypeInterface {

    /**
     * Grant identifier
     * @var string
     */
    protected $identifier = 'password';

    /**
     * Response type
     * @var string
     */
    protected $responseType = null;

    /**
     * Callback to authenticate a user's username , password ,facebook and linkedin id
     * @var function
     */
    protected $callback = null;

    /**
     * AuthServer instance
     * @var AuthServer
     */
    protected $authServer = null;

    /**
     * Access token expires in override
     * @var int
     */
    protected $accessTokenTTL = null;

    /**
     * Constructor
     * @param Authorization $authServer Authorization server instance
     * @return void
     */
    public function __construct(Authorization $authServer)
    {
        $this->authServer = $authServer;
    }

    /**
     * Return the identifier
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Return the response type
     * @return string
     */
    public function getResponseType()
    {
        return $this->responseType;
    }

    /**
     * Override the default access token expire time
     * @param int $accessTokenTTL
     * @return void
     */
    public function setAccessTokenTTL($accessTokenTTL)
    {
        $this->accessTokenTTL = $accessTokenTTL;
    }

    /**
     * Set the callback to verify a user's facebookId and linkedinId
     * @param callable $callback The callback function
     * @return void
     */
    public function setVerifyCredentialsCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * Return the callback function
     * @return callable
     */
    protected function getVerifyCredentialsCallback()
    {
        if (is_null($this->callback) || ! is_callable($this->callback)) {
            throw new Exception\InvalidGrantTypeException('Null or non-callable callback set');
        }

        return $this->callback;
    }

    /**
     * Complete the password grant
     * @param  null|array $inputParams
     * @return array
     */
    public function completeFlow($inputParams = null)
    {
        // Get the required params
        $authParams = $this->authServer->getParam(array('ClientId', 'ClientSecret', 'Email','Password','FBId','GooglePlusId','DeviceToken','Token','UserData','Platform'), 'post', $inputParams);

        if (is_null($authParams['ClientId'])) {
            throw new Exception\ClientException(sprintf($this->authServer->getExceptionMessage('invalid_request'), 'ClientId'), 0);
        }

        if (is_null($authParams['ClientSecret'])) {
            throw new Exception\ClientException(sprintf($this->authServer->getExceptionMessage('invalid_request'), 'ClientSecret'), 0);
        }

        // Validate client credentials
        $clientDetails = $this->authServer->getStorage('client')->getClient($authParams['ClientId'], $authParams['ClientSecret'], null, $this->identifier);

        if ($clientDetails === false) {
            throw new Exception\ClientException($this->authServer->getExceptionMessage('invalid_client'), 8);
        }

	   
	    $authParams['client_details'] = $clientDetails; // simplyshredded login check
		
			$flag = 0;
			if(!is_null($authParams['Email']) && !is_null($authParams['Password'])){
				$flag = 1;
			}
			// Check if user's facebookid or linkedin are correct
	         $userIdArray = call_user_func($this->getVerifyCredentialsCallback(), $authParams['Email'], $authParams['Password'], $authParams['FBId'], $authParams['GooglePlusId'], $authParams['DeviceToken'],$authParams['Token'],$authParams['UserData'], $authParams['Platform']);
			
        if ($userIdArray === false) {
            throw new Exception\ClientException($this->authServer->getExceptionMessage('invalid_credentials'), 0);
        }
		else{
			$userDetails = explode('##',$userIdArray);
			$userId = $userDetails[0];
			$userType = $userDetails[1];
			if($userType == 1)
				$userTypeText = 'user';
			else
				$userTypeText = 'merchant';
		}

        // Validate any scopes that are in the request
        $scope = $this->authServer->getParam('scope', 'post', $inputParams, '');
        $scopes = explode($this->authServer->getScopeDelimeter(), $scope);

        for ($i = 0; $i < count($scopes); $i++) {
            $scopes[$i] = trim($scopes[$i]);
            if ($scopes[$i] === '') unset($scopes[$i]); // Remove any junk scopes
        }

        if ($this->authServer->scopeParamRequired() === true && $this->authServer->getDefaultScope() === null && count($scopes) === 0) {
            throw new Exception\ClientException(sprintf($this->authServer->getExceptionMessage('invalid_request'), 'scope'), 0);
        } elseif (count($scopes) === 0 && $this->authServer->getDefaultScope() !== null) {
            if (is_array($this->authServer->getDefaultScope())) {
                $scopes = $this->authServer->getDefaultScope();
            } else {
                $scopes = array($this->authServer->getDefaultScope());
            }
        }

        $authParams['scopes'] = array();

        foreach ($scopes as $scope) {
            $scopeDetails = $this->authServer->getStorage('scope')->getScope($scope, $authParams['client_id'], $this->identifier);

            if ($scopeDetails === false) {
                throw new Exception\ClientException(sprintf($this->authServer->getExceptionMessage('invalid_scope'), $scope), 4);
            }

            $authParams['scopes'][] = $scopeDetails;
        }

        // Generate an access token
        $accessToken = SecureKey::make();
        $accessTokenExpiresIn = ($this->accessTokenTTL !== null) ? $this->accessTokenTTL : $this->authServer->getAccessTokenTTL();
        $accessTokenExpires = time() + $accessTokenExpiresIn;

        // Create a new session
        $sessionId = $this->authServer->getStorage('session')->createSession($authParams['ClientId'],$userTypeText, $userId);

        // Associate an access token with the session
        $accessTokenId = $this->authServer->getStorage('session')->associateAccessToken($sessionId, $accessToken, $accessTokenExpires);

        // Associate scopes with the access token
        foreach ($authParams['scopes'] as $scope) {
            $this->authServer->getStorage('session')->associateScope($accessTokenId, $scope['id']);
        }
		if($inputParams == 1){
			$response = array(
				'Status'		=>	"success",
				'UserId'		=>  $userId,
	            'AccessToken'  =>  $accessToken,
	            'TokenType'    =>  'bearer',
	            'Expires'       =>  $accessTokenExpires,
	            'ExpiresIn'    =>  $accessTokenExpiresIn
	        );
		}
		else{
	        $response = array(
				'Status'		=>	"success",
				'MerchantId'	=>  $userId,
	            'AccessToken'  =>  $accessToken,
	            'TokenType'    =>  'bearer',
	            'Expires'       =>  $accessTokenExpires,
	            'ExpiresIn'    =>  $accessTokenExpiresIn
	        );
		}

        // Associate a refresh token if set
        if ($this->authServer->hasGrantType('refresh_token')) {
            $refreshToken = SecureKey::make();
            $refreshTokenTTL = time() + $this->authServer->getGrantType('refresh_token')->getRefreshTokenTTL();
            $this->authServer->getStorage('session')->associateRefreshToken($accessTokenId, $refreshToken, $refreshTokenTTL, $authParams['client_id']);
            $response['refresh_token'] = $refreshToken;
        }
		
		$response_array['meta'] = array("code" =>"201","dataPropertyName"=> "login");
		$response_array['login'] = $response;
        return $response_array;
    }

}