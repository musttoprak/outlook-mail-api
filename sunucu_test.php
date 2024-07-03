<?php

// IMAP sunucu bilgileri
$hostname = '{outlook.office365.com:993/imap/ssl}INBOX';
//$username = 'hbdenemeci01@outlook.com';
//$password = '001100AA'; 
$username = 'imap_mail';
$password = 'imap_pass';

// Bağlantı süresini başlat
$start_time = microtime(true);

// IMAP sunucusuna bağlanma
$inbox = imap_open($hostname, $username, $password);

// Bağlantı süresini bitir
$end_time = microtime(true);
$total_time = $end_time - $start_time;

// Bağlantı başarılıysa
if ($inbox) {
    echo "IMAP sunucusuna başarıyla bağlandı. Bağlantı süresi: " . round($total_time, 4) . " saniye";
    // Bağlantıyı kapat
    imap_close($inbox);
} else {
    // Bağlantı başarısızsa
    echo "IMAP sunucusuna bağlanılamadı.";
}

?>
