<?php

namespace Database\Seeders;

use App\Models\ChatUser;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class DemoConversationsSeeder extends Seeder
{
    /**
     * Seed demo conversations for the documentation page live demos.
     * These conversations are persistent and always available.
     */
    public function run(): void
    {
        // Get demo workspace
        $demoWorkspace = Workspace::where('name', 'Demo Workspace')->first();

        if (!$demoWorkspace) {
            $this->command->error('Demo Workspace not found! Please create it first.');
            return;
        }

        $this->command->info('Creating demo conversations for docs page...');

        // Demo conversation IDs (UUIDs for predictable references)
        $conversations = [
            [
                'id' => '019c0001-0000-7000-a000-000000000001',
                'name' => '1-on-1 Demo: Alice & Bob',
                'type' => 'direct',
                'users' => [
                    ['external_id' => 'user_alice_123', 'name' => 'Alice', 'email' => 'alice@example.com'],
                    ['external_id' => 'user_bob_456', 'name' => 'Bob', 'email' => 'bob@example.com'],
                ],
                'messages' => [
                    ['sender' => 0, 'content' => 'Hey Bob! How are you?'],
                    ['sender' => 1, 'content' => 'Hi Alice! I\'m doing great, thanks for asking!'],
                    ['sender' => 0, 'content' => 'Want to grab coffee later?'],
                ]
            ],
            [
                'id' => '019c0002-0000-7000-a000-000000000002',
                'name' => 'Product Team',
                'type' => 'group',
                'users' => [
                    ['external_id' => 'user_team_lead', 'name' => 'Sarah (Team Lead)', 'email' => 'sarah@example.com'],
                    ['external_id' => 'user_john', 'name' => 'John', 'email' => 'john@example.com'],
                    ['external_id' => 'user_emma', 'name' => 'Emma', 'email' => 'emma@example.com'],
                ],
                'messages' => [
                    ['sender' => 0, 'content' => 'Team, we have the Q1 planning meeting at 2pm'],
                    ['sender' => 1, 'content' => 'Thanks for the reminder!'],
                    ['sender' => 2, 'content' => 'I\'ll be there 👍'],
                ]
            ],
            [
                'id' => '019c0003-0000-7000-a000-000000000003',
                'name' => 'Support: Billing Question',
                'type' => 'direct',
                'users' => [
                    ['external_id' => 'customer_jane_456', 'name' => 'Jane (Customer)', 'email' => 'jane@customer.com'],
                    ['external_id' => 'agent_support_1', 'name' => 'Support Agent', 'email' => 'support@example.com'],
                ],
                'messages' => [
                    ['sender' => 0, 'content' => 'Hi! I have a question about my invoice'],
                    ['sender' => 1, 'content' => 'Hello Jane! I\'m here to help. What\'s your question?'],
                ]
            ],
            [
                'id' => '019c0004-0000-7000-a000-000000000004',
                'name' => 'Anonymous Visitor Chat',
                'type' => 'direct',
                'users' => [
                    ['external_id' => 'anonymous_visitor_789', 'name' => 'Anonymous Visitor', 'email' => null],
                    ['external_id' => 'moderator_1', 'name' => 'Moderator', 'email' => 'moderator@example.com'],
                ],
                'messages' => [
                    ['sender' => 0, 'content' => 'Hello! I have a quick question'],
                    ['sender' => 1, 'content' => 'Hi there! Feel free to ask anything.'],
                ]
            ],
            [
                'id' => '019c0005-0000-7000-a000-000000000005',
                'name' => 'Design Files Discussion',
                'type' => 'direct',
                'users' => [
                    ['external_id' => 'user_designer_123', 'name' => 'Alex (Designer)', 'email' => 'alex@example.com'],
                    ['external_id' => 'user_developer_1', 'name' => 'Dev Team', 'email' => 'dev@example.com'],
                ],
                'messages' => [
                    ['sender' => 0, 'content' => 'Check out the new mockups I just uploaded!'],
                ]
            ],
            [
                'id' => '019c0006-0000-7000-a000-000000000006',
                'name' => 'Invite Your Friends!',
                'type' => 'direct',
                'users' => [
                    ['external_id' => 'user_chris_789', 'name' => 'Chris', 'email' => 'chris@example.com'],
                    ['external_id' => 'user_morgan_456', 'name' => 'Morgan', 'email' => 'morgan@example.com'],
                ],
                'messages' => [
                    ['sender' => 0, 'content' => 'Hey! Just invited you to try out this chat platform 👋'],
                    ['sender' => 1, 'content' => 'Thanks for the invite! This looks really cool!'],
                    ['sender' => 0, 'content' => 'Right? Super easy to integrate into any app'],
                ]
            ],
        ];

        foreach ($conversations as $convData) {
            // Create or update conversation
            $conversation = Conversation::updateOrCreate(
                ['id' => $convData['id']],
                [
                    'workspace_id' => $demoWorkspace->id,
                    'name' => $convData['name'],
                    'type' => $convData['type'],
                ]
            );

            $this->command->info("✓ Conversation: {$convData['name']}");

            // Create users and add as participants
            $users = [];
            $participantIds = [];

            foreach ($convData['users'] as $userData) {
                $user = ChatUser::updateOrCreate(
                    [
                        'workspace_id' => $demoWorkspace->id,
                        'external_id' => $userData['external_id'],
                    ],
                    [
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                    ]
                );

                $users[] = $user;
                $participantIds[$user->id] = ['role' => 'member'];
            }

            // Sync participants (won't duplicate if already exists)
            $conversation->participants()->syncWithoutDetaching($participantIds);

            // Create messages
            foreach ($convData['messages'] as $msgData) {
                $sender = $users[$msgData['sender']];

                Message::updateOrCreate(
                    [
                        'conversation_id' => $conversation->id,
                        'sender_id' => $sender->id,
                        'content' => $msgData['content'],
                    ],
                    []
                );
            }

            $this->command->info("  └─ Added {$conversation->participants()->count()} participants and {$conversation->messages()->count()} messages");
        }

        $this->command->info("\n✓ Demo conversations seeded successfully!");
        $this->command->info("  Use conversation IDs in docs page:");
        $this->command->info("  - demo-1on1-conversation (019c0001...)");
        $this->command->info("  - demo-group-conversation (019c0002...)");
        $this->command->info("  - demo-support-conversation (019c0003...)");
        $this->command->info("  - demo-anonymous-conversation (019c0004...)");
        $this->command->info("  - demo-files-conversation (019c0005...)");
    }
}
