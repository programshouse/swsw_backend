<?php

namespace App\traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByAdminArea
{
    protected function applyAdminAreaFilter(
        Builder $query,
        string $areaColumn = 'area_id'
    ): Builder {
        $admin = auth()->user();

        if (!$admin || $admin->role !== 'admin') {
            return $query;
        }

        if ($admin->isSuperAdmin()) {
            return $query;
        }

        if ($admin->isAreaAdmin()) {
            return $query->where(
                $areaColumn,
                $admin->admin_area_id
            );
        }

        return $query;
    }
}