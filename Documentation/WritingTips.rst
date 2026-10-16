..  _writing-tips:

============
Writing tips
============

-   Write for the editor, in their language and with the labels of their
    backend language: :guilabel:`Content > Layout`, not :guilabel:`Web > Page`.
-   One ``##`` per subject the editor looks for (a page type, a content
    element, a menu), with ``###`` for the tasks: *Create a news item*,
    *Fields*.
-   Steps as a numbered list, fields as a table *Field | What to fill in*.
-   Do not write numbers in the headings; set
    :ref:`numbered: true <confval-option-numbered>` instead. The numbers are
    added after the anchors are made, so ``## News items`` keeps
    ``#manual-news-items`` when a chapter is added before it. A ``###`` before
    the first ``##`` is not numbered.
-   Keep links within the manual out of the introduction: it is also shown on
    the dashboard, where they do not work.
-   Avoid text that only makes sense in another format ("Ctrl+click in Word").
