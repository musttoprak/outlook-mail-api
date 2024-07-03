<?php
// IMAP sunucu ayarları
$hostname = '{outlook.office365.com:993/imap/ssl}INBOX'; // IMAP sunucu adresi ve gelen kutusu
$username = 'imap_mail'; // E-posta adresi
$password = 'imap_pass'; // E-posta şifresi

// Bağlantıyı aç
$inbox = imap_open($hostname, $username, $password) or die('IMAP sunucusuna bağlanılamadı: ' . imap_last_error());

// Göndericinin e-posta adresi
$senderEmail = 'sender_mail';

// Göndericinin size attığı en son e-postayı al
$latestEmail = getLatestEmailBySender($inbox, $senderEmail);

// Eğer e-posta bulunduysa göster
if ($latestEmail) {
    echo '<h2>' . htmlspecialchars($latestEmail['subject']) . '</h2>';
    echo '<p><strong>Gönderen:</strong> ' . htmlspecialchars($latestEmail['from']) . '</p>';
    echo '<p><strong>Tarih:</strong> ' . htmlspecialchars($latestEmail['date']) . '</p>';
    echo '<div>' . htmlspecialchars($latestEmail['body']) . '</div>';
} else {
    echo "Belirtilen göndericiden hiç e-posta bulunamadı.";
}

// Bağlantıyı kapat
imap_close($inbox);

// Belirli bir göndericinin size gönderdiği en son e-postayı alacak fonksiyon
function getLatestEmailBySender($inbox, $senderEmail) {
    // Göndericiye ait e-postaları al
    $emails = imap_search($inbox, "FROM \"$senderEmail\"");
    
    // Eğer e-posta bulunamadıysa false döndür
    if (!$emails) {
        return false;
    }

    // E-postaları tarihe göre sırala
    rsort($emails);

    // En son e-postayı al
    $latestEmail = $emails[0];

    // E-posta detaylarını al
    $overview = imap_fetch_overview($inbox, $latestEmail, 0);

    // E-posta gövdesini al
    $body = imap_fetchbody($inbox, $latestEmail, 1);

    // E-posta bilgilerini döndür
    return array(
        'subject' => $overview[0]->subject,
        'from' => $overview[0]->from,
        'date' => $overview[0]->date,
        'body' => $body
    );
}
?>
