<?php
namespace Eccube\Service\Calculator;

use Eccube\Entity\Order;
use Eccube\Entity\OrderDetail;

class CalculateContext
{
    protected $Order;
    protected $OrderDetails = []; // Collection になってる？

    // $app['eccube.calculate.strategies'] に DI する
    protected $CalculateStrategies = [];

    public function executeCalculator()
    {
        foreach ($this->CalculateStrategies as $Strategy) {
            $Strategy->execute($this->OrderDetails);
        }

        foreach($this->OrderDetails as $OrderDetail) {
            if (!$this->Order->getOrderDetails()->contains($OrderDetail)) {
                $OrderDetail->setOrder($this->Order);
                $this->Order->addOrderDetail($OrderDetail);
                // ここのタイミングで Persist 可能?
            }
        }

        return $this->calculateOrder($this->Order);
    }

    public function calculateOrder(Order $Order)
    {
        // OrderDetails の計算結果を Order にセットする
        $subTotal = $Order->calculateSubTotal();
        $Order->setSubtotal($subTotal);
        $total = $Order->getTotalPrice();
        if ($total < 0) {
            $total = 0;
        }
        $Order->setTotal($total);
        $Order->setPaymentTotal($total);
        return $Order;
    }

    public function setCalculateStrategies(array $strategies)
    {
        $this->CalculateStrategies = $strategies;
    }

    public function setOrder(Order $Order)
    {
        $this->Order = $Order;
        $this->OrderDetails = $Order->getOrderDetails();
    }
}
