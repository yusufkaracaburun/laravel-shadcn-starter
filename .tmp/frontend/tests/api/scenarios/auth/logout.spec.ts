import { test } from '../../fixtures';
import {
  expectSuccess,
  expectStatus,
  expectUnauthenticated,
} from '../../features/shared/helpers/test-helpers';

test.describe('Logout', { tag: ['@api', '@auth', '@logout'] }, () => {
  test('should logout when authenticated', async ({ authenticatedAuthClient }) => {
    // Arrange: authenticatedAuthClient fixture provides pre-authenticated client
    // Verify authenticated before logout
    const beforeLogoutResponse = await authenticatedAuthClient.getUser();
    expectSuccess(beforeLogoutResponse);

    // Act
    const logoutResponse = await authenticatedAuthClient.logout();

    // Assert
    expectStatus(logoutResponse, 204);

    // Verify unauthenticated after logout
    const afterLogoutResponse = await authenticatedAuthClient.getUser();
    expectUnauthenticated(afterLogoutResponse);
  });

  test('should not logout when not authenticated', async ({ authClient }) => {
    // Arrange: Use unauthenticated client
    await authClient.getCsrfCookie(); // Get CSRF cookie (as a real SPA would do)

    // Act
    const response = await authClient.logout();

    // Assert
    expectUnauthenticated(response);
  });
});
