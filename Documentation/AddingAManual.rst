..  _adding-a-manual:

===============
Adding a manual
===============

A manual is a Markdown file in a sitepackage or custom extension, registered as
a dashboard widget in the :file:`Services.yaml` of that same extension.

..  _adding-a-manual-file:

Write the Markdown file
=======================

Put the file in :file:`Resources/Private/` of your sitepackage or custom
extension, for example :file:`Resources/Private/Manual/EditorManual.md`.

..  warning::
    Not in :file:`Resources/Public`: TYPO3 publishes that folder under
    :file:`public/_assets/`, so every file in it can be downloaded without
    logging in.

..  code-block:: markdown
    :caption: Resources/Private/Manual/EditorManual.md

    ---
    version: "1.0"
    date: 2026-10-16
    ---

    # Editor manual example.org

    One or two sentences about who this manual is for. Everything before the
    first h2 is the introduction shown in the widget.

    ## Working in TYPO3

    ### Where to find what

    | You want to | Where |
    |---|---|
    | Manage pages | **Content › Layout** |

    ![The page tree in Content › Layout](../../Public/Manual/page-tree.png)

    ## News items

    Read the [TYPO3 Editors Tutorial](https://docs.typo3.org/m/typo3/tutorial-editors/main/en-us/).

The file is GitHub Flavored Markdown. A complete example is
`Documentation/Example/EditorManual.md <https://github.com/proudnerds-typo3/pn_backend_manual/blob/main/Documentation/Example/EditorManual.md>`__
in the repository.

..  _adding-a-manual-reading:

How the file is read
====================

..  list-table::
    :header-rows: 1
    :widths: 30 70

    -   -   Markdown
        -   Result
    -   -   Front matter (``---`` … ``---``)
        -   ``version`` and ``date`` (``YYYY-MM-DD``) are shown under the title
            of the full view, so readers see how recent the manual is. Other
            keys are ignored; use them for other tools, such as a Word export.
            It must be valid YAML.
    -   -   First ``#`` heading
        -   Title of the manual, shown as h1 in the full view. Not repeated in
            the body. Without it, the widget title is used.
    -   -   Text before the first ``##``
        -   Introduction, shown in the widget and at the top of the full view.
    -   -   ``##`` headings
        -   Chapters: the table of contents in the widget and in the full view.
    -   -   ``###`` headings
        -   Sections of the chapter above them; also in the table of contents of
            the full view with :ref:`contentsDepth: 2 <confval-option-contentsdepth>`.
    -   -   ``##`` and ``###`` headings
        -   Get an anchor ``#manual-<slug>``, for example ``## News items``
            becomes ``#manual-news-items``. Use these for links within the
            manual. With :ref:`numbered <confval-option-numbered>` the number is
            shown but not part of the anchor.
    -   -   Tables
        -   The table style of the backend lists (``table-fit``,
            ``table table-striped``); long text wraps inside the cells.
    -   -   Links to other sites
        -   Open in a new window, with a hidden "opens in a new window" for
            screen readers.
    -   -   Raw HTML, ``javascript:`` links
        -   Stripped, also from the title, the table of contents and alt texts.
            To show HTML as an example, write it as code between backticks:
            ```<strong>``` (one backtick on each side).
    -   -   Code between backticks
        -   The code color of the backend, on a light surface; long code such
            as a URL wraps.
    -   -   Images
        -   See :ref:`adding-a-manual-images`.

..  _adding-a-manual-images:

Images and screenshots
======================

The manual and its images live in different folders: the manual in
:file:`Resources/Private/`, which is not published, and the images in
:file:`Resources/Public/`, which is published so the browser can load them.

..  code-block:: text

    Resources/
    ├── Private/
    │   └── Manual/
    │       └── EditorManual.md     ← the manual
    └── Public/
        └── Manual/
            └── page-tree.png       ← the image

In the manual, write the path to the image **relative to the Markdown file**:

..  code-block:: markdown

    ![The page tree in Content › Layout](../../Public/Manual/page-tree.png)

The path starts in the folder of the manual, :file:`Resources/Private/Manual/`:

#.  ``../`` goes up one folder, to :file:`Resources/Private/`
#.  the second ``../`` goes up to :file:`Resources/`
#.  ``Public/Manual/page-tree.png`` goes down to the image

This is the usual way to link an image in Markdown, so the image also shows in
the preview of your IDE and on GitHub or Bitbucket.

Only files inside :file:`Resources/Public` of the **same extension** are shown,
with a cache-busting URL, so a replaced screenshot shows up straight away.

..  tip::
    Avoid spaces in file names, for example :file:`page-tree.png` instead of
    :file:`page tree.png`. The image is shown either way, but the core cannot
    add the cache-busting part to a URL of a file with a space in its name.

An image that is missing or lies elsewhere is replaced by its alt text in
italics, as a browser does with a broken image. That applies to another folder,
a symlink out of :file:`Resources/Public`, an absolute path, a URL, a data URI,
and file names with ``#`` or ``?``. So always write a meaningful alt text: it
is what screen readers read, and what editors see while a screenshot does not
exist yet.

Next: :ref:`register the manual as a widget <configuration>`.
