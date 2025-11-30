// Generated from: tests/e2e/features/auth/auth-flow.feature
import { test } from "../../tests/e2e/fixtures.ts";

test.describe('Authentication Flow', () => {

  test('User can complete full authentication flow (register → login → logout)', async ({ When, Then, And, dashboardPage, loginPage, page, registerPage }) => { 
    await When('the user navigates to the register page', null, { registerPage }); 
    await And('the user fills in name "Test User", email "flowuser@example.com", password "password123", and password confirmation "password123"', null, { registerPage }); 
    await And('the user submits the registration form', null, { page, registerPage }); 
    await Then('the user should be redirected to the login page', null, { page }); 
    await When('the user fills in email "flowuser@example.com" and password "password123"', null, { loginPage }); 
    await And('the user submits the login form', null, { loginPage, page }); 
    await Then('the user should be on the dashboard page', null, { dashboardPage }); 
    await When('the user logs out', null, { dashboardPage, page }); 
    await Then('the user should be redirected to the login page', null, { page }); 
    await And('the user should not be able to access the dashboard', null, { page }); 
  });

  test('User can access protected routes after login', async ({ Given, When, Then, And, dashboardPage, loginPage, page, request }) => { 
    await Given('a user is registered with email "protected@example.com" and password "password123"', null, { request }); 
    await When('the user navigates to the login page', null, { loginPage }); 
    await And('the user fills in email "protected@example.com" and password "password123"', null, { loginPage }); 
    await And('the user submits the login form', null, { loginPage, page }); 
    await Then('the user should be on the dashboard page', null, { dashboardPage }); 
    await When('the user navigates to the dashboard directly', null, { dashboardPage }); 
    await Then('the user should be on the dashboard page', null, { dashboardPage }); 
  });

  test('User is redirected to login when accessing protected routes without auth', async ({ When, Then, And, dashboardPage, page }) => { 
    await When('the user navigates to the dashboard directly', null, { dashboardPage }); 
    await Then('the user should be redirected to the login page', null, { page }); 
    await And('the user should not be authenticated', null, { page }); 
  });

  test('User session persists after page reload', async ({ Given, When, Then, And, dashboardPage, loginPage, page, request }) => { 
    await Given('a user is registered with email "session@example.com" and password "password123"', null, { request }); 
    await When('the user navigates to the login page', null, { loginPage }); 
    await And('the user fills in email "session@example.com" and password "password123"', null, { loginPage }); 
    await And('the user submits the login form', null, { loginPage, page }); 
    await Then('the user should be on the dashboard page', null, { dashboardPage }); 
    await When('the user reloads the page', null, { page }); 
    await Then('the user should still be on the dashboard page', null, { dashboardPage }); 
    await And('the user should still be authenticated', null, { dashboardPage }); 
  });

});

// == technical section ==

test.use({
  $test: [({}, use) => use(test), { scope: 'test', box: true }],
  $uri: [({}, use) => use('tests/e2e/features/auth/auth-flow.feature'), { scope: 'test', box: true }],
  $bddFileData: [({}, use) => use(bddFileData), { scope: "test", box: true }],
});

