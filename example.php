<?php
require_once "Imap.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender = $_POST['sender'];

    $mailbox = '{outlook.office365.com:993/imap/ssl}';
    $username = 'imap_mail';
    $password = 'imap_pass';
    //$username = 'musttoprakk13@outlook.com';
    //$password = 'dU6Bjk5A';


    // open connection
    $imap = new Imap($mailbox, $username, $password);

    // stop on error
    if ($imap->isConnected() === false)
        die($imap->getError());

    // get all folders as array of strings
    //$folders = $imap->getFolders();
    //foreach($folders as $folder)
    //    echo $folder;

    // select folder Inbox
    $imap->selectFolder('INBOX');

    // count messages in current folder
    //$overallMessages = $imap->countMessages();
    //$unreadMessages = $imap->countUnreadMessages();

    // fetch all messages in the current folder
    $emails = $imap->getMessages(true, $sender);
    echo $emails['body'] ?? "Bulunamadı";

}


