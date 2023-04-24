// ***********************************************************
// This example support/index.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************


// Alternatively you can use CommonJS syntax:
require('./commands')
const dayjs = require('dayjs')

Cypress.dayjs = dayjs
// allow WP session to remain open during multiple cy.visit() invocations.
Cypress.Cookies.defaults({
    preserve: /wordpress_.*/
})
Cypress.on('uncaught:exception', (err, runnable) => {
  // returning false here prevents Cypress from failing the test
  return false
})