<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Api\ApiState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class Registration extends Controller
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
     * Todo: user Registration
     * Allow user to register
     */
    public function user_registration(Request $request)
    {
        /**
         * Todo: API Keys
         * name - string
         * username - string
         * email - string
         * phone - string
         * password - string
         * role - int (optional)
         */

        // Get Request Data
        $received = $request->getContent();

        // Log the data
        Log::info($this->api_log . "Request (v2 user_registration): " . $received);

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
}
