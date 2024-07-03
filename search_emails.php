<?php
include 'email_message_model.php';

// Kontrol formunun gönderilip gönderilmediğini kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender = $_POST['sender'];

    // Tüm e-postaları al
    $allEmails = getAllEmails($sender);

    if ($allEmails) {
        // E-postaları tarihe göre sırala
        usort($allEmails, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // En son e-postayı al
        $latestEmail = end($allEmails);
        // DateTime nesnesi oluştur
        $date = new DateTime(htmlspecialchars($latestEmail['date']), new DateTimeZone('UTC'));
        // Türkiye saat dilimine (Europe/Istanbul) göre ayarla
        $date->setTimezone(new DateTimeZone('Europe/Istanbul'));
        // Türk tarih formatına çevir
        $turkishDate = $date->format('d.m.Y H:i:s');

        // E-posta sonuçlarını göster
        echo '<div class="email-container">';
        echo '<h2>' . htmlspecialchars(mb_decode_mimeheader($latestEmail['subject']), ENT_QUOTES, 'UTF-8') . '</h2>';
        echo '<p><strong>Gönderen:</strong> ' . htmlspecialchars($latestEmail['from']) . '</p>';
        echo '<p><strong>Alıcı:</strong> ' . htmlspecialchars($latestEmail['to']) . '</p>';
        echo '<p><strong>Tarih:</strong> ' . $turkishDate . '</p>';
        echo '<p><strong>Klasör:</strong> ' . htmlspecialchars($latestEmail['folder']) . '</p>';
        echo '<div class="email-message">';
        echo '<pre>' . htmlspecialchars($latestEmail['body']) . '</pre>';
        echo '</div>';

        // Ekleri indirme düğmelerini göster
        if (!empty($latestEmail['attachments'])) {
            foreach ($latestEmail['attachments'] as $attachment) {
                if ($attachment['is_attachment']) {
                    $filename = $attachment['filename'];
                    $encoded_attachment = base64_encode($attachment['attachment']);
                    echo '<form action="download_attachment.php" method="post" target="_blank">';
                    echo "<input type='hidden' name='filename' value='" . htmlspecialchars($filename) . "'>";
                    echo "<input type='hidden' name='attachment' value='" . htmlspecialchars($encoded_attachment) . "'>";
                    echo '<button type="submit" class="attachment-download-btn">'.$filename.'</button>';
                    echo '</form>';
                }
            }
        }

        // Outlook Web App'te görüntülemek için bir düğme ekle
        echo '<form action="https://outlook.office365.com/owa/?ItemID=' . urlencode($latestEmail['message_id']) . '" method="get" target="_blank">';
        echo '<button type="submit" class="outlook-web-app-btn">Outlook Web App\'de Görüntüle</button>';
        echo '</form>';
        echo '</div>';
    } else {
        echo "Gönderenden e-posta bulunamadı: $sender";
    }
}

