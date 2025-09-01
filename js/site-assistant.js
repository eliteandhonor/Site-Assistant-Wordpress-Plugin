/* global SiteAssistant, wp */
(() => {
  'use strict';
  function createEl(tag, options = {}) {
    const el = document.createElement(tag);
    if (options.classList) {
      options.classList.forEach(cls => el.classList.add(cls));
    }
    if (options.attrs) {
      Object.entries(options.attrs).forEach(([key, val]) => el.setAttribute(key, val));
    }
    if (options.text) {
      el.textContent = options.text;
    }
    return el;
  }
  function init() {
    const bubble = createEl('button', { classList: ['sa-chat-bubble'], text: '💬' });
    document.body.appendChild(bubble);
    const modal = createEl('div', { classList: ['sa-modal', 'sa-hidden'] });
    const header = createEl('div', { classList: ['sa-modal-header'] });
    header.textContent = 'Site Assistant';
    const closeBtn = createEl('button', { classList: ['sa-close-btn'], text: '×' });
    header.appendChild(closeBtn);
    modal.appendChild(header);
    const messages = createEl('div', { classList: ['sa-messages'] });
    modal.appendChild(messages);
    const inputWrapper = createEl('div', { classList: ['sa-input-wrapper'] });
    const textarea = createEl('textarea', { classList: ['sa-input'], attrs: { placeholder: 'Type your message…' } });
    const sendBtn = createEl('button', { classList: ['sa-send-btn'], text: 'Send' });
    inputWrapper.appendChild(textarea);
    inputWrapper.appendChild(sendBtn);
    modal.appendChild(inputWrapper);
    document.body.appendChild(modal);
    let conversation = [];
    try {
      const saved = localStorage.getItem('saConversation');
      if (saved) {
        conversation = JSON.parse(saved);
        conversation.forEach(msg => appendMessage(messages, msg.role, msg.content));
      }
    } catch (e) {
      console.warn('Failed to load conversation:', e);
    }
    bubble.addEventListener('click', () => {
      modal.classList.toggle('sa-hidden');
    });
    closeBtn.addEventListener('click', () => {
      modal.classList.add('sa-hidden');
    });
    sendBtn.addEventListener('click', () => {
      const content = textarea.value.trim();
      if (!content) return;
      textarea.value = '';
      sendMessage(messages, content, conversation);
    });
    textarea.addEventListener('keypress', event => {
      if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendBtn.click();
      }
    });
    document.documentElement.style.setProperty('--sa-primary', SiteAssistant.primaryColour);
    document.documentElement.style.setProperty('--sa-accent', SiteAssistant.accentColour);
  }
  function appendMessage(container, role, content) {
    const msg = createEl('div', { classList: ['sa-message', `sa-${role}`] });
    msg.textContent = content;
    container.appendChild(msg);
    container.scrollTop = container.scrollHeight;
  }
  function sendMessage(container, content, conversation) {
    appendMessage(container, 'user', content);
    conversation.push({ role: 'user', content });
    localStorage.setItem('saConversation', JSON.stringify(conversation));
    const body = { messages: conversation };
    fetch(SiteAssistant.restUrl + 'chat', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': SiteAssistant.nonce,
      },
      body: JSON.stringify(body),
    })
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          appendMessage(container, 'assistant', data.error);
          return;
        }
        const reply = data.choices && data.choices[0] && data.choices[0].message ? data.choices[0].message.content : '…';
        appendMessage(container, 'assistant', reply);
        conversation.push({ role: 'assistant', content: reply });
        localStorage.setItem('saConversation', JSON.stringify(conversation));
        fetch(SiteAssistant.restUrl + 'save_conversation', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': SiteAssistant.nonce,
          },
          body: JSON.stringify({ conversation }),
        });
      })
      .catch(err => {
        console.error('Chat error:', err);
        appendMessage(container, 'assistant', 'An error occurred.');
      });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
