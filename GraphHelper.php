<?php

use Microsoft\Graph\Generated\Models;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\MailFolders\Item\Messages\MessagesRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\Generated\Users\Item\SendMail\SendMailPostRequestBody;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetQueryParameters;
use Microsoft\Graph\Generated\Users\Item\UserItemRequestBuilderGetRequestConfiguration;
use Microsoft\Graph\GraphRequestAdapter;
use Microsoft\Graph\GraphServiceClient;
use Microsoft\Kiota\Abstractions\Authentication\BaseBearerTokenAuthenticationProvider;

require_once 'DeviceCodeTokenProvider.php';

class GraphHelper {

    private static string $clientId = '';
    private static string $tenantId = '';
    private static string $graphUserScopes = '';
    private static DeviceCodeTokenProvider $tokenProvider;
    private static GraphServiceClient $userClient;


    public static function initializeGraphForUserAuth(): void {
        GraphHelper::$clientId = $_ENV['CLIENT_ID'];
        GraphHelper::$tenantId = $_ENV['TENANT_ID'];
        GraphHelper::$graphUserScopes = $_ENV['GRAPH_USER_SCOPES'];

        GraphHelper::$tokenProvider = new DeviceCodeTokenProvider(
            GraphHelper::$clientId,
            GraphHelper::$tenantId,
            GraphHelper::$graphUserScopes);
        $authProvider = new BaseBearerTokenAuthenticationProvider(GraphHelper::$tokenProvider);
        $adapter = new GraphRequestAdapter($authProvider);
        GraphHelper::$userClient = GraphServiceClient::createWithRequestAdapter($adapter);
    }


    public static function getUserToken(): string {
        return GraphHelper::$tokenProvider
            ->getAuthorizationTokenAsync('https://graph.microsoft.com')->wait();
    }


    public static function getUser(): Models\User {
        $configuration = new UserItemRequestBuilderGetRequestConfiguration();
        $configuration->queryParameters = new UserItemRequestBuilderGetQueryParameters();
        $configuration->queryParameters->select = ['displayName','mail','userPrincipalName'];
        return GraphHelper::$userClient->me()->get($configuration)->wait();
    }


    public static function getInbox(): Models\MessageCollectionResponse {
        $configuration = new MessagesRequestBuilderGetRequestConfiguration();
        $configuration->queryParameters = new MessagesRequestBuilderGetQueryParameters();
        // Only request specific properties
        $configuration->queryParameters->select = ['from','isRead','receivedDateTime','subject'];
        // Sort by received time, newest first
        $configuration->queryParameters->orderby = ['receivedDateTime DESC'];
        // Get at most 25 results
        $configuration->queryParameters->top = 25;
        return GraphHelper::$userClient->me()
            ->mailFolders()
            ->byMailFolderId('inbox')
            ->messages()
            ->get($configuration)->wait();
    }

    function sendMail(): void {
        try {
            // Send mail to the signed-in user
            // Get the user for their email address
            $user = GraphHelper::getUser();

            // For Work/school accounts, email is in Mail property
            // Personal accounts, email is in UserPrincipalName
            $email = $user->getMail();
            if (empty($email)) {
                $email = $user->getUserPrincipalName();
            }

            GraphHelper::sendMail('Testing Microsoft Graph', 'Hello world!', $email);

            print(PHP_EOL.'Mail sent.'.PHP_EOL.PHP_EOL);
        } catch (Exception $e) {
            print('Error sending mail: '.$e->getMessage().PHP_EOL.PHP_EOL);
        }
    }

    public static function makeGraphCall(): void {
        // INSERT YOUR CODE HERE
    }
}


?>