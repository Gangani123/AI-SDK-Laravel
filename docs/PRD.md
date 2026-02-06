# Product Requirements Document: AI Chatbot

## 1. Overview

Add an AI-powered chatbot to the Laravel starter kit using the Laravel AI SDK. The chatbot demonstrates real-time streaming, conversation memory, and automatic provider failover — serving as a showcase of Laravel AI SDK capabilities.

## 2. Goals

- Provide an interactive AI assistant accessible from the app sidebar
- Demonstrate streaming responses (word-by-word in real-time)
- Store and retrieve conversation history per user
- Automatically fail over between OpenAI and Anthropic providers
- Follow all existing project conventions (Inertia, Wayfinder, Pest, Tailwind)

## 3. User Stories

1. **As a user**, I can click "Chat" in the sidebar to open the AI chatbot page.
2. **As a user**, I can start a new conversation by typing a message and clicking send.
3. **As a user**, I can see the AI response appear word-by-word in real-time (streaming).
4. **As a user**, I can view a list of my past conversations in the left sidebar.
5. **As a user**, I can click a past conversation to reload its full message history.
6. **As a user**, I can delete a conversation I no longer need.
7. **As a user**, my conversations are private — no other user can see them.
8. **As a user**, if one AI provider is unavailable, the system automatically uses the other.

## 4. Feature Requirements

### 4.1 Chat Interface
- Two-panel layout: conversation list (left), chat area (right)
- "New Chat" button to start fresh conversations
- Message bubbles: user (right-aligned), AI (left-aligned)
- AI responses rendered as markdown with safe HTML
- Auto-scroll to latest message
- Input field with send button, disabled during streaming
- Streaming indicator while AI is responding
- Empty state when no conversation is selected
- Error state when both providers fail

### 4.2 Conversation Management
- Conversations stored in database (Laravel AI SDK tables)
- List sorted by most recent activity
- Each conversation shows a title/preview and timestamp
- Delete with confirmation
- Conversations scoped to authenticated user only

### 4.3 AI Agent
- General-purpose assistant personality
- Conversational memory (remembers context within a conversation)
- Provider failover: OpenAI -> Anthropic
- Max 4096 tokens per response
- Temperature: 0.7

### 4.4 Security & Performance
- Rate limiting: 20 messages per minute per user
- Form validation on message input (required, max 4000 chars)
- Authorization policy ensuring conversation ownership
- CSRF protection on all POST/DELETE requests

## 5. Technical Requirements

- **Backend:** Laravel AI SDK (`laravel/ai`), ChatAssistant agent, ChatController
- **Frontend:** Vue 3 page with native `fetch()` streaming, `marked` + `DOMPurify` for markdown
- **Database:** SQLite (existing), AI SDK migration tables
- **Providers:** OpenAI (primary), Anthropic (fallback)
- **Testing:** Pest tests with `ChatAssistant::fake()`

## 6. Acceptance Criteria

- [ ] Chat link appears in sidebar navigation
- [ ] Users can send messages and receive streamed AI responses
- [ ] Conversation history is persisted and loadable
- [ ] Conversations are scoped to the authenticated user
- [ ] Deleting a conversation removes it from the list
- [ ] Rate limiting prevents abuse (20 req/min)
- [ ] All Pest tests pass
- [ ] Code passes Pint, Prettier, and ESLint checks

## 7. Out of Scope

- File/image upload to AI
- Audio transcription or text-to-speech
- Embeddings or RAG (document Q&A)
- Admin panel for managing all conversations
- Model/provider selection UI (hardcoded failover)
- WebSocket-based streaming (using HTTP streaming instead)
