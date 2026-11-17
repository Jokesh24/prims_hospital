<?php

namespace App\Helpers;

class NumberHelper
{
    public static function amountInWords($number)
    {
        $no = floor($number);
        $point = round(($number - $no) * 100);

        $words = [
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        ];

        $digits = [
            '',
            'Hundred',
            'Thousand',
            'Lakh',
            'Crore'
        ];

        $str = [];

        $crore = floor($no / 10000000);
        $no %= 10000000;

        $lakh = floor($no / 100000);
        $no %= 100000;

        $thousand = floor($no / 1000);
        $no %= 1000;

        $hundred = floor($no / 100);
        $no %= 100;

        if ($crore)
            $str[] = self::twoDigits($crore, $words) . " Crore";

        if ($lakh)
            $str[] = self::twoDigits($lakh, $words) . " Lakh";

        if ($thousand)
            $str[] = self::twoDigits($thousand, $words) . " Thousand";

        if ($hundred)
            $str[] = $words[$hundred] . " Hundred";

        if ($no)
            $str[] = self::twoDigits($no, $words);

        $result = implode(' ', $str);

        if ($point > 0) {
            $result .= " and " .
                self::twoDigits($point, $words) .
                " Paise";
        }

        return trim($result) . " Only";
    }

    private static function twoDigits($number, $words)
    {
        if ($number < 21) {
            return $words[$number];
        }

        return $words[floor($number / 10) * 10] . ' ' . $words[$number % 10];
    }
}