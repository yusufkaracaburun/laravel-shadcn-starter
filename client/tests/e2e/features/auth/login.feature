Feature: User Login
  As a user
  I want to login to my account
  So that I can access protected features

  Scenario: User can login successfully with valid credentials
    Given a user is registered with email "test@example.com" and password "password123"
    When the user navigates to the login page
    And the user fills in email "test@example.com" and password "password123"
    And the user submits the login form
    Then the user should be redirected to the dashboard
    And the user should be authenticated

  Scenario: User cannot login with invalid credentials
    When the user navigates to the login page
    And the user fills in email "invalid@example.com" and password "wrongpassword"
    And the user submits the login form
    Then the user should see an error message
    And the user should remain on the login page

  Scenario: User is redirected to dashboard after successful login
    Given a user is registered with email "test@example.com" and password "password123"
    When the user navigates to the login page
    And the user fills in email "test@example.com" and password "password123"
    And the user submits the login form
    Then the user should be on the dashboard page

