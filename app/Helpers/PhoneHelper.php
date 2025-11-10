<?php

namespace App\Helpers;

class PhoneHelper
{
    public static function sanitizePhone($phone)
    {
        return preg_replace('/\D/', '', $phone);
    }

    public static function formatPhone($phone)
    {
        // Ex: 11999998888 => (11) 9 9999-8888
        return preg_replace('/(\d{2})(\d{1})(\d{4})(\d{4})/', '($1) $2 $3-$4', $phone);
    }
}
