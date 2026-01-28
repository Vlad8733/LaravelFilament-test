<?php

namespace App\Traits;

trait HasStatusLabels
{
    public function getStatusColorAttribute(): string
    {
        return $this->statusColors()[$this->status] ?? 'gray';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusLabels()[$this->status] ?? ucfirst($this->status);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    protected function statusColors(): array
    {
        return ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'completed' => 'success', 'failed' => 'danger', 'cancelled' => 'gray'];
    }

    protected function statusLabels(): array
    {
        return ['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected'), 'completed' => __('Completed'), 'failed' => __('Failed'), 'cancelled' => __('Cancelled')];
    }
}
