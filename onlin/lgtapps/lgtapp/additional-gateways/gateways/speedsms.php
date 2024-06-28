<?php

namespace SMSGateway;


class SpeedSMS {
    // docs at: https://speedsms.vn/sms-api-service/
    public static function sendSMS($gateway_fields, $mobile, $message, $test_call) {
        $access_token = $gateway_fields['access_token'];
        $sender = $gateway_fields['sender'];

        return self::process_sms($access_token, $sender, $mobile, $message, $test_call);
    }

    public static function process_sms($access_token, $sender, $mobile, $message, $test_call) {
        $curl = curl_init();
        $params = array(
            'access-token' => $access_token,
            'sender' => $sender,
            'to' => $mobile,
            'content' => $message,
            'type' => 'sms_type',
        );
        $encoded_query = http_build_query($params);
        curl_setopt($curl, CURLOPT_URL, 'https://api.speedsms.vn/index.php/sms/send?' . $encoded_query);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curl_error = curl_errno($curl);
        curl_close($curl);

        if($test_call) return $result;

        if ($curl_error !== 0) {
            return false;
        }

        $is_success = 200 <= $code && $code < 300;

        return $is_success;
    }

}
