/**
 * Filters the table of contents of the full view on the text of each chapter and section.
 * Every word of the query must occur; case and accents are ignored.
 */
export class ManualSearch {
  #nav
  #field
  #status
  #entries = []

  constructor(selector) {
    this.#nav = document.querySelector(selector)
    const search = this.#nav?.querySelector('[data-manual-search]')
    if (!search) {
      return
    }
    this.#field = search.querySelector('[data-manual-search-field]')
    this.#status = search.querySelector('[data-manual-search-empty]')
    this.#index()
    search.hidden = false
    this.init()
  }

  init() {
    this.#field.addEventListener('input', () => this.#filter())
    this.#field.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && this.#field.value !== '') {
        this.#field.value = ''
        this.#filter()
      }
    })
  }

  // The text of a heading runs until the next heading of the same or a higher level,
  // so a chapter also matches on the text of its sections
  #index() {
    for (const link of this.#nav.querySelectorAll('a[href^="#"]')) {
      const heading = document.getElementById(
        ManualSearch.#idFromHash(link.hash)
      )
      if (!heading) {
        continue
      }
      const level = ManualSearch.#level(heading)
      let text = ManualSearch.#text(heading)
      for (
        let node = heading.nextElementSibling;
        node && ManualSearch.#level(node) > level;
        node = node.nextElementSibling
      ) {
        text += ` ${ManualSearch.#text(node)}`
      }
      this.#entries.push({
        item: link.closest('li'),
        text: ManualSearch.#normalize(text)
      })
    }
  }

  #filter() {
    const words = ManualSearch.#normalize(this.#field.value)
      .split(/\s+/)
      .filter(Boolean)
    let matches = 0
    for (const { item, text } of this.#entries) {
      const match = words.every((word) => text.includes(word))
      item.hidden = !match
      matches += match ? 1 : 0
    }
    this.#status.textContent =
      words.length > 0 && matches === 0
        ? this.#status.dataset.manualSearchEmpty
        : ''
  }

  // 1 to 6 for a heading, 7 for any other element
  static #level(element) {
    const match = /^H([1-6])$/.exec(element.tagName)
    return match ? Number(match[1]) : 7
  }

  // Without the hidden label of the feedback links, which every heading has
  static #text(element) {
    const copy = element.cloneNode(true)
    copy
      .querySelectorAll('[data-manual-feedback-link]')
      .forEach((link) => link.remove())
    return copy.textContent
  }

  static #normalize(text) {
    return text
      .normalize('NFD')
      .replace(/\p{Diacritic}/gu, '')
      .toLowerCase()
  }

  static #idFromHash(hash) {
    try {
      return decodeURIComponent(hash.slice(1))
    } catch {
      return ''
    }
  }
}

const start = () => new ManualSearch('[data-manual-contents]')
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start)
} else {
  start()
}
