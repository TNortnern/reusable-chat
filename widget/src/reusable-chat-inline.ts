import { LitElement, html, css } from 'lit'
import { customElement, property } from 'lit/decorators.js'
import baseStyles from './styles/base.css?inline'

@customElement('reusable-chat-inline')
export class ReusableChatInline extends LitElement {
  static styles = css`${baseStyles}`

  @property({ attribute: 'api-key' }) apiKey = ''
  @property({ attribute: 'user-id' }) userId = ''
  @property({ attribute: 'user-name' }) userName = ''
  @property({ attribute: 'conversation-id' }) conversationId = ''

  render() {
    return html`
      <div style="padding: 20px; text-align: center; color: var(--rc-text-secondary);">
        Inline chat widget (conversation: ${this.conversationId || 'not set'})
      </div>
    `
  }
}

export default ReusableChatInline
