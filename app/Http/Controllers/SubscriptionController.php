<?php

namespace App\Http\Controllers;

use App\Products;
use App\Subscription;
use Illuminate\Http\Request;
use Unicodeveloper\Paystack\Paystack;
use Unicodeveloper\Paystack\Exceptions\PaymentVerificationFailedException;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $user = auth()->user();
        $product = Products::first();
        $reference = (new Paystack())->genTranxRef();
        $paystackKey = config('paystack.secretKey');

        return view('subscription.create', compact('user', 'product', 'reference', 'paystackKey'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
        return (new Paystack())->getAuthorizationUrl()->redirectNow();
    }

    /**
     * Obtain Paystack payment information.
     */
    public function handleGatewayCallback()
    {
        $user = auth()->user();
        try {
            $paymentDetails = (new Paystack())->getPaymentData();
        } catch (PaymentVerificationFailedException $e) {
            return redirect()->route('subscription.create', $user)->with(
                'message',
                [
                    'status' => 'alert-error',
                    'body'   => $e->getMessage(),
                ]
            );
        }

        $user->update([
            'authorization_code' => $paymentDetails['data']['authorization']['authorization_code'],
            'payment_date' => now()
        ]);

        Subscription::create([
            'user_id'     => $user->id,
            'amount'      => $paymentDetails['data']['amount'],
            'paystack_id' => $paymentDetails['data']['id'],
            'metadata'    => json_encode($paymentDetails['data']),
        ]);

        return redirect()->route('subscription.create', $user)->with(
            'message',
            [
                'status' => 'alert-success',
                'body'   => 'Payment successful',
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