function getAllEmails($sender) {
    $hostname = '{outlook.office365.com:993/imap/ssl}';
    $username = 'imap_mail';
    $password = 'imap_pass';
    //$username = 'musttoprakk13@outlook.com';
    //$password = 'dU6Bjk5A';
    $folders = ["INBOX"]; // İlgili tüm klasörler

    $startTime = microtime(true);
    $allEmails = [];

    foreach ($folders as $folder) {
        // Bağlantıyı aç
        $inbox = imap_open($hostname . $folder , $username, $password) or die('Outlook ile bağlantı kurulamadı: ' . imap_last_error());

        $openTime = microtime(true);
        echo ("<br>IMAP bağlantı süresi: " . ($openTime - $startTime) . " saniye");

        // Son 1 dakikayı hesapla
        //$time_limit = time() - 60; // şu andan itibaren 1 dakika önce
        //$date_limit = date("d-M-Y H:i:s", $time_limit);
        //echo $date_limit. "<br>";

        // E-postaları ara
        //$emails = imap_search($inbox, "FROM \"$sender\" SINCE \"$date_limit\"");


        // Sort emails by date in descending order and fetch only the most recent ones
        $recent_emails = imap_sort($inbox, SORTDATE, 1, SE_UID);

        // Define the time frame to look back (e.g., last 2 minutes)
        $time_frame = 2 * 60; // 2 minutes in seconds
        $current_time = time();

        // Iterate through the most recent emails and check their time
        $latest_email_id = null;
        foreach ($recent_emails as $email_id) {
            // Fetch the overview for this email
            $overview = imap_fetch_overview($inbox, $email_id, 0)[0];

            // Convert the email date to a timestamp
            $email_timestamp = strtotime($overview->date);

            // Check if the email is within the last 2 minutes
            if ($current_time - $email_timestamp <= $time_frame) {
                $latest_email_id = $email_id;
                break;
            }
        }

        // If a recent email was found within the time frame
        if ($latest_email_id) {
            // Fetch the email overview and body
            $overview = imap_fetch_overview($inbox, $latest_email_id, 0);
            $message = imap_fetchbody($inbox, $latest_email_id, 1);

            // Display the email details
            echo "Subject: " . $overview[0]->subject . "<br>";
            echo "From: " . $overview[0]->from . "<br>";
            echo "Date: " . $overview[0]->date . "<br>";
            echo "Message: " . $message . "<br>";
        } else {
            echo 'No recent emails found within the last 2 minutes.';
        }

        // Close the IMAP connection
        imap_close($inbox);

        exit();
        if ($emails !== false) {
            // E-postalar var, dizi işlemi yapabilirsiniz
            echo count($emails)."<br>";
        } else {
            // E-posta bulunamadı
            echo "E-posta bulunamadı <br>";
        }
        $searchTime = microtime(true);
        echo ("<br>E-posta arama süresi: " . ($searchTime - $filterTime) . " saniye");

        if ($emails) {
            foreach ($emails as $email_number) {
                $overview = imap_fetch_overview($inbox, $email_number);
                $structure = imap_fetchstructure($inbox, $email_number);

                $email = getEmailD($inbox, $email_number, $overview, $structure);
                echo $email['date'];
                $email['folder'] = $folder;
                $allEmails[] = $email;
            }
        }
        $processTime = microtime(true);
        echo ("<br>E-postaları işleme süresi: " . ($processTime - $searchTime) . " saniye");
        $endTime = microtime(true);
        echo ("<br>Toplam süre: " . ($endTime - $startTime) . " saniye");
    }
    // Bağlantıyı kapat
    imap_close($inbox);

    // Son 1 dakika içinde gelen e-postaları filtrele
    $filteredEmails = [];
    $time_limit = time() - 60; // şu andan itibaren 1 dakika önce

    foreach ($allEmails as $email) {
        $email_date = strtotime($email['date']);
        if ($email_date >= $time_limit) {
            $filteredEmails[] = $email;
        }
    }

    return $filteredEmails;
}

function getEmailD($inbox,$email_number,$overview,$structure){
    // E-posta gövdesi ve ekler
    $email_body = getBody($inbox, $email_number, $structure);
    $attachments = getAttachments($inbox, $email_number, $structure);

    // Check if the email body is base64 encoded
    if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $email_body)) {
        // Decode base64 if the body is encoded
        $email_body = base64_decode($email_body);
    }
    $email_body = strip_tags($email_body);

    $email = array(
        'subject' => $overview[0]->subject,
        'from' => $overview[0]->from,
        'to' => $overview[0]->to,
        'date' => $overview[0]->date,
        'body' => $email_body,
        'attachments' => $attachments,
        'message_id' => $overview[0]->message_id
    );
    return $email;
}
function getEmailDetails($inbox, $email_number) {
    echo "1<br>";
    $startTime = microtime(true);
    $overview = imap_fetch_overview($inbox, $email_number, 0);
    $structure = imap_fetchstructure($inbox, $email_number, 0);

    // E-posta gövdesi ve ekler
    $email_body = getBody($inbox, $email_number, $structure);
    $attachments = getAttachments($inbox, $email_number, $structure);

    // Check if the email body is base64 encoded
    if (preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $email_body)) {
        // Decode base64 if the body is encoded
        $email_body = base64_decode($email_body);
    }
    $email_body = strip_tags($email_body);

    $email = array(
        'subject' => $overview[0]->subject,
        'from' => $overview[0]->from,
        'to' => $overview[0]->to,
        'date' => $overview[0]->date,
        'body' => $email_body,
        'attachments' => $attachments,
        'message_id' => $overview[0]->message_id
    );
    $endTime = microtime(true);
    echo ("<br>getEmailDetails süresi: " . ($endTime - $startTime) . " saniye");
    return $email;
}

function getBody($inbox, $email_number, $structure) {
    $body = '';

    if (!isset($structure->parts) || !count($structure->parts)) { // not multipart
        //echo "Not multipart\n";
        $body = imap_body($inbox, $email_number);
        if ($structure->encoding == 3) { // BASE64
            //echo "Decoding BASE64\n";
            $body = base64_decode($body);
        } elseif ($structure->encoding == 4) { // QUOTED-PRINTABLE
            //echo "Decoding QUOTED-PRINTABLE\n";
            $body = quoted_printable_decode($body);
        }
    } else { // multipart
        //echo "Multipart\n";
        $body = getMultipartBody($inbox, $email_number, $structure);
    }
    return $body;
}

