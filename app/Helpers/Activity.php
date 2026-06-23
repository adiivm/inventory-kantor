<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class Activity
{
    public static function log(
        string $module,
        string $action,
        string $description,
        $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): ActivityLog {
        $user = Auth::user();

        $data = [
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ];

        if ($subject) {
            $data['subject_type'] = get_class($subject);
            $data['subject_id'] = $subject->id;
        }

        return ActivityLog::create($data);
    }

    public static function logCreate(string $module, string $label, $subject = null, ?array $values = null): ActivityLog
    {
        return static::log($module, 'create', "{$label} berhasil dibuat", $subject, null, $values);
    }

    public static function logUpdate(string $module, string $label, $subject = null, ?array $old = null, ?array $new = null): ActivityLog
    {
        return static::log($module, 'update', "{$label} berhasil diperbarui", $subject, $old, $new);
    }

    public static function logDelete(string $module, string $label, $subject = null, ?array $values = null): ActivityLog
    {
        return static::log($module, 'delete', "{$label} berhasil dihapus", $subject, $values, null);
    }
}
