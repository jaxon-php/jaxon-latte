<?php

namespace Jaxon\Latte;

use function Jaxon\jaxon;

jaxon()->di()->getViewRenderer()
    ->addRenderer('latte', fn() => new View());