function getAttachments($inbox, $email_number, $structure) {
    $attachments = [];

    if (isset($structure->parts) && count($structure->parts)) {
        for ($i = 0; $i < count($structure->parts); $i++) {
            $attachments[$i] = array(
                'is_attachment' => false,
                'filename' => '',
                'name' => '',
                'attachment' => ''
            );
            if ($structure->parts[$i]->ifdparameters) {
                foreach ($structure->parts[$i]->dparameters as $object) {
                    if (strtolower($object->attribute) == 'filename') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['filename'] = $object->value;
                    }
                }
            }

            if ($structure->parts[$i]->ifparameters) {
                foreach ($structure->parts[$i]->parameters as $object) {
                    if (strtolower($object->attribute) == 'name') {
                        $attachments[$i]['is_attachment'] = true;
                        $attachments[$i]['name'] = $object->value;
                    }
                }
            }

            if ($attachments[$i]['is_attachment']) {
                $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);
                if ($structure->parts[$i]->encoding == 3) //3 = BASE64 encoding
                {
                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                } elseif ($structure->parts[$i]->encoding == 4) //4 = QUOTED-PRINTABLE encoding
                {
                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                }
            }
        }
    }

    return $attachments;
}

function getMultipartBody($inbox, $email_number, $structure) {
    $body = '';

    foreach ($structure->parts as $part_number => $part) {
        if ($part->ifdisposition && strtolower($part->disposition) == 'attachment') {
            continue; // Skip attachments
        }

        if ($part->subtype == 'ALTERNATIVE') {
            $body = getAlternativeBody($inbox, $email_number, $part);
        } elseif ($part->subtype == 'PLAIN' || $part->subtype == 'HTML') {
            $part_body = imap_fetchbody($inbox, $email_number, $part_number + 1);
            if ($part->encoding == 3) { // BASE64
                $part_body = base64_decode($part_body);
            } elseif ($part->encoding == 4) { // QUOTED-PRINTABLE
                $part_body = quoted_printable_decode($part_body);
            }

            if ($part->subtype == 'HTML') {
                return $part_body; // Prefer HTML body over plain text
            }

            if (!$body) {
                $body = $part_body; // Use plain text if no HTML found
            }
        }
    }

    return $body;
}

function getAlternativeBody($inbox, $email_number, $part) {
    $body = '';

    foreach ($part->parts as $subpart_number => $subpart) {
        if ($subpart->subtype == 'HTML') {
            $part_body = imap_fetchbody($inbox, $email_number, '1.' . ($subpart_number + 1));
            if ($subpart->encoding == 3) { // BASE64
                $part_body = base64_decode($part_body);
            } elseif ($subpart->encoding == 4) { // QUOTED-PRINTABLE
                $part_body = quoted_printable_decode($part_body);
            }
            return $part_body; // Prefer HTML body
        }

        if ($subpart->subtype == 'PLAIN' && !$body) {
            $part_body = imap_fetchbody($inbox, $email_number, '1.' . ($subpart_number + 1));
            if ($subpart->encoding == 3) { // BASE64
                $part_body = base64_decode($part_body);
            } elseif ($subpart->encoding == 4) { // QUOTED-PRINTABLE
                $part_body = quoted_printable_decode($part_body);
            }
            $body = $part_body; // Use plain text if no HTML found
        }
    }

    return $body;
}

?>
<style>
.email-message {
    overflow-y: auto;
    max-height: 350px; /* veya istediğiniz bir değer */
    white-space: pre-wrap;
    word-wrap: break-word;
}

.email-container {
    border: 1px solid #ccc;
    margin: 10px;
    padding: 10px;
    transition: border-color 0.3s ease-in-out;
    border-radius: 12px;
}

.email-container:hover {
    border-color: #000;
}

.attachment-download-btn {
    background-color: red;
    color: #fff;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    margin-top: 10px;
}

.attachment-download-btn:hover {
    background-color: darkred;
}

.outlook-web-app-btn {
    background-color: #0078d4;
    color: #fff;
    border: none;
    padding: 5px 10px;
    cursor: pointer;
    margin-top: 10px;
}

.outlook-web-app-btn:hover {
    background-color: #005a9e;
}

</style>