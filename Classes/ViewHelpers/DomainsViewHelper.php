<?php

namespace KoninklijkeCollective\MyRedirects\ViewHelpers;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * ViewHelper: Domain Info for ui interaction
 *
 * @package KoninklijkeCollective\MyRedirects\ViewHelpers
 */
class DomainsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper implements \TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface
{

    use \TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;
    use \KoninklijkeCollective\MyRedirects\Functions\ObjectManagerTrait;
    use \KoninklijkeCollective\MyRedirects\Functions\BackendUserAuthenticationTrait;

    /**
     * @var boolean
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('as', 'integer', 'Rendered as variable', false, 'domains');
    }

    /**
     * Retrieve domains and return to view
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {

        if (static::getTableConfigurationService()->hasAllowedDomains()) {
            $domains = static::getTableConfigurationService()->parseDomainsInPageRoots(
                static::getDomainService()->getDomains(),
                static::getRootPageService()->getRootPages()
            );

            $select = [];
            $group = 0;
            foreach ($domains as $domain) {
                if ($domain[1] === '--div--') {
                    $group = $domain[0];
                } else {
                    $select[$group][] = $domain;
                }
            }

            $templateVariableContainer = $renderingContext->getVariableProvider();
            $templateVariableContainer->add($arguments['as'], $select);

            $output = $renderChildrenClosure();
            $templateVariableContainer->remove($arguments['as']);
            return $output;
        }

        return '';
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\TableConfigurationService|object
     */
    protected static function getTableConfigurationService()
    {
        return static::getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\TableConfigurationService::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\DomainService|object
     */
    protected static function getDomainService()
    {
        return static::getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\DomainService::class);
    }

    /**
     * @return \KoninklijkeCollective\MyRedirects\Service\RootPageService|object
     */
    protected static function getRootPageService()
    {
        return static::getObjectManager()->get(\KoninklijkeCollective\MyRedirects\Service\RootPageService::class);
    }
}
