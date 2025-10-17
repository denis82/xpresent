<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'service_id'     => 'required|exists:services,id',
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'booking_date'   => 'required|date',
            'start_time'     => 'required|date_format:H:i',
        ];
    }
}
