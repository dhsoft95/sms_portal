<?php

namespace App\Models;

use App\Jobs\SendSMSJob;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Campaigns extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'template_id',
        'region_name',
        'district_name',
        'category_name',
        'is_active',
        'is_scheduled',
        'scheduled_date',
        'scheduled_time',
        'timezone',
        'frequency',
        'status'
    ];

    protected $casts = [
        'is_scheduled' => 'boolean',
    ];

    protected $dates = [
        'scheduled_date',
        'scheduled_time',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function category()
    {
        return $this->belongsTo(categories::class);
    }

    public function district()
    {
        return $this->belongsTo(districts::class);
    }

    public function template()
    {
        return $this->belongsTo(templates::class, 'template_id');
    }

    protected static function booted()
    {
        Log::info('Campaign model booted');
        static::created(function ($campaign) {
            Log::info('Campaign created: ' . $campaign->id);
            if ($campaign->template) {
                $message = $campaign->template->content;
                $customersQuery = customer::query();

                if ($campaign->category_name) {
                    $customersQuery->where('category_name', $campaign->category_name);
                }

                if ($campaign->district_name) {
                    $customersQuery->where('district_name', $campaign->district_name);
                }
                if ($campaign->region_name) {
                    $customersQuery->where('region_name', $campaign->region_name);
                }

                $customers = $customersQuery->get();

                if ($campaign->is_scheduled) {
                    self::dispatchMessageByFrequency($campaign, $message, $customers);
                } else {
                    foreach ($customers as $customer) {
                        self::sendSMSInvitation($customer->phone, $message, $campaign);
                    }
                }
            }
        });
    }

    private static function dispatchMessageByFrequency($campaign, $message, $customers): void
    {
        switch ($campaign->frequency) {
            case 'One_time':
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($campaign->scheduled_date . ' ' . $campaign->scheduled_time, $campaign->timezone);
                    SendSMSJob::dispatch($customer->phone, $message, $campaign)
                        ->delay($scheduledDateTime);
                }
                break;
            case 'Daily':
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($campaign->scheduled_time, $campaign->timezone)->setTimezone('UTC');
                    SendSMSJob::dispatch($customer->phone, $message, $campaign)
                        ->dailyAt($scheduledDateTime->format('H:i'));
                }
                break;
            case 'Monthly':
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($campaign->scheduled_time, $campaign->timezone)->setTimezone('UTC');
                    SendSMSJob::dispatch($customer->phone, $message, $campaign)
                        ->monthlyOn($campaign->scheduled_date, $scheduledDateTime->format('H:i'));
                }
                break;
            default:
                foreach ($customers as $customer) {
                    $scheduledDateTime = Carbon::parse($campaign->scheduled_date . ' ' . $campaign->scheduled_time, $campaign->timezone);
                    SendSMSJob::dispatch($customer->phone, $message, $campaign)
                        ->delay($scheduledDateTime);
                }
                break;
        }
    }

    public static function sendSMSInvitation($phone, $message, $campaign)
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
