import type { WidgetTheme } from './types'

export function getDefaultTheme(): Required<WidgetTheme> {
  return {
    primaryColor: '#667eea',
    textColor: '#1f2937',
    backgroundColor: '#ffffff',
    headerColor: '#667eea',
    headerTextColor: '#ffffff',
    buttonColor: '#667eea',
    buttonTextColor: '#ffffff',
    fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
    borderRadius: '16px',
  }
}

export function generateStyles(theme: WidgetTheme = {}): string {
  const t = { ...getDefaultTheme(), ...theme }

  return `
    .chat-widget-root {
      --cw-primary: ${t.primaryColor};
      --cw-text: ${t.textColor};
      --cw-bg: ${t.backgroundColor};
      --cw-header: ${t.headerColor};
      --cw-header-text: ${t.headerTextColor};
      --cw-button: ${t.buttonColor};
      --cw-button-text: ${t.buttonTextColor};
      --cw-font: ${t.fontFamily};
      --cw-radius: ${t.borderRadius};

      font-family: var(--cw-font);
      font-size: 14px;
      line-height: 1.5;
      color: var(--cw-text);
      box-sizing: border-box;
    }

    .chat-widget-root *,
    .chat-widget-root *::before,
    .chat-widget-root *::after {
      box-sizing: inherit;
    }

    /* Chat Button */
    .cw-button {
      position: fixed;
      bottom: 20px;
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--cw-button);
      color: var(--cw-button-text);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transition: transform 0.2s, box-shadow 0.2s;
      z-index: 999998;
    }

    .cw-button:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .cw-button.bottom-right {
      right: 20px;
    }

    .cw-button.bottom-left {
      left: 20px;
    }

    .cw-button svg {
      width: 24px;
      height: 24px;
    }

    .cw-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      background: #ef4444;
      color: white;
      font-size: 11px;
      font-weight: 600;
      min-width: 18px;
      height: 18px;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0 4px;
    }

    /* Chat Window */
    .cw-window {
      position: fixed;
      bottom: 90px;
      width: 380px;
      height: 520px;
      background: var(--cw-bg);
      border-radius: var(--cw-radius);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      display: flex;
      flex-direction: column;
      overflow: hidden;
      z-index: 999999;
      opacity: 0;
      transform: translateY(20px) scale(0.95);
      transition: opacity 0.2s, transform 0.2s;
      pointer-events: none;
    }

    .cw-window.open {
      opacity: 1;
      transform: translateY(0) scale(1);
      pointer-events: auto;
    }

    .cw-window.bottom-right {
      right: 20px;
    }

    .cw-window.bottom-left {
      left: 20px;
    }

    @media (max-width: 420px) {
      .cw-window {
        width: calc(100vw - 20px);
        height: calc(100vh - 100px);
        bottom: 80px;
        right: 10px !important;
        left: 10px !important;
        border-radius: 12px;
      }
    }

    /* Header */
    .cw-header {
      background: var(--cw-header);
      color: var(--cw-header-text);
      padding: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .cw-header-info h3 {
      margin: 0;
      font-size: 16px;
      font-weight: 600;
    }

    .cw-header-info p {
      margin: 4px 0 0;
      font-size: 12px;
      opacity: 0.85;
    }

    .cw-close-btn {
      background: none;
      border: none;
      color: inherit;
      cursor: pointer;
      padding: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0.8;
      transition: opacity 0.2s;
    }

    .cw-close-btn:hover {
      opacity: 1;
    }

    .cw-status-dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #fbbf24;
      display: inline-block;
      margin-right: 6px;
    }

    .cw-status-dot.connected {
      background: #34d399;
      animation: cw-pulse 2s infinite;
    }

    @keyframes cw-pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    /* Messages Area */
    .cw-messages {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .cw-empty {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      color: #9ca3af;
      text-align: center;
      padding: 20px;
    }

    .cw-empty svg {
      width: 48px;
      height: 48px;
      margin-bottom: 12px;
      opacity: 0.5;
    }

    .cw-welcome {
      background: #f3f4f6;
      padding: 12px 16px;
      border-radius: 12px;
      margin-bottom: 8px;
    }

    /* Message */
    .cw-message {
      display: flex;
      gap: 10px;
      max-width: 85%;
    }

    .cw-message.own {
      align-self: flex-end;
      flex-direction: row-reverse;
    }

    .cw-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--cw-primary) 0%, #764ba2 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 12px;
      flex-shrink: 0;
    }

    .cw-message.own .cw-avatar {
      background: linear-gradient(135deg, #34d399 0%, #059669 100%);
    }

    .cw-bubble {
      background: #f3f4f6;
      padding: 10px 14px;
      border-radius: 16px;
      border-top-left-radius: 4px;
    }

    .cw-message.own .cw-bubble {
      background: var(--cw-primary);
      color: white;
      border-top-left-radius: 16px;
      border-top-right-radius: 4px;
    }

    .cw-msg-header {
      display: flex;
      gap: 8px;
      align-items: center;
      margin-bottom: 4px;
    }

    .cw-sender {
      font-weight: 600;
      font-size: 12px;
    }

    .cw-time {
      font-size: 10px;
      color: #9ca3af;
    }

    .cw-message.own .cw-time {
      color: rgba(255, 255, 255, 0.7);
    }

    .cw-msg-text {
      font-size: 14px;
      word-break: break-word;
    }

    /* Attachments */
    .cw-attachments {
      margin-top: 8px;
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .cw-attach-img {
      max-width: 180px;
      max-height: 120px;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.2s;
    }

    .cw-attach-img:hover {
      transform: scale(1.02);
    }

    .cw-attach-file {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 8px 12px;
      background: rgba(0, 0, 0, 0.05);
      border-radius: 6px;
      color: inherit;
      text-decoration: none;
      font-size: 12px;
    }

    .cw-message.own .cw-attach-file {
      background: rgba(255, 255, 255, 0.2);
    }

    /* Typing Indicator */
    .cw-typing {
      font-size: 12px;
      color: #6b7280;
      font-style: italic;
      padding: 4px 0;
    }

    /* File Preview */
    .cw-file-preview {
      padding: 8px 16px;
      border-top: 1px solid #e5e7eb;
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .cw-preview-item {
      position: relative;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 12px;
      background: #f3f4f6;
      border-radius: 8px;
      max-width: 180px;
    }

    .cw-preview-thumb {
      width: 40px;
      height: 40px;
      object-fit: cover;
      border-radius: 4px;
    }

    .cw-preview-icon {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #e5e7eb;
      border-radius: 4px;
      color: #6b7280;
    }

    .cw-preview-name {
      font-size: 11px;
      color: #374151;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 80px;
    }

    .cw-preview-remove {
      position: absolute;
      top: -6px;
      right: -6px;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background: #ef4444;
      color: white;
      border: none;
      cursor: pointer;
      font-size: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }

    /* Input Area */
    .cw-input-area {
      padding: 12px 16px;
      border-top: 1px solid #e5e7eb;
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .cw-icon-btn {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: transparent;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #6b7280;
      transition: background 0.2s, color 0.2s;
      flex-shrink: 0;
    }

    .cw-icon-btn:hover {
      background: #f3f4f6;
      color: var(--cw-primary);
    }

    .cw-input {
      flex: 1;
      padding: 10px 16px;
      border: 1px solid #e5e7eb;
      border-radius: 20px;
      font-size: 14px;
      outline: none;
      transition: border-color 0.2s;
      font-family: inherit;
    }

    .cw-input:focus {
      border-color: var(--cw-primary);
    }

    .cw-input::placeholder {
      color: #9ca3af;
    }

    .cw-send-btn {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: var(--cw-button);
      color: var(--cw-button-text);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s, opacity 0.2s;
      flex-shrink: 0;
    }

    .cw-send-btn:hover:not(:disabled) {
      transform: scale(1.05);
    }

    .cw-send-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .cw-hidden-input {
      display: none;
    }

    /* Emoji Picker */
    .cw-emoji-container {
      position: relative;
    }

    .cw-emoji-picker {
      position: absolute;
      bottom: 45px;
      left: 0;
      background: white;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      padding: 10px;
      z-index: 100;
      width: 260px;
    }

    .cw-emoji-grid {
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 2px;
    }

    .cw-emoji-btn {
      width: 28px;
      height: 28px;
      border: none;
      background: transparent;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s;
    }

    .cw-emoji-btn:hover {
      background: #f3f4f6;
    }

    /* Loading State */
    .cw-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .cw-spinner {
      width: 24px;
      height: 24px;
      border: 2px solid #e5e7eb;
      border-top-color: var(--cw-primary);
      border-radius: 50%;
      animation: cw-spin 0.8s linear infinite;
    }

    @keyframes cw-spin {
      to { transform: rotate(360deg); }
    }

    /* Error State */
    .cw-error {
      padding: 16px;
      background: #fef2f2;
      color: #dc2626;
      text-align: center;
      font-size: 13px;
    }
  `
}
