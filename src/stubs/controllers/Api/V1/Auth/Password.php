<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Api\ApiState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class Password extends Controller
{

    // API Defaults
    private $api_log = "Api: ";
    private $api_debug = True;
    private $api_response = 412;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Todo: v1 general response
     *
     * Pass api response and data
     */
    private function general_response(array $decoded, array $results, string $route)
    {
        // check if $results is empty
        if (count($results) == 0) {
            // Response
            $this->api_response = 404;
            $response_tree["message"] = "No results found";

            // Response
            $response_tree["response"] = [
                "state" => false,
                "message" => "Operation Failed",
            ];
        } else {
            // Response
            $this->api_response = 200;

            // Response
            $response_tree["response"] = [
                "state" => true,
                "results" => $results,
                "message" => "Operation Was Successful",
            ];
        }

        // Status - Precondition Failed
        $response = ApiState::response_code($this->api_response);

        // Default Return
        $response["sent"] = $decoded;

        // Response
        $response["response"] = array_key_exists("response", $response_tree) ? $response_tree["response"] : "";

        // Other
        $code_feedback = [
            "route" => $route,
            "controller" => class_basename(__CLASS__) . ".php",
        ];

        // Debug
        if ($this->api_debug) {
            $response["debug"] = $code_feedback;
        }

        // Set header Json
        return response()
            ->json($response)
            ->header("Content-Type", "application/json");
    }

    /**
     * Todo: Password Reset - Email
     * Allow user to reset password
     * Link will be sent to customer email
     */
    public function reset_via_email(Request $request)
    {
        // Get Request Data
        $received = $request->getContent();

        // Log the data
        Log::info($this->api_log . "Request (v1 reset_via_email): " . $received);

        // Decode Data
        $decoded = json_decode($received, true);

        // User Email
        $_user_email = $decoded['email'] ?? $decoded['e'] ?? null;

        // Reset Password
        $_user_id = \App\Models\Api\Auth\PasswordReset::password_resetlink_email(_email: $_user_email);

        // todo: NB return value has to be an array
        $results = [];
        if ($_user_id) {
            $results = ['user_id' => $_user_id];
        }

        // Response
        return $this->general_response($decoded, $results, 'v1/auth/password/reset-email');
    }

    /**
     * Todo: Password Reset - Phone
     * Allow user to reset password
     * Code will be sent to customer phone
     */
    public function reset_via_phone(Request $request)
    {
        // Get Request Data
        $received = $request->getContent();

        // Log the data
        Log::info($this->api_log . "Request (v1 reset_via_phone): " . $received);

        // Decode Data
        $decoded = json_decode($received, true);

        // User Phone
        $_user_phone = $decoded['phone'] ?? $decoded['p'] ?? null;

        // Reset Password
        $_user_id = \App\Models\Api\Auth\PasswordReset::password_resetlink_phone(_phone: $_user_phone);

        // todo: NB return value has to be an array
        $results = [];
        if ($_user_id) {
            $results = ['user_id' => $_user_id];
        }

        // Response
        return $this->general_response($decoded, $results, 'v1/auth/password/reset-phone');
    }

    /**
     * Todo: Set New Password
     * Get Token / OTP and Send with new password
     * If new password is set and comfirm email or sms is dispatched
     */
    public function set_new_password(Request $request)
    {
        // Get Request Data
        $received = $request->getContent();

        // Log the data
        Log::info($this->api_log . "Request (v1 reset_via_phone): " . $received);

        // Decode Data
        $decoded = json_decode($received, true);

        // Token & Password
        $_name = $decoded['name'] ?? $decoded['n'] ?? null;
        $_token = $decoded['token'] ?? $decoded['t'] ?? null;
        $_password = $decoded['password'] ?? $decoded['p'] ?? null;

        // todo: NB return value has to be an array
        $results = [];

        // Set New Account Password
        $_user_id = \App\Models\Api\Auth\PasswordReset::password_set_new(_token: $_token, _password: $_password, _token_name: $_name);

        // todo: NB return value has to be an array
        $results = [];
        if ($_user_id) {
            $results = ['user_id' => $_user_id];
        }

        // Response
        return $this->general_response($decoded, $results, 'v1/auth/password/set-password');
    }
}
