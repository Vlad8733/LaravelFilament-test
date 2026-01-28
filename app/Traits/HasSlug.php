<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug) && ! empty($model->{$model->slugSourceColumn()})) {
                $model->slug = static::generateUniqueSlug($model->{$model->slugSourceColumn()});
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->slugSourceColumn()) && empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->{$model->slugSourceColumn()}, $model->id);
            }
        });
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
