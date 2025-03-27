<?php

namespace App\Services;

use Exception;
use AfricasTalking\SDK\AfricasTalking;


class AfricasTalkingService
{

    // Instance
    protected $africasTalking;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $username = config('services.africastalking.api_username');
        $apiKey = config('services.africastalking.api_key');
        $this->africasTalking = new AfricasTalking($username, $apiKey);
    }

    /**
     * Todo: Send sms
     * Pass recipient
     * Pass message
     *
     * @param int $recipient
     * @param string $message
     */
    public static function send_message(string $recipient, string $message)
    {
        // Create an instance of the class
        $africas_talking = new self();

        // Format Phone


        // Call Package
        $sms = $africas_talking->africasTalking->sms();
        $response = $sms->send([
            'to' => $recipient,
            'message' => $message,
            'from' => config('services.africastalking.api_from'),
        ]);

        // Return
        return $response;
    }

    /**
     * Todo: Phone Number
     * Pass phone
     * Pass countryCode
     *
     * @param string $phone
     * @param string $countryCode (default is +254)
     */
    public static function formatPhoneNumber(string $phone, string $countryCode = '+254'): string
    {
        // Remove spaces and non-numeric characters except "+"
        $phone = preg_replace('/\s+/', '', $phone);

        // Normalize the country code (remove "+" for internal processing)
        $normalizedCode = ltrim($countryCode, '+');

        // If the number starts with the country code (with or without "+"), return it in the correct format
        if (strpos($phone, "+$normalizedCode") === 0) {
            return $countryCode . substr($phone, strlen("+$normalizedCode"));
        }

        if (strpos($phone, $normalizedCode) === 0) {
            return $countryCode . substr($phone, strlen($normalizedCode));
        }

        // If the number starts with "0", replace it with the country code
        if (strpos($phone, '0') === 0) {
            return $countryCode . substr($phone, 1);
        }

        // If the number does not match any expected pattern, return it unchanged
        return $phone;
    }

    /**
     * Todo: Send SMS 2
     * Pass recipient
     * Pass message
     *
     * @param int $recipient
     * @param string $message
     */
    public function send_optional($recipient, $message)
    {
        // Create an instance of the class
        $sms = $this->africasTalking->sms();

        // Call Package
        $response = $sms->send([
            'to' => $recipient,
            'message' => $message,
            'from' => config('services.africastalking.api_from'),
        ]);

        // Return
        return $response;
    }

    /**
     * Todo: Send SMS 3
     * Pass recipient
     * Pass message
     *
     * @param int $recipient
     * @param string $message
     */
    public function send_optional_manual($recipient, $message)
    {

        // Set your app credentials
        $username = config('services.africastalking.api_username');
        $apiKey = config('services.africastalking.api_key');

        // Initialize the SDK
        $AT = new AfricasTalking($username, $apiKey);

        // Get the SMS service
        $sms = $AT->sms();

        // Set the numbers you want to send to in international format
        $recipients = "$recipient";

        // Set your message
        $message = "$message";

        // Set your shortCode or senderId
        $from = config('services.africastalking.api_from');

        try {
            // Thats it, hit send and we'll take care of the rest
            $result = $sms->send([
                'to'      => $recipients,
                'message' => $message,
                'from'    => $from
            ]);

            return $result;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
