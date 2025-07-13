<?php

Jaxon\jaxon()->di()->getViewRenderer()
    ->addRenderer('latte', fn() => new Jaxon\Latte\View());
