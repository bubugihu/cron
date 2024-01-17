<?php

namespace App\Library\Api;

use Cake\Chronos\Date;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;

class Gmail_Api
{
    const URL_GET_MAIL = "https://script.google.com/macros/s/AKfycbzNjTOfWl8YkIOv2S10MNGHigf3RGraM17rEwSyApmeugHNVcxLdDM4F87-nUsdWCGUwQ/exec";
    const URL_MARK_READ = "https://script.google.com/macros/s/AKfycbwjiSpTSS3C-7YpGVXlqgPJsgdQmLcxJMek4pmdP1V0qrd9XE-SyOJGMOzno0TiZzAH/exec";
    public function getMailUnread()
    {
        $date = new Date();
        $today_format = $date->format("Y/m/d");
        $data = [
            'search' => "is:unread from:mailalert@acb.com.vn after:$today_format"
        ];
        Log::debug($data['search']);
        $ch = curl_init(self::URL_GET_MAIL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $data);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        Log::debug('HTTP code getMailUnread: ' . $httpcode);
        return json_decode($response, true);
    }

    public function markRead()
    {
        $date = new Date();
        $today_format = $date->format("Y/m/d");
        $data = [
            'search' => "is:unread from:mailalert@acb.com.vn after:$today_format"
        ];
        $ch = curl_init(self::URL_MARK_READ);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS , $data);
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        Log::debug('HTTP code markRead: ' . $httpcode);
        return json_decode($response, true);
    }

    public function getTransaction($mail_array)
    {
        $transaction = [];
        foreach($mail_array as $key_mail => $mail)
        {
            $body = $mail[2]; //body
            $match = [];
            preg_match_all('/\*(.*?)\*/', $body, $match);
            $transaction[$key_mail]['account'] =  $match[1][0];
            $transaction[$key_mail]['balance'] = $this->formatBalance($match[1][1]);

            $trans_date_array = explode("/",$match[1][2]);
            $trans_date = $trans_date_array[2] . "/" . $trans_date_array[1] . "/" . $trans_date_array[0] . " 10:00:00";
            $transaction[$key_mail]['trans_date'] =  new FrozenTime($trans_date);
            $transaction[$key_mail]['content'] =  $match[1][4];
            $transaction[$key_mail]['type'] =  substr($match[1][3],0,1);
            $transaction[$key_mail]['amount'] =  $this->formatAmount($match[1][3]);

        }
        return $transaction;
    }

    private function formatBalance($str)
    {
        return floatval(substr(str_replace(",","",$str), 0, -4));
    }

    private function formatAmount($str)
    {
        return floatval(substr(substr(str_replace(",","",$str), 0, -4),1));
    }
}