const bddFileData = [ // bdd-data-start
  {"pwTestLine":6,"pickleLine":6,"tags":[],"steps":[{"pwStepLine":7,"gherkinStepLine":7,"keywordType":"Action","textWithKeyword":"When the user navigates to the register page","stepMatchArguments":[]},{"pwStepLine":8,"gherkinStepLine":8,"keywordType":"Action","textWithKeyword":"And the user fills in name \"Test User\", email \"flowuser@example.com\", password \"password123\", and password confirmation \"password123\"","stepMatchArguments":[{"group":{"start":23,"value":"\"Test User\"","children":[{"start":24,"value":"Test User","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":42,"value":"\"flowuser@example.com\"","children":[{"start":43,"value":"flowuser@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":75,"value":"\"password123\"","children":[{"start":76,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":116,"value":"\"password123\"","children":[{"start":117,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":9,"gherkinStepLine":9,"keywordType":"Action","textWithKeyword":"And the user submits the registration form","stepMatchArguments":[]},{"pwStepLine":10,"gherkinStepLine":10,"keywordType":"Outcome","textWithKeyword":"Then the user should be redirected to the login page","stepMatchArguments":[]},{"pwStepLine":11,"gherkinStepLine":11,"keywordType":"Action","textWithKeyword":"When the user fills in email \"flowuser@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":24,"value":"\"flowuser@example.com\"","children":[{"start":25,"value":"flowuser@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":60,"value":"\"password123\"","children":[{"start":61,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":12,"gherkinStepLine":12,"keywordType":"Action","textWithKeyword":"And the user submits the login form","stepMatchArguments":[]},{"pwStepLine":13,"gherkinStepLine":13,"keywordType":"Outcome","textWithKeyword":"Then the user should be on the dashboard page","stepMatchArguments":[]},{"pwStepLine":14,"gherkinStepLine":14,"keywordType":"Action","textWithKeyword":"When the user logs out","stepMatchArguments":[]},{"pwStepLine":15,"gherkinStepLine":15,"keywordType":"Outcome","textWithKeyword":"Then the user should be redirected to the login page","stepMatchArguments":[]},{"pwStepLine":16,"gherkinStepLine":16,"keywordType":"Outcome","textWithKeyword":"And the user should not be able to access the dashboard","stepMatchArguments":[]}]},
  {"pwTestLine":19,"pickleLine":18,"tags":[],"steps":[{"pwStepLine":20,"gherkinStepLine":19,"keywordType":"Context","textWithKeyword":"Given a user is registered with email \"protected@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":32,"value":"\"protected@example.com\"","children":[{"start":33,"value":"protected@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":69,"value":"\"password123\"","children":[{"start":70,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":21,"gherkinStepLine":20,"keywordType":"Action","textWithKeyword":"When the user navigates to the login page","stepMatchArguments":[]},{"pwStepLine":22,"gherkinStepLine":21,"keywordType":"Action","textWithKeyword":"And the user fills in email \"protected@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":24,"value":"\"protected@example.com\"","children":[{"start":25,"value":"protected@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":61,"value":"\"password123\"","children":[{"start":62,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":23,"gherkinStepLine":22,"keywordType":"Action","textWithKeyword":"And the user submits the login form","stepMatchArguments":[]},{"pwStepLine":24,"gherkinStepLine":23,"keywordType":"Outcome","textWithKeyword":"Then the user should be on the dashboard page","stepMatchArguments":[]},{"pwStepLine":25,"gherkinStepLine":24,"keywordType":"Action","textWithKeyword":"When the user navigates to the dashboard directly","stepMatchArguments":[]},{"pwStepLine":26,"gherkinStepLine":25,"keywordType":"Outcome","textWithKeyword":"Then the user should be on the dashboard page","stepMatchArguments":[]}]},
  {"pwTestLine":29,"pickleLine":27,"tags":[],"steps":[{"pwStepLine":30,"gherkinStepLine":28,"keywordType":"Action","textWithKeyword":"When the user navigates to the dashboard directly","stepMatchArguments":[]},{"pwStepLine":31,"gherkinStepLine":29,"keywordType":"Outcome","textWithKeyword":"Then the user should be redirected to the login page","stepMatchArguments":[]},{"pwStepLine":32,"gherkinStepLine":30,"keywordType":"Outcome","textWithKeyword":"And the user should not be authenticated","stepMatchArguments":[]}]},
  {"pwTestLine":35,"pickleLine":32,"tags":[],"steps":[{"pwStepLine":36,"gherkinStepLine":33,"keywordType":"Context","textWithKeyword":"Given a user is registered with email \"session@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":32,"value":"\"session@example.com\"","children":[{"start":33,"value":"session@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":67,"value":"\"password123\"","children":[{"start":68,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":37,"gherkinStepLine":34,"keywordType":"Action","textWithKeyword":"When the user navigates to the login page","stepMatchArguments":[]},{"pwStepLine":38,"gherkinStepLine":35,"keywordType":"Action","textWithKeyword":"And the user fills in email \"session@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":24,"value":"\"session@example.com\"","children":[{"start":25,"value":"session@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":59,"value":"\"password123\"","children":[{"start":60,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":39,"gherkinStepLine":36,"keywordType":"Action","textWithKeyword":"And the user submits the login form","stepMatchArguments":[]},{"pwStepLine":40,"gherkinStepLine":37,"keywordType":"Outcome","textWithKeyword":"Then the user should be on the dashboard page","stepMatchArguments":[]},{"pwStepLine":41,"gherkinStepLine":38,"keywordType":"Action","textWithKeyword":"When the user reloads the page","stepMatchArguments":[]},{"pwStepLine":42,"gherkinStepLine":39,"keywordType":"Outcome","textWithKeyword":"Then the user should still be on the dashboard page","stepMatchArguments":[]},{"pwStepLine":43,"gherkinStepLine":40,"keywordType":"Outcome","textWithKeyword":"And the user should still be authenticated","stepMatchArguments":[]}]},
]; // bdd-data-end