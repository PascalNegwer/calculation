<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Calculation\Dependency\Plugin;

use Spryker\Zed\Calculation\Business\Model\CalculableInterface;

interface CalculatorPluginInterface
{

    /**
     * @param \Spryker\Zed\Calculation\Business\Model\CalculableInterface $calculableContainer
     */
    public function recalculate(CalculableInterface $calculableContainer);

}
