<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Calculation\Business\Model\Calculator;

use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ExpensesTransfer;
use Generated\Shared\Transfer\ExpenseTotalItemTransfer;
use Generated\Shared\Transfer\ExpenseTotalsTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Spryker\Zed\Calculation\Business\Model\CalculableInterface;
use Spryker\Zed\Calculation\Dependency\Plugin\TotalsCalculatorPluginInterface;

class ExpenseTotalsCalculator implements
    TotalsCalculatorPluginInterface,
    ExpenseTotalsCalculatorInterface
{

    /**
     * @param TotalsTransfer $totalsTransfer
     * @param CalculableInterface $calculableContainer
     * @param $calculableItems
     *
     * @return void
     */
    public function recalculateTotals(
        TotalsTransfer $totalsTransfer,
        CalculableInterface $calculableContainer,
        $calculableItems
    ) {
        $expenseTotal = $this->createExpenseTotalTransfer($calculableContainer, $calculableItems);
        $totalsTransfer->setExpenses($expenseTotal);
    }

    /**
     * @param CalculableInterface $calculableContainer
     *
     * @return int
     */
    public function calculateExpenseTotal(CalculableInterface $calculableContainer)
    {
        $orderExpensesTotal = 0;
        if ($calculableContainer->getCalculableObject()->getExpenses() instanceof ExpensesTransfer) {
            $expenses = $calculableContainer->getCalculableObject()->getExpenses()->getCalculationExpenses();
        } else {
            $expenses = $calculableContainer->getCalculableObject()->getExpenses();
        }

        foreach ($expenses as $expense) {
            if ($expense->getGrossPrice() !== null) {
                $orderExpensesTotal += $expense->getGrossPrice();
            }
        }

        return $orderExpensesTotal;
    }

    /**
     * @param \ArrayObject|CalculableItemInterface[] $calculableItems
     *
     * @return int
     */
    protected function calculateItemExpenseTotal(\ArrayObject $calculableItems)
    {
        $itemExpenseTotal = 0;
        foreach ($calculableItems as $item) {
            if ($item->getExpenses() instanceof ExpensesTransfer) {
                $expenses = $item->getExpenses()->getCalculationExpenses();
            } else {
                $expenses = $item->getExpenses();
            }

            foreach ($expenses as $expense) {
                if ($expense->getGrossPrice() !== null) {
                    $itemExpenseTotal += $expense->getGrossPrice();
                }
            }
        }

        return $itemExpenseTotal;
    }

    /**
     * @param CalculableInterface $calculableContainer
     * @param $calculableItems
     *
     * @return array
     */
    protected function sumExpenseItems(
        CalculableInterface $calculableContainer,
        $calculableItems
    ) {
        $orderExpenseItems = [];

        if ($calculableContainer->getCalculableObject()->getExpenses() instanceof ExpensesTransfer) {
            foreach ($calculableContainer->getCalculableObject()->getExpenses()->getCalculationExpenses() as $expense) {
                $this->transformExpenseToExpenseTotalItemInArray($expense, $orderExpenseItems);
            }
        }

        foreach ($calculableItems as $item) {
            foreach ($item->getExpenses() as $expense) {
                $this->transformExpenseToExpenseTotalItemInArray($expense, $orderExpenseItems);
            }
        }

        return $orderExpenseItems;
    }

    /**
     * @param ExpenseTransfer $expense
     * @param array $arrayOfExpenseTotalItems
     *
     * @return void
     */
    protected function transformExpenseToExpenseTotalItemInArray(ExpenseTransfer $expense, array &$arrayOfExpenseTotalItems)
    {
        if (!isset($arrayOfExpenseTotalItems[$expense->getType()])) {
            $item = new ExpenseTotalItemTransfer();
            $item->setName($expense->getName());
            $item->setType($expense->getType());
        } else {
            $item = $arrayOfExpenseTotalItems[$expense->getType()];
        }

        $item->setGrossPrice($item->getGrossPrice() + $expense->getGrossPrice());
        $item->setPriceToPay($item->getPriceToPay() + $expense->getPriceToPay());
        $arrayOfExpenseTotalItems[$expense->getType()] = $item;
    }

    /**
     * @param CalculableInterface $calculableContainer
     * @param $calculableItems
     *
     * @return ExpenseTotalsTransfer
     */
    protected function createExpenseTotalTransfer(
        CalculableInterface $calculableContainer,
        $calculableItems
    ) {
        $expenseTotalTransfer = new ExpenseTotalsTransfer();
        $expenseTotalTransfer->setTotalOrderAmount($this->calculateExpenseTotal($calculableContainer));
        $expenseTotalTransfer->setTotalItemAmount($this->calculateItemExpenseTotal($calculableItems));

        foreach ($this->sumExpenseItems($calculableContainer, $calculableItems) as $expenseTotalItem) {
            $expenseTotalTransfer->addExpenseItem($expenseTotalItem);
        }

        return $expenseTotalTransfer;
    }

}
