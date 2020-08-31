describe('Test Free - Import Feed', function() {
    before(function(){
        // login to WP
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear().type( Cypress.env('login') );
        cy.get('#user_pass').clear().type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-1 ";
    const feed = Cypress.env('import-feed');

    it.skip('Temporary test', function() {
        // empty.
    });

    it('Check Settings', function() {
        cy.visit('/admin.php?page=feedzy-settings');

        const settings = Cypress.env('settings');
        cy.get('.nav-tab').should('have.length', settings.tabs);
    })

    it('Creates a new import', function() {
        cy.visit('/post-new.php?post_type=feedzy_imports');

        // fill up the form
        cy.get('#title').clear().type( feed.url );
        cy.get('[name="feedzy_meta_data[source]"]').clear().type( feed.url );

        // locked for pro?
        cy.get('.only-pro').should('have.length', feed.locked);
        cy.get('[name="feedzy_meta_data[inc_key]"]').should('not.be.visible');
        cy.get('[name="feedzy_meta_data[exc_key]"]').should('not.be.visible');
        cy.get('[name="feedzy_meta_data[import_feed_delete_days]"]').should('not.be.visible');

        // because we cannot use chosen, we use the HTML element by forcing it to show
        cy.get('#feedzy_item_limit').invoke('show');
        cy.get('#feedzy_item_limit option').should('have.length', 2);
        cy.get('#feedzy_item_limit').select(feed.items);

        // feed item author for admin and user - DO NOT MOVE IT FROM HERE, OTHERWISE IT WILL FAIL.
        cy.get('[name="feedzy_meta_data[import_link_author_admin]"]').should('not.be.visible');
        cy.get('[name="feedzy_meta_data[import_link_author_public]"]').should('not.be.visible');

        cy.get('#feedzy_post_terms').invoke('show').then( () => {
            cy.get('#feedzy_post_terms').select(feed.taxonomy, {force:true});
        });

        cy.get('[name="feedzy_meta_data[import_post_title]"]').scrollIntoView().clear().type( PREFIX + feed.title, {force:true} );
        cy.get('[name="feedzy_meta_data[import_post_content]"]').scrollIntoView().clear().type( PREFIX + feed.fullcontent.content + feed.content, {force:true} );

        // image from URL
        cy.get('[name="feedzy_meta_data[import_post_featured_img]"]').scrollIntoView().clear().type( feed.image.url, {force:true} );

        // check disallowd magic tags
        const tags = feed.tags.disallowed;
        cy.get('a.dropdown-item').each(function(anchor){
            cy.wrap(tags.free).each(function(disallowed){
                cy.wrap(anchor).invoke('attr', 'data-field-tag').should('not.contain', disallowed);
            });
        });

        // check mandatory magic tags
        const mandatory = feed.tags.mandatory;
        var found_tags = [];
        cy.wrap(mandatory.free).each(function(reqd){
            cy.get('a.dropdown-item', {force: true}).each(function(element){
                if(reqd === element.attr('data-field-tag')){
                    found_tags.push(reqd);
                }
            });
        }).then((dumb) => {
            expect(found_tags).to.include.members(mandatory.free);
        });

        cy.get('button[type="submit"][name="save"]').scrollIntoView().click({force:true});

        // check if the import has been setup
        cy.url().should('include', 'edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').should('not.be.checked');
        cy.get('tr:nth-of-type(1) .feedzy-run-now').should('not.be.visible');
    })

    it('Verify the new import and activate', function() {
        cy.visit('/edit.php?post_type=feedzy_imports');

        cy.get('tr:nth-of-type(1) .row-title').click();
        cy.get('#title').should('have.value', feed.url);
        cy.get('[name="feedzy_meta_data[source]"]').should('have.value', feed.url);

        // because we cannot use chosen, we use the HTML element by forcing it to show
        cy.get('#feedzy_item_limit').invoke('show');
        cy.get('#feedzy_item_limit').should('have.value', feed.items);

        cy.get('#feedzy_post_terms').invoke('show').then( () => {
            cy.get('#feedzy_post_terms option:selected').should('have.length', feed.taxonomy.length);
        });

        cy.get('[name="feedzy_meta_data[import_post_title]"]').should('have.value', PREFIX + feed.title);
        cy.get('[name="feedzy_meta_data[import_post_content]"]').should('have.value', PREFIX + feed.fullcontent.content + feed.content);

        // image from URL
        cy.get('[name="feedzy_meta_data[import_post_featured_img]"]').should('have.value', feed.image.url);

        // publish
        cy.get('button[type="submit"][name="publish"]').scrollIntoView().click({force:true});
        cy.url().should('include', 'edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').should('be.checked');
        cy.get('tr:nth-of-type(1) .feedzy-run-now').should('be.visible');
    })

    it('Toggle the new import', function() {
        cy.visit('/edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').uncheck({force:true});

        cy.visit('/edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').should('not.be.checked');

        cy.get('tr:nth-of-type(1) .feedzy-toggle').check({force:true});
        cy.visit('/edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').should('be.checked');
    })

    it('Run the new import', function() {
        cy.visit('/edit.php?post_type=feedzy_imports')

        // run import
        cy.get('tr:nth-of-type(1) .feedzy-run-now').should('be.visible');
        cy.get('tr:nth-of-type(1) .feedzy-run-now').click();
        cy.wait(10 * parseInt(feed.wait));
        cy.get('tr:nth-of-type(1) .feedzy-error-critical').invoke('html').should('include', 'Successfully run');
        cy.get('tr:nth-of-type(1) .feedzy-error-critical').invoke('html').should('include', feed.items + ' items imported.');

        cy.visit('/edit.php?post_type=feedzy_imports')

        // check last run status column has all the data.
        cy.get('tr:nth-of-type(1) td.feedzy-last_run').invoke('html').then( ($html) => {
            cy.wrap($html).should('include', 'Imported ' + feed.items + ' item(s)');
            cy.wrap($html).should('include', 'hours');
            cy.wrap($html).should('include', 'minutes ago');
            cy.wrap($html).should('include', 'Items imported across runs: <b>' + feed.items + '</b>');
            cy.wrap($html).should('include', 'Total items found:');
            cy.wrap($html).should('include', feed.items + '</a>');

            // no duplicates.
            cy.wrap($html).should('not.include', 'Duplicates found:');
        });

    })

    it('Run the new import again', function() {
        cy.visit('/edit.php?post_type=feedzy_imports')

        // run import
        cy.get('tr:nth-of-type(1) .feedzy-run-now').should('be.visible');
        cy.get('tr:nth-of-type(1) .feedzy-run-now').click();
        cy.wait(2 * parseInt(feed.wait));
        cy.get('tr:nth-of-type(1) .feedzy-error-critical').invoke('html').should('include', 'Nothing imported');

        cy.visit('/edit.php?post_type=feedzy_imports')

        // check last run status column has all the data.
        cy.get('tr:nth-of-type(1) td.feedzy-last_run').invoke('html').then( ($html) => {
            cy.wrap($html).should('include', 'Imported 0 item(s)');
            cy.wrap($html).should('include', 'hours');
            cy.wrap($html).should('include', 'minutes ago');
            cy.wrap($html).should('include', 'Items imported across runs: <b>' + feed.items + '</b>');
            cy.wrap($html).should('include', 'Total items found:');
            cy.wrap($html).should('include', feed.items + '</a>');

            // duplicates found.
            cy.wrap($html).should('include', 'Duplicates found:');
        });

    })

    it('Verifies the new imported items', function() {
        cy.visit('/edit.php?post_type=post')

        // should have N posts.
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', feed.items);

        // should have item_custom_ in each post title
        cy.get('tr td a.row-title:contains("' + PREFIX + '"):contains("item_custom_")').should('have.length', feed.items);

        // should have categories and tags
        cy.get('tr td.categories:contains("' + PREFIX.trim() + '")').should('have.length', feed.items);
        cy.get('tr td.tags:contains("' + PREFIX.trim() + '")').should('have.length', feed.items);

        // all authors should be wordpress
        cy.get('tr td.author:contains("wordpress")').should('have.length.of.at.least', feed.items);

        // click to view post
        cy.get('tr td a.row-title:contains("' + PREFIX + '")').first().parent().parent().find('span.view a').click({ force: true });

        cy.wait(feed.wait);

        cy.get('body:contains("' + PREFIX + '")').should('have.length', 1);

        // check categories
        cy.get('body:contains("[#item_categories]")').should('have.length', 0);
        cy.get('body:contains("start:")').should('have.length', 1);
        cy.get('body:contains(":end")').should('have.length', 1);
        cy.get('body:contains("Drugs (Pharmaceuticals)")').should('have.length', 1);
        cy.get('body:contains("United States Politics and Government")').should('have.length', 1);

        // full content tag should exist
        cy.get('body:contains("' + feed.fullcontent.content + '")').should('have.length', 1);

        // featured image should exist.
        cy.get('.attachment-post-thumbnail.size-post-thumbnail.wp-post-image').should('have.length', 1);

        // author should be wordpress
        cy.get('li.post-author').should('have.length', 1);
        cy.get('li.post-author span.meta-text a:contains("wordpress")').should('have.length', 1);

    })


})
