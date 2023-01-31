<?php
namespace Ced\Fruugo\Api;

interface OrderRevenue
{
    /**
     * Returns order data to cedcommerce
     *
     * @api
     * @param string $currentMonth
     * @return json
     */
    public function getRevenueData(/*$currentMonth*/);
}