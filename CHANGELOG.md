# Changelog

All notable changes to `pn_backend_manual` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [1.2.0] – 2026-10-22

### Added

- The search field also highlights the words in the text of the full view; not
  in print
- The number of results below the search field, and arrow buttons to step
  through them (also `Enter` / `Shift+Enter` and `↓` / `↑` in the field); the
  current result is darker and scrolled into view, the counter shows *3 of 12*

### Changed

- Search ignores words shorter than three characters, for filtering as well,
  like the search in the core's Page TSconfig and TypoScript modules
- `composer.json` declares the version in `extra.typo3/cms.version` and an
  empty `extra.typo3/cms.Package.providesPackages`, so TYPO3 14.3 no longer
  reports `ext_emconf.php` as deprecated in classic mode
- The Extension Scanner no longer reports the false positives on `Node::$data`
  of league/commonmark, the logger and `Manual::$title`

### Fixed

- The focus outline of the search field is no longer cut off on the left by
  the scrolling table of contents

## [1.1.0] – 2026-10-16

### Added

- Search field above the table of contents of the full view; it filters the
  chapters and sections on their text while you type, ignoring case and accents
- Option `feedback` (a `mailto:` or `https://` address): a feedback link next
  to every chapter and section of the full view, and a *Give feedback* button
  for the manual as a whole; a mail names the manual and the section, in the
  subject and in its first line
- `version` and `date` from the front matter are shown under the title of the
  full view

### Changed

- Tables have the style of the backend lists (`table-fit`, striped) instead of
  borders; long text wraps inside the cells
- Inline code keeps the backend's code color on a light surface, with a little
  padding, so it reads better in running text; long code wraps
- The table of contents keeps room for its scrollbar, which no longer covers
  the search field or the current entry
- In the widget, the chapter numbers are right-aligned in one column, so
  "10." lines up with "1."; only the titles are underlined, not the numbers

## [1.0.1] – 2026-10-16

### Added

- `ext_emconf.php` with the category, state and constraints for the TER
- A manual for docs.typo3.org in `Documentation/`; the README is now an
  introduction with links to it

### Fixed

- Without the library `league/commonmark`, as in a classic mode installation
  from the TER, the widget and the full view show an error message instead of
  failing; the log names the missing library

## [1.0.0] – 2026-10-16

### Added

- Editor manuals as Markdown files in a sitepackage, registered as a
  `ManualWidget` service: one dashboard widget per manual, so access follows
  the core setting *Allowed dashboard widgets* per backend group
- Widget with the introduction and the chapters of the manual; full view on
  the backend route `pn_backend_manual` with a table of contents that stays in
  view and marks where the reader is
- Options per manual: `logo`, `numbered` (1., 1.1 in the headings and tables of
  contents), `contentsDepth` (sections in the table of contents of the full
  view) and `printButton`
- Print or save as PDF from the full view, with print styles that leave out the
  backend and start each chapter on a new page
- GitHub Flavored Markdown with tables; front matter is ignored, raw HTML and
  unsafe links are stripped, links to other sites open in a new window with a
  hidden label for screen readers
- Images relative to the Markdown file, served only from `Resources/Public` of
  the same extension; others are replaced by their alt text
- English, Dutch and German labels; light and dark backend

[Unreleased]: https://github.com/proudnerds-typo3/pn_backend_manual/compare/1.2.0...HEAD
[1.2.0]: https://github.com/proudnerds-typo3/pn_backend_manual/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/proudnerds-typo3/pn_backend_manual/compare/1.0.1...1.1.0
[1.0.1]: https://github.com/proudnerds-typo3/pn_backend_manual/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/proudnerds-typo3/pn_backend_manual/releases/tag/1.0.0
