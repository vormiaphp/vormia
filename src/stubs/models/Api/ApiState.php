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
	 * Generate Method to Validate Token passed by API
	 * Pass Token ID
	 * Pass Host name as optinal
	 *
	 * Return true if token is valid
	 */
	public static function validate($token_id, $host = null)
	{

		//Check if token is valid
		if ($token_id == 12345) {
			return true;
		} else {
			return false;
		}
	}

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
			'400' => 'Bad Request',
			'401' => 'Unauthorized',
			'402' => 'Payment Required',
			'403' => 'Forbidden',
			'404' => 'Not Found',
			'405' => 'Method Not Allowed',
			'406' => 'Not Acceptable',
			'407' => 'Proxy Authentication Required',
			'408' => 'Request Timeout',
			'409' => 'Conflict',
			'410' => 'Gone',
			'411' => 'Length Required',
			'412' => 'Precondition Failed',
			'413' => 'Request Entity Too Large',
			'414' => 'Request-URI Too Long',
			'415' => 'Unsupported Media Type',
			'416' => 'Requested Range Not Satisfiable',
			'417' => 'Expectation Failed',
			'500' => 'Internal Server Error',
			'501' => 'Not Implemented',
			'502' => 'Bad Gateway',
			'503' => 'Service Unavailable',
			'504' => 'Gateway Timeout',
			'505' => 'HTTP Version Not Supported',
		);

		http_response_code($code);

		// Check the code passed and return the code and meaning
		if (array_key_exists($code, $response_code)) {
			return array('status' => $code, 'value' => $response_code[$code]);
		} else {
			return array('status' => '500', 'value' => 'Internal Server Error');
		}
	}
}
