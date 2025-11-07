import { Laravel } from '@hyvor/laravel-playwright';

export async function resetDatabase(laravel: Laravel): Promise<void> {
  await laravel.artisan('migrate:fresh');
  await laravel.artisan('config:clear');
}

export async function seedDatabase(laravel: Laravel, seeder?: string): Promise<void> {
  if (seeder) {
    await laravel.artisan('db:seed', { class: seeder });
  } else {
    await laravel.artisan('db:seed');
  }
}

export async function createCustodianship(
  laravel: Laravel,
  userId: number,
  data: Partial<{
    name: string;
    message: string;
    interval_days: number;
    status: string;
    last_reset_at: string;
    next_trigger_at: string;
  }> = {}
): Promise<any> {
  return await laravel.databaseQuery(
    `INSERT INTO custodianships (user_id, name, message, interval_days, status, last_reset_at, next_trigger_at, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())`,
    [
      userId,
      data.name || 'Test Custodianship',
      data.message || 'Test message',
      data.interval_days || 30,
      data.status || 'draft',
      data.last_reset_at || new Date().toISOString(),
      data.next_trigger_at || new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString()
    ]
  );
}

export async function expireCustodianship(laravel: Laravel, custodianshipId: number): Promise<void> {
  await laravel.databaseQuery(
    `UPDATE custodianships SET next_trigger_at = NOW() - INTERVAL 1 DAY WHERE id = ?`,
    [custodianshipId]
  );
}

export async function setTimerToNearExpiry(
  laravel: Laravel,
  custodianshipId: number,
  daysRemaining: number = 5
): Promise<void> {
  await laravel.databaseQuery(
    `UPDATE custodianships SET next_trigger_at = NOW() + INTERVAL ? DAY WHERE id = ?`,
    [daysRemaining, custodianshipId]
  );
}

export async function getCustodianshipCount(laravel: Laravel, userId: number): Promise<number> {
  const result = await laravel.databaseQuery(
    `SELECT COUNT(*) as count FROM custodianships WHERE user_id = ?`,
    [userId]
  );
  return result[0]?.count || 0;
}

export async function getRecipientCount(laravel: Laravel, custodianshipId: number): Promise<number> {
  const result = await laravel.databaseQuery(
    `SELECT COUNT(*) as count FROM recipients WHERE custodianship_id = ?`,
    [custodianshipId]
  );
  return result[0]?.count || 0;
}

export async function addRecipient(
  laravel: Laravel,
  custodianshipId: number,
  email: string
): Promise<void> {
  await laravel.databaseQuery(
    `INSERT INTO recipients (custodianship_id, email, created_at, updated_at) VALUES (?, ?, NOW(), NOW())`,
    [custodianshipId, email]
  );
}

export async function getTotalAttachmentSize(
  laravel: Laravel,
  custodianshipId: number
): Promise<number> {
  const result = await laravel.databaseQuery(
    `SELECT COALESCE(SUM(size), 0) as total_size FROM attachments WHERE custodianship_id = ?`,
    [custodianshipId]
  );
  return result[0]?.total_size || 0;
}
