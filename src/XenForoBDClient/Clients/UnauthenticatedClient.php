<?php

namespace XenForoBDClient\Clients;

/**
 * An implementation of Client, which can be used to request information which doesn't need
 * authentication against the XenForo bd api (public information).
 * @package XenForoBDClient\Clients
 */
class UnauthenticatedClient extends BaseClient {
	public function isAuthenticated() {
		return false;
	}
}