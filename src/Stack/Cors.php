<?php

namespace Aycors\Stack;

use think\Request;
use think\Response;

class Cors
{

    protected $corsService;

    public static function json($data = [], $code = 200)
    {
        $request = Request::instance();
        $corsService = CorsService::instance();

        if (!$corsService->isCorsRequest($request)) {
            return Response::create($data, 'json', $code );
        }

        if ($corsService->isPreflightRequest($request)) {
            return $corsService->handlePreflightRequest($request);
        }

        if (!$corsService->isActualRequestAllowed($request)) {
            return new Response('Not allowed.', 403);
        }

        $response = Response::create($data, 'json',$code);
        return $corsService->addActualRequestHeaders($response, $request);

    }

}
