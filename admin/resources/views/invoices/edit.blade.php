@extends('layouts.app')

@section('title', 'Edit Invoice')
@section('page-title', 'Edit Invoice #' . $invoice->invoice_number)

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold">Edit Invoice</h3>
    </div>

    <div class="p-6">
        <form method="POST" action="{{ route('invoices.update', $invoice) }}">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Biller Information -->
                <div class="space-y-4">
                    <h4 class="text-md font-semibold text-gray-900 border-b pb-2">Biller Information</h4>
                    
                    <div>
                        <label for="biller_name" class="block text-sm font-medium text-gray-700 mb-2">Biller Name *</label>
                        <input 
                            type="text" 
                            id="biller_name" 
                            name="biller_name" 
                            value="{{ old('biller_name', $invoice->biller_name) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('biller_name') border-red-500 @enderror"
                            required
                        >
                        @error('biller_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="biller_email" class="block text-sm font-medium text-gray-700 mb-2">Biller Email *</label>
                        <input 
                            type="email" 
                            id="biller_email" 
                            name="biller_email" 
                            value="{{ old('biller_email', $invoice->biller_email) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('biller_email') border-red-500 @enderror"
                            required
                        >
                        @error('biller_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="biller_address" class="block text-sm font-medium text-gray-700 mb-2">Biller Address *</label>
                        <textarea 
                            id="biller_address" 
                            name="biller_address" 
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('biller_address') border-red-500 @enderror"
                            required
                        >{{ old('biller_address', $invoice->biller_address) }}</textarea>
                        @error('biller_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Invoice Details -->
                <div class="space-y-4">
                    <h4 class="text-md font-semibold text-gray-900 border-b pb-2">Invoice Details</h4>
                    
                    <div>
                        <label for="item" class="block text-sm font-medium text-gray-700 mb-2">Item *</label>
                        <input 
                            type="text" 
                            id="item" 
                            name="item" 
                            value="{{ old('item', $invoice->item) }}"
                            placeholder="e.g., Conference Registration, Sponsorship Package"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('item') border-red-500 @enderror"
                            required
                        >
                        @error('item')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="3"
                            placeholder="Detailed description of the item or service"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                            required
                        >{{ old('description', $invoice->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                            <input 
                                type="number" 
                                id="amount" 
                                name="amount" 
                                value="{{ old('amount', $invoice->amount) }}"
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('amount') border-red-500 @enderror"
                                required
                            >
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">Currency *</label>
                            <select 
                                id="currency" 
                                name="currency" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('currency') border-red-500 @enderror"
                                required
                            >
                                <option value="USD" {{ old('currency', $invoice->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency', $invoice->currency) === 'EUR' ? 'selected' : '' }}>EUR</option>
                                <option value="GBP" {{ old('currency', $invoice->currency) === 'GBP' ? 'selected' : '' }}>GBP</option>
                            </select>
                            @error('currency')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('invoices.show', $invoice) }}" class="px-6 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Update Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
