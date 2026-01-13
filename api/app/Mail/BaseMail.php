<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

abstract class BaseMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * Get the model associated with this mailable.
     */
    abstract protected function getModel(): Model;

    abstract protected function getContentMarkdown(): string;

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->getFromAddress(),
            replyTo: [$this->getReplyToAddress()],
            subject: $this->getSubject(),
            tags: ['mail', 'sent'],
            metadata: [
                'model_id'   => $this->getModel()->id,
                'model_type' => $this->getModel()->getMorphClass(),
            ],
        );
    }

    /**
     * Get the message headers.
     */
    public function headers(): Headers
    {
        $model = $this->getModel();

        return new Headers(
            text: [
                'X-Model-ID'   => (string) $model->id,
                'X-Model-Type' => $model->getMorphClass(),
            ],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->getContentMarkdown(),
            with: [
                'model' => $this->getModel(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Get the sender address for the mailable.
     */
    protected function getFromAddress(): Address
    {
        return new Address(config('mail.from.address'), config('mail.from.name'));
    }

    /**
     * Get the reply-to address for the mailable.
     */
    protected function getReplyToAddress(): Address
    {
        return new Address(config('mail.from.address'), config('mail.from.name'));
    }

    /**
     * Get the default subject for the mailable.
     */
    protected function getSubject(): string
    {
        return config('app.name') . ' Mail';
    }
}
