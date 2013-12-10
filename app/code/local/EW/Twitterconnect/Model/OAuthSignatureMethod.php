<?php
abstract class EW_Twitterconnect_Model_OAuthSignatureMethod
{
	abstract public function get_name();
	abstract public function build_signature($request, $consumer, $token);
	public function check_signature($request, $consumer, $token, $signature) {
		$built = $this->build_signature($request, $consumer, $token);
		return $built == $signature;
	}
} 
?>