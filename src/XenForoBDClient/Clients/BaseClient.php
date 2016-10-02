<?php

namespace XenForoBDClient\Clients;

/**
 * Abstract class that defines basic functions which are required to interact with the XenForo
 * bd api.
 * @package XenForoBDClient
 */
abstract class BaseClient implements Client {
	/**
	 * @var string|null The client ID of the api client to use.
	 */
	protected $clientId;
	/**
	 * @var string|null The client secret of the api client to use.
	 */
	protected $clientSecret;
	/**
	 * @var string|null The redirect uri. Must match with one of the whitelisted redirect urls in
	 * the api client identified by $clientId.
	 */
	protected $redirectUri;
	/**
	 * @var string|null The base url of the api endpoint, e.g. https://example.com/api. Needs to
	 * be a valid url.
	 */
	protected $baseUrl;

	public function setClientId( $clientId ) {
		if ( !is_string( $clientId ) ) {
			throw new \InvalidArgumentException( 'The client id needs to be a string, got ' .
				gettype ( $clientId ) );
		}
		$this->clientId = $clientId;

		return $this;
	}

	public function setClientSecret( $clientSecret ) {
		if ( !is_string( $clientSecret ) ) {
			throw new \InvalidArgumentException( 'The client secret needs to be a string, got ' .
				gettype ( $clientSecret ) );
		}
		$this->clientSecret = $clientSecret;

		return $this;
	}

	public function setBaseUrl( $baseUrl ) {
		if ( !filter_var($baseUrl, FILTER_VALIDATE_URL) ) {
			throw new \InvalidArgumentException( 'The given base url doesn\'t seem to be an URL.' );
		}
		$this->baseUrl = rtrim( $baseUrl, '/' );

		return $this;
	}

	public function getBaseUrl() {
		if ( $this->baseUrl === null ) {
			throw new \InvalidArgumentException( 'The base url isn\'t set so far.' );
		}
		return $this->baseUrl;
	}

	public function setRedirectUri( $redirectUri ) {
		$this->redirectUri = $redirectUri;

		return $this;
	}

	public function getAccessToken() {
		return null;
	}
}