# TYPO3 extension `pn_backend_manual`

**The editor manual lives where editors work: in the TYPO3 backend.**

Every site you deliver, and every custom extension you build, could need a
manual: which page types there are, what each field does, how a plugin or
module works, where to find the news items. Usually that is a Word or PDF file
that gets mailed once at launch, ends up in someone's downloads folder and is
out of date after the first change. With this extension the manual is a
Markdown file in the sitepackage or the custom extension, right next to the
code it describes, and editors open it from their dashboard.

- **Always at hand.** A dashboard widget shows the introduction and the
  chapters; one click opens the full manual inside the backend. No searching
  through mail or intranet, no separate login.
- **Easy to look things up.** A table of contents next to the text that stays
  in view, optional chapter numbers (1., 1.1) and sections, and a marker that
  shows where you are, like the selected page in the page tree.
- **Kept up to date with the site.** The manual is a file in git. Change a
  content element and update its chapter in the same commit; it is reviewed and
  deployed together with the code, so the manual never lags behind.
- **Written in plain Markdown.** Headings, lists, tables, links and
  screenshots. It previews in your IDE and on GitHub or Bitbucket, and the same
  file can feed a Word export for the client's archive.
- **On paper too.** A print button prints the manual or saves it as PDF,
  without the backend around it, each chapter on a new page.
- **The right manual for the right people.** One widget per manual, so access
  is granted per backend group with the core setting *Allowed dashboard
  widgets*. A manual for editors and one for administrators is two widgets.
- **For sitepackages and custom extensions.** Any extension can ship its own
  manual: the sitepackage for the site as a whole, a custom extension for its
  own plugin, module or records. The manual travels with the extension, so it
  is there on every site that installs it.
- **Safe by design.** The request never chooses a file, raw HTML in the
  Markdown is stripped, and images are only served from `Resources/Public`.
- **Fits the backend.** Core styles and tokens, light and dark backend,
  keyboard and screen reader friendly; English, Dutch and German labels.

Example 'Editor manual' widget on the dashboard, with the introduction and the chapters:

![Dashboard with the widget "Editor manual": an introduction, the numbered chapters and the button "Open the manual"](Documentation/Images/dashboard-widget.png)

The full view, with chapters and sections in the table of contents and the
current section marked:

![Full view of the manual: buttons "Go back" and "Print or save as PDF", the table of contents on the left with "3.1 Create a news item" marked, and the text of that section on the right](Documentation/Images/full-view.png)

Both show the example manual in
[`Documentation/Example/EditorManual.md`](Documentation/Example/EditorManual.md),
registered with `numbered: true` and `contentsDepth: 2`.

## Requirements

TYPO3 v14.3 LTS with `typo3/cms-dashboard` · PHP 8.3+ · `league/commonmark` ^2.10
(installed by Composer)

