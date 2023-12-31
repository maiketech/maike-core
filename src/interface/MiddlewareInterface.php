<?php

namespace maike\interface;

use maike\core\Request;

interface MiddlewareInterface
{
    public function handle(Request $request, \Closure $next);
}
