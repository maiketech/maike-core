<?php

namespace maike\interfaces;

interface ListenerInterface
{
    public function handle($event): void;
}
