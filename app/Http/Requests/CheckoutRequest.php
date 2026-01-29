<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'nullable|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_zip' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:2',
            'billing_same_as_shipping' => 'sometimes|boolean',
            'billing_name' => 'required_if:billing_same_as_shipping,false|nullable|string|max:255',
            'billing_address' => 'required_if:billing_same_as_shipping,false|nullable|string|max:255',
            'billing_city' => 'required_if:billing_same_as_shipping,false|nullable|string|max:100',
            'billing_state' => 'required_if:billing_same_as_shipping,false|nullable|string|max:100',
            'billing_zip' => 'required_if:billing_same_as_shipping,false|nullable|string|max:20',
            'billing_country' => 'required_if:billing_same_as_shipping,false|nullable|string|max:2',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function shippingData(): array
    {
        return [
            'name' => $this->input('shipping_name'),
            'email' => $this->input('shipping_email'),
            'phone' => $this->input('shipping_phone'),
            'address' => $this->input('shipping_address'),
            'city' => $this->input('shipping_city'),
            'state' => $this->input('shipping_state'),
            'zip' => $this->input('shipping_zip'),
            'country' => $this->input('shipping_country'),
            'notes' => $this->input('notes'),
        ];
    }

    public function billingData(): array
    {
        if ($this->boolean('billing_same_as_shipping', true)) {
            return [];
        }

        return [
            'name' => $this->input('billing_name'),
            'address' => $this->input('billing_address'),
            'city' => $this->input('billing_city'),
            'state' => $this->input('billing_state'),
            'zip' => $this->input('billing_zip'),
            'country' => $this->input('billing_country'),
        ];
    }
}
