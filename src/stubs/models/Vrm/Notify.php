<?php

namespace App\Models\Vrm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notify extends Model
{
    /**
     * Blank {blank}
     * Method is public and accessible via the web
     * Todo: For blank/empty/null notification
     *
     * @param  optional  $none - (anything passed will be ignored)
     *
     * @return null/blank
     */
    public static function blank($none = null)
    {

        // Return null
        return '';
    }

    /**
     * Success {success}
     * Method is public
     * Todo: For success notification
     *
     * @param  string  $message - (message to be displayed)
     *
     * @return string
     */
    public static function success($message = null)
    {
        // Check Flash Message
        (session()->has('message')) ? $message = session()->get('message') : $message;

        // Check Value
        $notify = (!is_null($message) && !empty($message)) ? $message : '<strong>Success!</strong> Operation was successful...';

        // Alert
        $alert = "
            <div class='alert alert-success' role='alert'>
                $notify
            </div>
        ";

        // Return the alert
        return $alert;
    }

    /**
     * Error {error}
     * Method is public
     * Todo: For error notification
     *
     * @param  string  $message - (message to be displayed)
     *
     * @return string
     */
    public static function error($message = null)
    {
        // Check Flash Message
        (session()->has('message')) ? $message = session()->get('message') : $message;

        //Check Value
        $notify = (!is_null($message) && !empty($message)) ? $message : '<strong>Error!</strong> Change a few things up and try again...';

        // Alert
        $alert = "
            <div class='alert alert-danger' role='alert'>
                $notify
            </div>
        ";

        // Return the alert
        return $alert;
    }

    /**
     * Warning {warning}
     * Method is public
     * Todo: For warning notification
     *
     * @param  string  $message - (message to be displayed)
     *
     * @return string
     */
    public static function warning($message = null)
    {
        // Check Flash Message
        (session()->has('message')) ? $message = session()->get('message') : $message;

        // Check Value
        $notify = (!is_null($message) && !empty($message)) ? $message : '<strong>Warning!</strong> This process cannot be revised...';

        // Alert
        $alert = "
            <div class='alert alert-warning' role='alert'>
                $notify
            </div>
        ";

        // Return the alert
        return $alert;
    }

    /**
     * Info {info}
     * Method is public
     * Todo: For info notification
     *
     * @param  string  $message - (message to be displayed)
     *
     * @return string
     */
    public static function info($message = null)
    {
        // Check Flash Message
        (session()->has('message')) ? $message = session()->get('message') : $message;

        // Check Value
        $notify = (!is_null($message) && !empty($message)) ? $message : '<strong>Info!</strong> Proceed ...';

        // Alert
        $alert = "
            <div class='alert alert-info' role='alert'>
                $notify
            </div>
        ";

        // Return the alert
        return $alert;
    }

    /**
     * Validation {valid}
     * Method is public, primarily used during validation for error notification
     * Todo: For validation notification
     *
     * @param  string  $message - (placeholder) null
     *
     * @return string
     */
    public static function valid($message = null)
    {
        // Return the flash session | if flash session valid is not set - return '' (empty string)
        return session()->get('valid', '');
    }

    /**
     * Notify {notify}
     * Method is public and is used to check if the notification is set via flash session
     * Todo: For notification Status
     *
     * @param  optional string  $key - [default = notification] (the flash session keyname)
     *
     * @return session data
     */
    public static function notify($key = 'notification')
    {
        // Return the flash session
        return session()->get($key, 'blank');
    }
}
