..  _access:

======================
Who sees which manual
======================

Access follows the dashboard: a user sees a manual if the widget is allowed for
one of their backend groups. Admins see all manuals.

#.  Edit the backend user group: module :guilabel:`Administration > Users`, or
    the group record in the page tree root.
#.  Tab :guilabel:`Module Permissions`: allow the module
    :guilabel:`Dashboard`, and tick the manual under
    :guilabel:`Allowed dashboard widgets`.

The user then adds the widget via :guilabel:`Dashboard > + > Documentation`.

..  _access-preset:

Give new users the widget straight away
=======================================

Add a dashboard preset to your sitepackage:

..  code-block:: php
    :caption: Configuration/Backend/DashboardPresets.php

    <?php

    return [
        'mySitepackageEditors' => [
            'title' => 'my_sitepackage.messages:dashboard.editors',
            'description' => 'my_sitepackage.messages:dashboard.editors.description',
            'iconIdentifier' => 'content-dashboard',
            'defaultWidgets' => ['mySitepackageManual'],
            'showInWizard' => true,
        ],
    ];

and select it for the group in its user TSconfig:

..  code-block:: typoscript
    :caption: User TSconfig of the backend group

    options.dashboard.dashboardPresetsForNewUsers = mySitepackageEditors
