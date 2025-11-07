export const validCustodianship = {
  name: 'Important Passwords',
  message: 'Here are my important passwords and access codes. Please use them responsibly.',
  interval_days: 30,
  recipients: [
    { email: 'recipient1@example.com' },
    { email: 'recipient2@example.com' }
  ]
};

export const minimalCustodianship = {
  name: 'Simple Message',
  message: 'This is a minimal custodianship for testing.',
  interval_days: 30,
  recipients: [
    { email: 'single-recipient@example.com' }
  ]
};

export const custodianshipWithAttachments = {
  name: 'Documents Package',
  message: 'Attached documents contain important information.',
  interval_days: 90,
  recipients: [
    { email: 'recipient@example.com' }
  ],
  attachments: [
    { name: 'document.pdf', size: 1024 * 1024 },
    { name: 'spreadsheet.xlsx', size: 512 * 1024 }
  ]
};

export const freemiumLimits = {
  maxCustodianships: 3,
  maxRecipients: 2,
  maxAttachmentSizeMB: 10,
  maxAttachmentSizeBytes: 10 * 1024 * 1024
};

export const intervalOptions = [
  { value: 7, label: '7 days' },
  { value: 30, label: '30 days' },
  { value: 60, label: '60 days' },
  { value: 90, label: '90 days' },
  { value: 180, label: '180 days' },
  { value: 365, label: '365 days' }
];

export const testUser = {
  name: 'Test User',
  email: 'testuser@example.com',
  password: 'password123'
};

export const testUsers = {
  basic: {
    name: 'Basic User',
    email: 'basic@example.com',
    password: 'password123'
  },
  withCustodianships: {
    name: 'User With Custodianships',
    email: 'with-custodianships@example.com',
    password: 'password123'
  },
  atLimit: {
    name: 'User At Limit',
    email: 'at-limit@example.com',
    password: 'password123'
  }
};

export const errorMessages = {
  custodianshipNameRequired: 'The name field is required.',
  emailInvalid: 'The email field must be a valid email address.',
  maxCustodiansReached: 'You have reached the maximum number of custodianships (3) for the free plan.',
  maxRecipientsReached: 'Maximum 2 recipients allowed in free plan.',
  maxAttachmentSizeExceeded: 'Total attachment size cannot exceed 10MB.',
  emailAlreadyTaken: 'The email has already been taken.'
};
