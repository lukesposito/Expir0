<?php

namespace App\Services\Deadlines;

use App\Models\Deadline;
use App\Models\DeadlineEvent;

class DeadlineEventLogger
{
    public function log(?Deadline $deadline, string $eventType, array $payload = [], ?int $actorUserId = null): DeadlineEvent
    {
        return DeadlineEvent::create([
            'deadline_id' => $deadline?->id,
            'actor_user_id' => $actorUserId,
            'event_type' => $eventType,
            'source_type' => $deadline?->source_type,
            'source_id' => $deadline?->source_id,
            'payload' => $payload,
        ]);
    }
}