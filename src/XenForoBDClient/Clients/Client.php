<?php

namespace XenForoBDClient\Clients;

/**
 * Abstract class that defines basic functions which are required to interact with the XenForo
 * bd api.
 * @package XenForoBDClient
 */
interface Client {
	public function setClientId( $clientId );

	public function setClientSecret( $clientSecret );

	public function setBaseUrl( $baseUrl );

	public function getBaseUrl();

	public function isAuthenticated();

	public function getAccessToken();
}