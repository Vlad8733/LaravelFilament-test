<?php

namespace App\Traits;

trait HasDefaultItem
{
    public function setAsDefault(): void
    {
        $col = $this->defaultOwnerColumn();
        self::where($col, $this->{$col})->where('id', '!=', $this->id)->update(['is_default' => false]);
        $this->update(['is_default' => true]);
    }

    protected function defaultOwnerColumn(): string
    {
        return 'user_id';
    }

    public function scopeDefault($q)
    {
        return $q->where('is_default', true);
    }

    public function scopeForOwner($q, $id)
    {
        return $q->where($this->defaultOwnerColumn(), $id);
    }
}
