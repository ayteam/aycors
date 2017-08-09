<?php

namespace Aycors\Stack;

use think\Config;
use think\Request;
use think\Response;

/**
 * Fork of asm89/stack-cors
 */
class CorsService
{
    private $options;
    protected static $instance;

    private function __construct()
    {
        $this->options = $this->normalizeOptions();
    }

    /**
     * @return static
     * get:
     */
    public static function instance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function normalizeOptions()
    {
        $options = Config::get('cors') + array(
                'supportsCredentials' => true,
                'allowedOrigins' => ['*'],
                'allowedHeaders' => ['*'],
                'allowedMethods' => ['*'],
                'exposedHeaders' => [],
                'maxAge' => 0,
            );

        // normalize array('*') to true
        if (in_array('*', $options['allowedOrigins'])) {
            $options['allowedOrigins'] = true;
        }
        if (in_array('*', $options['allowedHeaders'])) {
            $options['allowedHeaders'] = true;
        } else {
            $options['allowedHeaders'] = array_map('strtolower', $options['allowedHeaders']);
        }

        if (in_array('*', $options['allowedMethods'])) {
            $options['allowedMethods'] = true;
        } else {
            $options['allowedMethods'] = array_map('strtoupper', $options['allowedMethods']);
        }

        return $options;
    }

    public function isActualRequestAllowed(Request $request)
    {
        return $this->checkOrigin($request);
    }

    /**
     * 是否跨域请求
     * @param Request $request
     * @return bool
     */
    public function isCorsRequest(Request $request)
    {
        return $request->header('Origin') && !$this->isSameHost($request);
    }

    public function isPreflightRequest(Request $request)
    {
        return $this->isCorsRequest($request)
            && $request->method() === 'OPTIONS'
            &&  !empty($request->header('Access-Control-Request-Method'));
    }

    public function addActualRequestHeaders(Response $response, Request $request)
    {
        if (!$this->checkOrigin($request)) {
            return $response;
        }

        $response->header('Access-Control-Allow-Origin', $request->header('Origin'));

        if (!$response->getHeader('Vary')) {
            $response->header('Vary', 'Origin');
        } else {
            $response->header('Vary', $response->getHeader('Vary') . ', Origin');
        }

        if ($this->options['supportsCredentials']) {
            $response->header('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->options['exposedHeaders']) {
            $response->header('Access-Control-Expose-Headers', implode(', ', $this->options['exposedHeaders']));
        }

        return $response;
    }

    //
    public function handlePreflightRequest(Request $request)
    {
        if (true !== $check = $this->checkPreflightRequestConditions($request)) {
            return $check;
        }

        return $this->buildPreflightCheckResponse($request);
    }

    private function buildPreflightCheckResponse(Request $request)
    {
        $response = Response::create([], 'json');
        if ($this->options['supportsCredentials']) {
            $response->header('Access-Control-Allow-Credentials', 'true');
        }

        $response->header('Access-Control-Allow-Origin', $request->header('Origin'));

        if ($this->options['maxAge']) {
            $response->header('Access-Control-Max-Age', $this->options['maxAge']);
        }

        $allowMethods = $this->options['allowedMethods'] === true
            ? strtoupper($request->header('Access-Control-Request-Method'))
            : implode(', ', $this->options['allowedMethods']);
        $response->header('Access-Control-Allow-Methods', $allowMethods);

        $allowHeaders = $this->options['allowedHeaders'] === true
            ? strtoupper($request->header('Access-Control-Request-Headers'))
            : implode(', ', $this->options['allowedHeaders']);
        $response->header('Access-Control-Allow-Headers', $allowHeaders);

        return $response;
    }

    private function checkPreflightRequestConditions(Request $request)
    {
        if (!$this->checkOrigin($request)) {
            return $this->createBadRequestResponse(403, 'Origin not allowed');
        }

        if (!$this->checkMethod($request)) {
            return $this->createBadRequestResponse(405, 'Method not allowed');
        }

        // if allowedHeaders has been set to true ('*' allow all flag) just skip this check
        if ($this->options['allowedHeaders'] !== true && $request->header('Access-Control-Request-Headers')) {
            $headers        = strtolower($request->header('Access-Control-Request-Headers'));
            $requestHeaders = array_filter(explode(',', $headers));

            foreach ($requestHeaders as $header) {
                if (!in_array(trim($header), $this->options['allowedHeaders'])) {
                    return $this->createBadRequestResponse(403, 'Header not allowed');
                }
            }
        }

        return true;
    }

    private function createBadRequestResponse($code, $reason = '')
    {
        return new Response($reason, $code);
    }


    /**
     * 是否同源
     * @param Request $request
     * @return bool
     */
    private function isSameHost(Request $request)
    {
        return $request->header('Origin') === $request->domain();
    }

    private function checkOrigin(Request $request)
    {
        if ($this->options['allowedOrigins'] === true) {
            // allow all '*' flag
            return true;
        }
        $origin = $request->header('Origin');

        return in_array($origin, $this->options['allowedOrigins']);
    }

    private function checkMethod(Request $request)
    {
        if ($this->options['allowedMethods'] === true) {
            // allow all '*' flag
            return true;
        }

        $requestMethod = strtoupper($request->header('Access-Control-Request-Method'));
        return in_array($requestMethod, $this->options['allowedMethods']);
    }
}
