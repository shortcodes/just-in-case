import { Page, expect } from '@playwright/test';

export async function waitForPageLoad(page: Page, timeout: number = 5000): Promise<void> {
  await page.waitForLoadState('networkidle', { timeout });
}

export async function fillFormField(page: Page, selector: string, value: string): Promise<void> {
  await page.fill(selector, value);
  await page.waitForTimeout(100);
}

export async function clickButton(page: Page, selector: string): Promise<void> {
  await page.click(selector);
  await page.waitForTimeout(200);
}

export async function selectOption(page: Page, selector: string, value: string): Promise<void> {
  await page.selectOption(selector, value);
  await page.waitForTimeout(100);
}

export async function verifyToastMessage(page: Page, message: string): Promise<void> {
  await expect(page.locator('[data-testid="toast"]').getByText(message)).toBeVisible({ timeout: 5000 });
}

export async function verifyErrorMessage(page: Page, message: string): Promise<void> {
  await expect(page.locator('.error, .text-red-500').getByText(message)).toBeVisible({ timeout: 3000 });
}

export async function closeModal(page: Page): Promise<void> {
  await page.keyboard.press('Escape');
  await page.waitForTimeout(300);
}

export async function confirmModal(page: Page, buttonSelector: string = '[data-testid="confirm-button"]'): Promise<void> {
  await page.click(buttonSelector);
  await page.waitForTimeout(300);
}

export async function uploadFile(page: Page, inputSelector: string, filePath: string): Promise<void> {
  const fileInput = page.locator(inputSelector);
  await fileInput.setInputFiles(filePath);
  await page.waitForTimeout(500);
}

export function generateTestFile(sizeInBytes: number, filename: string = 'test-file.txt'): Buffer {
  const content = 'a'.repeat(sizeInBytes);
  return Buffer.from(content);
}

export async function scrollToElement(page: Page, selector: string): Promise<void> {
  await page.locator(selector).scrollIntoViewIfNeeded();
  await page.waitForTimeout(200);
}

export async function waitForRequest(page: Page, urlPattern: string | RegExp, timeout: number = 5000): Promise<void> {
  await page.waitForRequest(urlPattern, { timeout });
}

export async function waitForResponse(page: Page, urlPattern: string | RegExp, timeout: number = 5000): Promise<void> {
  await page.waitForResponse(urlPattern, { timeout });
}

export function formatDate(date: Date): string {
  return date.toISOString().split('T')[0];
}

export function addDays(date: Date, days: number): Date {
  const result = new Date(date);
  result.setDate(result.getDate() + days);
  return result;
}
