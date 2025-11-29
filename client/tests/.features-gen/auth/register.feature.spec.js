// Generated from: tests/e2e/features/auth/register.feature
import { test } from "playwright-bdd";

test.describe('User Registration', () => {

  test('User can register successfully with valid data', async ({ When, Then, And, page }) => { 
    await When('the user navigates to the register page', null, { page }); 
    await And('the user fills in name "Test User", email "newuser@example.com", password "password123", and password confirmation "password123"', null, { page }); 
    await And('the user submits the registration form', null, { page }); 
    await Then('the user should be redirected to the login page', null, { page }); 
    await And('the user should be able to login with email "newuser@example.com" and password "password123"', null, { page }); 
  });

  test('User cannot register with duplicate email', async ({ Given, When, Then, And, page, request }) => { 
    await Given('a user is registered with email "existing@example.com" and password "password123"', null, { request }); 
    await When('the user navigates to the register page', null, { page }); 
    await And('the user fills in name "Another User", email "existing@example.com", password "password123", and password confirmation "password123"', null, { page }); 
    await And('the user submits the registration form', null, { page }); 
    await Then('the user should see an error message about duplicate email', null, { page }); 
    await And('the user should remain on the register page', null, { page }); 
  });

  test('User is redirected to login after successful registration', async ({ When, Then, And, page }) => { 
    await When('the user navigates to the register page', null, { page }); 
    await And('the user fills in name "Test User", email "newuser2@example.com", password "password123", and password confirmation "password123"', null, { page }); 
    await And('the user submits the registration form', null, { page }); 
    await Then('the user should be on the login page', null, { page }); 
  });

});

// == technical section ==

test.use({
  $test: [({}, use) => use(test), { scope: 'test', box: true }],
  $uri: [({}, use) => use('tests/e2e/features/auth/register.feature'), { scope: 'test', box: true }],
  $bddFileData: [({}, use) => use(bddFileData), { scope: "test", box: true }],
});

const bddFileData = [ // bdd-data-start
  {"pwTestLine":6,"pickleLine":6,"tags":[],"steps":[{"pwStepLine":7,"gherkinStepLine":7,"keywordType":"Action","textWithKeyword":"When the user navigates to the register page","stepMatchArguments":[]},{"pwStepLine":8,"gherkinStepLine":8,"keywordType":"Action","textWithKeyword":"And the user fills in name \"Test User\", email \"newuser@example.com\", password \"password123\", and password confirmation \"password123\"","stepMatchArguments":[{"group":{"start":23,"value":"\"Test User\"","children":[{"start":24,"value":"Test User","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":42,"value":"\"newuser@example.com\"","children":[{"start":43,"value":"newuser@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":74,"value":"\"password123\"","children":[{"start":75,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":115,"value":"\"password123\"","children":[{"start":116,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":9,"gherkinStepLine":9,"keywordType":"Action","textWithKeyword":"And the user submits the registration form","stepMatchArguments":[]},{"pwStepLine":10,"gherkinStepLine":10,"keywordType":"Outcome","textWithKeyword":"Then the user should be redirected to the login page","stepMatchArguments":[]},{"pwStepLine":11,"gherkinStepLine":11,"keywordType":"Outcome","textWithKeyword":"And the user should be able to login with email \"newuser@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":44,"value":"\"newuser@example.com\"","children":[{"start":45,"value":"newuser@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":79,"value":"\"password123\"","children":[{"start":80,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]}]},
  {"pwTestLine":14,"pickleLine":13,"tags":[],"steps":[{"pwStepLine":15,"gherkinStepLine":14,"keywordType":"Context","textWithKeyword":"Given a user is registered with email \"existing@example.com\" and password \"password123\"","stepMatchArguments":[{"group":{"start":32,"value":"\"existing@example.com\"","children":[{"start":33,"value":"existing@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":68,"value":"\"password123\"","children":[{"start":69,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":16,"gherkinStepLine":15,"keywordType":"Action","textWithKeyword":"When the user navigates to the register page","stepMatchArguments":[]},{"pwStepLine":17,"gherkinStepLine":16,"keywordType":"Action","textWithKeyword":"And the user fills in name \"Another User\", email \"existing@example.com\", password \"password123\", and password confirmation \"password123\"","stepMatchArguments":[{"group":{"start":23,"value":"\"Another User\"","children":[{"start":24,"value":"Another User","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":45,"value":"\"existing@example.com\"","children":[{"start":46,"value":"existing@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":78,"value":"\"password123\"","children":[{"start":79,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":119,"value":"\"password123\"","children":[{"start":120,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":18,"gherkinStepLine":17,"keywordType":"Action","textWithKeyword":"And the user submits the registration form","stepMatchArguments":[]},{"pwStepLine":19,"gherkinStepLine":18,"keywordType":"Outcome","textWithKeyword":"Then the user should see an error message about duplicate email","stepMatchArguments":[]},{"pwStepLine":20,"gherkinStepLine":19,"keywordType":"Outcome","textWithKeyword":"And the user should remain on the register page","stepMatchArguments":[]}]},
  {"pwTestLine":23,"pickleLine":21,"tags":[],"steps":[{"pwStepLine":24,"gherkinStepLine":22,"keywordType":"Action","textWithKeyword":"When the user navigates to the register page","stepMatchArguments":[]},{"pwStepLine":25,"gherkinStepLine":23,"keywordType":"Action","textWithKeyword":"And the user fills in name \"Test User\", email \"newuser2@example.com\", password \"password123\", and password confirmation \"password123\"","stepMatchArguments":[{"group":{"start":23,"value":"\"Test User\"","children":[{"start":24,"value":"Test User","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":42,"value":"\"newuser2@example.com\"","children":[{"start":43,"value":"newuser2@example.com","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":75,"value":"\"password123\"","children":[{"start":76,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"},{"group":{"start":116,"value":"\"password123\"","children":[{"start":117,"value":"password123","children":[{"children":[]}]},{"children":[{"children":[]}]}]},"parameterTypeName":"string"}]},{"pwStepLine":26,"gherkinStepLine":24,"keywordType":"Action","textWithKeyword":"And the user submits the registration form","stepMatchArguments":[]},{"pwStepLine":27,"gherkinStepLine":25,"keywordType":"Outcome","textWithKeyword":"Then the user should be on the login page","stepMatchArguments":[]}]},
]; // bdd-data-end