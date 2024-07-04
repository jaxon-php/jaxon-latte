<?php

namespace Jaxon\Latte;

use Jaxon\App\View\Store;
use Jaxon\App\View\ViewInterface;
use Jaxon\App\View\ViewTrait;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JxnCall;
use Latte\Engine as LatteEngine;
use Latte\Runtime\Html;

use function substr;
use function strlen;
use function trim;
use function Jaxon\attr;
use function Jaxon\jq;
use function Jaxon\js;
use function Jaxon\pm;
use function Jaxon\rq;

class View implements ViewInterface
{
    use ViewTrait;

    /**
     * Render a view
     *
     * @param Store $store    A store populated with the view data
     *
     * @return string
     */
    public function render(Store $store): string
    {
        $sViewName = $store->getViewName();
        $sNamespace = $store->getNamespace();
        // For this view renderer, the view name doesn't need to be prepended with the namespace.
        $nNsLen = strlen($sNamespace) + 2;
        if(substr($sViewName, 0, $nNsLen) === $sNamespace . '::')
        {
            $sViewName = substr($sViewName, $nNsLen);
        }

        // View namespace
        $this->setCurrentNamespace($sNamespace);

        // Render the template
        $xRenderer = new LatteEngine();

        // Some attributes are wrapped in the Html object, so there are not escaped twice.
        // See https://latte.nette.org/en/develop#toc-disabling-auto-escaping-of-variable

        // Filters for custom Jaxon attributes
        $xRenderer->addFilter('jxnHtml', fn(JxnCall $xJxnCall) => new Html(attr()->html($xJxnCall)));
        $xRenderer->addFilter('jxnShow', fn(JxnCall $xJxnCall) => new Html(attr()->show($xJxnCall)));

        // Functions for custom Jaxon attributes
        $xRenderer->addFunction('jxnHtml', fn(JxnCall $xJxnCall) => new Html(attr()->html($xJxnCall)));
        $xRenderer->addFunction('jxnShow', fn(JxnCall $xJxnCall) => new Html(attr()->show($xJxnCall)));
        $xRenderer->addFunction('jxnTarget', fn(string $name = '') => new Html(attr()->target($name)));
        $xRenderer->addFunction('jxnOn', fn(string|array $on, JsExpr $xJsExpr, array $options = []) =>
            new Html(attr()->on($on, $xJsExpr, $options)));

        $xRenderer->addFunction('jq', fn(...$aParams) => jq(...$aParams));
        $xRenderer->addFunction('js', fn(...$aParams) => js(...$aParams));
        $xRenderer->addFunction('rq', fn(...$aParams) => rq(...$aParams));
        $xRenderer->addFunction('pm', fn() => pm());

        $xRenderer->setTempDirectory(__DIR__ . '/../cache');
        $sTemplateFile = $this->sDirectory . $sViewName . $this->sExtension;
        return trim($xRenderer->renderToString($sTemplateFile, $store->getViewData()), " \t\n");
    }
}
