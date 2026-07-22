<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'action_type',
        'subject_type',
        'subject_id',
        'performed_by',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Helper to record an audit log entry conveniently anywhere in the application.
     */
    public static function record(
        string $actionType,
        string $description,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?array $metadata = null,
        ?int $userId = null
    ): self {
        return static::create([
            'action_type'  => $actionType,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'performed_by' => $userId ?? auth()->id(),
            'description'  => $description,
            'metadata'     => $metadata,
        ]);
    }
}
