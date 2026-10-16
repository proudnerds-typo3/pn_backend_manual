/**
 * Marks the table of contents entry of the part of the manual the reader is at.
 * A clicked entry stays marked until the reader scrolls again: the last sections are often
 * too short to reach the top, so the position alone would mark the one before.
 */
export class ManualContents {
  #nav
  #entries = []
  #current = null
  #clicked = false
  #frame = 0

  constructor(selector) {
    this.#nav = document.querySelector(selector)
    if (!this.#nav) {
      return
    }
    for (const link of this.#nav.querySelectorAll('a[href^="#"]')) {
      const heading = document.getElementById(ManualContents.#idFromHash(link.hash))
      if (heading) {
        this.#entries.push({ heading, link })
      }
    }
    this.init()
  }

  init() {
    this.#nav.addEventListener('click', (event) => {
      const link = event.target.closest('a[href^="#"]')
      if (link) {
        this.#clicked = true
        this.#mark(link)
      }
    })
    for (const type of ['wheel', 'touchmove', 'keydown']) {
      window.addEventListener(
        type,
        () => {
          this.#clicked = false
        },
        { passive: true },
      )
    }
    // load: the headings move while images and fonts arrive, and a restored scroll position fires no scroll event
    for (const type of ['scroll', 'resize', 'load']) {
      window.addEventListener(type, () => this.#schedule(), { passive: true })
    }

    const targetId = ManualContents.#idFromHash(window.location.hash)
    const target = this.#entries.find(({ heading }) => heading.id === targetId)
    if (target) {
      this.#clicked = true
      this.#mark(target.link)
    } else {
      this.#update()
    }
  }

  // A hash is percent-encoded (#manual-%C3%A9%C3%A9n), an id is not (manual-één)
  static #idFromHash(hash) {
    try {
      return decodeURIComponent(hash.slice(1))
    } catch {
      return ''
    }
  }

  #schedule() {
    if (this.#clicked || this.#frame) {
      return
    }
    this.#frame = requestAnimationFrame(() => {
      this.#frame = 0
      this.#update()
    })
  }

  #update() {
    const page = document.scrollingElement
    if (page.scrollTop + page.clientHeight >= page.scrollHeight - 2) {
      this.#mark(this.#entries.at(-1)?.link ?? null)
      return
    }
    const threshold = this.#threshold()
    let current = null
    for (const { heading, link } of this.#entries) {
      if (heading.getBoundingClientRect().top > threshold) {
        break
      }
      current = link
    }
    this.#mark(current)
  }

  // A heading counts as reached once it is just below the sticky docheader
  #threshold() {
    const style = getComputedStyle(this.#nav)
    const docheader =
      parseFloat(style.getPropertyValue('--module-docheader-sticky-height')) ||
      0
    const rem = parseFloat(getComputedStyle(document.documentElement).fontSize)
    return docheader + 1.5 * rem
  }

  #mark(link) {
    if (link === this.#current) {
      return
    }
    this.#current?.removeAttribute('aria-current')
    this.#current = link
    if (link) {
      link.setAttribute('aria-current', 'location')
      this.#reveal(link)
    }
  }

  // Scrolls only the table of contents, never the page
  #reveal(link) {
    const nav = this.#nav
    if (nav.scrollHeight <= nav.clientHeight) {
      return
    }
    const top =
      link.getBoundingClientRect().top -
      nav.getBoundingClientRect().top +
      nav.scrollTop
    const bottom = top + link.offsetHeight
    if (top < nav.scrollTop) {
      nav.scrollTop = top
    } else if (bottom > nav.scrollTop + nav.clientHeight) {
      nav.scrollTop = bottom - nav.clientHeight
    }
  }
}

const start = () => new ManualContents('[data-manual-contents]')
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start)
} else {
  start()
}
