# Boost - Laravel AI Chatbot Starter Kit

A Laravel 12 starter kit featuring an AI-powered chatbot with real-time streaming, conversation history, and automatic provider failover between OpenAI and Anthropic.

Built with **Laravel 12**, **Vue 3**, **Inertia.js v2**, **Tailwind CSS v4**, and the **Laravel AI SDK**.

## Features

- **AI Chatbot** with real-time streaming responses (word-by-word)
- **Conversation Memory** - the AI remembers context within each conversation
- **Provider Failover** - automatically switches between OpenAI and Anthropic
- **Conversation History** - sidebar list of past conversations, click to reload
- **Markdown Rendering** - AI responses rendered as safe HTML with syntax highlighting
- **Rate Limiting** - 20 messages per minute per user
- **Authorization** - conversations are scoped to the authenticated user
- **Two-Factor Authentication** - TOTP-based 2FA via Laravel Fortify
- **Dark Mode** - full dark mode support

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12, PHP 8.4 |
| AI | Laravel AI SDK (`laravel/ai`) |
| Frontend | Vue 3, Inertia.js v2 |
| Styling | Tailwind CSS v4 |
| Auth | Laravel Fortify |
| Routes | Laravel Wayfinder (type-safe TypeScript routes) |
| Database | SQLite |
| Testing | Pest 3 |
| Formatting | Pint, Prettier, ESLint |

## Requirements

- PHP >= 8.4
- Node.js >= 18
- Composer
- An OpenAI API key and/or Anthropic API key

## Installation

```bash
# Clone the repository
git clone <repository-url> boost
cd boost

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Add your API keys to .env
# OPENAI_API_KEY=your-openai-key
# ANTHROPIC_API_KEY=your-anthropic-key

# Run migrations
php artisan migrate

# Build frontend assets
npm run build
```

## Running the Application

```bash
# Start all services (server, queue, logs, vite)
composer run dev
```

This starts:
- **Laravel server** at http://127.0.0.1:8000
- **Vite dev server** at http://localhost:5173
- **Queue worker** for background jobs
- **Log viewer** via Laravel Pail

Then open http://127.0.0.1:8000 in your browser.

## Usage

1. Register an account or log in
2. Click **Chat** in the sidebar
3. Type a message and press **Enter** or click the send button
4. Watch the AI response stream in real-time
5. Your conversations are saved and accessible from the sidebar

## Project Structure

```
app/
  Ai/Agents/ChatAssistant.php       # AI agent with conversation memory
  Http/Controllers/ChatController.php # Chat CRUD + streaming
  Http/Requests/Chat/SendMessageRequest.php # Validation
  Providers/AppServiceProvider.php   # Rate limiting config

resources/js/
  pages/Chat/Index.vue               # Chat page (two-panel layout)
  types/chat.ts                      # TypeScript types
  components/AppSidebar.vue          # Sidebar with Chat link

routes/
  chat.php                           # Chat routes (index, show, store, destroy)
  web.php                            # Main routes

tests/Feature/Chat/ChatTest.php      # 11 Pest tests

docs/                                # Documentation & tracking
  PRD.md                             # Product requirements
  phases/                            # Phase-wise implementation guides
  STATUS.md                          # Task status dashboard
  HISTORY.md                         # Implementation history
  AI_ACTIVITY.md                     # AI activity log
```

## Testing

```bash
# Run all tests
php artisan test --compact

# Run only chat tests
php artisan test --compact --filter=Chat

# Run with code formatting check
composer test
```

## Code Formatting

```bash
# PHP (Pint)
vendor/bin/pint

# JavaScript/Vue (Prettier)
npm run format

# Lint
npm run lint
```

## Configuration

### AI Providers

The chatbot uses OpenAI as the primary provider with Anthropic as a fallback. Configure in `.env`:

```env
OPENAI_API_KEY=your-openai-key
ANTHROPIC_API_KEY=your-anthropic-key
```

Provider settings can be customized in `config/ai.php`.

### Rate Limiting

The chat endpoint is rate-limited to 20 requests per minute per user. This can be adjusted in `app/Providers/AppServiceProvider.php`.

## License

MIT
