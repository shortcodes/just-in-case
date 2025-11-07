import { Page, expect, Locator } from '@playwright/test';

export class CustodianshipShowPage {
  private editButton: Locator;
  private deleteButton: Locator;
  private resetTimerButton: Locator;
  private activateButton: Locator;

  constructor(private page: Page) {
    this.editButton = page.getByRole('button', { name: /edit/i });
    this.resetTimerButton = page.locator('[data-testid="reset-timer-button"]');
    this.activateButton = page.locator('[data-testid="activate-button"]');
  }

  async goto(uuid: string) {
    await this.page.goto(`/custodianships/${uuid}`);
  }

  async clickEdit() {
    await this.editButton.click();
  }

  async clickResetTimer() {
    await this.resetTimerButton.click();
    await this.page.waitForTimeout(300);

    const confirmButton = this.page.getByRole('button', { name: /confirm/i });
    if (await confirmButton.isVisible()) {
      await confirmButton.click();
      await this.page.waitForLoadState('networkidle');
    }
  }

  async clickActivate() {
    await this.activateButton.click();
    await this.page.waitForTimeout(300);

    const confirmButton = this.page.getByRole('button', { name: /confirm/i });
    if (await confirmButton.isVisible()) {
      await confirmButton.click();
      await this.page.waitForLoadState('networkidle');
    }
  }

  async clickDelete() {
    await this.page.getByRole('button', { name: /delete/i }).click();
  }

  async confirmDelete(custodianshipName: string) {
    await this.page.getByText(/i understand this action is permanent/i).click();

    const confirmInput = this.page.getByPlaceholder(/enter custodianship name/i);
    await confirmInput.fill(custodianshipName);

    await this.page.getByRole('button', { name: /delete permanently/i }).click();
  }

  async verifyName(name: string) {
    await expect(this.page.locator('h1').getByText(name)).toBeVisible();
  }

  async verifyStatus(status: 'draft' | 'active' | 'completed' | 'delivery_failed') {
    const statusBadge = this.page.locator('[data-testid="status-badge"]');
    await expect(statusBadge).toContainText(status, { ignoreCase: true });
  }

  async verifyRecipient(email: string) {
    await expect(this.page.getByText(email)).toBeVisible();
  }

  async verifyMessageContent(content: string) {
    await this.page.getByRole('button', { name: /show/i }).click();
    await expect(this.page.getByText(content)).toBeVisible();
  }

  async verifyAttachment(fileName: string) {
    await expect(this.page.locator('#app').getByText(fileName).first()).toBeVisible();
  }

  async verifyTimerSection() {
    await expect(this.page.locator('[data-testid="timer-section"]')).toBeVisible();
  }

  async verifyInterval(interval: string) {
    await expect(this.page.getByText(new RegExp(interval, 'i'))).toBeVisible();
  }

  async verifyResetTimerButtonDisabled() {
    await expect(this.resetTimerButton).toBeDisabled();
  }

  async verifyDeleteButtonDisabled() {
    await expect(this.deleteButton).toBeDisabled();
  }

  async verifyRedirectedToIndex() {
    await expect(this.page).toHaveURL('/custodianships');
  }

  async expandResetHistory() {
    await this.page.getByText(/reset history/i).click();
  }

  async verifyResetHistoryCount(count: number) {
    await this.expandResetHistory();
    const rows = this.page.locator('table tbody tr');
    await expect(rows).toHaveCount(count);
  }
}
