<?php

namespace App\Jobs;

use App\Models\customer;
use App\Models\campaigns;
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
     * Recipient's phone number.
     *
     * @var string
     */
    protected $phone;

    /**
     * Content of the SMS message.
     *
     * @var string
     */
    protected $messageContent;

    /**
     * The campaign model instance.
     *
     * @var \App\Models\campaigns
     */
    protected $campaign;

    /**
     * Create a new job instance.
     *
     * @param string $phone
     * @param string $messageContent
     * @param \App\Models\campaigns $campaign
     */
    public function __construct($phone, $messageContent, campaigns $campaign)
    {
        $this->phone = $phone;
        $this->messageContent = $messageContent;
        $this->campaign = $campaign;
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
            $this->sendSMSInvitation($this->phone, $this->messageContent, $this->campaign);
        } catch (\Exception $e) {
            // Log any exceptions
            Log::error("Failed to send SMS to {$this->phone}: " . $e->getMessage());
            // Update campaign status to 'failed'
            $this->campaign->update(['status' => false]);
        }
    }

    /**
     * Method to send SMS invitation.
     *
     * @param string $phone
     * @param string $message
     * @param \App\Models\campaigns $campaign
     * @return void
     */
    public static function sendSMSInvitation($phone, $message, $campaign)
    {
        $client = new GuzzleClient();
        Log::info('SMS API URL: ' . env('SMS_API_URL'));

        try {
            $customer = customer::where('phone', $phone)->first();
            $firstName = $customer ? $customer->fname : '';
            $lastName = $customer ? $customer->lname : '';

            $finalMessage = "$message"; // Concatenate message here

            $requestData = [
                'recipient' => $phone,
                'sender_id' => 'YamahaTZ',
                'message' => $finalMessage,
            ];

            // Log request data before sending
            Log::info('Sending SMS request data: ' . json_encode($requestData));

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
                $campaign->update(['status' => true]);

                // Log successful SMS operation in the messages table
                message::create([
                    'campaign_id' => $campaign->id,
                    'region_name' => $campaign->region_name,
                    'district_name' => $campaign->district_name,
                    'category_name' => $campaign->category_name,
                    'status' => true,
                    'scheduled_date' => $campaign->scheduled_date,
                    'scheduled_time' => $campaign->scheduled_time,
                    'timezone' => $campaign->timezone,
                    'frequency' => $campaign->frequency,
                ]);
            } else {
                $errorMessage = isset($responseData['message']) ? $responseData['message'] : 'Unknown error';
                if (is_array($errorMessage)) {
                    $errorMessage = json_encode($errorMessage);
                }
                Log::error("Failed to send SMS to $phone: $errorMessage");
                $campaign->update(['status' => false]);

                // Log failed SMS operation in the messages table
                message::create([
                    'campaign_id' => $campaign->id,
                    'region_name' => $campaign->region_name,
                    'district_name' => $campaign->district_name,
                    'category_name' => $campaign->category_name,
                    'status' => false,
                    'scheduled_date' => $campaign->scheduled_date,
                    'scheduled_time' => $campaign->scheduled_time,
                    'timezone' => $campaign->timezone,
                    'frequency' => $campaign->frequency,
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to $phone: " . $e->getMessage());
            $campaign->update(['status' => false]);

            // Log exception in the messages table
            message::create([
                'campaign_id' => $campaign->id,
                'region_name' => $campaign->region_name,
                'district_name' => $campaign->district_name,
                'category_name' => $campaign->category_name,
                'status' => false,
                'scheduled_date' => $campaign->scheduled_date,
                'scheduled_time' => $campaign->scheduled_time,
                'timezone' => $campaign->timezone,
                'frequency' => $campaign->frequency,
            ]);
        }
    }
}
