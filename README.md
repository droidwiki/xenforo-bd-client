XenForo bd PHP Client
========================

This library allows to use the public api endpoint of XenForo ([XenForo [bd] Api](https://xenforo.com/community/resources/bd-api.1732/)).

Usage
-----
To authenticate an user using the OAuth2 protocol, you can use the following example. You need to create an API client at the target XenForo installation
at https://example.com/account/api.
```php
$client = new \XenForoBDClient\Clients\OAuth2Client();
$client->setBaseUrl( 'https://example.com/api/' )
	->setClientId( 'client_id' )
	->setClientSecret( 'client_secret' )
	->setRedirectUri( 'https://example2.com/redirect_target.php' )
	// see \XenForoBD\Scopes for all possible scopes
	->addScope( \XenForoBD\Scopes::READ );
if ( $_GET[ 'code' ] ) {
	$client->authenticate( $_GET['code'] );
	$user = new \XenForoBDClient\Users\User( $client );
	// will print the whole information array of the authenticated user
	var_dump($user->get( 'me' ));
} else {
	// redirect to the authentication url
	header( 'Location: ' . $client->getAuthenticationRequestUrl() );
}
```

To request information about an user using the user id, without needing to authenticate before doing it (e.g. using OAuth2), you can use the following example code:

```php
$client = new \XenForoBDClient\Clients\UnauthenticatedClient();
$client->setBaseUrl( 'http://www.android-hilfe.de/api/' );
$user = new \XenForoBDClient\Users\User( $client );
// the user id in XenForo would be 102719
var_dump( $user->get( 102719 ) );
```
