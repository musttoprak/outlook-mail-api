<?php
$client_id = 'client_id';
$client_secret = 'client_secret';
$tenant_id = 'tenant_id';
$redirectUri = 'http://localhost:6060/grapApi.php';
$scopes = ['User.Read', 'Mail.ReadWrite', 'Mail.ReadBasic', 'Mail.Read'];


$authorizationUrl = 'https://login.microsoftonline.com/' . $tenant_id . '/oauth2/v2.0/authorize?client_id=' . $client_id . '&response_type=code&redirect_uri=' . urlencode($redirectUri) . '&scope=' . urlencode(implode(' ', $scopes));

header('Location: ' . $authorizationUrl);
exit();