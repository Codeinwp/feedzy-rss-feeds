// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })


Cypress.Commands.add('verify_feedzy_frontend', (count) => {
    cy.get('div.feedzy-rss').should('have.length', 1);

    // feed title
    cy.get('div.feedzy-rss a.rss_title').should('have.length', 1);
    cy.get('div.feedzy-rss a.rss_title').should('contain', 'iTunes Store: Top Songs');

    // no style
    cy.get('ul.feedzy-style1').should('have.length', 0);

    // # of items
    cy.get('ul li.rss_item').should('have.length', count);

    // a valid image
    cy.get('div.rss_image .fetched').first().invoke('attr', 'style').should('contain', 'https://is3-ssl.mzstatic.com/image/thumb/Music123/v4/ba/e2/2a/bae22a5e-c878-da64-0ecc-4a3584a1a139/190295411411.jpg/100x100bb.png');
    cy.get('div.rss_image .fetched').last().invoke('attr', 'style').should('contain', 'https://is3-ssl.mzstatic.com/image/thumb/Music123/v4/c6/04/02/c604029f-732b-ba65-425c-45f2cf91151e/4050538505542.jpg/100x100bb.png');

    // title
    cy.get('div.feedzy-rss span.title').first().should('contain', 'Ed Sheeran');
    cy.get('div.feedzy-rss span.title').last().should('contain', 'Blanco Brown');

    // meta - there is no class "meta" which is different from style1
    cy.get('div.feedzy-rss div.rss_content').should('have.length', count);
    cy.get('div.feedzy-rss div.rss_content').first().should('contain', 'August 16, 2019 at 7:54 am');
    cy.get('div.feedzy-rss div.rss_content').last().should('contain', 'August 16, 2019 at 7:54 am');
    cy.get('div.feedzy-rss div.rss_content').should('contain', 'by');

    // multiple feed meta should not be present 
    cy.get('div.feedzy-rss div.rss_content').first().should('not.contain', '(');
    cy.get('div.feedzy-rss div.rss_content').last().should('not.contain', ')');

    // description
    cy.get('div.feedzy-rss div.rss_content p').should('have.length', count);

    // the audio controls
    cy.get('div.feedzy-rss div.rss_content audio').should('have.length', 0);

    // no price button.
    cy.get('button.price').should('have.length', 0);
});
