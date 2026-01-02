<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ValidateMessageContent
{
    /**
     * Maximum number of links allowed in a single message.
     */
    protected int $maxLinks = 5;

    /**
     * Minimum interval between identical messages (in seconds).
     */
    protected int $duplicateInterval = 10;

    /**
     * Number of recent messages to check for duplicates.
     */
    protected int $recentMessageCount = 5;

    /**
     * Maximum message length.
     */
    protected int $maxLength = 10000;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $content = $request->input('content');

        // Skip validation if no content (might be attachment-only message)
        if (empty($content)) {
            return $next($request);
        }

        // Check message length
        if (strlen($content) > $this->maxLength) {
            return response()->json([
                'error' => 'Message is too long.',
                'max_length' => $this->maxLength,
            ], 422);
        }

        // Get chat user for user-specific checks
        $chatUser = $request->chatUser;
        $userId = $chatUser?->id;

        if ($userId) {
            // Check for duplicate messages (spam prevention)
            $duplicateCheck = $this->checkDuplicateMessage($userId, $content);
            if ($duplicateCheck !== true) {
                return response()->json([
                    'error' => $duplicateCheck,
                ], 422);
            }

            // Store this message hash for future duplicate detection
            $this->storeMessageHash($userId, $content);
        }

        // Check for excessive links (spam indicator)
        $linkCount = $this->countLinks($content);
        if ($linkCount > $this->maxLinks) {
            return response()->json([
                'error' => 'Message contains too many links.',
                'max_links' => $this->maxLinks,
            ], 422);
        }

        // Optional profanity filter hook
        $profanityResult = $this->checkProfanity($content, $request);
        if ($profanityResult !== true) {
            return response()->json([
                'error' => $profanityResult,
            ], 422);
        }

        // Optional custom content filter hook
        $customResult = $this->runCustomFilters($content, $request);
        if ($customResult !== true) {
            return response()->json([
                'error' => $customResult,
            ], 422);
        }

        return $next($request);
    }

    /**
     * Check for duplicate/repeated messages from the same user.
     */
    protected function checkDuplicateMessage(int $userId, string $content): bool|string
    {
        $cacheKey = "user:{$userId}:recent_messages";
        $recentMessages = Cache::get($cacheKey, []);
        $contentHash = $this->hashContent($content);
        $now = time();

        foreach ($recentMessages as $message) {
            // Check if same content was sent recently
            if ($message['hash'] === $contentHash) {
                $timeSince = $now - $message['timestamp'];
                if ($timeSince < $this->duplicateInterval) {
                    return 'Please avoid sending duplicate messages.';
                }
            }
        }

        return true;
    }

    /**
     * Store a message hash for duplicate detection.
     */
    protected function storeMessageHash(int $userId, string $content): void
    {
        $cacheKey = "user:{$userId}:recent_messages";
        $recentMessages = Cache::get($cacheKey, []);

        // Add new message
        $recentMessages[] = [
            'hash' => $this->hashContent($content),
            'timestamp' => time(),
        ];

        // Keep only recent messages
        $recentMessages = array_slice($recentMessages, -$this->recentMessageCount);

        // Cache for the duplicate interval period
        Cache::put($cacheKey, $recentMessages, $this->duplicateInterval * 2);
    }

    /**
     * Generate a hash for message content (normalized).
     */
    protected function hashContent(string $content): string
    {
        // Normalize: lowercase, remove extra whitespace
        $normalized = strtolower(trim(preg_replace('/\s+/', ' ', $content)));
        return hash('xxh3', $normalized);
    }

    /**
     * Count the number of links in the message.
     */
    protected function countLinks(string $content): int
    {
        // Match URLs (http, https, www, and common TLDs)
        $pattern = '/https?:\/\/[^\s]+|www\.[^\s]+|\b[a-z0-9][-a-z0-9]*\.(com|org|net|io|co|app|dev|me|info|biz|xyz)\b/i';
        preg_match_all($pattern, $content, $matches);

        return count($matches[0]);
    }

    /**
     * Profanity filter hook.
     *
     * Override this method or use a service to implement actual profanity filtering.
     * Return true if content is acceptable, or an error message string if not.
     */
    protected function checkProfanity(string $content, Request $request): bool|string
    {
        // Get workspace settings for profanity filtering
        $workspace = $request->workspace;

        if (!$workspace) {
            return true;
        }

        // Check if profanity filter is enabled for this workspace
        $settings = $workspace->settings ?? [];
        $profanityFilterEnabled = $settings['profanity_filter_enabled'] ?? false;

        if (!$profanityFilterEnabled) {
            return true;
        }

        // Hook point for custom profanity filter implementation
        // You can:
        // 1. Use a package like 'snipe/banbuilder'
        // 2. Call an external API
        // 3. Use a custom word list from workspace settings
        //
        // Example implementation:
        //
        // $profanityFilter = app(ProfanityFilterService::class);
        // if ($profanityFilter->containsProfanity($content)) {
        //     return 'Your message contains inappropriate content.';
        // }

        return true;
    }

    /**
     * Custom content filter hook.
     *
     * Override this method to add custom content validation logic.
     * This could include:
     * - Spam detection using ML
     * - Pattern matching for specific content types
     * - Integration with external moderation APIs
     */
    protected function runCustomFilters(string $content, Request $request): bool|string
    {
        // Get workspace settings for custom filters
        $workspace = $request->workspace;

        if (!$workspace) {
            return true;
        }

        // Hook point for custom filters
        // Example implementations:
        //
        // 1. Blocked words from workspace settings:
        // $blockedWords = $settings['blocked_words'] ?? [];
        // foreach ($blockedWords as $word) {
        //     if (stripos($content, $word) !== false) {
        //         return 'Your message contains blocked content.';
        //     }
        // }
        //
        // 2. External moderation API:
        // $moderationResult = app(ModerationService::class)->check($content);
        // if (!$moderationResult->isClean()) {
        //     return 'Your message was flagged by our moderation system.';
        // }
        //
        // 3. Phone number / email detection (if not allowed):
        // if (preg_match('/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/', $content)) {
        //     return 'Phone numbers are not allowed in messages.';
        // }

        return true;
    }
}
