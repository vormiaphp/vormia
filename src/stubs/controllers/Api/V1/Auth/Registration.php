<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\Api\ApiState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class Registration extends Controller
{
    //? API Defaults
    private $api_log = "Api: ";
    private $api_debug = False;
    private $api_response = 503;

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
     * Todo: v2 general response
     *
     * Pass api response and data
     */
    private function general_response($decoded, $results, $route)
    {

        // Default
        $response_tree = [];

        // check if $results is empty
        if (count($results) == 0) {
            // Response
            $this->api_response = 204;
        } else {
            // Response
            $this->api_response = 200;

            // Response
            $response_tree['response'] = $results;
        }

        /**
         ** RESPONSE - RETURN
         */
        // Status - Precondition Failed
        $_http_response = ApiState::response_code($this->api_response);

        // Default Return
        $response['sent'] = $decoded;
        $response['response'] =  array_key_exists('response', $response_tree) ? $response_tree['response'] : false;
        $response['message'] = $_http_response['value'];

        // Debug
        if ($this->api_debug) {

            $code_feedback = [
                "route" => $route,
                "controller" => class_basename(__CLASS__) . ".php",
            ];

            $response["debug"] = $code_feedback;
        }

        // Set header Json
        return response()->json($response, $this->api_response);
    }

    /**
     * Todo: user Registration
     * Allow user to register
     */
    public function user_registration(Request $request)
    {
        // Get Request Data
        $received = $request->getContent();

        // Log the data
        Log::info($this->api_log . "Request (v1 user_registration): " . $received);

        // Decode Data
        $decoded = json_decode($received, true);

        // By Default we give new registered user a role of 1 - admin
        $_user_role = (array_key_exists('role', $decoded)) ? (int) $decoded['role'] : 1;

        // Register User
        $_user_id = \App\Models\Api\Auth\RegisterUser::user_registration($decoded, roleId: $_user_role);

        // todo: NB return value has to be an array
        $results = [];

        // Result - if user is registered
        if ($_user_id) {
            $results = [
                "user_id" => $_user_id,
                "role" => $_user_role,
            ];
        }

        // Response
        return $this->general_response($decoded, $results, 'v1/auth/register/user');
    }

    // TODO: ACCOUNT VERIFICATION

    /**
     * Todo: Email Verification
     * Verify account email
     */
    public function verification_email(Request $request)
    {
        // Get Request Data
        $received = $request->all();
        // Encode
        $received = json_encode($received);

        // Log the data
        Log::info($this->api_log . "Request (v1 verification_email): " . $received);

        // Decode Data
        $decoded = json_decode($received, true);

        // Check Values
        $_user = $decoded['user'] ?? $decoded['u'] ?? null;
        $_name = $decoded['name'] ?? $decoded['n'] ?? null;
        $_token = $decoded['token'] ?? $decoded['t'] ?? null;

        // Check User Token
        $_user = session('token_user') ?? $_user;

        // Verification
        $verification = new \App\Services\Verification();
        $verified = $verification->verifyVerificationCode(token: $_token, token_name: $_name, user_id: $_user);

        // Results
        $results = [];

        // Success
        if ($verified) {
            // Verify Email
            $_this_user = \App\Models\User::where('id', $verified->user)->first();
            // Verified at
            $_this_user->email_verified_at = now();
            $_this_user->flag = 1;
            $_this_user->save();

            // Results
            $results = ['user_id' => $_this_user->id];
        }

        // Response
        return $this->general_response($decoded, $results, 'v1/auth/register/verify-email');
    }

    /**
     * Todo: Phone Verification
     * Verify account phone number
     */
    public function verification_phone(Request $request)
    {
        // Get Request Data
        $received = $request->all();
        // Encode
        $received = json_encode($received);

        // Log the data
        Log::info($this->api_log . "Request (v1 verification_phone): " . $received);

        // Decode Data
        $decoded = json_decode($received, true);

        // Check Values
        $_user = $decoded['user'] ?? $decoded['u'] ?? null;
        $_name = $decoded['name'] ?? $decoded['n'] ?? null;
        $_token = $decoded['token'] ?? $decoded['t'] ?? null;

        // Check User Token
        $_user = session('token_user') ?? $_user;

        // Verification
        $verification = new \App\Services\Verification();
        $verified = $verification->verifyVerificationCode(token: $_token, token_name: $_name, user_id: $_user);

        // Results
        $results = [];

        // Success
        if ($verified) {
            // Verify Email
            $_this_user = \App\Models\User::where('id', $verified->user)->first();
            // Verified at
            $_this_user->phone_verified_at = now();
            $_this_user->flag = 1;
            $_this_user->save();

            // Results
            $results = ['user_id' => $_this_user->id];
        }

        // Response
        return $this->general_response($decoded, $results, 'v1/auth/register/verify-phone');
    }
}
