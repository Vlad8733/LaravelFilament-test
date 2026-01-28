<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ImportFinishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 600];

    protected int $importId;

    protected string $status;

    protected int $processed;

    protected int $failed;

    protected ?string $failedPath;

    public function __construct(int $importId, string $status, int $processed = 0, int $failed = 0, ?string $failedPath = null)
    {
        $this->importId = $importId;
        $this->status = $status;
        $this->processed = $processed;
        $this->failed = $failed;
        $this->failedPath = $failedPath;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $url = route('filament.resources.import-jobs.index');

        $mail = (new MailMessage)
            ->subject(__('notifications.import_subject', ['status' => $this->status]))
            ->greeting(__('notifications.import_greeting'))
            ->line(__('notifications.import_status', ['status' => $this->status]))
            ->line(__('notifications.import_processed', ['count' => $this->processed]))
            ->line(__('notifications.import_failed', ['count' => $this->failed]))
            ->action(__('notifications.view_imports'), $url);

        if ($this->failedPath) {
            $mail->line(__('notifications.import_failed_notice'));
        }

        return $mail;
    }

    public function toDatabase($notifiable)
    {
        return [
            'import_id' => $this->importId,
            'status' => $this->status,
            'processed' => $this->processed,
            'failed' => $this->failed,
            'failed_file_path' => $this->failedPath,
        ];
    }
}
