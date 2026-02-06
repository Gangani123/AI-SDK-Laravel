# Phase 1: Install & Configure Laravel AI SDK

## Objective

Install the Laravel AI SDK package, publish its configuration, run migrations, and configure API keys for both OpenAI and Anthropic providers.

## Tasks

### 1.1 Install the package
```bash
composer require laravel/ai
```

### 1.2 Publish configuration
```bash
php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"
```
This creates `config/ai.php`.

### 1.3 Run migrations
```bash
php artisan migrate
```
Creates `agent_conversations` and `agent_conversation_messages` tables.

### 1.4 Configure environment variables
Add to `.env`:
```
OPENAI_API_KEY=your-actual-key
ANTHROPIC_API_KEY=your-actual-key
```

Add to `.env.example` (empty placeholders):
```
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
```

### 1.5 Configure default provider
Review and configure `config/ai.php` with default models and provider settings.

### 1.6 Install frontend dependencies
```bash
npm install marked dompurify @types/dompurify
```
- `marked` — Markdown to HTML conversion for AI responses
- `dompurify` — Sanitize HTML output to prevent XSS
- `@types/dompurify` — TypeScript type definitions

## Files Modified
- `.env`
- `.env.example`
- `config/ai.php`
- `package.json`
- `database/migrations/` (auto-created by SDK)

## Verification
- `php artisan migrate:status` shows AI SDK tables as migrated
- `config/ai.php` exists and is configured
- `npm ls marked dompurify` shows packages installed
