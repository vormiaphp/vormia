<?php

namespace App\Models\Api\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // Todo: Request Password Reset Link (Via Email)
    public static function password_resetlink_email(string $_email)
    {

        // Check If Account Exist
        $_user = \App\Models\User::where('email', $_email)->first();

        // If User Does not Exist
        if (!$_user) {
            return null;
        }

        // Verification
        $verification = new \App\Services\Verification();

        // Reset Password Token
        $_reset_token = $verification->generateVerificationCode(user_id: $_user->id, token_name: 'passwordresetemail');

        // Account Password Resey
        $_reset_verification = config('services.app_url.reset') . "?n=passwordresetemail&t=$_reset_token";

        // Values
        $code_link = [
            'link' => url($_reset_verification),
            'title' => 'Reset Account Password',
            'otplink' => null
        ];

        // Create an instance of the class
        $instance = new self();
        $_notify_message = $instance->reset_account_password_message($_user->name, $code_link, generate: 'email');

        // Todo: send email
        // Dispatch email job to the queue
        if ($_notify_message['email']) {
            dispatch(new \App\Jobs\Email\SendEmail($_user->email, $_notify_message['email'], "RESET YOUR PASSWORD – ACTION REQUIRED"));

            // Return
            return $_user->id;
        }

        // Failed
        return false;
    }

    // Todo: Request Password Reset Link (Via Phone)
    public static function password_resetlink_phone(int $_phone)
    {

        // Check If Account Exist
        $_user = \App\Models\User::where('phone', $_phone)->first();

        // If User Does not Exist
        if (!$_user) {
            return null;
        }

        // Verification
        $verification = new \App\Services\Verification();

        // Random Number as Token
        $_sms_token = $verification->generateRandomNumbers(5);

        // Add SMS OTP
        $verification->generateVerificationCode(user_id: $_user->id, token: $_sms_token, token_name: 'passwordresetphone');

        // Values
        $code_link = ['otplink' => $_sms_token];

        // Create an instance of the class
        $instance = new self();
        $_notify_message = $instance->reset_account_password_message($_user->name, $code_link, generate: 'phone');

        // Todo: send phone number verification
        // Dispatch SMS job to the queue
        if ($_notify_message['sms']) {
            $_phoneNo = \App\Services\AfricasTalkingService::formatPhoneNumber($_user->phone);
            dispatch(new \App\Jobs\Sms\SendSms(
                $_phoneNo,
                $_notify_message['sms']
            ));

            // Return
            return $_user->id;
        }

        // Failed
        return false;
    }

    // Todo: Set New Password
    public static function password_set_new(string $_token, string $_password, ?string $_token_name = null)
    {
        // If Token is Null False
        if ($_token && $_password) {
            // Check Token if is valid
            $verification = new \App\Services\Verification();
            $verified = $verification->verifyVerificationCode(token: $_token, token_name: $_token_name);

            // Verified
            if ($verified) {

                // Set New Password
                $_user = \App\Models\User::where('id', $verified->user)->first();
                $_user->password = Hash::make($_password);
                $_user->save();

                // Message Type
                $_type = ($_token_name != 'passwordresetphone') ? 'email' : 'phone';

                // Create an instance of the class
                $instance = new self();
                $_notify_message = $instance->reset_password_confirmed_message($_user->name, generate: strtolower($_type));

                // todo: send email to confirm new password
                if (strtolower($_type) == 'phone') {
                    $_phoneNo = \App\Services\AfricasTalkingService::formatPhoneNumber($_user->phone);
                    dispatch(new \App\Jobs\Sms\SendSms(
                        $_phoneNo,
                        $_notify_message['sms']
                    ));
                } else {
                    dispatch(new \App\Jobs\Email\SendEmail($_user->email, $_notify_message['email'], "YOUR PASSWORD HAS BEEN SUCCESSFULLY UPDATED"));
                }

                // Return
                return $_user->id;
            }
        }

        // Failed
        return false;
    }

    // TODO: **************************** MESSAGES & MAIL ****************************

    /**
     * Todo: Password Reset Verification Message
     */
    private function reset_account_password_message(string $name,  array $code_link, string $generate = 'both'): array
    {

        // Return
        $results = ['email' => false, 'sms' => false];

        // App Name
        $_app_name = config('services.app_name');

        // OTP Link
        $_otp = (array_key_exists('otplink', $code_link)) ? $code_link['otplink'] : null;

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

            // **** PREPAIRE EMAIL ****

            // Title & Sub Title
            $_title = "Reset Your Password – Action Required!";
            $_subtitle = "";

            // Body
            $_body = "
                <p>Hello <strong>$name</strong>,</p>

                <p>We received a request to reset your password for $_app_name. If you made this request, click the button below to set a new password:</p>
                <p>If you did not request a password reset, please ignore this email. Your account remains secure.</p>
            ";

            // App Name
            $_support_mail = config('services.app_mail.support');

            // Outro
            $_outro = "
            <p>Need help? Contact our support team at <strong>$_support_mail</strong>.</p>

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
            $_sms = "$_app_name: Your password reset code is $_otp.\nUse this to reset your password. If you didn’t request this, ignore this message.";

            // Found - results
            $results['sms'] = $_sms;
        }

        // Return Data
        return $results;
    }

    /**
     * Todo: Confirmantion Password Reset Message
     */
    private function reset_password_confirmed_message(string $name, string $generate = 'both'): array
    {

        // Return
        $results = ['email' => false, 'sms' => false];

        // App Name
        $_app_name = config('services.app_name');

        // If is Both or Email
        if (in_array(strtolower(trim($generate)), ['both', 'email'])) {
            // Logo - Add Link to logo
            $_logo = null;

            // Links & Button
            $_btn['link'] =  null;
            $_btn['title'] = null;

            $_btn_extra = "";

            // **** PREPAIRE EMAIL ****

            // Title & Sub Title
            $_title = "";
            $_subtitle = "";

            // Body
            $_body = "
                <p>Hello <strong>$name</strong>,</p>

                <p>Your password for $_app_name has been successfully updated. If you made this change, no further action is needed.</p>
                <p>If you did not request this change, please reset your password immediately or contact our support team for assistance.</p>
            ";

            // App Name
            $_support_mail = config('services.app_mail.support');

            // Outro
            $_outro = "
            <p>For any concerns, reach out to us at <strong>$_support_mail</strong>.</p>

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
            $_sms = "$_app_name: Your password has been successfully updated. If this was not  you, reset your password immediately or contact support.";

            // Found - results
            $results['sms'] = $_sms;
        }

        // Return Data
        return $results;
    }
}
