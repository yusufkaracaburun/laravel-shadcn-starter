// Generated from: tests/e2e/features/auth/login.feature
import { test } from "playwright-bdd";

test.describe('User Login', () => {

  test('User can login successfully with valid credentials', async ({ Given, When, Then, And, page, request }) => { 
    await Given('a user is registered with email "test@example.com" and password "password123"', null, { request }); 
    await When('the user navigates to the login page', null, { page }); 
    await And('the user fills in email "test@example.com" and password "password123"', null, { page }); 
    await And('the user submits the login form', null, { page }); 
    await Then('the user should be redirected to the dashboard', null, { page }); 
    await And('the user should be authenticated', null, { page }); 
  });

  test('User cannot login with invalid credentials', async ({ When, Then, And, page }) => { 
    await When('the user navigates to the login page', null, { page }); 
    await And('the user fills in email "invalid@example.com" and password "wrongpassword"', null, { page }); 
    await And('the user submits the login form', null, { page }); 
    await Then('the user should see an error message', null, { page }); 
    await And('the user should remain on the login page', null, { page }); 
  });

  test('User is redirected to dashboard after successful login', async ({ Given, When, Then, And, page, request }) => { 
    await Given('a user is registered with email "test@example.com" and password "password123"', null, { request }); 
    await When('the user navigates to the login page', null, { page }); 
    await And('the user fills in email "test@example.com" and password "password123"', null, { page }); 
    await And('the user submits the login form', null, { page }); 
    await Then('the user should be on the dashboard page', null, { page }); 
  });

});

// == technical section ==

test.use({
  $test: [({}, use) => use(test), { scope: 'test', box: true }],
  $uri: [({}, use) => use('tests/e2e/features/auth/login.feature'), { scope: 'test', box: true }],
  $bddFileData: [({}, use) => use(bddFileData), { scope: "test", box: true }],
});

const bddFileData = [ // bdd-data-start
  {"pwTestLine":6,"pickleLine":6,"tags":[],"steps":[{"pwStepLine":7,"gherkinStepLine":7,"keywordType":"Context","textWithKeyword":"Given a user is registered with email \"test@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":32,"value":"\"test@example.com\"","children":[{"start":33,"value":"test@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":64,"value":"\"password123\"","children":[{"start":65,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":8,"gherkinStepLine":8,"keywordType":"Action","textWithKeyword":"When the user navigates to the login page","stepMatchArguments":[]},{"pwStepLine":9,"gherkinStepLine":9,"keywordType":"Action","textWithKeyword":"And the user fills in email \"test@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":24,"value":"\"test@example.com\"","children":[{"start":25,"value":"test@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":56,"value":"\"password123\"","children":[{"start":57,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":10,"gherkinStepLine":10,"keywordType":"Action","textWithKeyword":"And the user submits the login form","stepMatchArguments":[]},{"pwStepLine":11,"gherkinStepLine":11,"keywordType":"Outcome","textWithKeyword":"Then the user should be redirected to the dashboard","stepMatchArguments":[]},{"pwStepLine":12,"gherkinStepLine":12,"keywordType":"Outcome","textWithKeyword":"And the user should be authenticated","stepMatchArguments":[]}]},
  {"pwTestLine":15,"pickleLine":14,"tags":[],"steps":[{"pwStepLine":16,"gherkinStepLine":15,"keywordType":"Action","textWithKeyword":"When the user navigates to the login page","stepMatchArguments":[]},{"pwStepLine":17,"gherkinStepLine":16,"keywordType":"Action","textWithKeyword":"And the user fills in email \"invalid@example.com\" and password \"wrongpassword\"","stepMatchArguments":[{"group":{"start":24,"value":"\"invalid@example.com\"","children":[{"start":25,"value":"invalid@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":59,"value":"\"wrongpassword\"","children":[{"start":60,"value":"wrongpassword","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":18,"gherkinStepLine":17,"keywordType":"Action","textWithKeyword":"And the user submits the login form","stepMatchArguments":[]},{"pwStepLine":19,"gherkinStepLine":18,"keywordType":"Outcome","textWithKeyword":"Then the user should see an error message","stepMatchArguments":[]},{"pwStepLine":20,"gherkinStepLine":19,"keywordType":"Outcome","textWithKeyword":"And the user should remain on the login page","stepMatchArguments":[]}]},
  {"pwTestLine":23,"pickleLine":21,"tags":[],"steps":[{"pwStepLine":24,"gherkinStepLine":22,"keywordType":"Context","textWithKeyword":"Given a user is registered with email \"test@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":32,"value":"\"test@example.com\"","children":[{"start":33,"value":"test@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":64,"value":"\"password123\"","children":[{"start":65,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":25,"gherkinStepLine":23,"keywordType":"Action","textWithKeyword":"When the user navigates to the login page","stepMatchArguments":[]},{"pwStepLine":26,"gherkinStepLine":24,"keywordType":"Action","textWithKeyword":"And the user fills in email \"test@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":24,"value":"\"test@example.com\"","children":[{"start":25,"value":"test@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":56,"value":"\"password123\"","children":[{"start":57,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":27,"gherkinStepLine":25,"keywordType":"Action","textWithKeyword":"And the user submits the login form","stepMatchArguments":[]},{"pwStepLine":28,"gherkinStepLine":26,"keywordType":"Outcome","textWithKeyword":"Then the user should be on the dashboard page","stepMatchArguments":[]}]},
]; // bdd-data-end