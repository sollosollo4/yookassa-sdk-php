<?php

require_once '../vendor/autoload.php';

// Вариант через метод
$inputDataMatrix = '010463003407001221SxMGorvNuq6Wk91fgr92sdfsdfghfgjh';
$productCode = new \YooKassa\Helpers\ProductCode($inputDataMatrix);
$receiptItem = new \YooKassa\Model\ReceiptItem();
$receiptItem->setProductCode($productCode);

var_dump($receiptItem);

// Вариант через массив
$inputDataMatrix = '010463003407001221SxMGorvNuq6Wk91fgr92sdfsdfghfgjh';
$receiptItem = new \YooKassa\Model\ReceiptItem(array(
    'product_code' => (string)(new \YooKassa\Helpers\ProductCode($inputDataMatrix)),
));

var_dump($receiptItem);
