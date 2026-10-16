..  _configuration:

=============
Configuration
=============

Register the manual as a dashboard widget in the
:file:`Configuration/Services.yaml` of the same sitepackage or extension:

..  code-block:: yaml
    :caption: Configuration/Services.yaml

    services:
      # … your existing services

      my_sitepackage.manual:
        class: 'ProudNerds\PnBackendManual\Widgets\ManualWidget'
        arguments:
          $options:
            file: 'EXT:my_sitepackage/Resources/Private/Manual/EditorManual.md'
            logo: 'EXT:my_sitepackage/Resources/Public/Images/logo.svg'
            numbered: true
            contentsDepth: 2
            printButton: true
            feedback: 'mailto:web@example.org'
        tags:
          - name: dashboard.widget
            identifier: 'mySitepackageManual'
            groupNames: 'documentation'
            title: 'my_sitepackage.messages:manual.title'
            description: 'my_sitepackage.messages:manual.description'
            iconIdentifier: 'content-book'
            height: 'medium'
            width: 'medium'

The service name (``my_sitepackage.manual``) can be any unique name. Several
manuals, for example one for editors and one for administrators, are several
services with their own ``identifier``.

Flush the caches (``typo3 cache:flush``) after changing :file:`Services.yaml`.

..  _configuration-options:

Options
=======

The options are passed as ``$options`` to the widget class.

..  confval:: file
    :name: option-file
    :type: string
    :required: true

    ``EXT:`` path of the Markdown file, see :ref:`adding-a-manual-file`.

..  confval:: logo
    :name: option-logo
    :type: string

    ``EXT:`` path of an image in :file:`Resources/Public`, shown in the widget
    and the full view. It is shown on a white plate, because most logos are made
    for a light background and the backend may be dark. Decorative
    (``alt=""``): the title is next to it. A logo that cannot be found is left
    out.

..  confval:: numbered
    :name: option-numbered
    :type: boolean
    :Default: false

    ``true`` numbers the chapters (``##``) as 1., 2. and their sections
    (``###``) as 1.1, 1.2, in the headings and in both tables of contents.

..  confval:: contentsDepth
    :name: option-contentsdepth
    :type: integer
    :Default: 1

    The table of contents of the full view: ``1`` shows the chapters, ``2`` also
    their sections, indented under the chapter title, with the chapters in bold.
    The widget always shows the chapters only, because its height is fixed.

..  confval:: printButton
    :name: option-printbutton
    :type: boolean
    :Default: true

    ``false`` removes the :guilabel:`Print or save as PDF` button from the full
    view. See :ref:`full-view-print`.

..  confval:: feedback
    :name: option-feedback
    :type: string

    A ``mailto:`` or ``https://`` address. The full view then shows a feedback
    link next to every chapter and section, and a :guilabel:`Give feedback`
    button, so readers can report what is unclear or missing. A mail names the
    manual and the section in the subject and in its first line. Other values
    are ignored. See :ref:`full-view-feedback`.

..  _configuration-widget:

Widget settings
===============

The tag ``dashboard.widget`` is the core registration of a dashboard widget.

..  list-table::
    :header-rows: 1
    :widths: 25 15 60

    -   -   Setting
        -   Required
        -   Meaning
    -   -   ``identifier``
        -   yes
        -   Unique widget identifier. This is what you grant to backend groups,
            and the ``manual`` parameter of the full view. Use a prefix of your
            sitepackage or extension.
    -   -   ``groupNames``
        -   yes
        -   ``documentation`` puts the widget under
            :guilabel:`Add widget > Documentation`, next to the core
            documentation widgets.
    -   -   ``title``, ``description``
        -   yes
        -   Shown in the widget wizard and as widget title. Plain text or a
            label reference (``my_sitepackage.messages:key`` or ``LLL:EXT:…``).
    -   -   ``iconIdentifier``
        -   no
        -   Icon in the widget wizard; ``content-book`` fits a manual.
    -   -   ``height``, ``width``
        -   no
        -   ``small``, ``medium`` or ``large``. Widgets have a fixed height:
            choose the smallest one in which the introduction and the table of
            contents fit without scrolling.
