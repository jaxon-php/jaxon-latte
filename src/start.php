<?php

jaxon()->sentry()->addViewRenderer('latte', function () {
    return new Jaxon\Latte\View();
});
