<?php
class EW_Twitterconnect_Model_Twitterconnect extends Mage_Core_Model_Abstract
{
	public $token;
	public $consumerkey;
	public $consumersecret;
	public $consumer;
	public $callback;
	public $version;
	public $sha1_method;
	public $http_code;
	public $url;
	public $host = "https://api.twitter.com/1.1/";
	public $timeout = 30;
	public $connecttimeout = 30;
	public $ssl_verifypeer = TRUE;
	public $format = 'json';
	public $decode_json = TRUE;
	public $http_info;
	public $useragent = 'TwitterOAuth v0.2.0-beta2';
	
	const REQUEST_URL = "https://api.twitter.com/oauth/request_token";
	const AUTHORIZE_URL = "https://api.twitter.com/oauth/authorize";
	const AUTHENTICATE_URL = "https://api.twitter.com/oauth/authenticate";
	const ACCESS_URL = "https://api.twitter.com/oauth/access_token";
	
	public function __construct($configs)
	{
		$this->_init('twitterconnect/twitterconnect');
		$this->sha1_method = new EW_Twitterconnect_Model_OAuthSignatureMethodHMACSHA1();
		if(isset($configs['callback'])){
			$this->callback = $configs['callback'];
		}
		if(is_array($configs) && isset($configs['consumer_key']) && isset($configs['consumer_secret'])){
			$this->consumerkey = $configs['consumer_key'];
			$this->consumersecret = $configs['consumer_secret'];
			$this->consumer = new EW_Twitterconnect_Model_OAuthConsumer($this->consumerkey, $this->consumersecret, $this->callback);
		}
 		if (!empty($configs['oauth_token']) && !empty($configs['oauth_token_secret'])) {
 			$this->token = new EW_Twitterconnect_Model_OAuthConsumer($configs['oauth_token'], $configs['oauth_token_secret']);
 		} else {
 			$this->token = NULL;
 		}
	}
	
	public function getRequestToken()
	{
		if(($callbackurl = $this->callback) != null){
			$parameters = array();
		    $parameters['oauth_callback'] = $callbackurl; 
		    $request = $this->oAuthRequest(self::REQUEST_URL, 'GET', $parameters);
		    $token = EW_Twitterconnect_Model_OAuthUtil::parse_parameters($request);
		    $this->token = new EW_Twitterconnect_Model_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret'], $this->callback);
		    return $token;
		}
		return false;
	}
	public function oAuthRequest($url, $method, $parameters) {
		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
			$url = "{$this->host}{$url}.{$this->format}";
		}
		$request = EW_Twitterconnect_Model_OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method) {
			case 'GET':
				return $this->http($request->to_url(), 'GET');
			default:
				return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
		}
	}
	public function getAuthorizeURL($token, $sign_in_with_twitter = TRUE) {
	    if (is_array($token)) {
	      $token = $token['oauth_token'];
	    }
	    if (empty($sign_in_with_twitter)) {
	      return self::AUTHORIZE_URL . "?oauth_token={$token}";
	    } else {
	       return self::AUTHORIZE_URL . "?oauth_token={$token}";
	    }
  	}
	public function http($url, $method, $postfields = NULL) {
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
	
		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}
	
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close ($ci);
		return $response;
	}
	
	/**
	 * Get the header info to store.
	 */
	public function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
	
	public function get($url, $parameters = array()) {
	    $response = $this->oAuthRequest($url, 'GET', $parameters);
	    if ($this->format === 'json' && $this->decode_json) {
	      return json_decode($response);
	    }
	    return $response;
	}
	public function getAccessToken($oauth_verifier) {
	    $parameters = array();
	    $parameters['oauth_verifier'] = $oauth_verifier;
	    $request = $this->oAuthRequest(self::ACCESS_URL, 'GET', $parameters);
	    $token = EW_Twitterconnect_Model_OAuthUtil::parse_parameters($request);
	    $this->token = new EW_Twitterconnect_Model_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
	    return $token;
	}
	public function getXAuthToken($username, $password) {
	    $parameters = array();
	    $parameters['x_auth_username'] = $username;
	    $parameters['x_auth_password'] = $password;
	    $parameters['x_auth_mode'] = 'client_auth';
	    $request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
	    $token = EW_Twitterconnect_Model_OAuthUtil::parse_parameters($request);
	    $this->token = new EW_Twitterconnect_Model_OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
	    return $token;
	}
}