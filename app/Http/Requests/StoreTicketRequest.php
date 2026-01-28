<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'order_id' => ['nullable', 'integer', 'exists:orders,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => __('Please enter a subject for your ticket.'),
            'subject.max' => __('Subject cannot exceed 255 characters.'),
            'priority.required' => __('Please select a priority level.'),
            'priority.in' => __('Please select a valid priority level.'),
            'message.required' => __('Please describe your issue.'),
            'message.min' => __('Please provide more details (at least 10 characters).'),
            'attachments.max' => __('You can attach a maximum of 5 files.'),
            'attachments.*.max' => __('Each file must be less than 10MB.'),
            'attachments.*.mimes' => __('Only images, PDFs, and Word documents are allowed.'),
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            'low' => __('Low'),
            'medium' => __('Medium'),
            'high' => __('High'),
            'urgent' => __('Urgent'),
        ];
    }
}
