import { test, expect } from '../../fixtures';
import { expectSuccess } from '../../features/shared/helpers/test-helpers';

test.describe('Health Check', { tag: ['@api', '@health'] }, () => {
  test('should return 200 for health check endpoint', async ({ healthClient }) => {
    // Arrange & Act
    const response = await healthClient.check();

    // Assert
    expectSuccess(response);
  });

  test('should return valid response structure', async ({ healthClient }) => {
    // Arrange & Act
    const response = await healthClient.check();

    // Assert
    expectSuccess(response);
    const body = await response.text();
    expect(body).toBeTruthy();
  });
});
