<?php

return [

    'expired_custodianship' => [
        'subject' => ':userName sent you an important message - :appName',
        'greeting' => 'You are receiving this message because :userName stopped resetting their timer in the :appName application.',
        'message_header' => 'Here is the message dedicated for you:',
        'download_attachments' => 'Download attachments:',
        'download_button' => 'Download Attachments',
    ],

    'custodianship_owner_alert' => [
        'subject_bounced' => ':appName - Email Bounced',
        'subject_failed' => ':appName - Delivery Failed',
        'subject_default' => ':appName - Delivery Alert',
        'greeting' => 'Delivery Problem for Your Custodianship',
        'not_delivered' => 'The message for your custodianship \':custodianshipName\' was not delivered to :recipientEmail.',
        'reason' => 'Reason: :errorMessage',
        'check_email' => 'Please check the recipient email address and update your custodianship if needed.',
        'view_button' => 'View Custodianship',
    ],

];
