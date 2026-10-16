..  _changelog:

=========
Changelog
=========

The full changelog is maintained in the repository:
`CHANGELOG.md <https://github.com/proudnerds-typo3/pn_backend_manual/blob/main/CHANGELOG.md>`__.

Releases follow `Semantic Versioning <https://semver.org/>`__. Only
``Major.Minor`` versions are published as separate documentation branches,
because patch releases never introduce breaking changes or new features.

1.1.0
=====

-   :ref:`Search <full-view-search>`: a field above the table of contents of
    the full view filters it on the text of the chapters and sections
-   :ref:`Feedback <full-view-feedback>`: the new option
    :ref:`feedback <confval-option-feedback>` adds a feedback link next to every
    chapter and section and a :guilabel:`Give feedback` button; a mail names the
    manual and the section, in the subject and in its first line
-   :ref:`Version and date <full-view-version>` from the front matter are shown
    under the title
-   Tables have the style of the backend lists, inline code is easier to read,
    and the scrollbar of the table of contents no longer covers its entries
-   In the widget, the chapter numbers are right-aligned in one column, and only
    the titles are underlined

1.0.1
=====

-   :file:`ext_emconf.php` with the category, state and constraints for the TER
-   This manual on docs.typo3.org; the README is now an introduction with links
    to it
-   Fixed: without the library ``league/commonmark``, as in a classic mode
    installation from the TER, the widget and the full view show an error
    message instead of failing; the log names the missing library

1.0.0
=====

First release: editor manuals in Markdown as dashboard widgets with a full
view, with the options ``logo``, ``numbered``, ``contentsDepth`` and
``printButton``, print or save as PDF, and English, Dutch and German labels.
