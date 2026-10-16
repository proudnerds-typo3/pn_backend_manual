# Changelog

All notable changes to `pn_backend_manual` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

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

[Unreleased]: https://github.com/proudnerds-typo3/pn_backend_manual/compare/1.0.0...HEAD
[1.0.0]: https://github.com/proudnerds-typo3/pn_backend_manual/releases/tag/1.0.0
