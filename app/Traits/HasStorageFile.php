<?php

namespace App\Traits;

trait HasStorageFile
{
    public function getStorageUrl(string $col, ?string $fallback = null): ?string
    {
        $p = $this->{$col};
        if (! $p) {
            return $fallback;
        }
        if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) {
            return $p;
        }

        return asset('storage/'.$p);
    }

    public function hasStorageFile(string $col): bool
    {
        return ! empty($this->{$col});
    }
}
