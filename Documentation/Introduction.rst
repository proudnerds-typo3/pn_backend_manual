..  _introduction:

============
Introduction
============

Every site you deliver, and every custom extension you build, could need a
manual: which page types there are, what each field does, how a plugin or
module works, where to find the news items. Usually that is a Word or PDF file
that gets mailed once at launch, ends up in someone's downloads folder and is
out of date after the first change. With this extension the manual is a
Markdown file in the sitepackage or the custom extension, right next to the
code it describes, and editors open it from their dashboard.

What it does
============

**Always at hand**
    A dashboard widget shows the introduction and the chapters; one click opens
    the full manual inside the backend. No searching through mail or intranet,
    no separate login.

**Easy to look things up**
    A table of contents next to the text that stays in view, a search field
    that filters it while you type, optional chapter numbers (1., 1.1) and
    sections, and a marker that shows where you are, like the selected page in
    the page tree.

**Feedback from readers**
    A feedback link next to every chapter and section sends a mail about that
    section, so unclear or missing parts reach the author. The version and date
    under the title show how recent the manual is.

**Kept up to date with the site**
    The manual is a file in git. Change a content element and update its chapter
    in the same commit; it is reviewed and deployed together with the code, so
    the manual never lags behind.

**Written in plain Markdown**
    Headings, lists, tables, links and screenshots. It previews in your IDE and
    on GitHub or Bitbucket, and the same file can feed a Word export for the
    client's archive.

**On paper too**
    A print button prints the manual or saves it as PDF, without the backend
    around it, each chapter on a new page.

**The right manual for the right people**
    One widget per manual, so access is granted per backend group with the core
    setting :guilabel:`Allowed dashboard widgets`. A manual for editors and one
    for administrators is two widgets.

**For sitepackages and custom extensions**
    Any extension can ship its own manual: the sitepackage for the site as a
    whole, a custom extension for its own plugin, module or records. The manual
    travels with the extension, so it is there on every site that installs it.

**Safe by design**
    The request never chooses a file, raw HTML in the Markdown is stripped, and
    images are only served from :file:`Resources/Public`.

**Fits the backend**
    Core styles and tokens, light and dark backend, keyboard and screen reader
    friendly; English, Dutch and German labels.

Screenshots
===========

..  figure:: Images/dashboard-widget.png
    :alt: Dashboard with the widget "Editor manual": an introduction, the numbered chapters and the button "Open the manual"

    The widget on the dashboard, with the introduction and the chapters.

..  figure:: Images/full-view.png
    :alt: Full view: buttons "Go back", "Print or save as PDF" and "Give feedback", the title with "Version 1.2 · Updated October 16, 2026", the table of contents with a search field, and a feedback icon next to each heading

    The full view: version and date under the title, a search field above the
    table of contents, and buttons to print or give feedback.

..  figure:: Images/search.png
    :alt: The search field with "teaser": only "3. News items" and "3.2 Fields" are left in the table of contents, next to the table of fields

    Searching for "teaser" leaves the chapter and the section that mention it;
    the current section is marked.

All three show the example manual
`Documentation/Example/EditorManual.md <https://github.com/proudnerds-typo3/pn_backend_manual/blob/main/Documentation/Example/EditorManual.md>`__
from the repository, registered with :ref:`numbered <confval-option-numbered>`,
:ref:`contentsDepth: 2 <confval-option-contentsdepth>` and
:ref:`feedback <confval-option-feedback>`.
