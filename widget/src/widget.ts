interface WidgetConfig {
  workspace: string
  userToken?: string
  position?: 'bottom-right' | 'bottom-left'
  theme?: 'light' | 'dark' | 'auto'
}

class ChatWidgetManager {
  private iframe: HTMLIFrameElement | null = null
  private container: HTMLDivElement | null = null
  private config: WidgetConfig
  private isOpen = false
  private baseUrl: string

  constructor(config: WidgetConfig) {
    this.config = {
      position: 'bottom-right',
      theme: 'auto',
      ...config
    }
    this.baseUrl = (window as any).__CHAT_WIDGET_URL__ || 'http://localhost:3020'
  }

  init() {
    this.createContainer()
    this.createLauncher()
    this.setupMessageListener()
  }

  private createContainer() {
    this.container = document.createElement('div')
    this.container.id = 'chat-widget-container'
    this.container.style.cssText = `
      position: fixed;
      ${this.config.position === 'bottom-right' ? 'right: 20px;' : 'left: 20px;'}
      bottom: 20px;
      z-index: 999999;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    `
    document.body.appendChild(this.container)
  }

  private createLauncher() {
    const launcher = document.createElement('button')
    launcher.id = 'chat-widget-launcher'
    launcher.innerHTML = `
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
      </svg>
    `
    launcher.style.cssText = `
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--chat-accent, #2563eb);
      color: white;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transition: transform 0.2s, box-shadow 0.2s;
    `
    launcher.addEventListener('click', () => this.toggle())
    launcher.addEventListener('mouseenter', () => {
      launcher.style.transform = 'scale(1.05)'
      launcher.style.boxShadow = '0 6px 20px rgba(0,0,0,0.2)'
    })
    launcher.addEventListener('mouseleave', () => {
      launcher.style.transform = 'scale(1)'
      launcher.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)'
    })
    this.container!.appendChild(launcher)
  }

  private createIframe() {
    if (this.iframe) return

    const params = new URLSearchParams({
      workspace: this.config.workspace,
      theme: this.config.theme || 'auto',
      ...(this.config.userToken && { token: this.config.userToken })
    })

    this.iframe = document.createElement('iframe')
    this.iframe.src = `${this.baseUrl}/widget?${params.toString()}`
    this.iframe.style.cssText = `
      width: 380px;
      height: 520px;
      border: none;
      border-radius: 16px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.12);
      position: absolute;
      bottom: 70px;
      ${this.config.position === 'bottom-right' ? 'right: 0;' : 'left: 0;'}
      display: none;
      background: white;
    `
    this.container!.appendChild(this.iframe)
  }

  private setupMessageListener() {
    window.addEventListener('message', (event) => {
      if (event.origin !== this.baseUrl) return

      const { type, data } = event.data
      switch (type) {
        case 'chat:close':
          this.close()
          break
        case 'chat:message':
          this.dispatchEvent('message', data)
          break
        case 'chat:ready':
          this.dispatchEvent('ready', data)
          break
      }
    })
  }

  private dispatchEvent(name: string, detail: any) {
    const event = new CustomEvent(`chat:${name}`, { detail })
    window.dispatchEvent(event)
  }

  toggle() {
    if (this.isOpen) {
      this.close()
    } else {
      this.open()
    }
  }

  open() {
    this.createIframe()
    if (this.iframe) {
      this.iframe.style.display = 'block'
      this.isOpen = true
      this.dispatchEvent('open', {})
    }
  }

  close() {
    if (this.iframe) {
      this.iframe.style.display = 'none'
      this.isOpen = false
      this.dispatchEvent('close', {})
    }
  }
}

// Web Component
class ChatWidgetElement extends HTMLElement {
  private widget: ChatWidgetManager | null = null

  static get observedAttributes() {
    return ['workspace', 'user-token', 'position', 'theme']
  }

  connectedCallback() {
    const config: WidgetConfig = {
      workspace: this.getAttribute('workspace') || '',
      userToken: this.getAttribute('user-token') || undefined,
      position: (this.getAttribute('position') as 'bottom-right' | 'bottom-left') || 'bottom-right',
      theme: (this.getAttribute('theme') as 'light' | 'dark' | 'auto') || 'auto',
    }
    this.widget = new ChatWidgetManager(config)
    this.widget.init()
  }

  open() {
    this.widget?.open()
  }

  close() {
    this.widget?.close()
  }

  toggle() {
    this.widget?.toggle()
  }
}

// Register web component
customElements.define('chat-widget', ChatWidgetElement)

// Auto-init from script tag data attributes
document.addEventListener('DOMContentLoaded', () => {
  const script = document.querySelector('script[data-workspace]') as HTMLScriptElement
  if (script) {
    const widget = new ChatWidgetManager({
      workspace: script.dataset.workspace || '',
      userToken: script.dataset.userToken,
      position: (script.dataset.position as 'bottom-right' | 'bottom-left') || 'bottom-right',
      theme: (script.dataset.theme as 'light' | 'dark' | 'auto') || 'auto',
    })
    widget.init()
    ;(window as any).chatWidget = widget
  }
})

export { ChatWidgetManager, ChatWidgetElement }
