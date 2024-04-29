<?php

namespace App\Jobs;

use AllowDynamicProperties;
use App\Models\customer;
use App\Models\message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as GuzzleClient;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $phone;
    protected $messageContent;
    protected $messageOperation;

    /**
     * Create a new job instance.
     *
     * @param string $phone The recipient's phone number
     * @param string $messageContent The content of the SMS message
     * @param App\Models\message $messageOperation The message operation model instance
     */
    public function __construct($phone, $messageContent, message $messageOperation)
    {
        $this->phone = $phone;
        $this->messageContent = $messageContent;
        $this->messageOperation = $messageOperation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Call the sendSMSInvitation method
            $this->sendSMSInvitation($this->phone, $this->messageContent, $this->messageOperation);
        } catch (\Exception $e) {
            // Log any exceptions
            Log::error("Failed to send SMS to {$this->phone}: " . $e->getMessage());
            // Update message operation status to 'failed'
            $this->messageOperation->update(['status' => false]);
        }
    }
    /**
     * Method to send SMS invitation
     *
     * @param string $phone
     * @param string $message
     * @param message $messageOperation
     * @return void
     */
    public static function sendSMSInvitation($phone, $message, $messageOperation)
    {
        $client = new GuzzleClient();
        Log::info('SMS API URL: ' . env('SMS_API_URL'));

        try {
            $customer = Customer::where('phone', $phone)->first();
            $firstName = $customer ? $customer->fname : '';
            $lastName = $customer ? $customer->lname : '';

            $finalMessage = "$message"; // Concatenate message here

            $requestData = [
                'recipient' => $phone,
                'sender_id' => 'Info',
                'message' => $finalMessage,
            ];

            if (!$messageOperation->district_name) {
                $messageOperation->district_name = null;
                $messageOperation->save();
            }
            if (!$messageOperation->category_name) {
                $messageOperation->category_name = null;
                $messageOperation->save();
            }

            $response = $client->post(env('SMS_API_URL'), [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('SMS_API_TOKEN'),
                    'Accept' => 'application/json',
                ],
                'json' => $requestData,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            if (isset($responseData['status']) && $responseData['status'] === 'success') {
                $successMessage = isset($responseData['data']) ? $responseData['data'] : 'SMS sent successfully';
                if (is_array($successMessage)) {
                    $successMessage = json_encode($successMessage);
                }
                Log::info("SMS sent to $phone: $successMessage");
                $messageOperation->update(['status' => true]);
            } else {
                $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'Unknown error';
                if (is_array($errorMessage)) {
                    $errorMessage = json_encode($errorMessage);
                }
                Log::error("Failed to send SMS to $phone: $errorMessage");
                $messageOperation->update(['status' => false]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to $phone: " . $e->getMessage());
            $messageOperation->update(['status' => false]);
        }
    }
}
