<?php

Jaxon\jaxon()->di()->getViewManager()->addRenderer('latte', function () {
    return new Jaxon\Latte\View();
});
