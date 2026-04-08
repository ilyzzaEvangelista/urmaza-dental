<?php

namespace App\Models;

use App\Support\SanctumBearerToken;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Persist an audit row. Resolves staff user from Sanctum middleware or Bearer token (routes without auth middleware still get user_id when a token is sent).
     * Pass $userId when the actor is known but not yet on the request (e.g. login before the session/token is established).
     *
     * @param  array<string, mixed>|null  $properties
     */
    public static function record(
        Request $request,
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $properties = null,
        ?int $userId = null,
    ): self {
        $attrs = [
            'user_id' => $userId ?? static::staffUserId($request),
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'properties' => $properties,
        ];

        if ($subject !== null) {
            $attrs['subject_type'] = $subject->getMorphClass();
            $attrs['subject_id'] = $subject->getKey();
        }

        return static::query()->create($attrs);
    }

    protected static function staffUserId(Request $request): ?int
    {
        if ($id = $request->attributes->get('sanctum_user_id')) {
            return (int) $id;
        }

        if ($user = $request->user('sanctum')) {
            return $user->id;
        }

        if ($user = $request->user()) {
            return $user->id;
        }

        $token = SanctumBearerToken::normalize($request->bearerToken());
        if ($token === '') {
            return null;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        $model = $accessToken?->tokenable;

        return $model instanceof User ? $model->id : null;
    }
}
