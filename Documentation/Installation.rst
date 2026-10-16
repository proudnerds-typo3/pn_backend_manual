..  _installation:

============
Installation
============

Requirements
============

-   TYPO3 v14.3 LTS with the system extension :composer:`typo3/cms-dashboard`
-   PHP 8.3, 8.4 or 8.5
-   :composer:`league/commonmark` ^2.10, installed by Composer

..  important::
    **Composer installations only.** The extension is also listed in the
    `TYPO3 Extension Repository <https://extensions.typo3.org/extension/pn_backend_manual>`__,
    but a classic mode installation from there lacks the library
    ``league/commonmark``. The widget and the full view then show an error, and
    the log names the missing library.

Install the extension
=====================

..  code-block:: bash

    composer require proudnerds/pn-backend-manual

Let the sitepackage or extension that ships a manual depend on it as well,
because its :file:`Services.yaml` refers to the widget class:

..  code-block:: json
    :caption: composer.json of the sitepackage or extension

    "require": {
        "proudnerds/pn-backend-manual": "^1.0"
    }

No database changes, no TypoScript, no site set. Continue with
:ref:`adding-a-manual`.
