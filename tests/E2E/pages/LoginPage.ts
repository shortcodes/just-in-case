import { Page, expect } from '@playwright/test';

export class LoginPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/login');
  }

  async fillEmail(email: string) {
    await this.page.fill('#email', email);
  }

  async fillPassword(password: string) {
    await this.page.fill('#password', password);
  }

  async clickSubmit() {
    await this.page.click('button[type="submit"]');
  }

  async login(email: string, password: string) {
    await this.fillEmail(email);
    await this.fillPassword(password);
    await this.clickSubmit();
  }

  async verifyErrorMessage(message: string) {
    await expect(this.page.locator('.text-red-500, .text-destructive').getByText(message)).toBeVisible();
  }

  async verifyRedirectedToDashboard() {
    await expect(this.page).toHaveURL('/custodianships');
  }
}
