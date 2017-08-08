<?php

namespace Aycors;

use think\Request;
use think\Response;

class Cors
{

    public static function json($data = [], $code = 200,$header = [], $options = [])
    {
        $request = Request::instance();
        $corsService = new CorsService($options);

        if (!$corsService->isCorsRequest($request)) {
            return Response::create($data, 'json', $code, $header, $options);
        }

        if ($corsService->isPreflightRequest($request)) {
            return $corsService->handlePreflightRequest($request);
        }

        if (!$corsService->isActualRequestAllowed($request)) {
            return new Response('Not allowed.', 403);
        }

        $response = Response::create($data, 'json', $code, $options);
        return $corsService->addActualRequestHeaders($response, $request);

    }


}
