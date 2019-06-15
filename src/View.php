<?php

namespace Jaxon\Latte;

use Jaxon\Contracts\View as ViewContract;
use Jaxon\Ui\View\Store;

class View implements ViewContract
{
    use \Jaxon\Features\View\Namespaces;

    /**
     * Render a view
     *
     * @param Store         $store        A store populated with the view data
     *
     * @return string        The string representation of the view
     */
    public function render(Store $store)
    {
        $sViewName = $store->getViewName();
        $sNamespace = $store->getNamespace();
        // For this view renderer, the view name doesn't need to be prepended with the namespace.
        $nNsLen = strlen($sNamespace) + 2;
        if(substr($sViewName, 0, $nNsLen) == $sNamespace . '::')
        {
            $sViewName = substr($sViewName, $nNsLen);
        }

        // View namespace
        $this->setCurrentNamespace($sNamespace);

        // Render the template
        $xRenderer = new \Latte\Engine;
        $xRenderer->setTempDirectory(__DIR__ . '/../cache');
        $sTemplateFile = $this->sDirectory . $sViewName . $this->sExtension;
        return trim($xRenderer->renderToString($sTemplateFile, $store->getViewData()), " \t\n");
    }
}
