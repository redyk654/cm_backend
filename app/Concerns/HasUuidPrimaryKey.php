<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Clé primaire UUID (ordonnée) pour les ressources métier.
 *
 * S'appuie sur le trait natif HasUuids de Laravel : UUID générés côté application,
 * ordonnés dans le temps pour préserver la localité d'index.
 */
trait HasUuidPrimaryKey
{
    use HasUuids;

    /**
     * La clé primaire n'est pas auto-incrémentée.
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * La clé primaire est une chaîne (UUID).
     */
    public function getKeyType(): string
    {
        return 'string';
    }
}
