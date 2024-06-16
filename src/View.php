<?php

namespace Jaxon\Latte;

use Jaxon\App\View\Store;
use Jaxon\App\View\ViewInterface;
use Jaxon\App\View\ViewTrait;
use Jaxon\Script\JsCall;
use Jaxon\Script\JsExpr;
use Latte\Engine as LatteEngine;

use function substr;
use function strlen;
use function trim;
use function Jaxon\attr;

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
        // Functions and filters for custom Jaxon attributes
        $xRenderer->addFunction('jxnFunc', fn(JsExpr $xJsExpr) => attr()->func($xJsExpr));
        $xRenderer->addFunction('jxnShow', fn(JsCall $xJsCall) => attr()->show($xJsCall));
        $xRenderer->addFilter('jxnFunc', fn(JsExpr $xJsExpr) => attr()->func($xJsExpr));
        $xRenderer->addFilter('jxnShow', fn(JsCall $xJsCall) => attr()->show($xJsCall));

        $xRenderer->setTempDirectory(__DIR__ . '/../cache');
        $sTemplateFile = $this->sDirectory . $sViewName . $this->sExtension;
        return trim($xRenderer->renderToString($sTemplateFile, $store->getViewData()), " \t\n");
    }
}
