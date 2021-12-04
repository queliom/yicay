<?php

namespace App\Services;

class AuthorizationService {

    public static function get() {

		$ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://run.mocky.io/v3/8fafdd68-a090-496f-8c9a-3442cf30dae6' );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		$response = json_decode(curl_exec($ch), true);;

		curl_close($ch);

        if($response['message'] != 'Autorizado') {
            return false;
        }

        return true;

    }

}