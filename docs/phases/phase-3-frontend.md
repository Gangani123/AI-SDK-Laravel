# Phase 3: Frontend — Chat Page with Streaming

## Objective

Build the Vue chat page with a two-panel layout, real-time streaming, markdown rendering, and conversation management UI.

## Tasks

### 3a. TypeScript Types — `resources/js/types/chat.ts`
```typescript
export type Conversation = {
    id: number;
    title: string | null;
    updated_at: string;
};

export type Message = {
    role: 'user' | 'assistant';
    content: string;
    created_at: string;
};
```
Export from `resources/js/types/index.ts`.

### 3b. Chat Page — `resources/js/pages/Chat/Index.vue`

**Props from controller:**
```typescript
interface Props {
    conversations: Conversation[];
    activeConversation?: {
        id: number;
        messages: Message[];
    };
}
```

**Left panel (conversation sidebar):**
- "New Chat" button at top
- Scrollable list of conversations (title, relative time)
- Active conversation highlighted with background color
- Delete button (trash icon) on hover
- Empty state when no conversations exist

**Right panel (chat area):**
- Message thread with scroll container
- User messages: right-aligned, colored bubble
- AI messages: left-aligned, rendered as markdown (marked + DOMPurify)
- Auto-scroll to bottom on new messages
- Text input (textarea) + send button at bottom
- Send button disabled while streaming
- Streaming indicator (pulsing dots) while AI responds
- Empty state: centered message "Start a new conversation"
- Error state: alert banner when request fails

**Streaming implementation:**
```typescript
const response = await fetch('/chat/' + conversationId, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'text/event-stream',
    },
    body: JSON.stringify({ message: userMessage }),
});

const reader = response.body.getReader();
const decoder = new TextDecoder();

while (true) {
    const { done, value } = await reader.read();
    if (done) break;
    const chunk = decoder.decode(value);
    // Append chunk to AI message content
}
```

After stream completes, reload conversation list via `router.reload({ only: ['conversations'] })`.

**Components used:** Button, Input, Separator, Avatar, Skeleton, Spinner

## Files Created/Modified
- `resources/js/types/chat.ts` (new)
- `resources/js/types/index.ts` (modified — add export)
- `resources/js/pages/Chat/Index.vue` (new)

## Verification
- Page renders without errors at `/chat`
- Can type a message and see streaming response
- Markdown formatted responses render correctly
- Auto-scroll works on new messages
- Conversation list updates after sending a message
