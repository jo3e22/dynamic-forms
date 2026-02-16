<?php

namespace App\Services;

use App\Models\ActivityLog\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ActivityLogService
{
    protected ?string $batchUuid = null;

    public function startBatch(): string
    {
        $this->batchUuid = Str::uuid()->toString();
        return $this->batchUuid;
    }

    public function endBatch(): void
    {
        $this->batchUuid = null;
    }

    public function log(
        string $description,
        ?Model $subject = null,
        ?Model $causer = null,
        ?string $event = null,
        array $properties = [],
        ?string $logName = null
    ): ActivityLog {
        return ActivityLog::create([
            'log_name' => $logName,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'event' => $event,
            'causer_type' => $causer ? get_class($causer) : null,
            'causer_id' => $causer?->id,
            'properties' => $properties,
            'batch_uuid' => $this->batchUuid,
            'created_at' => now(),
        ]);
    }

    public function getLogsFor(Model $subject, ?int $limit = null)
    {
        $query = ActivityLog::where('subject_type', get_class($subject))
            ->where('subject_id', $subject->id)
            ->with('causer')
            ->latest();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getLogsByUser(Model $causer, ?int $limit = null)
    {
        $query = ActivityLog::where('causer_type', get_class($causer))
            ->where('causer_id', $causer->id)
            ->with('subject')
            ->latest();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}