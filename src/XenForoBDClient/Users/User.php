<?php

namespace XenForoBDClient\Users;

use XenForoBDClient\Clients\Client;

/**
 * Implements functions to retrieve information about a specific user from the XenForo bd Api.
 *
 * @package XenForoBDClient\Users
 */
class User {
	/**
	 * @const The url template used to request the information of an user, unauthenticated.
	 */
	const USERS_BASE_URL = '%s/index.php?users/%s';
	/**
	 * @const The url template used to request the information of an user, authenticated.
	 */
	const USERS_BASE_URL_AUTHENTICATED = self::USERS_BASE_URL . '&oauth_token=%s';

	/**
	 * @var array|null An array of error messages, when a request of information failed and error
	 * messages are provided.
	 */
	private $errors;

	/**
	 * @var Client The Client to use.
	 */
	private $client;

	public function __construct( Client $client ) {
		$this->client = $client;
	}

	/**
	 * Returns the information of the given user (if the user exists), otherwise returns false.
	 * If false is returned, it's possible that more information about the error was provided by
	 * the XenForo API, which then can be retrieved with the getErrors method.
	 *
	 * @param string|integer $userId The user ID of the user to request. The value "me" retrieves
	 * the information of the currently authenticated user, if one is already authenticated.
	 * @return bool|array
	 */
	public function get( $userId ) {
		if ( $userId !== 'me' && !is_int( $userId ) ) {
			throw new \InvalidArgumentException( 'The user ID must be the value "me" or an' .
				' integer, ' . gettype( $userId ) . ' given.' );
		}
		$userInfo = $this->fetchUserInfo( $userId );
		if ( !$userInfo ) {
			return false;
		}
		return $userInfo;
	}

	/**
	 * Returns an array of errors if there were some, otherwise an empty array.
	 * @return array
	 */
	public function getErrors() {
		if ($this->errors === null ) {
			return [];
		}
		return $this->errors;
	}

	private function fetchUserInfo( $userIdentifier ) {
		if ( $this->client->isAuthenticated() ) {
			$requestUrl = sprintf(
				self::USERS_BASE_URL_AUTHENTICATED,
				$this->client->getBaseUrl(),
				$userIdentifier,
				$this->client->getAccessToken()
			);
		} else {
			$requestUrl = sprintf(
				self::USERS_BASE_URL,
				$this->client->getBaseUrl(),
				$userIdentifier
			);
		}

		$httpClient = new \Net_Http_Client();
		$httpClient->get( $requestUrl );

		$json = json_decode( $httpClient->getBody(), true );
		if ( $httpClient->getStatus() !== 200 ) {
			if ( isset( $json['errors'] ) ) {
				$this->errors = $json['errors'];
			}

			return false;
		}

		return $json;
	}
}