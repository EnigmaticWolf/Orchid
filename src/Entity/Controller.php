<?php

namespace AEngine\Orchid\Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Controller
 *
 * For example controller may determine methods:
 * @method void                     before(RequestInterface $request, ResponseInterface $response)
 * @method string|ResponseInterface index(RequestInterface $request, ResponseInterface $response)
 * @method void                     after(RequestInterface $request, ResponseInterface $response)
 */
abstract class Controller
{
    /**
     * If set to False stops controller execution,
     * but before the method will work in any case
     *
     * @var bool
     */
    public $execute = true;
}
