describe('Test Shortcode for free', function() {
    before(function(){
        // login to WP
        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-sc-0-" + Cypress.moment().unix() + " ";

    it('Create the shortcode', function() {
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        cy.visit('/post-new.php');

        // fill up the form
        cy.get('#title').type( PREFIX + 'shortcode');
        cy.get('#content_ifr').then(function ($iframe) {
            const $body = $iframe.contents().find("body").html( Cypress.env('shortcode') );
        });
        cy.get('#publish').click({force: true});
    });

    it('View the shortcode', function() {
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        cy.visit('/edit.php?post_type=post')

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

        // click to view post
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').first().parent().parent().find('span.view a').click({ force: true });

        // feedzy block
        cy.get('div.feedzy-rss').should('have.length', 1);

        // feed title
        cy.get('div.feedzy-rss a.rss_title').should('have.length', 1);
        cy.get('div.feedzy-rss a.rss_title').should('contain', 'iTunes Store: Top Songs');

        // no style
        cy.get('ul.feedzy-style1').should('have.length', 0);

        // # of items
        cy.get('ul li.rss_item').should('have.length', 10);

        // a valid image
        cy.get('div.rss_image .fetched').first().invoke('attr', 'style').should('contain', 'https://is4-ssl.mzstatic.com/image/thumb/Music123/v4/99/73/63/99736372-7338-45ad-37de-c61bfb765c26/054391945495.jpg/100x100bb.png');
        cy.get('div.rss_image .fetched').last().invoke('attr', 'style').should('contain', 'https://is3-ssl.mzstatic.com/image/thumb/Music123/v4/c6/04/02/c604029f-732b-ba65-425c-45f2cf91151e/4050538505542.jpg/100x100bb.png');

        // title
        cy.get('div.feedzy-rss span.title').first().should('contain', 'Blake Shelton');
        cy.get('div.feedzy-rss span.title').last().should('contain', 'Blanco Brown');

        // meta - there is no class "meta" which is different from style1
        cy.get('div.feedzy-rss div.rss_content').should('have.length', 10);
        cy.get('div.feedzy-rss div.rss_content').first().should('contain', 'August 16, 2019 at 7:54 am');
        cy.get('div.feedzy-rss div.rss_content').last().should('contain', 'August 16, 2019 at 7:54 am');
        cy.get('div.feedzy-rss div.rss_content').should('contain', 'by');

        // description
        cy.get('div.feedzy-rss div.rss_content p').should('have.length', 10);

        // the audio controls
        cy.get('div.feedzy-rss div.rss_content audio').should('have.length', 0);

        // no price button.
        cy.get('button.price').should('have.length', 0);
    });



})
