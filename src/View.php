<?php

namespace Jaxon\Latte;

use Jaxon\App\View\Store;
use Jaxon\App\View\ViewInterface;
use Jaxon\App\View\ViewTrait;
use Jaxon\Script\JsExpr;
use Jaxon\Script\JxnCall;
use Latte\Engine as LatteEngine;
use Latte\Runtime\Html;

use function Jaxon\attr;
use function Jaxon\jaxon;
use function Jaxon\jq;
use function Jaxon\js;
use function Jaxon\pm;
use function Jaxon\rq;
use function substr;
use function strlen;
use function trim;

class View implements ViewInterface
{
    use ViewTrait;

    /**
     * @var LatteEngine|null
     */
    private ?LatteEngine $xRenderer = null;

    /**
     * @return LatteEngine
     */
    private function _renderer(): LatteEngine
    {
        if(!$this->xRenderer)
        {
            // Render the template
            $this->xRenderer = new LatteEngine();
            $this->xRenderer->setTempDirectory(__DIR__ . '/../cache');

            // Some attributes are wrapped in the Html object, so there are not escaped twice.
            // See https://latte.nette.org/en/develop#toc-disabling-auto-escaping-of-variable

            // Filters for custom Jaxon attributes
            $this->xRenderer->addFilter('jxnHtml', fn(JxnCall $xJxnCall) => new Html(attr()->html($xJxnCall)));
            $this->xRenderer->addFilter('jxnShow', fn(JxnCall $xJxnCall) => new Html(attr()->show($xJxnCall)));
            $this->xRenderer->addFilter('jxnOn', fn(JsExpr $xJsExpr, string|array $on, array $options = []) =>
                new Html(attr()->on($on, $xJsExpr, $options)));
            $this->xRenderer->addFilter('jxnClick', fn(JsExpr $xJsExpr, array $options = []) =>
                new Html(attr()->click($xJsExpr, $options)));

            // Functions for custom Jaxon attributes
            $this->xRenderer->addFunction('jxnHtml', fn(JxnCall $xJxnCall) => new Html(attr()->html($xJxnCall)));
            $this->xRenderer->addFunction('jxnShow', fn(JxnCall $xJxnCall) => new Html(attr()->show($xJxnCall)));
            $this->xRenderer->addFunction('jxnOn', fn(string|array $on, JsExpr $xJsExpr, array $options = []) =>
                new Html(attr()->on($on, $xJsExpr, $options)));
            $this->xRenderer->addFunction('jxnClick', fn(JsExpr $xJsExpr, array $options = []) =>
                new Html(attr()->click($xJsExpr, $options)));
            $this->xRenderer->addFunction('jxnTarget', fn(string $name = '') => new Html(attr()->target($name)));

            $this->xRenderer->addFunction('jq', fn(...$aParams) => jq(...$aParams));
            $this->xRenderer->addFunction('js', fn(...$aParams) => js(...$aParams));
            $this->xRenderer->addFunction('rq', fn(...$aParams) => rq(...$aParams));
            $this->xRenderer->addFunction('pm', fn() => pm());

            // Functions for Jaxon js and CSS codes
            $this->xRenderer->addFunction('jxnCss', fn() => new Html(jaxon()->css()));
            $this->xRenderer->addFunction('jxnJs', fn() => new Html(jaxon()->js()));
            $this->xRenderer->addFunction('jxnScript', fn() => new Html(jaxon()->script()));
        }
        return $this->xRenderer;
    }

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

        $sTemplateFile = $this->sDirectory . $sViewName . $this->sExtension;
        return trim($this->_renderer()->renderToString($sTemplateFile, $store->getViewData()), " \t\n");
    }
}
