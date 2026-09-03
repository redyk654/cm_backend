<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Entrée du journal d'audit. Immuable : jamais modifiée ni supprimée.
 */
class AuditLog extends Model
{
    use HasUuidPrimaryKey;

    /** Les entrées sont figées : pas de date de mise à jour. */
    public const UPDATED_AT = null;

    // Actions
    public const ACTION_CREATE = 'CREATE';

    public const ACTION_UPDATE = 'UPDATE';

    public const ACTION_DELETE = 'DELETE';

    public const ACTION_LOGIN = 'LOGIN';

    public const ACTION_LOGIN_FAILED = 'LOGIN_FAILED';

    public const ACTION_LOGOUT = 'LOGOUT';

    public const ACTION_DOWNLOAD = 'DOWNLOAD';

    public const ACTION_VALIDATE = 'VALIDATE';

    public const ACTION_GENERATE = 'GENERATE';

    // Type d'acteur
    public const ACTOR_USER = 'user';

    public const ACTOR_SYSTEM = 'system';

    // Statut
    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILURE = 'failure';

    protected $fillable = [
        'user_id',
        'actor_type',
        'action',
        'status',
        'failure_reason',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'description',
        'context',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeByUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeForEntity(Builder $query, Model $entity): Builder
    {
        return $query->where('auditable_type', $entity->getMorphClass())
            ->where('auditable_id', $entity->getKey());
    }

    public function scopeBetweenDates(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term): void {
            $q->where('action', 'like', "%{$term}%")
                ->orWhere('auditable_type', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE => 'Création',
            self::ACTION_UPDATE => 'Modification',
            self::ACTION_DELETE => 'Suppression',
            self::ACTION_LOGIN => 'Connexion',
            self::ACTION_LOGIN_FAILED => 'Tentative de connexion échouée',
            self::ACTION_LOGOUT => 'Déconnexion',
            self::ACTION_DOWNLOAD => 'Téléchargement',
            self::ACTION_VALIDATE => 'Validation',
            self::ACTION_GENERATE => 'Génération',
            default => $this->action,
        };
    }
}
