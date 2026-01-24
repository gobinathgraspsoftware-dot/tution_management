<?php

namespace App\Http\Requests;

use App\Models\Inventory;
use Illuminate\Foundation\Http\FormRequest;

class PosTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('process-pos-sale') || $this->user()->can('access-pos');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.inventory_id' => [
                'required',
                'exists:inventory,id',
                function ($attribute, $value, $fail) {
                    $inventory = Inventory::find($value);
                    if (!$inventory || $inventory->status !== 'active') {
                        $fail('The selected item is not available.');
                    }
                },
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    // Extract index from attribute (items.0.quantity -> 0)
                    preg_match('/items\.(\d+)\.quantity/', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    
                    if ($index !== null) {
                        $inventoryId = $this->input("items.{$index}.inventory_id");
                        $inventory = Inventory::find($inventoryId);
                        
                        if ($inventory && $value > $inventory->current_stock) {
                            $fail("Insufficient stock for {$inventory->name}. Available: {$inventory->current_stock}");
                        }
                    }
                },
            ],
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,qr',
            'amount_received' => 'required_if:payment_method,cash|nullable|numeric|min:0',
            'change_amount' => 'nullable|numeric|min:0',
            'reference_number' => 'required_if:payment_method,qr|nullable|string|max:100',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'items' => 'cart items',
            'items.*.inventory_id' => 'item',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'price',
            'payment_method' => 'payment method',
            'amount_received' => 'amount received',
            'reference_number' => 'reference number',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Please add at least one item to the cart.',
            'items.min' => 'Please add at least one item to the cart.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Invalid payment method selected.',
            'amount_received.required_if' => 'Amount received is required for cash payments.',
            'reference_number.required_if' => 'Reference number is required for QR payments.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up items array
        if ($this->has('items') && is_array($this->items)) {
            $cleanedItems = array_filter($this->items, function ($item) {
                return isset($item['inventory_id']) && isset($item['quantity']) && $item['quantity'] > 0;
            });
            
            $this->merge([
                'items' => array_values($cleanedItems),
            ]);
        }
        
        // Set defaults
        $this->merge([
            'discount' => $this->discount ?? 0,
            'tax' => $this->tax ?? 0,
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Calculate and validate total if needed
        $total = $this->calculateTotal();
        
        if ($this->payment_method === 'cash') {
            $amountReceived = (float) $this->amount_received;
            if ($amountReceived < $total) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], []),
                    response()->json([
                        'message' => 'Amount received is less than total amount.',
                        'errors' => ['amount_received' => ['Amount received must be at least RM' . number_format($total, 2)]],
                    ], 422)
                );
            }
        }
    }

    /**
     * Calculate total amount
     */
    protected function calculateTotal(): float
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }
        
        return $subtotal - (float) $this->discount + (float) $this->tax;
    }
}
