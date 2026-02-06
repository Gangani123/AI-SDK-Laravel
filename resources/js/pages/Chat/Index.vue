<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import DOMPurify from 'dompurify';
import { MessageSquare, Plus, Send, Trash2 } from 'lucide-vue-next';
import { marked } from 'marked';
import { nextTick, ref, watch } from 'vue';
import ChatController from '@/actions/App/Http/Controllers/ChatController';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import chat from '@/routes/chat';
import {
    type ActiveConversation,
    type BreadcrumbItem,
    type ChatMessage,
    type Conversation,
} from '@/types';

const props = defineProps<{
    conversations: Conversation[];
    activeConversation?: ActiveConversation;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Chat',
        href: chat.index.url(),
    },
];

const messageInput = ref('');
const isStreaming = ref(false);
const errorMessage = ref('');
const messagesContainer = ref<HTMLElement | null>(null);

const localMessages = ref<ChatMessage[]>([]);

watch(
    () => props.activeConversation,
    (conversation) => {
        localMessages.value = conversation?.messages
            ? [...conversation.messages]
            : [];
        errorMessage.value = '';
        scrollToBottom();
    },
    { immediate: true },
);

function getCsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function renderMarkdown(content: string): string {
    const html = marked.parse(content, { async: false }) as string;
    return DOMPurify.sanitize(html);
}

async function scrollToBottom(): Promise<void> {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
}

async function sendMessage(): Promise<void> {
    const message = messageInput.value.trim();
    if (!message || isStreaming.value) {
        return;
    }

    messageInput.value = '';
    errorMessage.value = '';
    isStreaming.value = true;

    localMessages.value.push({
        role: 'user',
        content: message,
        created_at: new Date().toISOString(),
    });

    localMessages.value.push({
        role: 'assistant',
        content: '',
        created_at: new Date().toISOString(),
    });

    await scrollToBottom();

    const url = props.activeConversation
        ? ChatController.store.url(props.activeConversation.id)
        : ChatController.store.url();

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'text/event-stream',
                'X-XSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify({ message }),
        });

        if (!response.ok) {
            throw new Error(`Request failed with status ${response.status}`);
        }

        const reader = response.body?.getReader();
        if (!reader) {
            throw new Error('No response body');
        }

        const decoder = new TextDecoder();
        const assistantIndex = localMessages.value.length - 1;

        while (true) {
            const { done, value } = await reader.read();
            if (done) {
                break;
            }

            const chunk = decoder.decode(value, { stream: true });
            localMessages.value[assistantIndex].content += chunk;
            await scrollToBottom();
        }
    } catch (error) {
        errorMessage.value =
            error instanceof Error
                ? error.message
                : 'Failed to get a response. Please try again.';

        if (localMessages.value.length > 0) {
            const last = localMessages.value[localMessages.value.length - 1];
            if (last.role === 'assistant' && last.content === '') {
                localMessages.value.pop();
            }
        }
    } finally {
        isStreaming.value = false;
        router.reload({ only: ['conversations', 'activeConversation'] });
    }
}

function deleteConversation(id: string): void {
    if (!confirm('Delete this conversation?')) {
        return;
    }
    router.delete(ChatController.destroy.url(id));
}

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}
</script>

