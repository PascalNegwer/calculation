<?php
/**
 * Copyright © 2017-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Calculation\Business\Calculator;

use ArrayObject;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Calculation\Business\Calculator\CalculatorInterface;

class RefundableAmountCalculator implements CalculatorInterface
{

    /**
     * @param \Generated\Shared\Transfer\CalculableObjectTransfer $calculableObjectTransfer
     *
     * @return void
     */
    public function recalculate(CalculableObjectTransfer $calculableObjectTransfer)
    {
        $this->calculateRefundableAmountForItems($calculableObjectTransfer->getItems());
        $this->calculateRefundableAmountForExpenses($calculableObjectTransfer->getExpenses());
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ItemTransfer[] $items
     *
     * @return void
     */
    protected function calculateRefundableAmountForItems(ArrayObject $items)
    {
        foreach ($items as $itemTransfer) {
            $itemTransfer->requireUnitPriceToPayAggregation();

            $itemTransfer->setRefundableAmount(
                $itemTransfer->getUnitPriceToPayAggregation() - $itemTransfer->getCanceledAmount()
            );
        }
    }

    /**
     * @param \ArrayObject|\Generated\Shared\Transfer\ExpenseTransfer[] $expenses
     *
     * @return void
     */
    protected function calculateRefundableAmountForExpenses(ArrayObject $expenses)
    {
        foreach ($expenses as $expenseTransfer) {
            $expenseTransfer->setRefundableAmount(
                $expenseTransfer->getUnitPriceToPayAggregation() - $expenseTransfer->getCanceledAmount()
            );
        }
    }

}
