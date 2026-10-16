/**
 * Opens the print dialog, which can also save as PDF. The button sits in the docheader,
 * so the click is delegated from the document.
 */
export class ManualPrint {
  #selector

  constructor(selector) {
    this.#selector = selector
    this.init()
  }

  init() {
    document.addEventListener('click', (event) => {
      if (event.target.closest(this.#selector)) {
        window.print()
      }
    })
  }
}

new ManualPrint('[data-manual-print]')
