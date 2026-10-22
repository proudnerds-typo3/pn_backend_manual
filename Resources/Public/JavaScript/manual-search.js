/**
 * Filters the table of contents of the full view on the text of each chapter and section,
 * highlights the words in the text and steps through them. Every word of the query must occur;
 * case and accents are ignored, and words shorter than MIN_WORD_LENGTH are left out.
 */
import coreLabels from '~labels/backend.messages'
import labels from '~labels/pn_backend_manual.messages'

// The same minimum as the search in the core's Page TSconfig and TypoScript modules
const MIN_WORD_LENGTH = 3
const HIGHLIGHT = 'pn-backend-manual-search'
const HIGHLIGHT_CURRENT = 'pn-backend-manual-search-current'

export class ManualSearch {
  #nav
  #body
  #field
  #steps
  #status
  #entries = []
  #textNodes = null
  #ranges = []
  #current = -1

  constructor(selector, bodySelector) {
    this.#nav = document.querySelector(selector)
    const search = this.#nav?.querySelector('[data-manual-search]')
    if (!search) {
      return
    }
    this.#body = document.querySelector(bodySelector)
    this.#field = search.querySelector('[data-manual-search-field]')
    this.#steps = search.querySelectorAll('[data-manual-search-step]')
    this.#status = search.querySelector('[data-manual-search-status]')
    this.#index()
    search.hidden = false
    this.init()
  }

  init() {
    this.#field.addEventListener('input', () => this.#search())
    this.#field.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && this.#field.value !== '') {
        this.#field.value = ''
        this.#search()
      } else if (event.key === 'Enter' || event.key === 'ArrowDown') {
        event.preventDefault()
        this.#step(event.shiftKey && event.key === 'Enter' ? -1 : 1)
      } else if (event.key === 'ArrowUp') {
        event.preventDefault()
        this.#step(-1)
      }
    })
    for (const button of this.#steps) {
      button.addEventListener('click', () =>
        this.#step(button.dataset.manualSearchStep === 'previous' ? -1 : 1)
      )
    }
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

  #search() {
    const words = ManualSearch.#normalize(this.#field.value)
      .split(/\s+/)
      .filter((word) => word.length >= MIN_WORD_LENGTH)
    const entries = this.#filter(words)
    this.#highlight(words)
    for (const button of this.#steps) {
      button.disabled = this.#ranges.length === 0
    }
    if (words.length === 0) {
      this.#status.textContent = ''
    } else if (entries === 0) {
      this.#status.textContent = labels.get('manual.search.empty')
    } else {
      this.#status.textContent = coreLabels.get(
        'collapse_state_search.numberOfSearchMatches',
        { count: this.#ranges.length }
      )
    }
  }

  #filter(words) {
    let matches = 0
    for (const { item, text } of this.#entries) {
      const match = words.every((word) => text.includes(word))
      item.hidden = !match
      matches += match ? 1 : 0
    }
    return matches
  }

  // A Highlight leaves the DOM alone, so anchors, feedback links and the current entry keep working
  #highlight(words) {
    this.#ranges = []
    this.#current = -1
    if (!this.#body || !('highlights' in CSS)) {
      return
    }
    CSS.highlights.delete(HIGHLIGHT_CURRENT)
    if (words.length === 0) {
      CSS.highlights.delete(HIGHLIGHT)
      return
    }
    for (const { node, text, starts, ends } of this.#bodyTextNodes()) {
      for (const word of words) {
        for (
          let index = text.indexOf(word);
          index !== -1;
          index = text.indexOf(word, index + word.length)
        ) {
          const range = new Range()
          range.setStart(node, starts[index])
          range.setEnd(node, ends[index + word.length - 1])
          this.#ranges.push(range)
        }
      }
    }
    this.#ranges.sort((a, b) => a.compareBoundaryPoints(Range.START_TO_START, b))
    CSS.highlights.set(HIGHLIGHT, new Highlight(...this.#ranges))
  }

  // The first step starts from where the reader is, not from the top of the manual
  #step(direction) {
    const count = this.#ranges.length
    if (count === 0) {
      return
    }
    if (this.#current === -1) {
      const threshold = this.#threshold()
      const next = this.#ranges.findIndex(
        (range) => range.getBoundingClientRect().top >= threshold
      )
      const first = next === -1 ? 0 : next
      this.#current = direction > 0 ? first : (first - 1 + count) % count
    } else {
      this.#current = (this.#current + direction + count) % count
    }
    const range = this.#ranges[this.#current]
    const highlight = new Highlight(range)
    highlight.priority = 1
    CSS.highlights.set(HIGHLIGHT_CURRENT, highlight)
    this.#reveal(range)
    this.#status.textContent = labels.get('manual.search.position', {
      current: this.#current + 1,
      count
    })
  }

  // Centered, so the sticky docheader never covers it
  #reveal(range) {
    const rect = range.getBoundingClientRect()
    const smooth = !window.matchMedia('(prefers-reduced-motion: reduce)')
      .matches
    window.scrollBy({
      top: rect.top + rect.height / 2 - window.innerHeight / 2,
      behavior: smooth ? 'smooth' : 'instant'
    })
  }

  #threshold() {
    const style = getComputedStyle(this.#nav)
    return (
      parseFloat(style.getPropertyValue('--module-docheader-sticky-height')) ||
      0
    )
  }

  // Built on first use: most readers never search
  #bodyTextNodes() {
    if (this.#textNodes) {
      return this.#textNodes
    }
    this.#textNodes = []
    const walker = document.createTreeWalker(this.#body, NodeFilter.SHOW_TEXT, {
      acceptNode: (node) =>
        node.parentElement.closest('[data-manual-feedback-link]')
          ? NodeFilter.FILTER_REJECT
          : NodeFilter.FILTER_ACCEPT
    })
    while (walker.nextNode()) {
      this.#textNodes.push(ManualSearch.#mapped(walker.currentNode))
    }
    return this.#textNodes
  }

  // Normalizes per character and keeps where each normalized character came from, because
  // normalizing can change the length: an accent may be a separate character in the source
  static #mapped(node) {
    let text = ''
    const starts = []
    const ends = []
    let offset = 0
    for (const character of node.data) {
      const normalized = ManualSearch.#normalize(character)
      const end = offset + character.length
      if (normalized === '' && ends.length > 0) {
        ends[ends.length - 1] = end
      }
      for (const part of normalized) {
        text += part
        starts.push(offset)
        ends.push(end)
      }
      offset = end
    }
    return { node, text, starts, ends }
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

const start = () =>
  new ManualSearch('[data-manual-contents]', '[data-manual-body]')
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start)
} else {
  start()
}
