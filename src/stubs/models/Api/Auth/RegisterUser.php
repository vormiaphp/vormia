<?php

namespace App\Models\Api\Auth;

use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

class RegisterUser extends Model
{
    // Todo: User Registration
    public static function user_registration(array $userInfo, int $roleId): int
    {

        // Verification
        $verification = new \App\Services\Verification();

        // Random Number as Token
        $_sms_token = $verification->generateRandomNumbers(5);

        // Register Account
        try {
            // Take User Info and Register
            $_user = new User();
            $_user->name = $userInfo["name"];
            $_user->username = $userInfo["username"];
            $_user->email = $userInfo["email"];
            $_user->phone = $userInfo["phone"];
            $_user->password = Hash::make($userInfo["password"]);
            $_user->flag = 0;
            $_user->save();

            // Add Roles
            $_user->roles()->attach($roleId);

            // Add SMS OTP
            $verification->generateVerificationCode(user_id: $_user->id, token: $_sms_token, token_name: 'registerphone');

            // Add Email Verification
            $_verify_token = $verification->generateVerificationCode(user_id: $_user->id, token_name: 'registeremail');

            // Account Verification
            $_auth_verification = config('services.app_url.verification') . "?u=$_user->id&n=registeremail&t=$_verify_token";

            // Values
            $code_link = [
                'link' => url($_auth_verification),
                'title' => 'Verify Email Account',
                'otplink' => $_sms_token
            ];

            // Create an instance of the class
            $instance = new self();
            $_notify_message = $instance->user_account_verification_message($userInfo["name"], $code_link);

            // Todo: send email & phone number verification
            // Dispatch email job to the queue
            if ($_notify_message['email']) {
                dispatch(new \App\Jobs\Vrm\SendMail($userInfo['email'], $_notify_message['email'], "VERIFY YOUR USER ACCOUNT – ACTION REQUIRED"));
            }

            // Dispatch SMS job to the queue
            if ($_notify_message['sms']) {
                $_phoneNo = \App\Services\AfricasTalkingService::formatPhoneNumber($userInfo["phone"]);
                dispatch(new \App\Jobs\Vrm\SendSms(
                    $_phoneNo,
                    $_notify_message['sms']
                ));
            }

            // Return
            return $_user->id;
        } catch (Exception $e) {
            // Log the error for debugging
            Log::error('User registration failed: ' . $e->getMessage());

            return false;
        }
    }

    // TODO: **************************** MESSAGES & MAIL ****************************

    /**
     * Todo: User Verification Message
     */
    private function user_account_verification_message(string $name,  array $code_link, string $generate = 'both'): array
    {

        // Return
        $results = ['email' => false, 'sms' => false];

        // If is Both or Email
        if (in_array(strtolower(trim($generate)), ['both', 'email'])) {
            // Logo - Add Link to logo
            $_logo = null;

            // Links & Button
            $_btn['link'] = (array_key_exists('link', $code_link)) ? $code_link['link'] : null;
            $_btn['title'] = (array_key_exists('title', $code_link)) ? $code_link['title'] : null;

            $_btn_extra = "
            <p>or - copy/click the link below</p>
            <a href='{$_btn['link']}'> {$_btn['link']}</a>
            ";

            $_otp_link = (array_key_exists('otplink', $code_link)) ? $code_link['otplink'] : null;

            // **** PREPAIRE EMAIL ****

            // Title & Sub Title
            $_title = "Verify Your User Account – Action Required!";
            $_subtitle = "You're one step away from connecting with students, complete your verification now.";

            // App Name
            $_app_name = config('services.app_name');

            // Body
            $_body = "
            <p>Hello <strong>$name</strong>,</p>

            <p>Thank you for signing up as a User on $_app_name!</p>

            <p>To ensure the security and authenticity of our platform, we require all users to verify their accounts before they can start accepting work.

            <p>If you did not sign up for a User account, please ignore this email.</p>

            <p>To complete your verification, please click the button below:</p>
            ";

            // App Name
            $_support_mail = config('services.app_mail.support');

            // Outro
            $_outro = "
           <p>If you have any questions or need assistance, feel free to contact our support team at <strong>$_support_mail</strong>.</p>
           <p>Looking forward to seeing you share your knowledge!</p>

           <br />

           <p>Best Regards,</p>
           <p>$_app_name Team</p>
            ";

            // Found - results
            $results['email'] = [
                'logo' => $_logo,
                'title' => $_title,
                'subtitle' => $_subtitle,
                'body' => $_body,
                'outro' => $_outro,
                'btn' => $_btn,
                'btn_extra' => $_btn_extra
            ];
        }

        // If is Both or Phone
        if (in_array(strtolower(trim($generate)), ['both', 'phone'])) {
            // **** PREPAIRE SMS ****
            $_sms = "$_app_name: Your verification code is $_otp_link.\nEnter this code to verify your user account. If you did not  request this, ignore this message.";

            // Found - results
            $results['sms'] = $_sms;
        }

        // Return Data
        return $results;
    }
}
