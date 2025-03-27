<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helpers
use Illuminate\Support\Str;

class ApiState extends Model
{
    use HasFactory;

    /**
     * Response Code
     * Pass code number default is 400
     *
     * Return arry code as status=>passed code number and value as code meaning
     */
    public static function response_code($code = 400)
    {
        // Example 400 => Bad Request
        $response_code = array(
            '200' => 'Success',
            '201' => 'Created - Resource Created',
            '202' => 'Accepted - Processing',
            '204' => 'Success, No Data',

            '400' => 'Bad Request - Invalid Input',
            '401' => 'Unauthorized - Authentication Required',
            '403' => 'Access Denied',
            '404' => 'Resource Not Found',
            '405' => 'Method Not Allowed - Wrong HTTP Method',
            '422' => 'Validation Failed',

            '500' => 'Internal Server Error',
            '502' => 'Bad Gateway',
            '503' => 'Service Unavailable',
            '504' => 'Gateway Timeout',
        );

        http_response_code($code);

        // Check the code passed and return the code and meaning
        if (array_key_exists($code, $response_code)) {
            return array('status' => $code, 'value' => $response_code[$code]);
        } else {
            return array('status' => '500', 'value' => $response_code[500]);
        }
    }
}
