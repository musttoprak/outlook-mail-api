<?php
require_once realpath(__DIR__ . '/vendor/autoload.php');
require_once 'GraphHelper.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['CLIENT_ID', 'TENANT_ID', 'GRAPH_USER_SCOPES']);






initializeGraph();
var_dump("initialize finished");
greetUser();
var_dump("greetUser finished");

displayAccessToken();
var_dump("displayAccessToken finished");

listInbox();
var_dump("listInbox finished");

exit();
$choice = 2;

switch ($choice) {
    case 1:
        displayAccessToken();
        break;
    case 2:
        listInbox();
        break;
    case 3:
        sendMail();
        break;
    case 4:
        makeGraphCall();
        break;
    case 0:
    default:
        print('Goodbye...' . PHP_EOL);
}


function initializeGraph(): void
{
    GraphHelper::initializeGraphForUserAuth();
}

function greetUser(): void
{
    try {
        $user = GraphHelper::getUser();
        print('Hello, ' . $user->getDisplayName() . '!' . PHP_EOL);

        // For Work/school accounts, email is in Mail property
        // Personal accounts, email is in UserPrincipalName
        $email = $user->getMail();
        if (empty($email)) {
            $email = $user->getUserPrincipalName();
        }
        print('Email: ' . $email . PHP_EOL . PHP_EOL);
    } catch (Exception $e) {
        print('Error getting user: ' . $e->getMessage() . PHP_EOL . PHP_EOL);
    }
}

function displayAccessToken(): void
{
    try {
        $token = GraphHelper::getUserToken();
        print('User token: ' . $token . PHP_EOL . PHP_EOL);
    } catch (Exception $e) {
        print('Error getting access token: ' . $e->getMessage() . PHP_EOL . PHP_EOL);
    }
}

function listInbox(): void
{
    try {
        $messages = GraphHelper::getInbox();

        // Output each message's details
        foreach ($messages->getValue() as $message) {
            print('Message: ' . $message->getSubject() . PHP_EOL);
            print('  From: ' . $message->getFrom()->getEmailAddress()->getName() . PHP_EOL);
            $status = $message->getIsRead() ? "Read" : "Unread";
            print('  Status: ' . $status . PHP_EOL);
            print('  Received: ' . $message->getReceivedDateTime()->format(\DateTimeInterface::RFC2822) . PHP_EOL);
        }

        $nextLink = $messages->getOdataNextLink();
        $moreAvailable = isset($nextLink) && $nextLink != '' ? 'True' : 'False';
        print(PHP_EOL . 'More messages available? ' . $moreAvailable . PHP_EOL . PHP_EOL);
    } catch (Exception $e) {
        print('Error getting user\'s inbox: ' . $e->getMessage() . PHP_EOL . PHP_EOL);
    }
}

function sendMail(): void
{
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

        print(PHP_EOL . 'Mail sent.' . PHP_EOL . PHP_EOL);
    } catch (Exception $e) {
        print('Error sending mail: ' . $e->getMessage() . PHP_EOL . PHP_EOL);
    }
}

function makeGraphCall(): void
{
    try {
        GraphHelper::makeGraphCall();
    } catch (Exception $e) {
        print(PHP_EOL . 'Error making Graph call' . PHP_EOL . PHP_EOL);
    }
}