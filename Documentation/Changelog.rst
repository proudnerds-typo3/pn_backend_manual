..  _changelog:

=========
Changelog
=========

The full changelog is maintained in the repository:
`CHANGELOG.md <https://github.com/proudnerds-typo3/pn_backend_manual/blob/main/CHANGELOG.md>`__.

Releases follow `Semantic Versioning <https://semver.org/>`__. Only
``Major.Minor`` versions are published as separate documentation branches,
because patch releases never introduce breaking changes or new features.

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
