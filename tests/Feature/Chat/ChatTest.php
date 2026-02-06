<?php

use App\Ai\Agents\ChatAssistant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('guests are redirected to the login page', function () {
    $this->get(route('chat.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view the chat index', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('chat.index'))
        ->assertOk();
});

test('authenticated users can view a conversation', function () {
    $user = User::factory()->create();
    $conversationId = createConversation($user);

    $this->actingAs($user)
        ->get(route('chat.show', $conversationId))
        ->assertOk();
});

test('users cannot view another users conversation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $conversationId = createConversation($otherUser);

    $this->actingAs($user)
        ->get(route('chat.show', $conversationId))
        ->assertForbidden();
});

test('users can send a message and receive a response', function () {
    ChatAssistant::fake(['Hello! How can I help you today?']);

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('chat.store'), [
            'message' => 'Hello, AI!',
        ]);

    $response->assertOk();

    ChatAssistant::assertPrompted(fn ($prompt) => $prompt->contains('Hello, AI!'));
});

test('users can continue an existing conversation', function () {
    ChatAssistant::fake(['I remember our conversation!']);

    $user = User::factory()->create();
    $conversationId = createConversation($user);

    $response = $this->actingAs($user)
        ->post(route('chat.store', $conversationId), [
            'message' => 'Continue our chat',
        ]);

    $response->assertOk();

    ChatAssistant::assertPrompted(fn ($prompt) => $prompt->contains('Continue our chat'));
});

test('users cannot send messages to another users conversation', function () {
    ChatAssistant::fake();

    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $conversationId = createConversation($otherUser);

    $this->actingAs($user)
        ->post(route('chat.store', $conversationId), [
            'message' => 'Trying to hijack',
        ])
        ->assertForbidden();
});

test('users can delete their own conversation', function () {
    $user = User::factory()->create();
    $conversationId = createConversation($user);

    $this->actingAs($user)
        ->delete(route('chat.destroy', $conversationId))
        ->assertRedirect(route('chat.index'));

    expect(
        DB::table('agent_conversations')->where('id', $conversationId)->exists()
    )->toBeFalse();
});

test('users cannot delete another users conversation', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $conversationId = createConversation($otherUser);

    $this->actingAs($user)
        ->delete(route('chat.destroy', $conversationId))
        ->assertForbidden();

    expect(
        DB::table('agent_conversations')->where('id', $conversationId)->exists()
    )->toBeTrue();
});

test('message validation rejects empty messages', function () {
    ChatAssistant::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('chat.store'), [
            'message' => '',
        ])
        ->assertSessionHasErrors('message');
});

test('message validation rejects messages over 4000 characters', function () {
    ChatAssistant::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('chat.store'), [
            'message' => str_repeat('a', 4001),
        ])
        ->assertSessionHasErrors('message');
});

/**
 * Create a test conversation for the given user.
 */
function createConversation(User $user): string
{
    $id = (string) \Illuminate\Support\Str::uuid();

    DB::table('agent_conversations')->insert([
        'id' => $id,
        'user_id' => $user->id,
        'title' => 'Test Conversation',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $id;
}
