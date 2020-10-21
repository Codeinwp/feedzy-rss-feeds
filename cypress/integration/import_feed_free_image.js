describe('Test Free - Import Feed Images', function() {
    before(function(){
        // login to WP
        Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

        cy.visit(Cypress.env('host') + 'wp-login.php');
        cy.get('#user_login').clear({force:true}).type( Cypress.env('login') );
        cy.get('#user_pass').clear({force:true}).type( Cypress.env('pass') );
        cy.get('#wp-submit').click();
    });

    const PREFIX = "feedzy-1-1 ";
    const feed = Cypress.env('import-feed');

    it.skip('Temporary test', function() {
        // empty
    });

    it('Create import WITH fallback image', function() {
        // 1. CREATE
        cy.visit('/post-new.php?post_type=feedzy_imports');

        // fill up the form
        cy.get('#title').clear({force:true}).type( feed.url_no_images );
        cy.get('[name="feedzy_meta_data[source]"]').clear({force:true}).type( feed.url_no_images );

        cy.get('#feedzy_item_limit').invoke('val', '').clear({force:true}).type(feed.items).blur();

        cy.get('#feedzy_post_terms').invoke('show').then( () => {
            cy.get('#feedzy_post_terms').select(feed.taxonomy, {force:true});
        });

        cy.get('[name="feedzy_meta_data[import_post_title]"]').scrollIntoView({force:true}).clear({force:true}).type( PREFIX + feed.title, {force:true} );
        cy.get('[name="feedzy_meta_data[import_post_content]"]').scrollIntoView({force:true}).clear({force:true}).type( PREFIX + feed.fullcontent.content + feed.content, {force:true} );

        // image from tag
        cy.get('[name="feedzy_meta_data[import_post_featured_img]"]').scrollIntoView({force:true}).clear({force:true}).type( feed.image.tag, {force:true} );

        // fallback image
        cy.get('span#feedzy_image_fallback_span').should('be.empty');

        // fallback image
        cy.get('#feedzy-media-upload-add').scrollIntoView({force:true}).click({force:true}).then( () => {
            cy.get('.media-modal-content').then( ($modal) => {
                cy.wrap($modal).find('#menu-item-browse').click({force:true});
                cy.wrap($modal).find('ul.attachments li').first().click({force:true});
                cy.wrap($modal).find('button.media-button-select').click({force:true});
            }).then( () => {
                cy.get('span#feedzy_image_fallback_span').should('not.be.empty');
                cy.get('span#feedzy_image_fallback_span img').invoke('attr', 'src').then(($src) => {
                    let fallback_image_url = $src.trim();
                    cy.log("Using fallback image", fallback_image_url);

                    // saving in a temp env variable
                    Cypress.env( { "temp_fallback_img": fallback_image_url } );
                });
            });
        });

        // publish
        cy.get('button[type="submit"][name="publish"]').scrollIntoView({force:true}).click({force:true});

        // check if the import has been setup
        cy.url().should('include', 'edit.php?post_type=feedzy_imports');
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').should('be.visible');

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

        // 2. RUN
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').click();
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('html').should('include', 'Importing');

        cy.wait(3 * parseInt(feed.wait));
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
            expect(parseInt($value)).to.equal(0); // error, because image not found.
        });

        // 3. VERIFY IMPORTED ITEMS
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(4) a').first().click();

        // should have N posts.
        cy.get('table.posts tbody tr').should('have.length', feed.items);

        // click to view post
        cy.get('table.posts tbody tr td a.row-title').first().parent().parent().find('span.view a').click({ force: true });

        cy.wait(feed.wait);

        cy.get('body:contains("' + PREFIX + '")').should('have.length', 1);

        // featured image should exist.
        cy.get('.attachment-post-thumbnail.size-post-thumbnail.wp-post-image').should('have.length', 1);

        // featured image should be the fallback image
        // the name may not be exact because it will be a larger image than the thumbnail we chose.
        cy.get('.attachment-post-thumbnail.size-post-thumbnail.wp-post-image').invoke('attr', 'src').should('contain', Cypress.env("temp_fallback_img"));
    })

    it('Create import EXCLUDING imageless items', function() {
        // 1. CREATE
        cy.visit('/post-new.php?post_type=feedzy_imports');

        // fill up the form
        cy.get('#title').clear({force:true}).type( feed.url_no_images );
        cy.get('[name="feedzy_meta_data[source]"]').clear({force:true}).type( feed.url_no_images );

        // exclude imageless items
        cy.get('[name="feedzy_meta_data[exc_noimage]"]').scrollIntoView({force:true}).click({force:true});

        cy.get('#feedzy_item_limit').invoke('val', '').clear({force:true}).type(feed.items).blur();

        cy.get('#feedzy_post_terms').invoke('show').then( () => {
            cy.get('#feedzy_post_terms').select(feed.taxonomy, {force:true});
        });

        cy.get('[name="feedzy_meta_data[import_post_title]"]').scrollIntoView({force:true}).clear({force:true}).type( PREFIX + feed.title, {force:true} );
        cy.get('[name="feedzy_meta_data[import_post_content]"]').scrollIntoView({force:true}).clear({force:true}).type( PREFIX + feed.fullcontent.content + feed.content, {force:true} );

        // image from tag
        cy.get('[name="feedzy_meta_data[import_post_featured_img]"]').scrollIntoView({force:true}).clear({force:true}).type( feed.image.tag, {force:true} );

        // fallback image row should not be shown
        cy.get('#feedzy_image_fallback').should('not.be.visible');

        // publish
        cy.get('button[type="submit"][name="publish"]').scrollIntoView({force:true}).click({force:true});

        // check if the import has been setup
        cy.url().should('include', 'edit.php?post_type=feedzy_imports');
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').should('be.visible');

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

        // 2. RUN
        cy.get('table.posts:nth-of-type(1) .feedzy-run-now').click();
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('html').should('include', 'Importing');

        cy.wait(3 * parseInt(feed.wait));
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('html').should('include', 'Nothing imported');

        cy.visit('/edit.php?post_type=feedzy_imports')

        // check last run status has all the data.
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(1)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(0)); // found
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(2)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(0); // duplicate
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(3)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(0)); // imported
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(4)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(parseInt(0)); // cumulative
        });
        cy.get('table.posts:nth-of-type(1) tr.feedzy-import-status-row td:nth-of-type(1) table tr:nth-of-type(1) td:nth-of-type(5)').invoke('data', 'value').should(($value) => {
            expect(parseInt($value)).to.equal(1); // success
        });

    })


})
