<?php

return [

    'expired_custodianship' => [
        'subject' => ':userName wysłał Ci ważną wiadomość - :appName',
        'greeting' => 'Otrzymujesz tę wiadomość, ponieważ :userName przestał resetować licznik w aplikacji :appName.',
        'message_header' => 'Oto wiadomość dedykowana dla Ciebie:',
        'download_attachments' => 'Pobierz załączniki:',
        'download_button' => 'Pobierz załączniki',
    ],

    'custodianship_owner_alert' => [
        'subject_bounced' => ':appName - E-mail odrzucony',
        'subject_failed' => ':appName - Błąd dostarczenia',
        'subject_default' => ':appName - Alert dostarczenia',
        'greeting' => 'Problem z dostarczeniem powiernictwa',
        'not_delivered' => 'Wiadomość dla Twojego powiernictwa \':custodianshipName\' nie została dostarczona do :recipientEmail.',
        'reason' => 'Powód: :errorMessage',
        'check_email' => 'Sprawdź adres e-mail odbiorcy i zaktualizuj swoje powiernictwo w razie potrzeby.',
        'view_button' => 'Zobacz powiernictwo',
    ],

];
