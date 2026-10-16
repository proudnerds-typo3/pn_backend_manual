..  _full-view:

=============
The full view
=============

:guilabel:`Open the manual` in the widget, or a chapter in its table of
contents, opens the whole manual in the content area: title and logo, the
version and date of the manual, a table of contents with a search field next to
the text, a :guilabel:`Go back` button to the dashboard, a
:guilabel:`Print or save as PDF` button and, when configured, a
:guilabel:`Give feedback` button.

..  figure:: Images/full-view.png
    :alt: Full view: buttons "Go back", "Print or save as PDF" and "Give feedback", the title with "Version 1.2 · Updated October 16, 2026", the table of contents with a search field, and a feedback icon next to each heading

    The full view of the example manual.

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
    backend and a changed primary color. It has ``aria-current="location"``.
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
-   always use the light colors, also in a dark backend

Remove the button with :ref:`printButton: false <confval-option-printbutton>`.

..  _full-view-search:

Search
======

The search field above the table of contents filters it while the reader types.
An entry stays when every word occurs in its heading or its text; a chapter
also stays when one of its sections matches. Case and accents are ignored, so
``uber`` finds *Über uns*. :kbd:`Escape` clears the field. Without JavaScript
the field is not shown.

..  figure:: Images/search.png
    :alt: The search field with "teaser": only "3. News items" and "3.2 Fields" are left in the table of contents, next to the table of fields

    Searching for "teaser" leaves the chapter and the section that mention it.

..  _full-view-feedback:

Feedback
========

With the option :ref:`feedback <confval-option-feedback>`, every chapter and
section gets a small feedback icon next to its heading, and the docheader shows
:guilabel:`Give feedback` for the manual as a whole.

For a mail address, the link next to a heading opens a mail about that section:

-   the subject names the manual and the section, for example
    *Feedback: Editor manual example.org – 3.2 Fields*
-   the first line of the mail repeats the section: *Section: 3.2 Fields*

A web address, for example a feedback form, opens in a new window. The links
are added by JavaScript and have a hidden text for screen readers,
*Give feedback on 3.2 Fields*. They are left out when printing.

..  _full-view-version:

Version and date
================

``version`` and ``date`` from the :ref:`front matter <adding-a-manual-reading>`
are shown under the title, the date in the language of the backend user:
*Version 1.2 · Updated October 16, 2026*. Update them with every change, so
readers know whether they read the current text.

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