**Composer installations only.** The extension is also listed in the
[TER](https://extensions.typo3.org/extension/pn_backend_manual), but a classic
mode installation from there lacks `league/commonmark`: the widget and the full
view then show an error, and the log names the missing library.

## Installation

```bash
composer require proudnerds/pn-backend-manual
```

Let the sitepackage or extension that ships a manual depend on it as well,
because its `Services.yaml` refers to the widget class:

```json
"require": {
    "proudnerds/pn-backend-manual": "^1.0"
}
```

No database changes, no TypoScript, no site set.

## Adding a manual

### 1. Write the Markdown file

Put the file in `Resources/Private/` of your sitepackage or custom extension,
for example `Resources/Private/Manual/EditorManual.md`. Not in `Resources/Public`: every
file there can be downloaded without logging in.

```markdown
---
type: manual
version: "1.0"
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
```

How the file is read:

| Markdown | Result |
|---|---|
| Front matter (`---` … `---`) | Ignored. Use it for other tools, such as a Word export. It must be valid YAML. |
| First `#` heading | Title of the manual, shown as h1 in the full view. Not repeated in the body. Without it, the widget title is used. |
| Text before the first `##` | Introduction, shown in the widget and at the top of the full view. |
| `##` headings | Chapters: the table of contents in the widget and in the full view. |
| `###` headings | Sections of the chapter above them; also in the table of contents of the full view with `contentsDepth: 2`. |
| `##` and `###` headings | Get an anchor `#manual-<slug>`, e.g. `## News items` → `#manual-news-items`. Use these for links within the manual. With `numbered: true` the number is shown but not part of the anchor. |
| Tables | Backend table style. |
| Links to other sites | Open in a new window, with a hidden "opens in a new window" for screen readers. |
| Raw HTML, `javascript:` links | Stripped, also from the title, the table of contents and alt texts. |
| Images | See below. |

### 2. Images and screenshots

Write image paths **relative to the Markdown file**, so the manual also
previews correctly in your IDE or on GitHub/Bitbucket:

```
Resources/
├── Private/Manual/EditorManual.md        ![…](../../Public/Manual/page-tree.png)
└── Public/Manual/page-tree.png
```

Only files inside `Resources/Public` of the **same extension** are shown, with
a cache-busting URL, so a replaced screenshot shows up straight away. An image
that is missing or lies elsewhere (another folder, a symlink out of
`Resources/Public`, an absolute path, a URL, a data URI) is replaced by its alt
text in italics, as a browser does with a broken image. The same goes for file
names with `#` or `?`. So always write a meaningful alt text: it is what screen
readers read, and what editors see while a screenshot does not exist yet.

### 3. Register the manual as a widget

In `Configuration/Services.yaml` of the same sitepackage or extension:

```yaml
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
    tags:
      - name: dashboard.widget
        identifier: 'mySitepackageManual'
        groupNames: 'documentation'
        title: 'my_sitepackage.messages:manual.title'
        description: 'my_sitepackage.messages:manual.description'
        iconIdentifier: 'content-book'
        height: 'medium'
        width: 'medium'
```

| Setting | Required | Meaning |
|---|---|---|
| service name (`my_sitepackage.manual`) | yes | Any unique service name. |
| `$options.file` | yes | `EXT:` path of the Markdown file. |
| `$options.logo` | no | `EXT:` path of an image in `Resources/Public`. Shown on a white plate, because most logos are made for a light background and the backend may be dark. Decorative (`alt=""`): the title is next to it. A logo that cannot be found is left out. |
| `$options.numbered` | no | `true` numbers the chapters (`##`) as 1., 2. and their sections (`###`) as 1.1, 1.2, in the headings and in both tables of contents. Default `false`. |
| `$options.contentsDepth` | no | Table of contents of the full view: `1` shows the chapters (default), `2` also their sections, indented under the chapter title, with the chapters in bold. The widget always shows the chapters only, because its height is fixed. |
| `$options.printButton` | no | `false` removes the *Print or save as PDF* button from the full view. Default `true`. |
| `identifier` | yes | Unique widget identifier. This is what you grant to backend groups, and the `manual` parameter of the full view. Use a prefix of your sitepackage or extension. |
| `groupNames` | yes | `documentation` puts the widget under *Add widget › Documentation*, next to the core documentation widgets. |
| `title`, `description` | yes | Shown in the widget wizard and as widget title. Plain text or a label reference (`my_sitepackage.messages:key` or `LLL:EXT:…`). |
| `iconIdentifier` | no | Icon in the widget wizard; `content-book` fits a manual. |
| `height`, `width` | no | `small`, `medium` or `large`. Widgets have a fixed height: choose the smallest one in which the introduction and the table of contents fit without scrolling. |

Several manuals (for example one for editors and one for administrators) are
several services with their own `identifier`.

Flush the caches (`typo3 cache:flush`) after changing `Services.yaml`.

## Who sees which manual

Access follows the dashboard: a user sees a manual if the widget is allowed for
one of their backend groups. Admins see all manuals.

1. Edit the backend user group (module **Administration › Users**, or the
   group record in the page tree root).
2. Tab **Module Permissions**: allow the module **Dashboard**, and tick the
   manual under **Allowed dashboard widgets**.

The user then adds the widget via **Dashboard › + › Documentation**. To give
new users the widget straight away, add a dashboard preset to your sitepackage
(`Configuration/Backend/DashboardPresets.php`):

```php
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
```

and select it for the group in its user TSconfig:

```
options.dashboard.dashboardPresetsForNewUsers = mySitepackageEditors
```

## The full view

*Open the manual* in the widget, or a chapter in its table of contents, opens
the whole manual in the content area: title and logo, a table of contents next
to the text, a *Go back* button to the dashboard and a *Print or save as PDF*
button.

The print button opens the browser's print dialog, which also saves as PDF.
The print styles leave out the docheader, put the table of contents above the
text, start every chapter after the first on a new page, add the URL after each
link to another site, and always use the light colours, also in a dark backend.

The table of contents:

- stays in view while scrolling, and scrolls itself when it is taller than the
  screen
- marks the chapter or section the reader is at: the entry whose heading has
  just passed below the docheader. Nothing is marked in the introduction; at the
  end of the page the last entry is.
- keeps a clicked entry marked until the reader scrolls again, because the last
  sections are often too short to reach the top
- shows the marked entry like the selected page in the page tree (the core
  tokens `--typo3-component-active-*`), so it follows the light and dark
  backend and a changed primary colour, and sets `aria-current="location"`
- shows a keyboard focus ring inside the entry, also on the marked one
- works without JavaScript, only without the marking

A click on an entry leaves room between the heading and the docheader.

The full view is the backend route `pn_backend_manual` (GET only) with the
widget identifier as only parameter. The file comes from the widget
registration and the widget permission of the user is checked; an unknown
identifier, a widget that is not a manual or a widget the user may not use all
give a 404 *Manual not available*. A manual that cannot be read or parsed (for
example invalid YAML in the front matter) gives *Manual cannot be shown*, and
the error is logged. Like every backend route the URL carries a token, so it
cannot be bookmarked; link to the dashboard instead.

## Writing tips

- Write for the editor, in their language and with the labels of their backend
  language: *Content › Layout*, not *Web › Page*.
- One `##` per subject the editor looks for (a page type, a content element,
  a menu), with `###` for the tasks: *Create a news item*, *Fields*.
- Steps as a numbered list, fields as a table *Field | What to fill in*.
- Do not write numbers in the headings; set `numbered: true` instead. The
  numbers are added after the anchors are made, so `## News items` keeps
  `#manual-news-items` when a chapter is added before it. A `###` before the
  first `##` is not numbered.
- Keep links within the manual out of the introduction: it is also shown on
  the dashboard, where they do not work.
- Avoid text that only makes sense in another format ("Ctrl+click in Word").

## Development

```bash
composer install
.Build/bin/phpunit -c phpunit.xml --testsuite unit
```

The JavaScript is a plain ES module without a build step, registered in
`Configuration/JavaScriptModules.php`. Flush the caches after changing that
file, because the import map is cached. Labels use the translation domain
`pn_backend_manual.messages` (`Resources/Private/Language/messages.xlf`).

| File | Role |
|---|---|
| `Widgets\ManualWidget` | The dashboard widget; holds the `file`, `logo`, `numbered` and `contentsDepth` options |
| `Controller\ManualController` | Full view, route `pn_backend_manual` |
| `Manual\ManualFinder` | Finds a manual among the widgets the user may use |
| `Manual\ManualLoader` | Reads an `EXT:` file, sets up the image resolver for its extension and builds public URLs |
| `Rendering\MarkdownRenderer` | Markdown → title, introduction, body, table of contents with sections, and heading numbers |
| `Rendering\ImageResolver` | Allows only images inside `Resources/Public` |
| `Resources/Public/JavaScript/manual-contents.js` | Marks the current entry in the table of contents of the full view |
| `Resources/Public/JavaScript/manual-print.js` | Opens the print dialog from the print button |

`ManualFinder` uses the dashboard's `WidgetRegistry`, which is marked
`@internal` in TYPO3 and has no public alternative. It is the only class that
touches it; check it when updating TYPO3.

## Credits

Created and maintained by [ProudNerds](https://www.proudnerds.com/):
Jacco van der Post.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
