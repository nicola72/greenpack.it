<?php
namespace App\Service;


class GoogleRecaptcha
{
    public static function verifyGoogleRecaptcha($data,$secret)
    {
        if(!isset($data['g-recaptcha-response']) && !empty($data['g-recaptcha-response']))
        {
            return false;
        }

        $verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
        $responseData = json_decode($verifyResponse);

        if($responseData->success)
        {
            return true;
        }
        return false;
    }
}