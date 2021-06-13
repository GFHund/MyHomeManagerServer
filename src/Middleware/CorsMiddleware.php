<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CorsMiddleware implements MiddlewareInterface{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        $response = $handler->handle($request);
        $response = $response->cors($request)
        ->allowOrigin(['*','http://127.0.0.1:4200','http://localhost:4200'])
        ->allowMethods(['GET',' POST',' PUT','DELETE',' OPTIONS'])
        ->allowHeaders(['*'])
        ->build();
        return $response;
    }
}