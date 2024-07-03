<?php
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Authentication\Oauth\ClientCredentialContext;

$clientId = 'clientId';
$clientSecret = 'clientSecret';
$tenantId = 'tenantId';
$redirectUri = 'http://localhost:6060/grapApi.php';
$scopes = ['User.Read', 'Mail.ReadWrite', 'Mail.ReadBasic', 'Mail.Read'];

$tokenContext = new ClientCredentialContext(
    $tenantId,
    $clientId,
    $clientSecret
);

$graphClient = new GraphServiceClient($tokenContext, $scopes);;


var_dump($graphClient);
