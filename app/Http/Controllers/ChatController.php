<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ChatAssistant;
use App\Http\Requests\Chat\SendMessageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    /**
     * Show the chat page with conversation list.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Chat/Index', [
            'conversations' => $this->getConversations($request),
        ]);
    }

    /**
     * Show a specific conversation.
     */
    public function show(Request $request, string $conversation): Response
    {
        $this->authorizeConversation($request, $conversation);

        $messages = DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversation)
            ->whereIn('role', ['user', 'assistant'])
            ->orderBy('created_at')
            ->get(['role', 'content', 'created_at']);

        return Inertia::render('Chat/Index', [
            'conversations' => $this->getConversations($request),
            'activeConversation' => [
                'id' => $conversation,
                'messages' => $messages->map(fn ($m) => [
                    'role' => $m->role,
                    'content' => $m->content,
                    'created_at' => $m->created_at,
                ])->values()->all(),
            ],
        ]);
    }

    /**
     * Send a message and stream the AI response.
     */
    public function store(SendMessageRequest $request, ?string $conversation = null): mixed
    {
        $user = $request->user();
        $agent = new ChatAssistant;

        if ($conversation) {
            $this->authorizeConversation($request, $conversation);

            return $agent
                ->continue($conversation, as: $user)
                ->stream($request->validated('message'));
        }

        return $agent
            ->forUser($user)
            ->stream($request->validated('message'));
    }

    /**
     * Delete a conversation.
     */
    public function destroy(Request $request, string $conversation): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeConversation($request, $conversation);

        DB::table('agent_conversations')
            ->where('id', $conversation)
            ->delete();

        DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversation)
            ->delete();

        return to_route('chat.index');
    }

    /**
     * Get conversations for the authenticated user.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getConversations(Request $request): array
    {
        return DB::table('agent_conversations')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->get(['id', 'title', 'updated_at'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'updated_at' => $c->updated_at,
            ])
            ->all();
    }

    /**
     * Authorize that the conversation belongs to the authenticated user.
     */
    protected function authorizeConversation(Request $request, string $conversation): void
    {
        $exists = DB::table('agent_conversations')
            ->where('id', $conversation)
            ->where('user_id', $request->user()->id)
            ->exists();

        if (! $exists) {
            abort(403, 'Unauthorized access to conversation.');
        }
    }
}
