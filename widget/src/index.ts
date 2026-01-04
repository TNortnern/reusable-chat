import { ReusableChat } from './reusable-chat'
import { ReusableChatInline } from './reusable-chat-inline'

// Register custom elements
if (!customElements.get('reusable-chat')) {
  customElements.define('reusable-chat', ReusableChat)
}

if (!customElements.get('reusable-chat-inline')) {
  customElements.define('reusable-chat-inline', ReusableChatInline)
}

// Export for programmatic use
export { ReusableChat, ReusableChatInline }

// Auto-init any existing elements
document.querySelectorAll('reusable-chat, reusable-chat-inline').forEach(el => {
  // Elements will auto-initialize via connectedCallback
})

console.log('[Reusable Chat] Widget loaded v1.0.0')
