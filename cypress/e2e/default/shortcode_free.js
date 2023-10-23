describe('Test Shortcode for free', function() {
    beforeEach(function(){
        // login to WP
        cy.visit('wp-login.php');
        cy.wait( 1000 );
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-sc-0-" + Cypress.dayjs().unix() + " ";

    it('Create the shortcode', function() {
        cy.visit('/wp-admin/post-new.php');

        // fill up the form
        cy.get('#title').type( PREFIX + 'shortcode-single');
        cy.get('#content_ifr').then(function ($iframe) {
            const $body = $iframe.contents().find("body").html( Cypress.env('shortcode').single );
        });
        cy.get('#publish').click({force: true});
    });

    it('View the shortcode', function() {
        cy.visit('/wp-admin/edit.php?post_type=post')

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + 'shortcode-single")').should('have.length', 1);

        // click to view post
        cy.get('tr td a.row-title:contains("' + PREFIX + 'shortcode-single")').first().parent().parent().find('span.view a').click({ force: true });

        cy.verify_feedzy_frontend(Cypress.env('shortcode').single_results);
    });

    it('Create the shortcode (multiple feeds)', function() {
        cy.visit('/wp-admin/post-new.php');

        // fill up the form
        cy.get('#title').type( PREFIX + 'shortcode-multiple');
        cy.get('#content_ifr').then(function ($iframe) {
            const $body = $iframe.contents().find("body").html( Cypress.env('shortcode').multiple );
        });
        cy.get('#publish').click({force: true});
    });

    it('View the shortcode (multiple feeds)', function() {
        cy.visit('/wp-admin/edit.php?post_type=post')

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + 'shortcode-multiple")').should('have.length', 1);

        // click to view post
        cy.get('tr td a.row-title:contains("' + PREFIX + 'shortcode-multiple")').first().parent().parent().find('span.view a').click({ force: true });

        // feedzy block
        cy.get('div.feedzy-rss').should('have.length', 1);

        // feed title
        cy.get('div.feedzy-rss a.rss_title').should('have.length', 0);

        // no style
        cy.get('ul.feedzy-style1').should('have.length', 0);

        // # of items
        cy.get('ul li.rss_item').should('have.length', Cypress.env('shortcode').multiple_results);

        // title
        cy.get('div.feedzy-rss span.title').first().should('contain', '10+ Best Themes');
        cy.get('div.feedzy-rss span.title').last().should('contain', 'Bluehost vs Hostinger');

        // meta - there is no class "meta" which is different from style1
        cy.get('div.feedzy-rss div.rss_content').should('have.length', Cypress.env('shortcode').multiple_results);
        cy.get('div.feedzy-rss div.rss_content').first().should('contain', 'December 27, 2019 at 12:26 pm');
        cy.get('div.feedzy-rss div.rss_content').last().should('contain', 'December 17, 2019 at 8:36 am');
        cy.get('div.feedzy-rss div.rss_content').should('contain', 'by');

        // multiple feed meta
        cy.get('div.feedzy-rss div.rss_content').first().should('contain', 'Priya');
        cy.get('div.feedzy-rss div.rss_content').first().should('contain', 'CodeinWP');
        cy.get('div.feedzy-rss div.rss_content').last().should('contain', 'Megan Jones');
        cy.get('div.feedzy-rss div.rss_content').last().should('contain', 'ThemeIsle Blog');

        // description
        cy.get('div.feedzy-rss div.rss_content p').should('have.length', Cypress.env('shortcode').multiple_results);

        // the audio controls
        cy.get('div.feedzy-rss div.rss_content audio').should('have.length', 0);

        // no price button.
        cy.get('button.price').should('have.length', 0);
    });

})
