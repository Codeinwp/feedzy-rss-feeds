describe('Test Free - gutenberg', function() {
    before(function(){
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        // login to WP
        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-sc-1-" + Cypress.moment().unix() + " ";

    it('Insert a block', function() {
        cy.visit('/post-new.php');

        // get rid of that irritating popup
        cy.get('.nux-dot-tip__disable').click();


        // insert a feedzy block
        cy.get('div.edit-post-header-toolbar .block-editor-inserter button').click();
        cy.get('.components-popover__content').then(function ($popup) {
            cy.wrap($popup).find('.block-editor-inserter__search').type('feedzy');
            cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li').should('have.length', 1);
            cy.wrap($popup).find('.block-editor-inserter__results ul.block-editor-block-types-list li button').click();
        });

        // see the block has the correct elements.
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-button.is-default').should('have.length', 1);

        cy.get('textarea.editor-post-title__input').type(PREFIX);
        // insert a feed
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').type( Cypress.env("url") );
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-button.is-default').click().then( () => {
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss div.rss_header').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', 5);

            // change settings
            // clear does not work on number fields. Targetting slider and triggering 'change' doesn't work either. So we have to mix n match.

            // max = 3
            cy.get('div.edit-post-sidebar div.components-base-control.feedzy-max input.components-range-control__number').invoke('val', '').clear().type('11').blur();
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', 11);

            // offset = 1
            cy.get('div.edit-post-sidebar div.components-base-control.feedzy-offset input.components-range-control__number').invoke('val', '').clear().type('1').blur();
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', 10);

            cy.get('button.editor-post-publish-button').click();
        });

    });


    it('View the post', function() {
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

        // # of items
        cy.get('ul li.rss_item').should('have.length', 10);

        // title
        cy.get('div.feedzy-rss span.title').first().should('contain', 'Ed Sheeran');
        cy.get('div.feedzy-rss span.title').last().should('contain', 'Normani');

    });

})