<template>
    <Head title="Chat" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-[calc(100vh-8rem)] gap-0 overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"
        >
            <!-- Conversation Sidebar -->
            <div
                class="flex w-72 shrink-0 flex-col border-r border-sidebar-border/70 bg-sidebar dark:border-sidebar-border dark:bg-sidebar"
            >
                <div class="p-3">
                    <Link :href="chat.index()">
                        <Button variant="outline" class="w-full gap-2">
                            <Plus class="size-4" />
                            New Chat
                        </Button>
                    </Link>
                </div>

                <div class="flex-1 overflow-y-auto px-2 pb-2">
                    <div
                        v-if="conversations.length === 0"
                        class="px-3 py-8 text-center text-sm text-muted-foreground"
                    >
                        No conversations yet
                    </div>

                    <div
                        v-for="conv in conversations"
                        :key="conv.id"
                        class="group relative"
                    >
                        <Link
                            :href="chat.show(conv.id)"
                            class="flex items-center rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent"
                            :class="{
                                'bg-accent font-medium':
                                    activeConversation?.id === conv.id,
                            }"
                        >
                            <MessageSquare
                                class="mr-2 size-4 shrink-0 text-muted-foreground"
                            />
                            <span class="truncate">
                                {{ conv.title || 'New conversation' }}
                            </span>
                        </Link>
                        <button
                            class="absolute top-1/2 right-2 -translate-y-1/2 rounded p-1 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:text-destructive"
                            @click.prevent="deleteConversation(conv.id)"
                        >
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="flex flex-1 flex-col bg-background">
                <!-- Messages -->
                <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4">
                    <!-- Empty state -->
                    <div
                        v-if="localMessages.length === 0"
                        class="flex h-full flex-col items-center justify-center gap-3 text-muted-foreground"
                    >
                        <MessageSquare class="size-12 opacity-20" />
                        <p class="text-lg font-medium">
                            Start a new conversation
                        </p>
                        <p class="text-sm">
                            Type a message below to begin chatting with the AI
                            assistant.
                        </p>
                    </div>

                    <!-- Messages list -->
                    <div v-else class="mx-auto max-w-3xl space-y-4">
                        <div
                            v-for="(msg, index) in localMessages"
                            :key="index"
                            class="flex"
                            :class="
                                msg.role === 'user'
                                    ? 'justify-end'
                                    : 'justify-start'
                            "
                        >
                            <div
                                class="max-w-[80%] rounded-2xl px-4 py-2.5"
                                :class="
                                    msg.role === 'user'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted text-foreground'
                                "
                            >
                                <!-- User message -->
                                <p
                                    v-if="msg.role === 'user'"
                                    class="text-sm whitespace-pre-wrap"
                                >
                                    {{ msg.content }}
                                </p>

                                <!-- Assistant message with markdown -->
                                <div
                                    v-else-if="msg.content"
                                    class="prose prose-sm dark:prose-invert max-w-none [&_code]:rounded [&_code]:bg-background/50 [&_code]:px-1.5 [&_code]:py-0.5 [&_code]:text-xs [&_pre]:overflow-x-auto [&_pre]:rounded-lg [&_pre]:bg-background/50 [&_pre]:p-3"
                                    v-html="renderMarkdown(msg.content)"
                                />

                                <!-- Streaming indicator -->
                                <div
                                    v-else
                                    class="flex items-center gap-2 py-1"
                                >
                                    <Spinner class="size-3.5" />
                                    <span class="text-sm text-muted-foreground"
                                        >Thinking...</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error banner -->
                <div
                    v-if="errorMessage"
                    class="mx-4 mb-2 rounded-lg bg-destructive/10 px-4 py-2 text-sm text-destructive"
                >
                    {{ errorMessage }}
                </div>

                <!-- Input area -->
                <div
                    class="border-t border-sidebar-border/70 p-4 dark:border-sidebar-border"
                >
                    <div class="mx-auto flex max-w-3xl gap-2">
                        <textarea
                            v-model="messageInput"
                            :disabled="isStreaming"
                            rows="1"
                            class="flex-1 resize-none rounded-xl border border-input bg-background px-4 py-2.5 text-sm shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none disabled:opacity-50"
                            placeholder="Type your message..."
                            @keydown="handleKeydown"
                        />
                        <Button
                            :disabled="!messageInput.trim() || isStreaming"
                            class="shrink-0 self-end rounded-xl"
                            @click="sendMessage"
                        >
                            <Spinner v-if="isStreaming" class="size-4" />
                            <Send v-else class="size-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
