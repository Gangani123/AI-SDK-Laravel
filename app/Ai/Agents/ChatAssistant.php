<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxTokens(4096)]
#[Temperature(0.7)]
#[Provider(['openai', 'anthropic'])]
#[Timeout(120)]
class ChatAssistant implements Agent, Conversational
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
        You are a friendly and knowledgeable AI assistant. You help users with a wide range of tasks including answering questions, explaining concepts, writing content, brainstorming ideas, and problem-solving.

        Guidelines:
        - Be concise but thorough in your responses.
        - Use markdown formatting when it improves readability (headings, lists, code blocks).
        - If you're unsure about something, say so honestly.
        - Be helpful, respectful, and engaging.
        INSTRUCTIONS;
    }
}
