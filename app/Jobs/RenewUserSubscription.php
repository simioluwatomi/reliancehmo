<?php

namespace App\Jobs;

use App\User;
use App\Products;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class RenewUserSubscription implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Config::get('paystack.paymentUrl');
        $client = new Client(
            [
                'base_uri' => Config::get('paystack.paymentUrl'),
                'headers'  => [
                    'Authorization' => 'Bearer '.Config::get('paystack.secretKey'),
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
            ]
        );

        $data = [
            'authorization_code' => $this->user->authorization_code,
            'email'              => $this->user->email,
            'amount'             => (int) Products::first()->amount * 100,
        ];

        $client->post('/transaction/charge_authorization', ['json' => $data]);
    }
}
