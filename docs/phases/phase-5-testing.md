# Phase 5: Testing & Code Formatting

## Objective

Write comprehensive Pest tests for all chat functionality and ensure code passes all formatting checks.

## Tasks

### 5a. Tests — `tests/Feature/Chat/ChatTest.php`

Create with `php artisan make:test --pest Chat/ChatTest`.

**Test cases:**
1. Unauthenticated users are redirected from `/chat`
2. Authenticated users can view the chat index page
3. Users can create a new conversation by sending a message (use `ChatAssistant::fake()`)
4. Users can continue an existing conversation
5. Users can delete their own conversation
6. Users cannot view another user's conversation (403)
7. Users cannot delete another user's conversation (403)
8. Rate limiting returns 429 after exceeding limit
9. Message validation rejects empty messages
10. Message validation rejects messages over 4000 characters

**Mocking pattern:**
```php
use App\Ai\Agents\ChatAssistant;

ChatAssistant::fake(['This is a test response from the AI assistant.']);
```

### 5b. Code Formatting

**PHP:**
```bash
vendor/bin/pint --dirty --format agent
```

**JavaScript/Vue:**
```bash
npx prettier --write resources/js/pages/Chat/ resources/js/types/chat.ts
npx eslint --fix resources/js/pages/Chat/ resources/js/types/chat.ts
```

## Files Created
- `tests/Feature/Chat/ChatTest.php` (new)

## Verification
```bash
php artisan test --compact --filter=Chat
```
All tests pass with zero failures.
