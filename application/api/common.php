<?php

use think\exception\HttpResponseException;
use think\Request;
use think\Response;

/**
 * 失败返回
 * @param $msg
 * @param null $data
 * @param int $code
 * DateTime: 2024-03-12 22:26
 */
function app_exception($msg, $data = [], $code = 0)
{
    data_response($code, $data, $msg);
}

/**
 * 成功返回
 * @param $code
 * @param null $data
 * @param string $msg
 * DateTime: 2024-03-12 22:26
 */
function app_response($code, $data = [], $msg = '')
{
    data_response($code, $data, $msg);
}

function data_response($code, $data, $msg, $type = 'json', array $header = [])
{
    $request = Request::instance();
    $result = [
        'traceId' => $request->param('traceId', ''),
        'success'  => $code == 200,
        'host' => $request->ip(),
        'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
    ];
    if (!$result['success']) {
        $result['errorCode'] = $code;
        $result['errorMessage'] = $msg;
    }

    if (isset($header['statuscode'])) {
        $code = $header['statuscode'];
        unset($header['statuscode']);
    } else {
        //未设置状态码,根据code值判断
        $code = $code >= 1000 || $code < 200 ? 200 : $code;
    }
    $response = Response::create($result, $type, $code)->header($header);
    throw new HttpResponseException($response);
}