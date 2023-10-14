<?php
namespace maike\core;

use Spatie\Macroable\Macroable;

class Request extends \think\Request
{
    use Macroable;
    
    protected $filter = ['trim', 'htmlspecialchars'];
}

