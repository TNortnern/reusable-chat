import { createApp, h } from 'vue'
import Widget from './components/Widget.vue'
import { generateStyles } from './styles'
import type { WidgetConfig } from './types'

declare global {
  interface Window {
    ChatWidget: typeof ChatWidgetManager
  }
}

class ChatWidgetManager {
  private app: ReturnType<typeof createApp> | null = null
  private container: HTMLDivElement | null = null
  private styleElement: HTMLStyleElement | null = null
  private config: WidgetConfig

  constructor(config: WidgetConfig) {
    this.config = {
      position: 'bottom-right',
      apiUrl: '',
      wsHost: '',
      wsPort: 443,
      wsKey: '',
      title: 'Chat with us',
      subtitle: 'We typically reply in a few minutes',
      placeholder: 'Type a message...',
      ...config,
    }
  }

  init(): void {
    if (this.container) return

    // Create shadow container for style isolation
    this.container = document.createElement('div')
    this.container.id = 'chat-widget-root'
    this.container.className = 'chat-widget-root'
    document.body.appendChild(this.container)

    // Inject styles
    this.styleElement = document.createElement('style')
    this.styleElement.textContent = generateStyles(this.config.theme)
    document.head.appendChild(this.styleElement)

    // Create Vue app
    this.app = createApp({
      render: () => h(Widget, { config: this.config }),
    })

    this.app.mount(this.container)
  }

  destroy(): void {
    if (this.app) {
      this.app.unmount()
      this.app = null
    }

    if (this.container) {
      this.container.remove()
      this.container = null
    }

    if (this.styleElement) {
      this.styleElement.remove()
      this.styleElement = null
    }
  }

  updateConfig(config: Partial<WidgetConfig>): void {
    this.config = { ...this.config, ...config }
    // Re-init with new config
    this.destroy()
    this.init()
  }
}

// Web Component wrapper
class ChatWidgetElement extends HTMLElement {
  private widget: ChatWidgetManager | null = null

  static get observedAttributes() {
    return [
      'workspace-id',
      'user-id',
      'user-token',
      'api-url',
      'ws-host',
      'ws-port',
      'ws-key',
      'position',
      'title',
      'subtitle',
    ]
  }

  connectedCallback() {
    const config: WidgetConfig = {
      workspaceId: this.getAttribute('workspace-id') || '',
      userId: this.getAttribute('user-id') || undefined,
      userToken: this.getAttribute('user-token') || undefined,
      apiUrl: this.getAttribute('api-url') || '',
      wsHost: this.getAttribute('ws-host') || '',
      wsPort: parseInt(this.getAttribute('ws-port') || '443', 10),
      wsKey: this.getAttribute('ws-key') || '',
      position: (this.getAttribute('position') as 'bottom-right' | 'bottom-left') || 'bottom-right',
      title: this.getAttribute('title') || 'Chat with us',
      subtitle: this.getAttribute('subtitle') || undefined,
    }

    this.widget = new ChatWidgetManager(config)
    this.widget.init()
  }

  disconnectedCallback() {
    this.widget?.destroy()
    this.widget = null
  }
}

// Register web component
if (!customElements.get('chat-widget')) {
  customElements.define('chat-widget', ChatWidgetElement)
}

// Auto-init from script tag data attributes
function autoInit() {
  const script = document.currentScript as HTMLScriptElement | null
  if (!script) return

  const config: WidgetConfig = {
    workspaceId: script.dataset.workspaceId || '',
    userId: script.dataset.userId,
    userToken: script.dataset.userToken,
    apiUrl: script.dataset.apiUrl || '',
    wsHost: script.dataset.wsHost || '',
    wsPort: parseInt(script.dataset.wsPort || '443', 10),
    wsKey: script.dataset.wsKey || '',
    position: (script.dataset.position as 'bottom-right' | 'bottom-left') || 'bottom-right',
    title: script.dataset.title,
    subtitle: script.dataset.subtitle,
    welcomeMessage: script.dataset.welcomeMessage,
  }

  if (config.workspaceId && config.apiUrl) {
    const widget = new ChatWidgetManager(config)

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => widget.init())
    } else {
      widget.init()
    }

    window.ChatWidget = ChatWidgetManager
    ;(window as any).chatWidget = widget
  }
}

autoInit()

export { ChatWidgetManager, ChatWidgetElement }
