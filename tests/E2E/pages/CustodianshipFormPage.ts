import { Page, expect, Locator } from '@playwright/test';

export class CustodianshipFormPage {
  private nameInput: Locator;
  private messageTextarea: Locator;
  private intervalValueInput: Locator;
  private intervalUnitSelect: Locator;
  private addRecipientButton: Locator;
  private saveButton: Locator;
  private cancelButton: Locator;
  private fileInput: Locator;

  constructor(private page: Page) {
    this.nameInput = page.locator('#name');
    this.messageTextarea = page.locator('#messageContent');
    this.intervalValueInput = page.locator('#intervalValue');
    this.addRecipientButton = page.getByRole('button', { name: /add recipient/i });
    this.saveButton = page.getByRole('button', { name: /^save$/i });
    this.cancelButton = page.getByRole('button', { name: /cancel/i });
    this.fileInput = page.locator('input[type="file"]');
  }

  async gotoCreate() {
    await this.page.goto('/custodianships/create');
  }

  async gotoEdit(uuid: string) {
    await this.page.goto(`/custodianships/${uuid}/edit`);
  }

  async fillName(name: string) {
    await this.nameInput.fill(name);
  }

  async fillMessage(message: string) {
    await this.messageTextarea.fill(message);
  }

  async fillInterval(value: number, unit: 'minutes' | 'hours' | 'days' = 'days') {
    await this.intervalValueInput.fill(value.toString());

    const selectTrigger = this.page.locator('.w-32 button').first();
    await selectTrigger.waitFor({ state: 'visible', timeout: 5000 });
    await selectTrigger.click();

    await this.page.waitForTimeout(500);

    const option = this.page.locator(`[role="option"]`).filter({ hasText: new RegExp(unit, 'i') });
    await option.click();
  }

  async addRecipient(email: string) {
    await this.addRecipientButton.click();

    const recipientInputs = this.page.locator('input[type="email"]');
    const count = await recipientInputs.count();
    await recipientInputs.nth(count - 1).fill(email);
  }

  async fillRecipients(emails: string[]) {
    for (const email of emails) {
      await this.addRecipient(email);
    }
  }

  async removeRecipient(index: number) {
    const recipientInputs = this.page.locator('input[type="email"][placeholder*="example"]');
    const container = recipientInputs.nth(index).locator('..');
    const removeButton = container.locator('button[type="button"]');
    await removeButton.click();
  }

  async uploadFile(filePath: string) {
    await this.fileInput.setInputFiles(filePath);
  }

  async uploadMultipleFiles(filePaths: string[]) {
    await this.fileInput.setInputFiles(filePaths);
  }

  async waitForUploadComplete() {
    await this.page.waitForTimeout(500);
    const progressBars = this.page.locator('[role="progressbar"]');
    const count = await progressBars.count();
    if (count > 0) {
      await progressBars.first().waitFor({ state: 'hidden', timeout: 10000 }).catch(() => {});
    }
    await this.page.waitForTimeout(1000);
  }

  async removeAttachment(fileName: string) {
    const fileRow = this.page.locator('#app div').filter({ hasText: fileName }).filter({ has: this.page.locator('button') });
    await fileRow.locator('button').last().click();
  }

  async clickSave() {
    const navigationPromise = this.page.waitForURL(/\/custodianships/);
    await this.saveButton.click();
    await navigationPromise;
  }

  async clickCancel() {
    await this.cancelButton.click();
  }

  async clickSaveAndResetTimer() {
    const responsePromise = this.page.waitForResponse(
      response => (response.url().includes('/custodianships') &&
                   (response.status() === 200 || response.status() === 302))
    );

    await this.saveButton.click();
    await this.page.waitForTimeout(300);

    const resetButton = this.page.getByRole('button', { name: /save & reset timer/i });
    if (await resetButton.isVisible()) {
      await resetButton.click();
    }

    await responsePromise;
    await this.page.waitForLoadState('networkidle');
  }

  async clickSaveOnly() {
    const responsePromise = this.page.waitForResponse(
      response => (response.url().includes('/custodianships') &&
                   (response.status() === 200 || response.status() === 302))
    );

    await this.saveButton.click();
    await this.page.waitForTimeout(300);

    const saveOnlyButton = this.page.getByRole('button', { name: /save only/i });
    if (await saveOnlyButton.isVisible()) {
      await saveOnlyButton.click();
    }

    await responsePromise;
    await this.page.waitForLoadState('networkidle');
  }

  async fillForm(data: {
    name: string;
    message?: string;
    intervalValue?: number;
    intervalUnit?: 'minutes' | 'hours' | 'days';
    recipients?: string[];
  }) {
    await this.fillName(data.name);

    if (data.message) {
      await this.fillMessage(data.message);
    }

    if (data.intervalValue && data.intervalUnit) {
      await this.fillInterval(data.intervalValue, data.intervalUnit);
    }

    if (data.recipients && data.recipients.length > 0) {
      await this.fillRecipients(data.recipients);
    }
  }

  async verifyNameError(message: string) {
    const errorLocator = this.nameInput.locator('..').locator('..').getByText(message);
    await expect(errorLocator).toBeVisible();
  }

  async verifyRecipientError(message?: string | RegExp) {
    const emailInputs = this.page.locator('input[type="email"]');
    const count = await emailInputs.count();

    let foundInvalid = false;
    for (let i = 0; i < count; i++) {
      const input = emailInputs.nth(i);
      const isInvalid = await input.evaluate((el: HTMLInputElement) => !el.validity.valid);
      if (isInvalid) {
        foundInvalid = true;
        break;
      }
    }

    expect(foundInvalid).toBe(true);
  }

  async verifyAttachmentSizeError() {
    await expect(this.page.getByText(/exceed.*10MB/i)).toBeVisible();
  }

  async verifyDraftBanner() {
    await expect(this.page.getByText(/will be created as a draft/i)).toBeVisible();
  }

  async verifyRedirectedToShow() {
    await expect(this.page).toHaveURL(/\/custodianships\/[a-f0-9-]+$/);
  }

  async verifyRedirectedToIndex() {
    await expect(this.page).toHaveURL('/custodianships');
  }

  async getRecipientCount(): Promise<number> {
    const recipientInputs = this.page.locator('input[type="email"]');
    return await recipientInputs.count();
  }

  async verifyAddRecipientButtonDisabled() {
    await expect(this.addRecipientButton).toBeDisabled();
  }
}
