<?php

namespace App\services;

use App\Models\PointTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PointService
{
    public function add(
        Model $owner,
        int $points,
        string $source,
        ?Model $reference = null,
        ?string $notes = null
    ): PointTransaction {
        if ($points <= 0) {
            throw new InvalidArgumentException(
                'Points must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $owner,
            $points,
            $source,
            $reference,
            $notes
        ) {
            return $owner->pointTransactions()->create([
                'source' => $source,
                'points' => $points,

                'reference_type' => $reference
                    ? $reference::class
                    : null,

                'reference_id' => $reference?->getKey(),

                'notes' => $notes,
            ]);
        });
    }

    public function deduct(
        Model $owner,
        int $points,
        string $source,
        ?Model $reference = null,
        ?string $notes = null
    ): PointTransaction {
        if ($points <= 0) {
            throw new InvalidArgumentException(
                'Points must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $owner,
            $points,
            $source,
            $reference,
            $notes
        ) {
            $currentPoints = (int) $owner
                ->pointTransactions()
                ->lockForUpdate()
                ->sum('points');

            if ($currentPoints < $points) {
                throw new InvalidArgumentException(
                    'Insufficient points balance.'
                );
            }

            return $owner->pointTransactions()->create([
                'source' => $source,
                'points' => -$points,

                'reference_type' => $reference
                    ? $reference::class
                    : null,

                'reference_id' => $reference?->getKey(),

                'notes' => $notes,
            ]);
        });
    }

    public function balance(Model $owner): int
    {
        return (int) $owner
            ->pointTransactions()
            ->sum('points');
    }


    public function addOnce(
    Model $owner,
    int $points,
    string $source,
    Model $reference,
    ?string $notes = null
): PointTransaction {
    if ($points <= 0) {
        throw new InvalidArgumentException(
            'Points must be greater than zero.'
        );
    }

    return DB::transaction(function () use (
        $owner,
        $points,
        $source,
        $reference,
        $notes
    ) {
        $existingTransaction = $owner
            ->pointTransactions()
            ->where('source', $source)
            ->where('reference_type', $reference::class)
            ->where('reference_id', $reference->getKey())
            ->first();

        if ($existingTransaction) {
            return $existingTransaction;
        }

        return $owner->pointTransactions()->create([
            'source' => $source,
            'points' => $points,
            'reference_type' => $reference::class,
            'reference_id' => $reference->getKey(),
            'notes' => $notes,
        ]);
    });
}
}