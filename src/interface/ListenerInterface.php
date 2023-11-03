<?php

namespace maike\interface;

interface ListenerInterface
{
    public function handle($event): void;
}
