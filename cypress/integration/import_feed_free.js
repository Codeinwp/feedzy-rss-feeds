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

    it('Create/Verify/Run import', function() {
        // 1. CREATE
        cy.visit('/post-new.php?post_type=feedzy_imports');

        // fill up the form
        cy.get('#title').clear().type( feed.url );
        cy.get('[name="feedzy_meta_data[source]"]').clear().type( feed.url );

        // locked for pro?
        cy.get('.only-pro').should('have.length', feed.locked);

        /* @TODO: make this work someday
        cy.get('[name="feedzy_meta_data[inc_key]"]').should('not.be.visible');
        cy.get('[name="feedzy_meta_data[exc_key]"]').should('not.be.visible');
        cy.get('[name="feedzy_meta_data[import_feed_delete_days]"]').should('not.be.visible');
        cy.get('[name="feedzy_meta_data[import_link_author_admin]"]').should('not.be.visible');
        cy.get('[name="feedzy_meta_data[import_link_author_public]"]').should('not.be.visible');
        */

        cy.get('#feedzy_item_limit').invoke('val', '').clear().type(feed.items).blur();

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

        // should bring you back to the edit screen, not the listing screen
        cy.url().should('not.include', 'edit.php?post_type=feedzy_imports');

        // show a notice.
        cy.get('div.notice.feedzy-error-critical').should('be.visible');
    })

    it('Update the new import with VALID url', function() {
        cy.visit('/edit.php?post_type=feedzy_imports');

        cy.get('tr:nth-of-type(1) .row-title').click();

        // fill up the form
        cy.get('#title').clear().type( feed.url );
        cy.get('[name="feedzy_meta_data[source]"]').clear().type( feed.url );
        cy.get('.f1 fieldset:nth-of-type(1) .f1-buttons button.btn-next').scrollIntoView().click();

        cy.get('.f1 fieldset:nth-of-type(2) .f1-buttons button.btn-next').scrollIntoView().click();

        cy.get('button[type="submit"][name="save"]').scrollIntoView().click({force:true});

        // check if the import has been setup
        cy.url().should('include', 'edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').should('not.be.checked');
        cy.get('tr:nth-of-type(1) .feedzy-run-now').should('not.be.visible');

        // 2. VERIFY
        cy.get('tr:nth-of-type(1) .row-title').click();
        cy.get('#title').should('have.value', feed.url);
        cy.get('[name="feedzy_meta_data[source]"]').should('have.value', feed.url);
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

        // 3. TOGGLE
        cy.get('tr:nth-of-type(1) .feedzy-toggle').uncheck({force:true});

        cy.visit('/edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').should('not.be.checked');

        // activate.
        cy.get('tr:nth-of-type(1) .feedzy-toggle').check({force:true});
        cy.visit('/edit.php?post_type=feedzy_imports');
        cy.get('tr:nth-of-type(1) .feedzy-toggle').should('be.checked');

        // check last run status has all the initial data.
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(0); // found
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(2)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(0); // duplicate
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(3)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(0); // imported
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(4)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(0); // cumulative
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(5)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(-1); // success
        });

        // 4. RUN
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').should('be.visible');
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').click();
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('html').should('include', 'Importing');

        cy.wait(10 * parseInt(feed.wait));
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('html').should('include', 'Successfully run');

        cy.visit('/edit.php?post_type=feedzy_imports')

        // check last run status has all the data.
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(feed.items)); // found
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(2)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(0); // duplicate
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(3)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(feed.items)); // imported
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(4)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(feed.items)); // cumulative
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(5)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(1); // success
        });

        // 5. RUN AGAIN
        cy.visit('/edit.php?post_type=feedzy_imports')

        // run import
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').should('be.visible');
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').click();
        cy.wait(2 * parseInt(feed.wait));
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('html').should('include', 'Nothing imported');

        cy.visit('/edit.php?post_type=feedzy_imports')

        // check last run status has all the data.
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(feed.items)); // found
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(2)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(feed.items)); // duplicate
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(3)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(0); // imported
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(4)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(feed.items)); // cumulative
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(5)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(1); // success
        });

        cy.visit('/edit.php?post_type=feedzy_imports')

        // 6. VERIFY IMPORTED ITEMS
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(4) a').first().click();

        // should have N posts.
        cy.get('table.posts tbody tr').should('have.length', feed.items);

        // should have item_custom_ in each post title
        cy.get('table.posts tbody tr td a.row-title:contains("item_custom_")').should('have.length', feed.items);

        // should have categories and tags
        cy.get('table.posts tbody tr td.categories').should('have.length', feed.items);
        cy.get('table.posts tbody tr td.tags').should('have.length', feed.items);

        // all authors should be wordpress
        cy.get('table.posts tbody tr td.author:contains("wordpress")').should('have.length', feed.items);

        // click to view post
        cy.get('table.posts tbody tr td a.row-title').first().parent().parent().find('span.view a').click({ force: true });

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
