..  _development:

===========
Development
===========

..  code-block:: bash

    composer install
    .Build/bin/phpunit -c phpunit.xml --testsuite unit

The JavaScript is a plain ES module without a build step, registered in
:file:`Configuration/JavaScriptModules.php`. Flush the caches after changing
that file, because the import map is cached. Labels use the translation domain
``pn_backend_manual.messages`` (:file:`Resources/Private/Language/messages.xlf`).

..  list-table::
    :header-rows: 1
    :widths: 40 60

    -   -   File
        -   Role
    -   -   ``Widgets\ManualWidget``
        -   The dashboard widget; holds the options
    -   -   ``Controller\ManualController``
        -   Full view, route ``pn_backend_manual``
    -   -   ``Manual\ManualFinder``
        -   Finds a manual among the widgets the user may use
    -   -   ``Manual\ManualLoader``
        -   Reads an ``EXT:`` file, sets up the image resolver for its extension
            and builds public URLs
    -   -   ``Rendering\MarkdownRenderer``
        -   Markdown to title, introduction, body, table of contents with
            sections, and heading numbers
    -   -   ``Rendering\ImageResolver``
        -   Allows only images inside :file:`Resources/Public`
    -   -   :file:`Resources/Public/JavaScript/manual-contents.js`
        -   Marks the current entry in the table of contents of the full view
    -   -   :file:`Resources/Public/JavaScript/manual-print.js`
        -   Opens the print dialog from the print button
    -   -   :file:`Resources/Public/JavaScript/manual-search.js`
        -   Filters the table of contents on the search field
    -   -   :file:`Resources/Public/JavaScript/manual-feedback.js`
        -   Puts a feedback link next to each chapter and section

..  note::
    ``ManualFinder`` uses the dashboard's ``WidgetRegistry``, which is marked
    ``@internal`` in TYPO3 and has no public alternative. It is the only class
    that touches it; check it when updating TYPO3.

Render this manual
==================

..  code-block:: bash

    docker run --rm -v "$(pwd)":/project -w /project \
        ghcr.io/typo3-documentation/render-guides:latest \
        --config=Documentation --no-progress --fail-on-log

The output is in :file:`Documentation-GENERATED-temp/Index.html`.
