<?php

namespace XenForoBDClient\Clients;

class OAuth2Client extends BaseClient {
	/**
	 * @const string The url template used to create an OAuth2 authentication request url.
	 */
	const AUTHENTICATION_REQUEST_URL_TPL = '%s/index.php?oauth/authorize/&client_id=%s&redirect_uri=%s&response_type=code&scope=%s';
	/**
	 * @const string The url used to request the access token after a successful authentication.
	 */
	const AUTNETICATION_AUTH_URL_TPL = '%s/index.php?oauth/token';

	/**
	 * @var array The set of scopes to request access for.
	 */
	protected $scopes = [];
	/**
	 * @var string|null The access token retrieved from the OAuth2 provider once the user is
	 * authenticated.
	 */
	protected $accessToken;

	/**
	 * Adds the given scope to the set of scopes to request from the user.
	 *
	 * @param $scope string The Scope. Hint: Valid Scopes are defined as constants in
	 *  XenForoBD\\Scopes.
	 * @return $this
	 */
	public function addScope( $scope ) {
		$this->scopes[] = $scope;

		return $this;
	}

	/**
	 * Returns a list of scopes, seperated by a whitespace.
	 *
	 * @return string
	 */
	protected function getScopes() {
		return implode( ' ', $this->scopes );
	}

	/**
	 * Returns the authentication URL, which should be used to redirect the user to. The user
	 * will be promted with a request to access the data (based on the scopes set before this
	 * function was called).
	 *
	 * You need to set the client ID (self::setClientId()), the redirect uri
	 * (self::setRedirectUri()) as well as the base url (self::setBaseUrl()) before calling this
	 * function.
	 *
	 * @return string The authentication url to redirect the user to
	 */
	public function getAuthenticationRequestUrl() {
		if ( $this->clientId === null ) {
			throw new \LogicException( 'The client id must be set before an authentication' .
				' request url can be build.' );
		} elseif ( $this->redirectUri === null ) {
			throw new \LogicException( 'The redirect url must be set before an authentication' .
				' request url can be build.' );
		} elseif ( $this->baseUrl === null ) {
			throw new \LogicException( 'The base url must be set before an authentication' .
				' request url can be build.' );
		}

		$url = sprintf(
			self::AUTHENTICATION_REQUEST_URL_TPL,
			$this->baseUrl,
			rawurlencode( $this->clientId ),
			rawurlencode( $this->redirectUri ),
			rawurlencode( $this->getScopes() )
		);

		return $url;
	}

	/**
	 * Attempts to authenticate the user at the OAuth provider with the given code and the set
	 * client ID.
	 *
	 * @param $code
	 * @return bool|string
	 */
	public function authenticate( $code ) {
		if ( !is_string( $code ) ) {
			throw new \InvalidArgumentException( 'The authorization code must be a string, ' .
				gettype( $code ) . ' given.' );
		} elseif ( $this->baseUrl === null ) {
			throw new \LogicException( 'The base url must be set before the authentication' .
			    ' can be started.' );
		} elseif ( $this->clientId === null ) {
			throw new \LogicException( 'The client id must be set before the authentication' .
				' can be started.' );
		} elseif ( $this->clientSecret === null ) {
			throw new \LogicException( 'The client secret must be set before the authentication' .
				' can be started.' );
		}

		$tokenUrl = sprintf(
			self::AUTNETICATION_AUTH_URL_TPL,
			$this->baseUrl
		);

		$postFields = array(
			'grant_type' => 'authorization_code',
			'client_id' => $this->clientId,
			'client_secret' => $this->clientSecret,
			'code' => $code,
			'redirect_uri' => $this->redirectUri,
		);

		$httpClient = new \Net_Http_Client();
		$httpClient->post( $tokenUrl, $postFields );
		if ( $httpClient->getStatus() != 200 ) {
			return false;
		}

		$accessToken = $this->fetchAccessToken( $httpClient );
		$this->setAccessToken( $accessToken );

		return $accessToken;
	}

	/**
	 * Sets the given access token, which then will be used to make requests against the api
	 * endpoint as an authenticated user (given, that the access token is (still) valid).
	 *
	 * @param $accessToken
	 */
	public function setAccessToken( $accessToken ) {
		$this->accessToken = $accessToken;
	}

	/**
	 * Returns the access token, if one is set already.
	 *
	 * @return null|string
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}

	/**
	 * Tries to fetch the access token from a http response body of an authentication request to
	 * the OAuth2 provider.
	 *
	 * @param \Net_Http_Client $client
	 * @return string
	 */
	protected function fetchAccessToken( \Net_Http_Client $client ) {
		if ( $client->getStatus() !== 200 ) {
			throw new \InvalidArgumentException( 'The http client must have the http status code,' .
				' got ' . $client->getStatus() );
		}
		$httpResponseBody = $client->getBody();
		$responseJson = json_decode( $httpResponseBody, true );
		if ( !$responseJson || !isset( $responseJson['access_token'] ) ) {
			return '';
		}
		return $responseJson['access_token'];
	}

	public function isAuthenticated() {
		return $this->accessToken !== null;
	}
}