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


Cypress.Commands.add('verify_feedzy_frontend', (options) => {
    cy.get('div.feedzy-rss-1').should('have.length', 1);

    // feed title
    cy.get('div.feedzy-rss-1 a.rss_title').should('have.length', 1);
    cy.get('div.feedzy-rss-1 a.rss_title').should('contain', 'iTunes Store: Top Songs');

    // no style
    cy.get('ul.feedzy-style1').should('have.length', 0);
    // # of items
    console.log(options);
    cy.get('ul li.rss_item').should('have.length', options.results);

    // a valid image
    cy.get('div.rss_image .fetched').first().invoke('attr', 'style').should('contain', options.thumb_1);
    cy.get('div.rss_image .fetched').last().invoke('attr', 'style').should('contain', options.thumb_2);
    //cy.get('div.rss_image .fetched').first().invoke('attr', 'style').should('contain', 'https://is5-ssl.mzstatic.com/image/thumb/Music123/v4/20/6f/b5/206fb560-6fd5-15f9-0b68-88d309ffc5a6/19UMGIM53909.rgb.jpg/100x100bb.png');
    //cy.get('div.rss_image .fetched').last().invoke('attr', 'style').should('contain', 'https://is4-ssl.mzstatic.com/image/thumb/Music123/v4/0d/13/51/0d1351cc-298c-0c1e-f4e0-3745091b21ec/19UMGIM53914.rgb.jpg/100x100bb.png');

    // title
    cy.get('div.feedzy-rss-1 span.title').first().should('contain', options.post_title_1);
    cy.get('div.feedzy-rss-1 span.title').last().should('contain', options.post_title_2);

    // cy.get('div.feedzy-rss-1 span.title').first().should('contain', 'Taylor Swift');
    // cy.get('div.feedzy-rss-1 span.title').last().should('contain', 'Camila Cabello');

    // meta - there is no class "meta" which is different from style1
    cy.get('div.feedzy-rss-1 div.rss_content').should('have.length', options.results);
    cy.get('div.feedzy-rss-1 div.rss_content').first().should('contain', 'August 16, 2019 at 7:54 am');
    cy.get('div.feedzy-rss-1 div.rss_content').last().should('contain', 'August 16, 2019 at 7:54 am');
    cy.get('div.feedzy-rss-1 div.rss_content').should('contain', 'by');

    // multiple feed meta should not be present 
    cy.get('div.feedzy-rss-1 div.rss_content').first().should('not.contain', '(');
    cy.get('div.feedzy-rss-1 div.rss_content').last().should('not.contain', ')');

    // description
    cy.get('div.feedzy-rss-1 div.rss_content p').should('have.length', options.results);

    // the audio controls
    cy.get('div.feedzy-rss-1 div.rss_content audio').should('have.length', 0);

    // no price button.
    cy.get('button.price').should('have.length', 0);
});
