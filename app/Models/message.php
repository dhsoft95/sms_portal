<?php

namespace App\Models;



use App\Jobs\SendSMSJob;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class message extends Model
{
    use HasFactory;

    protected $table = 'message_operations';

    protected $fillable = [
        'campaign_id',
        'district_name',
        'category_name',
        'status',
        'scheduled_date',
        'scheduled_time',
        'timezone',
        'frequency',
    ];

    public function campaign()
    {
        return $this->belongsTo(campaigns::class);
    }

    public function district()
    {
        return $this->belongsTo(districts::class, 'district_name');
    }

    public function category()
    {
        return $this->belongsTo(categories::class, 'category_name');
    }

    public static function booted()
    {
        static::created(function ($messageOperation) {
            $campaign = campaigns::findOrFail($messageOperation->campaign_id);
            $template = $campaign->template;

            if ($template) {
                $message = $template->content;
                $customersQuery = Customer::query();

                if ($messageOperation->category_name) {
                    $customersQuery->where('category_name', $messageOperation->category_name);
                }

                if ($messageOperation->district_name) {
                    $customersQuery->where('district_name', $messageOperation->district_name);
                }

                $customers = $customersQuery->get();

                if ($messageOperation->is_scheduled) {
                    // Dispatch job for each customer based on frequency
                    self::dispatchMessageByFrequency($messageOperation, $message, $customers);
                } else {
                    // Send messages immediately without dispatching
                    foreach ($customers as $customer) {
                        self::sendSMSInvitation($customer->phone, $message, $messageOperation);
                    }
                }
            }
        });
    }

    // Method to dispatch messages based on frequency
    private static function dispatchMessageByFrequency($messageOperation, $message, $customers): void
    {
        // Adjust the scheduling based on frequency
        switch ($messageOperation->frequency) {
            case 'One_time':
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($messageOperation->scheduled_date . ' ' . $messageOperation->scheduled_time, $messageOperation->timezone);
                    SendSMSJob::dispatch($customer->phone, $message, $messageOperation)
                        ->delay($scheduledDateTime);
                }
                break;
            case 'Daily':
                // Schedule for each day
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($messageOperation->scheduled_time, $messageOperation->timezone)->setTimezone('UTC');
                    SendSMSJob::dispatch($customer->phone, $message, $messageOperation)
                        ->dailyAt($scheduledDateTime->format('H:i'));
                }
                break;
            case 'Monthly':
                // Schedule for each month
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($messageOperation->scheduled_time, $messageOperation->timezone)->setTimezone('UTC');
                    SendSMSJob::dispatch($customer->phone, $message, $messageOperation)
                        ->monthlyOn($messageOperation->scheduled_date, $scheduledDateTime->format('H:i'));
                }
                break;
            default:
                // Default to one-time if frequency is not recognized
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($messageOperation->scheduled_date . ' ' . $messageOperation->scheduled_time, $messageOperation->timezone);
                    SendSMSJob::dispatch($customer->phone, $message, $messageOperation)
                        ->delay($scheduledDateTime);
                }
                break;
        }
    }

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
