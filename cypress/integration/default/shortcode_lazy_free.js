describe('Test Lazy Shortcode for free', function() {
    before(function(){

        // login to WP
        cy.visit('wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-scl-0-" + Cypress.moment().unix() + " ";

    it('Create the shortcode', function() {
        cy.visit('/wp-admin/post-new.php');

        // fill up the form
        cy.get('#title').type( PREFIX + 'shortcode-lazy');
        cy.get('#content_ifr').then(function ($iframe) {
            const $body = $iframe.contents().find("body").html( Cypress.env('shortcode').lazy );
        });
        cy.get('#publish').click({force: true});
    });

    it('View the shortcode', function() {
        cy.visit('/wp-admin/edit.php?post_type=post')

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + 'shortcode-lazy")').should('have.length', 1);

        // click to view post
        cy.get('tr td a.row-title:contains("' + PREFIX + 'shortcode-lazy")').first().parent().parent().find('span.view a').click({ force: true });

        // feedzy block
        cy.get('div.feedzy-rss').should('have.length', 0);

        // loading div should be present
        cy.get('div.feedzy-lazy').should('have.length', 1);
        cy.get('div.feedzy-lazy.loading').should('have.length', 0);
        cy.get('div.feedzy-lazy').should('contain', 'Loading');

        // wait some more time.
        cy.wait(5000);

        // loading class should vanish
        cy.get('div.feedzy-lazy').should('have.length', 1);
        cy.get('div.feedzy-lazy.loading').should('have.length', 0);

        cy.verify_feedzy_frontend(Cypress.env('shortcode').single_results);

    });


})
