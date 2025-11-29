Feature: User Registration
  As a new user
  I want to register for an account
  So that I can access the application

  Scenario: User can register successfully with valid data
    When the user navigates to the register page
    And the user fills in name "Test User", email "newuser@example.com", password "password123", and password confirmation "password123"
    And the user submits the registration form
    Then the user should be redirected to the login page
    And the user should be able to login with email "newuser@example.com" and password "password123"

  Scenario: User cannot register with duplicate email
    Given a user is registered with email "existing@example.com" and password "password123"
    When the user navigates to the register page
    And the user fills in name "Another User", email "existing@example.com", password "password123", and password confirmation "password123"
    And the user submits the registration form
    Then the user should see an error message about duplicate email
    And the user should remain on the register page

  Scenario: User is redirected to login after successful registration
    When the user navigates to the register page
    And the user fills in name "Test User", email "newuser2@example.com", password "password123", and password confirmation "password123"
    And the user submits the registration form
    Then the user should be on the login page

