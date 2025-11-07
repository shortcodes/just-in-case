import { Page, expect, Locator } from '@playwright/test';

export class DashboardPage {
  private createNewButton: Locator;
  private emptyStateCreateButton: Locator;

  constructor(private page: Page) {
    this.createNewButton = page.getByRole('button', { name: /create new/i });
    this.emptyStateCreateButton = page.getByRole('button', { name: /create your first custodianship/i });
  }

  async goto() {
    await this.page.goto('/custodianships');
  }

  async clickCreateNew() {
    if (await this.emptyStateCreateButton.isVisible()) {
      await this.emptyStateCreateButton.click();
    } else {
      await this.createNewButton.click();
    }
  }

  async getCustodianshipCards() {
    return this.page.locator('[data-testid="custodianship-card"]').all();
  }

  async getCustodianshipCount(): Promise<number> {
    const cards = await this.getCustodianshipCards();
    return cards.length;
  }

  async verifyEmptyState() {
    await expect(this.page.getByRole('heading', { level: 3 }).filter({ hasText: /no custodianships yet/i })).toBeVisible();
  }

  async verifyCustodianshipExists(name: string) {
    await expect(this.page.getByText(name)).toBeVisible();
  }

  async clickCustodianshipByName(name: string) {
    await this.page.getByText(name).click();
  }

  async activateCustodianship(name: string) {
    const card = this.page.locator('[data-testid="custodianship-card"]').filter({ hasText: name });
    await card.locator('[data-testid="activate-button"]').click();
    await this.page.waitForTimeout(300);

    const confirmButton = this.page.getByRole('button', { name: /confirm/i });
    if (await confirmButton.isVisible()) {
      await confirmButton.click();
      await this.page.waitForLoadState('networkidle');
    }
  }

  async resetTimerForCustodianship(name: string) {
    const card = this.page.locator('[data-testid="custodianship-card"]').filter({ hasText: name });
    await card.locator('[data-testid="reset-timer-button"]').click();
    await this.page.waitForTimeout(300);

    const confirmButton = this.page.getByRole('button', { name: /confirm/i });
    if (await confirmButton.isVisible()) {
      await confirmButton.click();
      await this.page.waitForLoadState('networkidle');
    }
  }

  async verifyEmailVerificationBanner() {
    await expect(this.page.getByText(/verify your email|email verification/i)).toBeVisible();
  }

  async verifyCreateNewButtonDisabled() {
    await expect(this.createNewButton).toBeDisabled();
  }

  async verifyLimitMessage() {
    await expect(this.page.getByText(/reached the limit/i)).toBeVisible();
  }
}
