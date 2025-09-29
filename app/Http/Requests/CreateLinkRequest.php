<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CreateLinkRequest
 * 
 * Validates incoming requests to create shortened links
 */
class CreateLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     * 
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // No authentication required for now
    }

    /**
     * Get the validation rules that apply to the request
     * 
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'url' => [
                'required',
                'url',
                'max:2048',
                'regex:/^https?:\/\//', // Must start with http:// or https://
            ],
            'ttl_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:525600', // Max 1 year
            ],
            'custom_code' => [
                'nullable',
                'string',
                'min:6',
                'max:10',
                'regex:/^[a-zA-Z0-9]+$/', // Only alphanumeric
            ],
        ];
    }

    /**
     * Get custom error messages for validation
     * 
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required' => 'URL is required',
            'url.url' => 'Invalid URL format',
            'url.max' => 'URL cannot exceed 2048 characters',
            'url.regex' => 'URL must start with http:// or https://',
            'ttl_minutes.integer' => 'TTL must be a number',
            'ttl_minutes.min' => 'TTL must be at least 1 minute',
            'ttl_minutes.max' => 'TTL cannot exceed 1 year',
            'custom_code.min' => 'Custom code must be at least 6 characters',
            'custom_code.max' => 'Custom code cannot exceed 10 characters',
            'custom_code.regex' => 'Custom code can only contain letters and numbers',
        ];
    }
}
