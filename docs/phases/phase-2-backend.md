# Phase 2: Backend — Agent, Controller, Routes, Validation, Authorization

## Objective

Build all backend components: the AI agent class, chat controller, form request validation, authorization policy, rate limiting, and route definitions.

## Tasks

### 2a. AI Agent — `app/Ai/Agents/ChatAssistant.php`
- Create via `php artisan make:agent ChatAssistant`
- Implements `Agent`, `Conversational`
- Uses `Promptable`, `RemembersConversations` traits
- General-purpose assistant instructions
- Provider failover: `['openai', 'anthropic']`
- Attributes: `#[MaxTokens(4096)]`, `#[Temperature(0.7)]`

### 2b. Form Request — `app/Http/Requests/Chat/SendMessageRequest.php`
- Create via `php artisan make:request Chat/SendMessageRequest`
- Rules: `message` => required, string, max:4000
- Follow project pattern: array-based rules, explicit return types

### 2c. Authorization Policy — `app/Policies/ConversationPolicy.php`
- Create manually (no policies exist yet in project)
- Methods: `view(User, conversation)`, `update(User, conversation)`, `delete(User, conversation)`
- All check `$user->id === $conversation->user_id`
- Register in `AppServiceProvider`

### 2d. Rate Limiting
- Add to `AppServiceProvider::boot()`:
  ```php
  RateLimiter::for('chat', function (Request $request) {
      return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
  });
  ```
- Apply `throttle:chat` middleware to the store route

### 2e. Controller — `app/Http/Controllers/ChatController.php`

| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/chat` | List conversations, render Chat/Index |
| `show($conversation)` | GET `/chat/{conversation}` | Load conversation with messages |
| `store(SendMessageRequest)` | POST `/chat/{conversation?}` | Stream AI response |
| `destroy($conversation)` | DELETE `/chat/{conversation}` | Delete conversation |

- `store()`: New conversation uses `forUser()->stream()`, existing uses `continue()->stream()`
- `show()` and `destroy()` authorize via policy
- Catch provider failures and return error response

### 2f. Routes — `routes/chat.php`
```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{conversation?}', [ChatController::class, 'store'])->name('chat.store')->middleware('throttle:chat');
    Route::delete('chat/{conversation}', [ChatController::class, 'destroy'])->name('chat.destroy');
});
```
Require from `routes/web.php`.

## Files Created/Modified
- `app/Ai/Agents/ChatAssistant.php` (new)
- `app/Http/Controllers/ChatController.php` (new)
- `app/Http/Requests/Chat/SendMessageRequest.php` (new)
- `app/Policies/ConversationPolicy.php` (new)
- `app/Providers/AppServiceProvider.php` (modified — rate limiter + policy)
- `routes/chat.php` (new)
- `routes/web.php` (modified — add require)

## Verification
- `php artisan route:list --path=chat` shows all 4 chat routes
- No PHP syntax errors: `php artisan about` runs cleanly
