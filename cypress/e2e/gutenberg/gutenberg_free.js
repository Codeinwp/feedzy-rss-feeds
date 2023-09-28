describe('Test Free - gutenberg', function() {
    beforeEach(function(){
        // login to WP
        cy.visit('wp-login.php');
        cy.wait( 1000 );
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-scg-0-" + Cypress.dayjs().unix();

    it('Insert a block', function() {

        cy.visit('/wp-admin/post-new.php');

        // get rid of that irritating popup
        cy.get('.edit-post-welcome-guide .components-modal__header button').click();

        cy.get('.edit-post-visual-editor__post-title-wrapper .editor-post-title__input').type(PREFIX);

        // insert a feedzy block
        cy.get('div.edit-post-header__toolbar button.edit-post-header-toolbar__inserter-toggle').click({force:true});
        cy.get('.edit-post-editor__inserter-panel-content').then(function ($popup) {
            cy.wrap($popup).find('.components-search-control__input').type('feedzy');
            cy.wrap($popup).find('.block-editor-block-types-list .editor-block-list-item-feedzy-rss-feeds-feedzy-block').should('have.length', 1);
            cy.wrap($popup).find('.block-editor-block-types-list .editor-block-list-item-feedzy-rss-feeds-feedzy-block').click({force:true});
        });

        // see the block has the correct elements.
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.components-button.is-primary').should('have.length', 1);

        var gutenberg = Cypress.env("gutenberg");
        // insert a feed
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').type( gutenberg.url );
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.components-button.is-primary').click().then( () => {
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss div.rss_header').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default').should('have.length', 1);
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', 5);

            cy.get( '.interface-pinned-items button.components-button.has-icon' ).click()

            cy.get( 'div[data-type="feedzy-rss-feeds/feedzy-block"]' ).focus();

            // change settings
            // clear does not work on number fields. Targetting slider and triggering 'change' doesn't work either. So we have to mix n match.

            cy.get('div.edit-post-sidebar div.components-base-control.feedzy-max input.components-input-control__input').invoke('val', '').clear({force:true}).type(gutenberg.max, {force:true}).blur({force:true});
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', gutenberg.max);

            cy.get('.fz-section-header-panel .header-tab:eq(2)').click({force:true}).then( () => {
                cy.get('.feedzy-advanced-options .components-panel__body-toggle:eq(0)').click({force:true}).then( () => {
                    cy.get('div.edit-post-sidebar div.components-base-control.feedzy-offset input.components-input-control__input').invoke('val', '').clear({force:true}).type(gutenberg.offset, {force:true}).blur({force:true});
                });
            });
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', parseInt(gutenberg.max) - parseInt(gutenberg.offset));

            // item options
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-meta input.components-text-control__input').type(gutenberg.meta, {force: true});
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-multiple-meta input.components-text-control__input').type(gutenberg.multiple_meta, {force: true});
            /* for pro 
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-include input.components-text-control__input').type(gutenberg.include);
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-ban input.components-text-control__input').type(gutenberg.ban);
            */
            cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', gutenberg.results);

            // image options
            cy.get('.fz-section-header-panel .header-tab:eq(1)').click({force:true}).then( () => {
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options button.components-button.components-panel__body-toggle').click().then( () => {
                    cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options .feedzy-thumb .components-select-control__input').select(gutenberg.thumb);
                });
            });

            cy.get('button.editor-post-publish-panel__toggle').click().then( () => {
                cy.get('button.editor-post-publish-button').click();
            });
        });

        cy.wait(2000);

    });

    it('Verify inserted block', function() {

        cy.visit('/wp-admin/edit.php?post_type=post');

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

        // edit post
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').click();

        // see the block has the correct elements.
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').should('have.length', 0);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-primary.is-large').should('have.length', 0);

        cy.get('.edit-post-visual-editor__post-title-wrapper .editor-post-title__input').should('contain', PREFIX);

        var gutenberg = Cypress.env("gutenberg");
        // check inserted feed
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').focus();
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss div.rss_header').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default').should('have.length', 1);
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li').should('have.length', gutenberg.results.toString());

        cy.get('div.edit-post-sidebar div.components-base-control.feedzy-max input.components-input-control__input').should('have.value', gutenberg.max.toString());
        cy.get( '.fz-section-header-panel .header-tab:eq(2)').click({force:true}).then( () => {
            cy.get( '.feedzy-advanced-options .components-panel__body-toggle:eq(0)').click({force:true}).then( () => {
                cy.get('div.edit-post-sidebar div.components-base-control.feedzy-offset input.components-input-control__input').should('have.value', gutenberg.offset.toString());
                // item options
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-meta input.components-text-control__input').should('have.value', gutenberg.meta);
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-multiple-meta input.components-text-control__input').should('have.value', gutenberg.multiple_meta);
                /* for pro 
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-include input.components-text-control__input').should('have.value', gutenberg.include);
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-advanced-options div.components-base-control.feedzy-ban input.components-text-control__input').should('have.value', gutenberg.ban);
                */
            });
        });

        // image options
        cy.get('.fz-section-header-panel .header-tab:eq(1)').click({force:true}).then( () => {
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options button.components-panel__body-toggle').click().then( () => {
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-image-options .feedzy-thumb .components-select-control__input').invoke('prop', 'selectedIndex').should('equal', 1);
            });
        });

        // we want to do this so that the next test succeeds - otherwise it will throw up an alert box and stop the page
        cy.get('button.editor-post-publish-button').click();

    });

    it('View the post', function() {
        cy.visit('/wp-admin/edit.php?post_type=post')

        var gutenberg = Cypress.env("gutenberg");
        gutenberg.thumb_1 = 'https://is3-ssl.mzstatic.com/image/thumb/Music123/v4/ba/e2/2a/bae22a5e-c878-da64-0ecc-4a3584a1a139/190295411411.jpg/100x100bb.png';
        gutenberg.thumb_2 = 'https://is3-ssl.mzstatic.com/image/thumb/Music123/v4/c6/04/02/c604029f-732b-ba65-425c-45f2cf91151e/4050538505542.jpg/100x100bb.png';
        gutenberg.post_title_1 = 'Justin Bieber';
        gutenberg.post_title_2 = 'Blanco Brown';
        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

        // click to view post
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').first().parent().parent().find('span.view a').click({ force: true });

        cy.verify_feedzy_frontend(gutenberg);

    });

    it('Modify inserted block and make it LAZY', function() {

        cy.visit('/wp-admin/edit.php?post_type=post');

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

        // edit post
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').click();

        var gutenberg = Cypress.env("gutenberg");
        // check inserted feed
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').focus();

        // switch on lazy
        cy.get( '.fz-section-header-panel .header-tab:eq(2)').click({force:true}).then( () => {
            cy.get( '.feedzy-advanced-options .components-panel__body-toggle:eq(0)').click({force:true}).then( () => {
                cy.get('div.edit-post-sidebar div.components-base-control.feedzy-lazy input').click();
            });
        });

        // we want to do this so that the next test succeeds - otherwise it will throw up an alert box and stop the page
        cy.get('button.editor-post-publish-button').click();

        cy.visit('/wp-admin/edit.php?post_type=post')

    });

    it('View the LAZY post', function() {
        cy.visit('/wp-admin/edit.php?post_type=post')

        var gutenberg = Cypress.env("gutenberg");
        gutenberg.results = 3;
        gutenberg.thumb_1 = 'https://is5-ssl.mzstatic.com/image/thumb/Music123/v4/20/6f/b5/206fb560-6fd5-15f9-0b68-88d309ffc5a6/19UMGIM53909.rgb.jpg/100x100bb.png';
        gutenberg.thumb_2 = 'https://is4-ssl.mzstatic.com/image/thumb/Music123/v4/0d/13/51/0d1351cc-298c-0c1e-f4e0-3745091b21ec/19UMGIM53914.rgb.jpg/100x100bb.png';
        gutenberg.post_title_1 = 'Taylor Swift';
        gutenberg.post_title_2 = 'Camila Cabello';

        // should have 1 post.
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

        // click to view post
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').first().parent().parent().find('span.view a').click({ force: true });

        // feedzy block
        cy.get('div.feedzy-rss').should('have.length', 0);

        // loading div should be present
        cy.get('div.feedzy-lazy').should('have.length', 1);
        cy.get('div.feedzy-lazy.loading').should('have.length', 0);
        // cy.get('div.feedzy-lazy').should('contain', 'Loading');

        // wait some more time.
        cy.wait(5000);

        // loading class should vanish
        cy.get('div.feedzy-lazy').should('have.length', 1);
        cy.get('div.feedzy-lazy.loading').should('have.length', 0);

        cy.verify_feedzy_frontend(gutenberg);
    });

})
