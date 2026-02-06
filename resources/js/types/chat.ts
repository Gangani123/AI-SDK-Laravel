export type Conversation = {
    id: string;
    title: string | null;
    updated_at: string;
};

export type ChatMessage = {
    role: 'user' | 'assistant';
    content: string;
    created_at: string;
};

export type ActiveConversation = {
    id: string;
    messages: ChatMessage[];
};
