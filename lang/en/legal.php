<?php

return [
    'privacy_policy' => [
        'title' => 'Privacy Policy',
        'last_updated' => 'Last updated',
        'data_controller' => [
            'title' => '1. Data Controller',
            'content' => 'The data controller responsible for processing your personal data is Shortcodes Roman Szymański, NIP 8871788633, ul. Okrzei 28, 55-080 Kąty Wrocławskie, Poland. For questions regarding data protection, please contact us at roman.szymanski@shortcodes.pl.',
        ],
        'data_collected' => [
            'title' => '2. Data We Collect',
            'intro' => 'We collect and process the following categories of personal data:',
            'items' => [
                'Account information: name, email address, password (securely stored)',
                'Custodianship data: message content, recipient email addresses, timer intervals',
                'Attachments: files you upload to custodianships (stored encrypted)',
                'Technical data: IP addresses, browser information, access logs',
                'Usage data: timer resets, custodianship activity, login history',
            ],
        ],
        'legal_basis' => [
            'title' => '3. Legal Basis for Processing',
            'intro' => 'We process your data based on the following legal grounds under GDPR Article 6:',
            'items' => [
                'Contract performance: To provide the Just In Case service you registered for',
                'Consent: For optional features and communications you explicitly agree to',
                'Legitimate interests: To improve our service, prevent fraud, and ensure security',
            ],
        ],
        'data_usage' => [
            'title' => '4. How We Use Your Data',
            'intro' => 'Your personal data is used for:',
            'items' => [
                'Managing timer countdowns and automated message delivery',
                'Storing and securely transmitting custodianship content and attachments',
                'Sending reminder emails before timer expiration',
                'Providing customer support and technical assistance',
                'Detecting and preventing fraudulent activity',
                'Complying with legal obligations',
            ],
        ],
        'data_retention' => [
            'title' => '5. Data Retention',
            'content' => 'We retain your personal data for as long as your account is active. Inactive accounts (no login or timer reset for 24 months) will be automatically deleted along with all associated data. You may delete your account at any time from your profile settings.',
        ],
        'user_rights' => [
            'title' => '6. Your Rights (GDPR)',
            'intro' => 'Under GDPR, you have the following rights:',
            'items' => [
                'Right to access: Request a copy of your personal data',
                'Right to rectification: Correct inaccurate or incomplete data',
                'Right to erasure: Delete your account and all associated data',
                'Right to data portability: Receive your data in a machine-readable format',
                'Right to object: Object to certain types of data processing',
                'Right to withdraw consent: Revoke consent for optional features',
            ],
            'footer' => 'To exercise these rights, contact us at roman.szymanski@shortcodes.pl or use the account settings in your dashboard.',
        ],
        'data_security' => [
            'title' => '7. Data Security',
            'intro' => 'We implement industry-standard security measures to protect your data:',
            'items' => [
                'Encryption at-rest for all attachments',
                'Encrypted transmission using HTTPS/TLS',
                'Securely stored passwords using industry-standard hashing',
                'Access controls and authentication mechanisms',
                'Regular security monitoring',
            ],
        ],
        'third_party' => [
            'title' => '8. Third-Party Services',
            'intro' => 'We use trusted third-party service providers to operate Just In Case:',
            'items' => [
                'Cloud storage providers: Encrypted file storage for attachments',
                'Email service providers: Delivery of custodianship messages and notifications',
            ],
            'footer' => 'All third-party providers are GDPR-compliant and process data only as instructed by us under strict data processing agreements.',
        ],
        'international_transfers' => [
            'title' => '9. International Data Transfers',
            'intro' => 'Your data may be transferred to and processed in countries outside the European Economic Area (EEA). We ensure adequate protection through:',
            'items' => [
                'EU-US Data Privacy Framework compliance',
                'Standard Contractual Clauses (SCCs) with third-party providers',
                'Encryption and security safeguards for all data transfers',
            ],
        ],
        'cookies' => [
            'title' => '10. Cookies',
            'content' => 'We use essential cookies to maintain your login session. No tracking or analytics cookies are used without your consent.',
        ],
        'changes' => [
            'title' => '11. Changes to This Policy',
            'content' => 'We may update this Privacy Policy from time to time. We will notify you of material changes via email or a prominent notice on our website. Continued use of the service after changes constitutes acceptance of the updated policy.',
        ],
        'contact' => [
            'title' => '12. Contact Us',
            'intro' => 'For questions or concerns about this Privacy Policy or data protection:',
            'email' => 'Email: roman.szymanski@shortcodes.pl',
        ],
    ],

    'terms_of_service' => [
        'title' => 'Terms of Service',
        'last_updated' => 'Last updated',
        'acceptance' => [
            'title' => '1. Acceptance of Terms',
            'content' => 'By creating an account and using Just In Case, you agree to be bound by these Terms of Service. If you do not agree to these terms, you may not use the service.',
        ],
        'service_description' => [
            'title' => '2. Service Description',
            'intro' => 'Just In Case is an automated information delivery service that allows users to:',
            'items' => [
                'Create custodianships containing messages and file attachments',
                'Set timer intervals for automatic message delivery',
                'Designate recipients who will receive messages if timers expire',
                'Reset timers to prevent automatic delivery',
            ],
        ],
        'disclaimer' => [
            'title' => '3. Not a legal testament',
            'intro' => 'Just In Case is NOT a legal testament, will, or legal service of any kind.',
            'items' => [
                'This service does not replace legal estate planning or testamentary documents',
                'Messages and instructions have no legal binding force',
                'We are not lawyers and provide no legal advice',
                'For legally binding inheritance arrangements, consult a qualified attorney',
                'Just In Case is solely an automated message timer and delivery system',
            ],
        ],
        'user_responsibilities' => [
            'title' => '4. User Responsibilities',
            'intro' => 'You agree to:',
            'items' => [
                'Provide accurate and current recipient email addresses',
                'Reset timers regularly to prevent unintended message delivery',
                'Keep your account credentials secure and confidential',
                'Not use the service for illegal, harmful, or fraudulent purposes',
                'Not upload malware, viruses, or malicious content',
                'Not violate third-party intellectual property rights',
                'Comply with all applicable laws and regulations',
            ],
        ],
        'free_plan' => [
            'title' => '5. Free Plan Limitations',
            'intro' => 'The Free plan includes the following limits:',
            'items' => [
                'Maximum 3 custodianships per user',
                'Maximum 2 recipients per custodianship',
                'Maximum 10MB total attachment size per custodianship',
            ],
            'footer' => 'These limits may be changed or removed in future paid plans.',
        ],
        'service_availability' => [
            'title' => '6. Service Availability',
            'intro' => 'We strive to provide reliable service, but we do not guarantee uninterrupted or error-free operation. The service is provided "as is" without warranties of any kind. We reserve the right to:',
            'items' => [
                'Perform scheduled maintenance with reasonable notice',
                'Modify or discontinue features temporarily or permanently',
                'Suspend accounts violating these Terms',
            ],
        ],
        'email_delivery' => [
            'title' => '7. Email Delivery',
            'intro' => 'While we make reasonable efforts to deliver messages reliably:',
            'items' => [
                'We cannot guarantee delivery to all email addresses',
                'Messages may be delayed, filtered as spam, or bounced',
                'We are not responsible for recipient email server issues',
                'Invalid or non-existent recipient emails may cause delivery failure',
            ],
            'footer' => 'You will be notified if message delivery fails.',
        ],
        'content_ownership' => [
            'title' => '8. Content Ownership and Restrictions',
            'intro' => 'You retain ownership of all content you upload. However, you may not upload:',
            'items' => [
                'Content that violates laws or regulations',
                'Threatening, defamatory, or harassing content',
                'Malware, viruses, or harmful code',
                'Content infringing third-party copyrights, trademarks, or rights',
            ],
            'footer' => 'We reserve the right to remove prohibited content and terminate accounts violating this policy.',
        ],
        'termination' => [
            'title' => '9. Account Termination',
            'intro' => 'We may suspend or terminate your account if you:',
            'items' => [
                'Violate these Terms of Service',
                'Engage in fraudulent or abusive behavior',
                'Fail to respond to security or verification requests',
            ],
            'footer' => 'You may delete your account at any time from profile settings. Account deletion is permanent and irreversible.',
        ],
        'liability' => [
            'title' => '10. Limitation of Liability',
            'intro' => 'To the maximum extent permitted by law:',
            'items' => [
                'Just In Case is provided "as is" without warranties',
                'We are not liable for service interruptions, data loss, or delivery failures',
                'We are not responsible for consequences of timer expiration or message delivery',
                'Our total liability is limited to the amount you paid (if any) in the past 12 months',
            ],
        ],
        'indemnification' => [
            'title' => '11. Indemnification',
            'intro' => 'You agree to indemnify and hold Just In Case harmless from claims, damages, or expenses arising from:',
            'items' => [
                'Your use of the service',
                'Your violation of these Terms',
                'Your violation of third-party rights',
                'Content you upload or messages you send',
            ],
        ],
        'governing_law' => [
            'title' => '12. Governing Law',
            'content' => 'These Terms are governed by the laws of Poland. Any disputes shall be resolved in Polish courts.',
        ],
        'changes' => [
            'title' => '13. Changes to Terms',
            'content' => 'We may update these Terms from time to time. Material changes will be communicated via email or prominent notice. Continued use after changes constitutes acceptance of updated Terms.',
        ],
        'contact' => [
            'title' => '14. Contact',
            'intro' => 'For questions about these Terms of Service:',
            'email' => 'Email: roman.szymanski@shortcodes.pl',
        ],
    ],

    'legal_disclaimer' => [
        'title' => 'Legal Disclaimer',
        'important' => [
            'title' => 'Notice',
            'content' => 'Just In Case is NOT a legal testament, will, or any form of legal service.',
        ],
        'what_it_is' => [
            'title' => 'What Just In Case IS:',
            'items' => [
                'An automated message timer and delivery system',
                'A tool to store and transmit information to designated recipients',
                'A convenient way to share passwords, instructions, and documents',
            ],
        ],
        'what_it_is_not' => [
            'title' => 'What Just In Case is NOT:',
            'items' => [
                'A legally binding testament or will',
                'A replacement for proper estate planning',
                'Legal advice or legal service of any kind',
                'Guaranteed to deliver messages in all circumstances',
                'A substitute for consulting with qualified legal professionals',
            ],
        ],
        'no_legal_force' => [
            'title' => 'No Legal Force or Binding Effect',
            'intro' => 'Messages, instructions, and content stored in Just In Case have no legal binding force and cannot be used as:',
            'items' => [
                'A valid testament or will under any jurisdiction',
                'Legal proof of ownership, inheritance, or asset transfer',
                'A legally enforceable contract or agreement',
                'Evidence in legal or probate proceedings',
            ],
        ],
        'no_guarantees' => [
            'title' => 'No Guarantees of Delivery',
            'intro' => 'While we strive for reliable service, we cannot and do not guarantee:',
            'items' => [
                'Messages will be delivered in all circumstances',
                'Delivery will occur at the exact timer expiration time',
                'Recipients will actually receive or read the messages',
                'Attachments will remain accessible indefinitely',
                'The service will remain operational continuously without interruption',
            ],
        ],
        'consult_professionals' => [
            'title' => 'Consult Legal Professionals',
            'intro' => 'For legally binding arrangements regarding:',
            'items' => [
                'Wills and testaments',
                'Estate planning and inheritance',
                'Asset distribution and trusts',
                'Power of attorney or guardianship',
                'Any other legal matters',
            ],
            'footer' => 'YOU MUST consult with a qualified attorney licensed in your jurisdiction.',
        ],
        'user_responsibility' => [
            'title' => 'User Responsibility',
            'intro' => 'By using Just In Case, you acknowledge that:',
            'items' => [
                'You understand this is not a legal service',
                'You are responsible for proper legal arrangements through appropriate legal channels',
                'Just In Case cannot replace professional legal advice',
                'We are not liable for any legal consequences arising from your use of the service',
            ],
        ],
        'liability_limitation' => [
            'title' => 'Limitation of Liability',
            'content' => 'Just In Case, its owners, operators, and employees are not liable for any consequences, damages, or losses arising from service use, non-use, delivery failures, or reliance on messages as legal documents. Use this service at your own risk.',
        ],
    ],
];
