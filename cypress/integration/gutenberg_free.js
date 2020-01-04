describe('Test Free - gutenberg', function() {
    before(function(){
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        // login to WP
        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-sc-1-" + Cypress.moment().unix();

    it('Insert a block', function() {
        cy.visit('/post-new.php');

        // get rid of that irritating popup
        cy.get('.nux-dot-tip__disable').click();

        cy.get('textarea.editor-post-title__input').type(PREFIX);

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


        var gutenberg = Cypress.env("gutenberg");
        // insert a feed
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').type( gutenberg.url );
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-button.is-default').click().then( () => {
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss div.rss_header').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', 5);

            // change settings
            // clear does not work on number fields. Targetting slider and triggering 'change' doesn't work either. So we have to mix n match.

            cy.get('div.edit-post-sidebar div.components-base-control.feedzy-max input.components-range-control__number').invoke('val', '').clear().type(gutenberg.max).blur();
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', gutenberg.max);

            cy.get('div.edit-post-sidebar div.components-base-control.feedzy-offset input.components-range-control__number').invoke('val', '').clear().type(gutenberg.offset).blur();
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', parseInt(gutenberg.max) - parseInt(gutenberg.offset));

            // item options
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options button.components-panel__body-toggle').click().then( () => {
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-meta input.components-text-control__input').type(gutenberg.meta, {force: true});
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-multiple-meta input.components-text-control__input').type(gutenberg.multiple_meta, {force: true});

                /* for pro 
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-include input.components-text-control__input').type(gutenberg.include);
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-ban input.components-text-control__input').type(gutenberg.ban);
                */
                cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', gutenberg.results);
            });

            // image options
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options button.components-panel__body-toggle').click().then( () => {
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options div.components-base-control.feedzy-thumb select.components-select-control__input').select(gutenberg.thumb);
            });

            cy.get('button.editor-post-publish-panel__toggle').click().then( () => {
                cy.get('button.editor-post-publish-button').click();
            });
        });

        cy.wait(2000);

    });

    it('Verify inserted block', function() {
        cy.visit('/edit.php?post_type=post')

        var gutenberg = Cypress.env("gutenberg");

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

        // edit post
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').click();

        // see the block has the correct elements.
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').should('have.length', 0);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-button.is-default').should('have.length', 0);

        cy.get('textarea.editor-post-title__input').should('contain', PREFIX);

        var gutenberg = Cypress.env("gutenberg");
        // check inserted feed
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').focus();
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss div.rss_header').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', gutenberg.results.toString());

        cy.get('div.edit-post-sidebar div.components-base-control.feedzy-max input.components-range-control__number').should('have.value', gutenberg.max.toString());
        cy.get('div.edit-post-sidebar div.components-base-control.feedzy-offset input.components-range-control__number').should('have.value', gutenberg.offset.toString());

        // item options
        cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options button.components-panel__body-toggle').click().then( () => {
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-meta input.components-text-control__input').should('have.value', gutenberg.meta);
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-multiple-meta input.components-text-control__input').should('have.value', gutenberg.multiple_meta);
            /* for pro 
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-include input.components-text-control__input').should('have.value', gutenberg.include);
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-ban input.components-text-control__input').should('have.value', gutenberg.ban);
            */
        });

        // image options
        cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options button.components-panel__body-toggle').click().then( () => {
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options div.components-base-control.feedzy-thumb select.components-select-control__input').invoke('prop', 'selectedIndex').should('equal', 1);
        });

        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').blur();

    });

    it('View the shortcode', function() {
        cy.visit('/edit.php?post_type=post')

        var gutenberg = Cypress.env("gutenberg");

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
        cy.get('ul li.rss_item').should('have.length', gutenberg.results);

        // a valid image
        cy.get('div.rss_image .fetched').first().invoke('attr', 'style').should('contain', 'https://is3-ssl.mzstatic.com/image/thumb/Music123/v4/ba/e2/2a/bae22a5e-c878-da64-0ecc-4a3584a1a139/190295411411.jpg/100x100bb.png');
        cy.get('div.rss_image .fetched').last().invoke('attr', 'style').should('contain', 'https://is3-ssl.mzstatic.com/image/thumb/Music123/v4/c6/04/02/c604029f-732b-ba65-425c-45f2cf91151e/4050538505542.jpg/100x100bb.png');

        // title
        cy.get('div.feedzy-rss span.title').first().should('contain', 'Ed Sheeran');
        cy.get('div.feedzy-rss span.title').last().should('contain', 'Blanco Brown');

        // meta - there is no class "meta" which is different from style1
        cy.get('div.feedzy-rss div.rss_content').should('have.length', gutenberg.results);
        cy.get('div.feedzy-rss div.rss_content').first().should('contain', 'August 16, 2019 at 7:54 am');
        cy.get('div.feedzy-rss div.rss_content').last().should('contain', 'August 16, 2019 at 7:54 am');
        cy.get('div.feedzy-rss div.rss_content').should('contain', 'by');

        // multiple feed meta should not be present 
        cy.get('div.feedzy-rss div.rss_content').first().should('not.contain', '(');
        cy.get('div.feedzy-rss div.rss_content').last().should('not.contain', ')');

        // description
        cy.get('div.feedzy-rss div.rss_content p').should('have.length', gutenberg.results);

        // the audio controls
        cy.get('div.feedzy-rss div.rss_content audio').should('have.length', 0);

        // no price button.
        cy.get('button.price').should('have.length', 0);
    });


})
