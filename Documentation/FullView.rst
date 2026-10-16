..  _full-view:

=============
The full view
=============

:guilabel:`Open the manual` in the widget, or a chapter in its table of
contents, opens the whole manual in the content area: title and logo, a table
of contents next to the text, a :guilabel:`Go back` button to the dashboard and
a :guilabel:`Print or save as PDF` button.

..  _full-view-contents:

The table of contents
=====================

-   It stays in view while scrolling, and scrolls itself when it is taller than
    the screen.
-   It marks the chapter or section the reader is at: the entry whose heading
    has just passed below the docheader. Nothing is marked in the introduction;
    at the end of the page the last entry is.
-   A clicked entry stays marked until the reader scrolls again, because the
    last sections are often too short to reach the top.
-   The marked entry looks like the selected page in the page tree (the core
    tokens ``--typo3-component-active-*``), so it follows the light and dark
    backend and a changed primary colour. It has ``aria-current="location"``.
-   A keyboard focus ring is shown inside the entry, also on the marked one.
-   Without JavaScript everything works, only without the marking.

A click on an entry leaves room between the heading and the docheader.

..  _full-view-print:

Print or save as PDF
====================

The print button opens the browser's print dialog, which also saves as PDF. The
print styles:

-   leave out the docheader
-   put the table of contents above the text
-   start every chapter after the first on a new page
-   add the URL after each link to another site
-   always use the light colours, also in a dark backend

Remove the button with :ref:`printButton: false <confval-option-printbutton>`.

..  _full-view-route:

Route and errors
================

The full view is the backend route ``pn_backend_manual`` (GET only) with the
widget identifier as only parameter. The file comes from the widget
registration, and the widget permission of the user is checked.

-   An unknown identifier, a widget that is not a manual or a widget the user
    may not use all give a 404 :guilabel:`Manual not available`.
-   A manual that cannot be read or parsed, for example because of invalid YAML
    in the front matter, gives :guilabel:`Manual cannot be shown`, and the error
    is logged.

Like every backend route, the URL carries a token, so it cannot be bookmarked;
link to the dashboard instead.
