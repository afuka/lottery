<?php

namespace App\Repository\Sms;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use Illuminate\Support\Arr;

class Alibaba
{
    public function send($mobile, $code, $sign = '八零五')
    {
        AlibabaCloud::accessKeyClient('LTAI4FyU8R55hVYh2BXqP36g', 'qnfpf0acPWsOZKslX9tlAqTzCTILh4')
            ->regionId('cn-hangzhou')
            ->asDefaultClient();

        try {
            $result = AlibabaCloud::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $mobile,
                        'SignName' => $sign,
                        'TemplateCode' => "SMS_198880037",
                        'TemplateParam' => json_encode([
                            'code' => $code
                        ]),
                    ],
                ])->request();
            
            $result = $result->toArray();
            if(Arr::get($result, 'Code', '') != 'OK') {
                throw new \Exception(Arr::get($result, 'Message', ''), 1);
            }
            return true;
        } catch (ClientException $e) {
            throw new \Exception($e->getErrorMessage(), 1);
        } catch (ServerException $e) {
            throw new \Exception($e->getErrorMessage(), 1);

        }
    }
}
