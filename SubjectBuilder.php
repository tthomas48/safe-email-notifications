<?php

namespace Modules\SafeEmailNotifications;

/**
 * Builds a transactional subject payload (SubjectResult) from a conversation.
 * Framework-agnostic: no Laravel/FreeScout calls. Caller is responsible for translation.
 *
 * Thread type constants match FreeScout's App\Thread so real Thread objects work when used in FreeScout.
 */
class SubjectBuilder
{
    public const TYPE_CUSTOMER = 1;
    public const TYPE_MESSAGE = 2;
    public const TYPE_NOTE = 3;
    public const TYPE_LINEITEM = 4;
    public const ACTION_TYPE_STATUS_CHANGED = 1;
    public const ACTION_TYPE_USER_CHANGED = 2;

    /**
     * Build a subject result for the user notification email.
     *
     * @param object $conversation Must have: number, threads (iterable), customer (optional)
     * @return SubjectResult|null Subject payload, or null to use default subject
     */
    public function build($conversation): ?SubjectResult
    {
        if ($conversation === null) {
            return null;
        }

        $thread = $this->getLatestThread($conversation->threads ?? []);
        if ($thread === null) {
            return null;
        }

        $prefix = '[#' . ($conversation->number ?? '') . '] ';
        $customer = $conversation->customer ?? null;
        $threads = $conversation->threads ?? [];

        // TYPE_LINEITEM - status/assignee changes
        if ((int) $thread->type === self::TYPE_LINEITEM) {
            if ((int) $thread->action_type === self::ACTION_TYPE_STATUS_CHANGED) {
                $person = $this->getPersonName($thread, 'getCreatedBy');
                $status = method_exists($thread, 'getStatusName') ? $thread->getStatusName() : '';
                return new SubjectResult($prefix, 'status_changed', ['person' => $person, 'status' => $status]);
            }
            if ((int) $thread->action_type === self::ACTION_TYPE_USER_CHANGED) {
                $person = method_exists($thread, 'getAssigneeName') ? $thread->getAssigneeName(false, null) : 'Someone';
                return new SubjectResult($prefix, 'assigned', ['person' => $person]);
            }
            return null;
        }

        // TYPE_NOTE
        if ((int) $thread->type === self::TYPE_NOTE) {
            $person = $this->getPersonName($thread, 'getCreatedBy');
            return new SubjectResult($prefix, 'note_added', ['person' => $person]);
        }

        // TYPE_CUSTOMER - new conversation or customer reply
        if ((int) $thread->type === self::TYPE_CUSTOMER) {
            $name = ($customer && method_exists($customer, 'getFullName'))
                ? $customer->getFullName(true)
                : 'Someone';
            $count = $this->countThreads($threads);
            if ($count <= 1) {
                return new SubjectResult($prefix, 'requesting_assistance', ['name' => $name]);
            }
            return new SubjectResult($prefix, 'replied', ['name' => $name]);
        }

        // TYPE_MESSAGE - user/agent reply
        if ((int) $thread->type === self::TYPE_MESSAGE) {
            $person = $this->getPersonName($thread, 'getCreatedBy');
            return new SubjectResult($prefix, 'replied', ['name' => $person]);
        }

        return null;
    }

    private function getLatestThread($threads): ?object
    {
        $latest = null;
        $latestAt = null;
        foreach ($threads as $t) {
            $at = isset($t->created_at) ? (is_object($t->created_at) ? $t->created_at->timestamp ?? 0 : (int) strtotime((string) $t->created_at)) : 0;
            if ($latestAt === null || $at >= $latestAt) {
                $latest = $t;
                $latestAt = $at;
            }
        }
        return $latest;
    }

    private function countThreads($threads): int
    {
        if (is_array($threads)) {
            return count($threads);
        }
        if (is_object($threads) && method_exists($threads, 'count')) {
            return (int) $threads->count();
        }
        $n = 0;
        foreach ($threads as $_) {
            $n++;
        }
        return $n;
    }

    private function getPersonName(object $thread, string $method): string
    {
        if (!method_exists($thread, $method)) {
            return 'Someone';
        }
        $creator = $thread->{$method}();
        if ($creator === null || !method_exists($creator, 'getFullName')) {
            return 'Someone';
        }
        return $creator->getFullName(true);
    }
}
