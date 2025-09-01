# Site Assistant

Site Assistant is a WordPress plugin that adds a floating AI chatbot and voice message recorder to every page. It provides a simple UI for visitors to ask questions or leave voice messages and stores conversations for analysis.

## Features

- Floating chat bubble and modal for interacting with the AI assistant.
- Voice message recorder with email notifications.
- Customisable colours and admin email via settings page.
- REST API endpoints to send messages, upload voice recordings, view stats, save conversations and analyse conversations.
- Conversation logs stored in WordPress options (Phase 1 foundation).

## Installation

1. Download this repository as a ZIP or clone it.
2. Copy the `site-assistant` folder to your `wp-content/plugins` directory.
3. Activate the **Site Assistant** plugin via the WordPress admin.
4. Navigate to **Settings → Site Assistant** to configure the admin email and colours.

## Usage

- A chat bubble will appear on every front‑end page. Click it to open the chat modal and start chatting with the AI assistant. Messages are persisted in local storage and stored on the server for analysis.
- To record a voice message, speak into the microphone (feature to be added in Phase 2). The recording will be saved to `wp-content/uploads/site-assistant` and emailed to the configured admin.
- Use the REST API endpoints to integrate the assistant with other systems:
  - `POST /site-assistant/v1/chat` – send chat messages and receive AI responses.
  - `POST /site-assistant/v1/voice` – upload voice recordings.
  - `POST /site-assistant/v1/stats` – view or reset plugin stats.
  - `POST /site-assistant/v1/save_conversation` – save conversation history.
  - `POST /site-assistant/v1/analyze_conversation` – summarise a conversation.
  - `POST /site-assistant/v1/analyze_all_conversations` – summarise all stored conversations.

## Development

The plugin consists of several files:

- `site-assistant.php` – Main plugin bootstrap file. Defines constants and initialises the plugin.
- `includes/class-site-assistant.php` – Core class that registers settings, enqueues assets and defines REST API routes and handlers.
- `js/site-assistant.js` – Front‑end script that handles the chat UI, sends messages to the API and saves conversations.
- `css/site-assistant.css` – Styling for the chat bubble and modal.
- `tests/test-site-assistant.php` – PHPUnit tests to verify REST route registration and settings sanitisation.
- `Project Overview.txt` – Project requirements and design notes.

### Security Notes

- REST API routes are public; validate and sanitise all inputs.
- Voice recordings are saved in the uploads directory; ensure correct file permissions and avoid path traversal attacks.
- Conversation logs are stored in WordPress options in Phase 1; consider moving to a custom table for scalability and privacy.
- Remote calls to the completions endpoint have a 30 second timeout; consider asynchronous requests for longer tasks.

## Limitations & Next Steps

This is a Phase 1 foundation. It lacks the leave message UI, document upload management, advanced analytics, multilingual support and proactive prompts described in later phases. Upcoming improvements include:

- Implementing a dedicated “Leave Message” UI with new conversation flow.
- Supporting document uploads and local file indexing for better answers.
- Adding advanced analytics and visual reports.
- Using a custom database table for conversations and adding retention policies.
- Adding dark‑mode toggle, mobile UX refinements, and accessibility (ARIA labels).
- Improving summarisation endpoints with richer analysis and batching.

Contributions are welcome; please open issues or pull requests to discuss improvements.
