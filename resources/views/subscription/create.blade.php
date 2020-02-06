@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Subscription Renewal</div>

                    <div class="card-body">
                        @if (session('message'))
                            <div class="alert {{ session('message.status') }}" role="alert">
                                {{ session('message.body') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('subscription.pay', $user) }}" role="form">
                            @csrf
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="email" value="{{ $user->email }}">
                            <input type="hidden" name="reference" value="{{ $reference }}">
                            <input type="hidden" name="amount" value="{{ (int) $product->price * 100 }}">
                            <input type="hidden" name="key" value="{{ $paystackKey }}">
                            <div class="form-group">
                                <select name="product" id="product"
                                        class="form-control custom-select @error('product') is-invalid @enderror"
                                        required>
                                    <option value="" selected disabled hidden>Select from the list of available products
                                    </option>
                                    <option value="{{ $product->id }}"
                                        {{ old('$product') == $product ? 'selected' : '' }}>
                                        {{ "{$product->name} || Price: {$product->price}" }}
                                    </option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Pay With Paystack</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
