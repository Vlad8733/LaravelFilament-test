<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ImportFinishedNotification extends Notification
{
    use Queueable;

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
            ->subject('Import finished: ' . $this->status)
            ->greeting('Import finished')
            ->line("Status: {$this->status}")
            ->line("Processed: {$this->processed}")
            ->line("Failed: {$this->failed}")
            ->action('View imports', $url);

        if ($this->failedPath) {
            $mail->line('There are failed rows. Download the failed CSV from the admin imports page.');
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
