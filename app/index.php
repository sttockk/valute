<?php

header("Content-Type: text/plain; charset=utf-8");

require __DIR__ . '/Models/Currency.php';

$currency = new Currency(["USD", "BGN", "BRL", "KRW", "CNY"]);

try {
    $currency->checkValute();
} catch (\Exception $e) {
    echo "Error " . $e->getMessage();
}
