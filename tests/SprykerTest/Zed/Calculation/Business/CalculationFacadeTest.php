<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Calculation\Business;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\ExpenseBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\DataBuilder\ProductOptionBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\Transfer\CalculableObjectTransfer;
use Generated\Shared\Transfer\CalculatedDiscountTransfer;
use Generated\Shared\Transfer\ExpenseTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\ProductOptionTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Spryker\Shared\Calculation\CalculationPriceMode;
use Spryker\Zed\Calculation\Business\CalculationBusinessFactory;
use Spryker\Zed\Calculation\Business\CalculationFacade;
use Spryker\Zed\Calculation\CalculationDependencyProvider;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\DiscountAmountAggregatorForGenericAmountPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\DiscountTotalCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ExpenseTotalCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\GrandTotalCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\InitialGrandTotalCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ItemDiscountAmountFullAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ItemProductOptionPriceAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ItemSubtotalAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\ItemTaxAmountFullAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\PriceCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\PriceToPayAggregatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\RefundableAmountCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\RefundTotalCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\SubtotalCalculatorPlugin;
use Spryker\Zed\Calculation\Communication\Plugin\Calculator\TaxTotalCalculatorPlugin;
use Spryker\Zed\Calculation\Dependency\Service\CalculationToUtilPriceServiceBridge;
use Spryker\Zed\Kernel\Container;

/**
 * Auto-generated group annotations
 * @group SprykerTest
 * @group Zed
 * @group Calculation
 * @group Business
 * @group Facade
 * @group CalculationFacadeTest
 * Add your own group annotations below this line
 */
class CalculationFacadeTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\Calculation\CalculationBusinessTester
     */
    protected $tester;

    /**
     * @dataProvider calculatePriceShouldSetDefaultStorePriceValuesDataProvider
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    public function testCalculatePriceShouldSetDefaultStorePriceValuesForItem(QuoteTransfer $quoteTransfer): void
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new PriceCalculatorPlugin(),
            ]
        );

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];
        $this->assertNotEmpty(
            $calculatedItemTransfer->getSumGrossPrice(),
            'Item sum gross price is not set.'
        );
        $this->assertSame(
            $calculatedItemTransfer->getUnitPrice(),
            $calculatedItemTransfer->getUnitGrossPrice(),
            'Item unit price must be the same as the item unit gross price'
        );
        $this->assertNotEmpty($calculatedItemTransfer->getSumPrice(), 'Item sum price is not set.');
        $this->assertSame(
            $calculatedItemTransfer->getSumPrice(),
            $calculatedItemTransfer->getSumGrossPrice(),
            'Item sum price must be the same as the item sum gross price'
        );
    }

    /**
     * @return array
     */
    public function calculatePriceShouldSetDefaultStorePriceValuesDataProvider(): array
    {
        return [
            'int stock' => $this->getDataForCalculatePriceShouldSetDefaultStorePriceValues(2, 1),
            'float stock' => $this->getDataForCalculatePriceShouldSetDefaultStorePriceValues(2.5, 1),
        ];
    }

    /**
     * @dataProvider calculatePriceShouldSetDefaultStorePriceValuesDataProvider
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    public function testCalculatePriceShouldSetDefaultStorePriceValuesForItemOption(QuoteTransfer $quoteTransfer): void
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new PriceCalculatorPlugin(),
            ]
        );

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];
        $calculatedItemProductOptionTransfer = $calculatedItemTransfer->getProductOptions()[0];
        $this->assertNotEmpty(
            $calculatedItemProductOptionTransfer->getSumGrossPrice(),
            "Product option sum gross price is not set."
        );
        $this->assertSame(
            $calculatedItemProductOptionTransfer->getUnitPrice(),
            $calculatedItemProductOptionTransfer->getUnitGrossPrice(),
            'Product option unit price must be the same as the unit gross price'
        );
        $this->assertNotEmpty(
            $calculatedItemProductOptionTransfer->getSumPrice(),
            "Product option sum price is not set."
        );
        $this->assertSame(
            $calculatedItemProductOptionTransfer->getSumPrice(),
            $calculatedItemProductOptionTransfer->getSumGrossPrice(),
            'Product option sum price must be equal sum gross price'
        );
    }

    /**
     * @return void
     */
    public function testCalculatePriceShouldSetDefaultStorePriceValuesForExpense(): void
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new PriceCalculatorPlugin(),
            ]
        );
        /**
         * @var \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
         */
        $quoteTransfer = $this->getDataForCalculatePriceShouldSetDefaultStorePriceValues(2, 1)[0];

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedExpenseTransfer = $quoteTransfer->getExpenses()[0];
        $this->assertNotEmpty(
            $calculatedExpenseTransfer->getSumGrossPrice(),
            'Expense sum gross price is not set.'
        );
        $this->assertSame(
            $calculatedExpenseTransfer->getUnitPrice(),
            $calculatedExpenseTransfer->getUnitGrossPrice(),
            'Expense unit price must be the same as the unit gross price'
        );
        $this->assertNotEmpty($calculatedExpenseTransfer->getSumPrice(), 'Expense sum price is not set.');
        $this->assertSame(
            $calculatedExpenseTransfer->getSumPrice(),
            $calculatedExpenseTransfer->getSumGrossPrice(),
            'Expense sum price must be the same as the sum gross price'
        );
    }

    /**
     * @param int|float $productQuantity
     * @param int $expenseQuantity
     *
     * @return array
     */
    protected function getDataForCalculatePriceShouldSetDefaultStorePriceValues($productQuantity, int $expenseQuantity): array
    {
        $quoteTransfer = (new QuoteBuilder())->seed([
            QuoteTransfer::PRICE_MODE => CalculationPriceMode::PRICE_MODE_GROSS,
        ])->build();

        $itemTransfer = (new ItemBuilder())->seed([
            ItemTransfer::QUANTITY => $productQuantity,
            ItemTransfer::UNIT_GROSS_PRICE => 100,
        ])->build();

        $productOptionTransfer = (new ProductOptionBuilder())->seed([
            ProductOptionTransfer::QUANTITY => $productQuantity,
            ProductOptionTransfer::UNIT_GROSS_PRICE => 10,
        ])->build();

        $itemTransfer->addProductOption($productOptionTransfer);

        $quoteTransfer->addItem($itemTransfer);

        $expenseTransfer = (new ExpenseBuilder())->seed([
            ExpenseTransfer::QUANTITY => $expenseQuantity,
            ExpenseTransfer::UNIT_GROSS_PRICE => 100,
        ])->build();

        $quoteTransfer->addExpense($expenseTransfer);

        return [$quoteTransfer];
    }

    /**
     * @return void
     */
    public function testCalculateProductOptionPriceAggregationShouldSumAllOptionPrices()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new ItemProductOptionPriceAggregatorPlugin(),
            ]
        );

        $itemTransfer = new ItemTransfer();
        $productOptionTransfer = new ProductOptionTransfer();
        $productOptionTransfer->setSumPrice(20);
        $itemTransfer->addProductOption($productOptionTransfer);

        $productOptionTransfer = new ProductOptionTransfer();
        $productOptionTransfer->setSumPrice(20);
        $itemTransfer->addProductOption($productOptionTransfer);

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->addItem($itemTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];

        $this->assertSame(40, $calculatedItemTransfer->getSumProductOptionPriceAggregation());
    }

    /**
     * @dataProvider calculateSumDiscountAmountShouldSumAllItemDiscountsDataProvider
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param int $expectedItemSumDiscountAmountAggregation
     * @param int $expectedExpenseSumDiscountAmountAggregation
     *
     * @return void
     */
    public function testCalculateSumDiscountAmountShouldSumAllItemDiscounts(QuoteTransfer $quoteTransfer, int $expectedItemSumDiscountAmountAggregation, int $expectedExpenseSumDiscountAmountAggregation): void
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new DiscountAmountAggregatorForGenericAmountPlugin(),
            ]
        );

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];
        $calculatedExpenseTransfer = $quoteTransfer->getExpenses()[0];

        $this->assertSame($expectedItemSumDiscountAmountAggregation, $calculatedItemTransfer->getSumDiscountAmountAggregation());
        $this->assertSame($expectedExpenseSumDiscountAmountAggregation, $calculatedExpenseTransfer->getSumDiscountAmountAggregation());
    }

    /**
     * @return array
     */
    public function calculateSumDiscountAmountShouldSumAllItemDiscountsDataProvider(): array
    {
        return [
            'int stock' => $this->getDataForCalculateSumDiscountAmountShouldSumAllItemDiscounts(2, 20, 160, 40),
            'float stock' => $this->getDataForCalculateSumDiscountAmountShouldSumAllItemDiscounts(1.33, 20, 108, 27),
            'float stock small price' => $this->getDataForCalculateSumDiscountAmountShouldSumAllItemDiscounts(1.13, 1, 4, 1),
        ];
    }

    /**
     * @param int|float $quantity
     * @param int $unitAmount
     * @param int $itemResult
     * @param int $expenseResult
     *
     * @return array
     */
    protected function getDataForCalculateSumDiscountAmountShouldSumAllItemDiscounts($quantity, int $unitAmount, int $itemResult, int $expenseResult): array
    {
        $calculatedDiscountMap = [
            CalculatedDiscountTransfer::ID_DISCOUNT => 1,
            CalculatedDiscountTransfer::UNIT_AMOUNT => $unitAmount,
            CalculatedDiscountTransfer::QUANTITY => $quantity,
        ];
        $baseTransferMap = [
            ExpenseTransfer::UNIT_PRICE => 200,
            ExpenseTransfer::SUM_PRICE => 200,
        ];
        $quoteTransfer = (new QuoteBuilder())->build();
        $itemTransfer = (new ItemBuilder())
            ->seed($baseTransferMap)
            ->withCalculatedDiscount($calculatedDiscountMap)
            ->withCalculatedDiscount($calculatedDiscountMap)
            ->withCalculatedDiscount($calculatedDiscountMap)
            ->withCalculatedDiscount($calculatedDiscountMap)->build();
        $quoteTransfer->addItem($itemTransfer);

        $expenseTransfer = (new ExpenseBuilder())
            ->seed($baseTransferMap)
            ->withCalculatedDiscount($calculatedDiscountMap)
            ->build();

        $quoteTransfer->addExpense($expenseTransfer);

        return [$quoteTransfer, $itemResult, $expenseResult];
    }

    /**
     * @return void
     */
    public function testCalculateFullDiscountAmountShouldSumAllItemsAndAdditions()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new ItemDiscountAmountFullAggregatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumDiscountAmountAggregation(20);

        $productOptionTransfer = new ProductOptionTransfer();
        $productOptionTransfer->setSumDiscountAmountAggregation(20);

        $itemTransfer->addProductOption($productOptionTransfer);

        $quoteTransfer->addItem($itemTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];

        $this->assertSame(40, $calculatedItemTransfer->getSumDiscountAmountFullAggregation());
    }

    /**
     * @return void
     */
    public function testCalculateTaxAmountFullAggregationShouldSumAllTaxesWithAdditions()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new ItemTaxAmountFullAggregatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumTaxAmount(10);

        $productOptionTransfer = new ProductOptionTransfer();
        $productOptionTransfer->setSumTaxAmount(10);

        $itemTransfer->addProductOption($productOptionTransfer);

        $quoteTransfer->addItem($itemTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];
        $this->assertSame(20, $calculatedItemTransfer->getSumTaxAmountFullAggregation());
    }

    /**
     * @return void
     */
    public function testCalculateSumAggregationShouldSumItemAndAllAdditionPrices()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new ItemSubtotalAggregatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setUnitPrice(5);
        $itemTransfer->setSumPrice(10);

        $productOptionTransfer = new ProductOptionTransfer();
        $productOptionTransfer->setUnitPrice(5);
        $productOptionTransfer->setSumPrice(10);
        $itemTransfer->addProductOption($productOptionTransfer);

        $productOptionTransfer = new ProductOptionTransfer();
        $productOptionTransfer->setUnitPrice(5);
        $productOptionTransfer->setSumPrice(20);
        $itemTransfer->addProductOption($productOptionTransfer);

        $quoteTransfer->addItem($itemTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];
        $this->assertSame(40, $calculatedItemTransfer->getSumSubtotalAggregation());
        $this->assertSame(15, $calculatedItemTransfer->getUnitSubtotalAggregation());
    }

    /**
     * @return void
     */
    public function testCalculatePriceToPayAggregation()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new PriceToPayAggregatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setUnitSubtotalAggregation(20);
        $itemTransfer->setSumSubtotalAggregation(40);
        $itemTransfer->setSumDiscountAmountFullAggregation(5);

        $quoteTransfer->addItem($itemTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];
        $this->assertSame(35, $calculatedItemTransfer->getSumPriceToPayAggregation());
    }

    /**
     * @return void
     */
    public function testCalculateSubtotalShouldSumAllItemsWithAdditions()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new SubtotalCalculatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumSubtotalAggregation(10);
        $quoteTransfer->addItem($itemTransfer);

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumSubtotalAggregation(10);
        $quoteTransfer->addItem($itemTransfer);

        $totalsTransfer = new TotalsTransfer();
        $quoteTransfer->setTotals($totalsTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedTotalsTransfer = $quoteTransfer->getTotals();
        $this->assertSame(20, $calculatedTotalsTransfer->getSubtotal());
    }

    /**
     * @return void
     */
    public function testCalculateExpenseTotalShouldSumAllOrderExpenses()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new ExpenseTotalCalculatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setSumPrice(10);
        $quoteTransfer->addExpense($expenseTransfer);

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setSumPrice(10);
        $quoteTransfer->addExpense($expenseTransfer);

        $totalsTransfer = new TotalsTransfer();
        $quoteTransfer->setTotals($totalsTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedOrderExpenseTotal = $quoteTransfer->getTotals()->getExpenseTotal();
        $this->assertSame(20, $calculatedOrderExpenseTotal);
    }

    /**
     * @return void
     */
    public function testCalculateDiscountTotalShouldSumAllDiscounts()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new DiscountTotalCalculatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumDiscountAmountFullAggregation(10);
        $quoteTransfer->addItem($itemTransfer);

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumDiscountAmountFullAggregation(10);
        $quoteTransfer->addItem($itemTransfer);

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setSumDiscountAmountAggregation(10);
        $quoteTransfer->addExpense($expenseTransfer);

        $totalsTransfer = new TotalsTransfer();
        $quoteTransfer->setTotals($totalsTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedTotalDiscountAmount = $quoteTransfer->getTotals()->getDiscountTotal();
        $this->assertSame(30, $calculatedTotalDiscountAmount);
    }

    /**
     * @return void
     */
    public function testCalculateTaxTotalShouldSumAllTaxAmounts()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new TaxTotalCalculatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumTaxAmountFullAggregation(10);
        $quoteTransfer->addItem($itemTransfer);

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumTaxAmountFullAggregation(10);
        $quoteTransfer->addItem($itemTransfer);

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setSumTaxAmount(10);
        $quoteTransfer->addExpense($expenseTransfer);

        $totalsTransfer = new TotalsTransfer();
        $quoteTransfer->setTotals($totalsTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedTaxAmount = $quoteTransfer->getTotals()->getTaxTotal()->getAmount();

        $this->assertSame(30, $calculatedTaxAmount);
    }

    /**
     * @dataProvider calculateRefundTotalShouldSumAllRefundableAmountsDataProvider
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param int $expectedResult
     *
     * @return void
     */
    public function testCalculateRefundTotalShouldSumAllRefundableAmounts(QuoteTransfer $quoteTransfer, int $expectedResult): void
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new RefundTotalCalculatorPlugin(),
            ]
        );

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedRefundTotal = $quoteTransfer->getTotals()->getRefundTotal();

        $this->assertSame($expectedResult, $calculatedRefundTotal);
    }

    /**
     * @return array
     */
    public function calculateRefundTotalShouldSumAllRefundableAmountsDataProvider(): array
    {
        return [
            'int data' => $this->getDataForCalculateRefundTotalShouldSumAllRefundableAmounts(30),
        ];
    }

    /**
     * @param int $expectedResult
     *
     * @return array
     */
    protected function getDataForCalculateRefundTotalShouldSumAllRefundableAmounts(int $expectedResult): array
    {
        $quoteTransfer = (new QuoteBuilder())
            ->withExpense([
                ExpenseTransfer::REFUNDABLE_AMOUNT => 10,
            ])
            ->build();
        $itemTransfer = (new ItemBuilder())->seed([
            ItemTransfer::REFUNDABLE_AMOUNT => 10,
        ])->withProductOption([
            ProductOptionTransfer::REFUNDABLE_AMOUNT => 10,
        ])->build();
        $quoteTransfer->addItem($itemTransfer);

        $totalsTransfer = new TotalsTransfer();
        $quoteTransfer->setTotals($totalsTransfer);

        return [$quoteTransfer, $expectedResult];
    }

    /**
     * @return void
     */
    public function testCalculateRefundableAmount()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new RefundableAmountCalculatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumPriceToPayAggregation(10);
        $itemTransfer->setCanceledAmount(5);

        $quoteTransfer->addItem($itemTransfer);

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setSumPriceToPayAggregation(10);
        $expenseTransfer->setCanceledAmount(2);
        $quoteTransfer->addExpense($expenseTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedItemTransfer = $quoteTransfer->getItems()[0];
        $calculatedExpenseTransfer = $quoteTransfer->getExpenses()[0];

        $this->assertSame(5, $calculatedItemTransfer->getRefundableAmount());
        $this->assertSame(8, $calculatedExpenseTransfer->getRefundableAmount());
    }

    /**
     * @return void
     */
    public function testCalculateGrandTotal()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new GrandTotalCalculatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumPriceToPayAggregation(100);
        $quoteTransfer->addItem($itemTransfer);

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setSumPriceToPayAggregation(150);
        $quoteTransfer->addExpense($expenseTransfer);

        $totalsTransfer = new TotalsTransfer();

        $quoteTransfer->setTotals($totalsTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedGrandTotal = $quoteTransfer->getTotals()->getGrandTotal();

        $this->assertSame(250, $calculatedGrandTotal);
    }

    /**
     * @return void
     */
    public function testCalculateInitialGrandTotal()
    {
        $calculationFacade = $this->createCalculationFacade(
            [
                new InitialGrandTotalCalculatorPlugin(),
            ]
        );

        $quoteTransfer = new QuoteTransfer();

        $itemTransfer = new ItemTransfer();
        $itemTransfer->setSumSubtotalAggregation(200);
        $quoteTransfer->addItem($itemTransfer);

        $expenseTransfer = new ExpenseTransfer();
        $expenseTransfer->setSumPrice(350);
        $quoteTransfer->addExpense($expenseTransfer);

        $totalsTransfer = new TotalsTransfer();

        $quoteTransfer->setTotals($totalsTransfer);

        $calculationFacade->recalculateQuote($quoteTransfer);

        $calculatedGrandTotal = $quoteTransfer->getTotals()->getGrandTotal();

        $this->assertSame(550, $calculatedGrandTotal);
    }

    /**
     * @param array $calculatorPlugins
     *
     * @return \Spryker\Zed\Calculation\Business\CalculationFacade
     */
    protected function createCalculationFacade(array $calculatorPlugins)
    {
        $calculationFacade = new CalculationFacade();

        $calculationBusinessFactory = new CalculationBusinessFactory();

        $container = new Container();
        $container[CalculationDependencyProvider::SERVICE_UTIL_PRICE] = function () {
            return new CalculationToUtilPriceServiceBridge(
                $this->tester->getLocator()->utilPrice()->service()
            );
        };

        $container[CalculationDependencyProvider::QUOTE_CALCULATOR_PLUGIN_STACK] = function () use ($calculatorPlugins) {
            return $calculatorPlugins;
        };

        $container[CalculationDependencyProvider::PLUGINS_QUOTE_POST_RECALCULATE] = function (Container $container) {
            return [];
        };

        $calculationBusinessFactory->setContainer($container);
        $calculationFacade->setFactory($calculationBusinessFactory);

        return $calculationFacade;
    }

    /**
     * @return void
     */
    public function testRemoveCanceledAmountResetsCancelledAmount()
    {
        // Assign
        $calculationFacade = new CalculationFacade();
        $items = (new ItemTransfer())->setCanceledAmount(100);
        $calculableObjectTransfer = new CalculableObjectTransfer();
        $calculableObjectTransfer->setItems(new ArrayObject([$items]));
        $expectedCancelledAmount = 0;

        // Act
        $calculationFacade->removeCanceledAmount($calculableObjectTransfer);
        $actualCancelledAmount = $calculableObjectTransfer->getItems()[0]->getCanceledAmount();

        // Assert
        $this->assertSame($expectedCancelledAmount, $actualCancelledAmount);
    }
}
