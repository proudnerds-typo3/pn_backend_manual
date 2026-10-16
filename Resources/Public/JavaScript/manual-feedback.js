/**
 * Puts a feedback link next to each chapter and section of the full view. A mail names the
 * manual and the section in the subject and in its first line; a web address is used as is.
 */
export class ManualFeedback {
  #body
  #template

  constructor(selector) {
    this.#body = document.querySelector(selector)
    this.#template = document.querySelector('[data-manual-feedback-template]')
    if (this.#body && this.#template) {
      this.init()
    }
  }

  init() {
    // Headings before the first chapter belong to the introduction, which is not a section
    let inChapter = false
    for (const heading of this.#body.querySelectorAll(
      ':scope > h2, :scope > h3'
    )) {
      inChapter ||= heading.tagName === 'H2'
      if (inChapter) {
        heading.append(this.#link(heading.textContent.trim()))
      }
    }
  }

  #link(section) {
    const { address, subject, label } = this.#template.dataset
    const text = label.replace('%s', section)
    const link = document.createElement('a')
    link.className = 'manual__feedback'
    link.dataset.manualFeedbackLink = ''
    link.title = text
    link.append(this.#template.content.cloneNode(true))
    const hidden = document.createElement('span')
    hidden.className = 'visually-hidden'
    hidden.textContent = text
    link.append(hidden)
    if (/^mailto:/i.test(address)) {
      link.href = this.#mail(address, `${subject} – ${section}`, section)
      // In the module iframe, the backend's Content Security Policy blocks a mailto: navigation
      link.target = '_top'
    } else {
      link.href = address
      link.target = '_blank'
      link.rel = 'noopener noreferrer'
    }
    return link
  }

  // Built by hand: URLSearchParams writes a space as "+", which mail programs show as is
  #mail(address, subject, section) {
    const body = `${this.#template.dataset.section}: ${section}\r\n\r\n`
    const query = `subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`
    return `${address}${address.includes('?') ? '&' : '?'}${query}`
  }
}

const start = () => new ManualFeedback('[data-manual-feedback]')
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', start)
} else {
  start()
}
