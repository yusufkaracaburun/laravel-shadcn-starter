Feature: Authentication Flow
  As a user
  I want to complete the full authentication flow
  So that I can access and use the application

  Scenario: User can complete full authentication flow (register → login → logout)
    When the user navigates to the register page
    And the user fills in name "Test User", email "flowuser@example.com", password "password123", and password confirmation "password123"
    And the user submits the registration form
    Then the user should be redirected to the login page
    When the user fills in email "flowuser@example.com" and password "password123"
    And the user submits the login form
    Then the user should be on the dashboard page
    When the user logs out
    Then the user should be redirected to the login page
    And the user should not be able to access the dashboard

  Scenario: User can access protected routes after login
    Given a user is registered with email "protected@example.com" and password "password123"
    When the user navigates to the login page
    And the user fills in email "protected@example.com" and password "password123"
    And the user submits the login form
    Then the user should be on the dashboard page
    When the user navigates to the dashboard directly
    Then the user should be on the dashboard page

  Scenario: User is redirected to login when accessing protected routes without auth
    When the user navigates to the dashboard directly
    Then the user should be redirected to the login page
    And the user should not be authenticated

  Scenario: User session persists after page reload
    Given a user is registered with email "session@example.com" and password "password123"
    When the user navigates to the login page
    And the user fills in email "session@example.com" and password "password123"
    And the user submits the login form
    Then the user should be on the dashboard page
    When the user reloads the page
    Then the user should still be on the dashboard page
    And the user should still be authenticated

