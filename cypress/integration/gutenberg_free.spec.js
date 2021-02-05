describe('Test Free - gutenberg', function () {
  before(function () {
    Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

    // login to WP
    cy.visit(Cypress.env('host') + 'wp-login.php');
    cy.get('#user_login').clear().type(Cypress.env('login'));
    cy.get('#user_pass').clear().type(Cypress.env('pass'));
    cy.get('#wp-submit').click();
  });

  const PREFIX = 'feedzy-scg-0-' + Cypress.moment().unix();

  it('Insert a block', function () {
    Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

    cy.visit('/post-new.php');

    // get rid of that irritating popup
    cy.get('.nux-dot-tip__disable').click();

    cy.get('textarea.editor-post-title__input').type(PREFIX);

    // insert a feedzy block
    cy.get('div.edit-post-header-toolbar .block-editor-inserter button').click();
    cy.get('.components-popover__content').then(function ($popup) {
      cy.wrap($popup).find('.block-editor-inserter__search').type('feedzy');
      cy.wrap($popup)
        .find('.block-editor-inserter__results ul.block-editor-block-types-list li')
        .should('have.length', 1);
      cy.wrap($popup)
        .find('.block-editor-inserter__results ul.block-editor-block-types-list li button')
        .click();
    });

    // see the block has the correct elements.
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').should('have.length', 1);
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').should(
      'have.length',
      1,
    );
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-primary.is-large').should(
      'have.length',
      1,
    );

    var gutenberg = Cypress.env('gutenberg');
    // insert a feed
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').type(gutenberg.url);
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-primary.is-large')
      .click()
      .then(() => {
        cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss').should(
          'have.length',
          1,
        );
        cy.get(
          'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss div.rss_header',
        ).should('have.length', 1);
        cy.get(
          'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default',
        ).should('have.length', 1);
        cy.get(
          'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li',
        ).should('have.length', 5);

        // change settings
        // clear does not work on number fields. Targetting slider and triggering 'change' doesn't work either. So we have to mix n match.

        cy.get(
          'div.edit-post-sidebar div.components-base-control.feedzy-max input.components-range-control__number',
        )
          .invoke('val', '')
          .clear({ force: true })
          .type(gutenberg.max, { force: true })
          .blur({ force: true });
        cy.get(
          'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li',
        ).should('have.length', gutenberg.max);

        cy.get(
          'div.edit-post-sidebar div.components-base-control.feedzy-offset input.components-range-control__number',
        )
          .invoke('val', '')
          .clear({ force: true })
          .type(gutenberg.offset, { force: true })
          .blur({ force: true });
        cy.get(
          'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li',
        ).should('have.length', parseInt(gutenberg.max) - parseInt(gutenberg.offset));

        // item options
        cy.get(
          'div.edit-post-sidebar div.components-panel__body.feedzy-item-options button.components-panel__body-toggle',
        )
          .click()
          .then(() => {
            cy.get(
              'div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-meta input.components-text-control__input',
            ).type(gutenberg.meta, { force: true });
            cy.get(
              'div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-multiple-meta input.components-text-control__input',
            ).type(gutenberg.multiple_meta, { force: true });
            /* for pro 
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-include input.components-text-control__input').type(gutenberg.include);
                cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-ban input.components-text-control__input').type(gutenberg.ban);
                */
            cy.get(
              'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li',
            ).should('have.length', gutenberg.results);
          });

        // image options
        cy.get(
          'div.edit-post-sidebar div.components-panel__body.feedzy-image-options button.components-panel__body-toggle',
        )
          .click()
          .then(() => {
            cy.get(
              'div.edit-post-sidebar div.components-panel__body.feedzy-image-options div.components-base-control.feedzy-thumb select.components-select-control__input',
            ).select(gutenberg.thumb);
          });

        cy.get('button.editor-post-publish-panel__toggle')
          .click()
          .then(() => {
            cy.get('button.editor-post-publish-button').click();
          });
      });

    cy.wait(2000);
  });

  it('Verify inserted block', function () {
    Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

    cy.visit('/edit.php?post_type=post');

    // should have 1 post.
    cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

    // edit post
    cy.get('tr td a.row-title:contains("' + PREFIX + '")').click();

    // see the block has the correct elements.
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').should('have.length', 1);
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] input[type="url"]').should(
      'have.length',
      0,
    );
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] button.is-primary.is-large').should(
      'have.length',
      0,
    );

    cy.get('textarea.editor-post-title__input').should('contain', PREFIX);

    var gutenberg = Cypress.env('gutenberg');
    // check inserted feed
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').focus();
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss').should(
      'have.length',
      1,
    );
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss div.rss_header').should(
      'have.length',
      1,
    );
    cy.get(
      'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default',
    ).should('have.length', 1);
    cy.get(
      'div[data-type="feedzy-rss-feeds/feedzy-block"] div.feedzy-rss ul.feedzy-default li',
    ).should('have.length', gutenberg.results.toString());

    cy.get(
      'div.edit-post-sidebar div.components-base-control.feedzy-max input.components-range-control__number',
    ).should('have.value', gutenberg.max.toString());
    cy.get(
      'div.edit-post-sidebar div.components-base-control.feedzy-offset input.components-range-control__number',
    ).should('have.value', gutenberg.offset.toString());
    // item options
    cy.get(
      'div.edit-post-sidebar div.components-panel__body.feedzy-item-options button.components-panel__body-toggle',
    )
      .click()
      .then(() => {
        cy.get(
          'div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-meta input.components-text-control__input',
        ).should('have.value', gutenberg.meta);
        cy.get(
          'div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-multiple-meta input.components-text-control__input',
        ).should('have.value', gutenberg.multiple_meta);
        /* for pro 
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-include input.components-text-control__input').should('have.value', gutenberg.include);
            cy.get('div.edit-post-sidebar div.components-panel__body.feedzy-item-options div.components-base-control.feedzy-ban input.components-text-control__input').should('have.value', gutenberg.ban);
            */
      });

    // image options
    cy.get(
      'div.edit-post-sidebar div.components-panel__body.feedzy-image-options button.components-panel__body-toggle',
    )
      .click()
      .then(() => {
        cy.get(
          'div.edit-post-sidebar div.components-panel__body.feedzy-image-options div.components-base-control.feedzy-thumb select.components-select-control__input',
        )
          .invoke('prop', 'selectedIndex')
          .should('equal', 1);
      });

    // we want to do this so that the next test succeeds - otherwise it will throw up an alert box and stop the page
    cy.get('button.editor-post-publish-button').click();
  });

  it('View the post', function () {
    cy.visit('/edit.php?post_type=post');

    var gutenberg = Cypress.env('gutenberg');

    // should have 1 post.
    cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

    // click to view post
    cy.get('tr td a.row-title:contains("' + PREFIX + '")')
      .first()
      .parent()
      .parent()
      .find('span.view a')
      .click({ force: true });

    cy.verify_feedzy_frontend(gutenberg.results);
  });

  it('Modify inserted block and make it LAZY', function () {
    Cypress.config('baseUrl', Cypress.env('host') + 'wp-admin/');

    cy.visit('/edit.php?post_type=post');

    // should have 1 post.
    cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

    // edit post
    cy.get('tr td a.row-title:contains("' + PREFIX + '")').click();

    // check inserted feed
    cy.get('div[data-type="feedzy-rss-feeds/feedzy-block"]').focus();

    // switch on lazy
    cy.get('div.edit-post-sidebar div.components-base-control.feedzy-lazy input').click();

    // we want to do this so that the next test succeeds - otherwise it will throw up an alert box and stop the page
    cy.get('button.editor-post-publish-button').click();

    cy.visit('/edit.php?post_type=post');
  });

  it('View the LAZY post', function () {
    cy.visit('/edit.php?post_type=post');

    var gutenberg = Cypress.env('gutenberg');

    // should have 1 post.
    cy.get('tr td a.row-title:contains("' + PREFIX + '")').should('have.length', 1);

    // click to view post
    cy.get('tr td a.row-title:contains("' + PREFIX + '")')
      .first()
      .parent()
      .parent()
      .find('span.view a')
      .click({ force: true });

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

    cy.verify_feedzy_frontend(gutenberg.results);
  });
});
