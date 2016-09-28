<?php

namespace XenForoBDClient\Users;

use XenForoBDClient\Client;

/**
 * Implements functions to retrieve information about a specific user from the XenForo bd Api.
 *
 * @package XenForoBDClient\Users
 */
class User {
	/**
	 * @const The url template used to request the information of an user.
	 */
	const USERS_BASE_URL = '%s/index.php?users/%s&oauth_token=%s';

	/**
	 * @var Client The Client to use.
	 */
	private $client;

	public function __construct( Client $client ) {
		$this->client = $client;
	}

	/**
	 * Returns the infromation of the given user (if the user exists), otherwise retuns false.
	 * @param $userId The user ID of the user to request. The value "me" retrieves the
	 * information of the currently authenticated user, if one is already authenticated.
	 * @return bool|array
	 */
	public function get( $userId ) {
		if ( $userId !== 'me' && !is_int( $userId ) ) {
			throw new \InvalidArgumentException( 'The user ID must be the value "me" or an 
				integer, ' . gettype( $userId ) . ' given.' );
		}
		$userInfo = $this->fetchUserInfo( $userId );
		if ( !$userInfo ) {
			return false;
		}
		return $userInfo;
	}

	private function fetchUserInfo( $userIdentifier ) {
		$requestUrl = sprintf(
			self::USERS_BASE_URL,
			$this->client->getBaseUrl(),
			$userIdentifier,
			$this->client->getAccessToken()
		);

		$httpClient = new \Net_Http_Client();
		$httpClient->get( $requestUrl );

		if ( $httpClient->getStatus() !== 200 ) {
			return false;
		}
		$json = json_decode( $httpClient->getBody(), true );
		return $json;
	}
}