<?php

require_once '../vendor/autoload.php';

$client = new \YooKassa\Client();
// по логин/паролю
$client->setAuth('xxxxxx', 'test_XXXXXXX');
// или по oauth токену
$client->setAuthToken('token_XXXXXXX');

// Получение информации о подключенном магазине
try {
    $response = $client->me();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Запрос на создание платежа
try {
    $idempotenceKey = uniqid('', true);
    $response = $client->createPayment(
        array(
            'amount' => array(
                'value' => '1.00',
                'currency' => 'RUB',
            ),
            'confirmation' => array(
                'type' => 'redirect',
                'return_url' => 'https://merchant-site.ru/payment-notification',
            ),
            'description' => 'Оплата заказа на сумму 1 руб',
            'metadata' => array(
                'orderNumber' => 1001
            ),
            'capture' => true,
        ),
        $idempotenceKey
    );
    $confirmation = $response->getConfirmation();
    $redirectUrl = $confirmation->getConfirmationUrl();
    // Далее производим редирект на полученный URL
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Запрос на частичное подтверждение платежа
$paymentId = '24e89cb0-000f-5000-9000-1de77fa0d6df';
try {
    $response = $client->capturePayment(
        array(
            'amount' => array(
                'value' => '1500.00',
                'currency' => 'RUB',
            ),
            'transfers' => array(
                array(
                    'account_id' => '123',
                    'amount' => array(
                        'value' => '1000.00',
                        'currency' => 'RUB',
                    ),
                ),
                array(
                    'account_id' => '456',
                    'amount' => array(
                        'value' => '500.00',
                        'currency' => 'RUB',
                    ),
                ),
            ),
        ),
        $paymentId,
        uniqid('', true)
    );
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Запрос на отмену незавершенного платежа
$paymentId = '24e89cb0-000f-5000-9000-1de77fa0d6df';
try {
    $response = $client->cancelPayment($paymentId, uniqid('', true));
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Запрос на создание чека
try {
    $response = $client->createReceipt(
        array(
            'customer' => array(
                'email' => 'johndoe@yoomoney.ru',
                'phone' => '79000000000',
            ),
            'type' => 'payment',
            'payment_id' => '24e89cb0-000f-5000-9000-1de77fa0d6df',
            'on_behalf_of' => '123',
            'send' => true,
            'items' => array(
                array(
                    'description' => 'Платок Gucci',
                    'quantity' => '1.00',
                    'amount' => array(
                        'value' => '3000.00',
                        'currency' => 'RUB',
                    ),
                    'vat_code' => 2,
                    'payment_mode' => 'full_payment',
                    'payment_subject' => 'commodity',
                ),
            ),
            'tax_system_code' => 1,
        ),
        uniqid('', true)
    );
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Запрос на создание возврата
try {
    $response = $client->createRefund(
        array(
            'payment_id' => '24e89cb0-000f-5000-9000-1de77fa0d6df',
            'amount' => array(
                'value' => '1000.00',
                'currency' => 'RUB',
            ),
            'sources' => array(
                array(
                    'account_id' => '456',
                    'amount' => array(
                        'value' => '1000.00',
                        'currency' => 'RUB',
                    )
                ),
            ),
        ),
        uniqid('', true)
    );
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить информацию о платеже
try {
    $response = $client->getPaymentInfo('24e89cb0-000f-5000-9000-1de77fa0d6df');
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить информацию о чеке
try {
    $response = $client->getReceiptInfo('ra-27ed1660-0001-0050-7a5e-10f80e0f0f29');
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить информацию о возврате
try {
    $response = $client->getRefundInfo('216749f7-0016-50be-b000-078d43a63ae4');
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Работа с Webhook
// В данном примере мы устанавливаем вебхуки для succeeded и canceled уведомлений.
// А так же проверяем, не установлены ли уже вебхуки. И если установлены на неверный адрес, удаляем.
try {
    $webHookUrl = 'https://merchant-site.ru/payment-notification';
    $needWebHookList = array(
        \YooKassa\Model\NotificationEventType::PAYMENT_SUCCEEDED,
        \YooKassa\Model\NotificationEventType::PAYMENT_CANCELED
    );
    $currentWebHookList = $client->getWebhooks()->getItems();
    foreach ($needWebHookList as $event) {
        $hookIsSet = false;
        foreach ($currentWebHookList as $webHook) {
            if ($webHook->getEvent() !== $event) {
                continue;
            }
            if ($webHook->getUrl() !== $webHookUrl) {
                $client->removeWebhook($webHook->getId());
            } else {
                $hookIsSet = true;
            }
            break;
        }
        if (!$hookIsSet) {
            $client->addWebhook(array('event' => $event, 'url' => $webHookUrl));
        }
    }
    $response = 'SUCCESS';
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить список платежей с фильтрацией
$cursor = null;
$params = array(
    'limit' => 30,
    'status' => \YooKassa\Model\PaymentStatus::CANCELED,
    'payment_method' => \YooKassa\Model\PaymentMethodType::BANK_CARD,
    'created_at_gte' => '2021-01-01T00:00:00.000Z',
    'created_at_lt' => '2021-03-30T23:59:59.999Z',
);
try {
    do {
        $params['cursor'] = $cursor;
        $payments = $client->getPayments($params);
        foreach ($payments->getItems() as $payment) {
            echo $payment->getCreatedAt()->format('Y-m-d H:i:s') . ' - ' .
                 $payment->getStatus() . ' - ' .
                 $payment->getId() . "\n";
        }
    } while ($cursor = $payments->getNextCursor());
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить список чеков с фильтрацией
$cursor = null;
$params = array(
    'limit' => 30,
    'status' => \YooKassa\Model\ReceiptRegistrationStatus::SUCCEEDED,
    'payment_id' => '1da5c87d-0984-50e8-a7f3-8de646dd9ec9',
    'created_at_gte' => '2021-01-01T00:00:00.000Z',
    'created_at_lt' => '2021-03-30T23:59:59.999Z',
);
try {
    do {
        $params['cursor'] = $cursor;
        $receipts = $client->getReceipts($params);
        foreach ($receipts->getItems() as $receipt) {
            echo $receipt->getStatus() . ' - ' . $receipt->getId() . "\n";
        }
    } while ($cursor = $receipts->getNextCursor());
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить список возвратов с фильтрацией
$cursor = null;
$params = array(
    'limit' => 30,
    'status' => \YooKassa\Model\RefundStatus::SUCCEEDED,
    'payment_id' => '1da5c87d-0984-50e8-a7f3-8de646dd9ec9',
    'created_at_gte' => '2021-01-01T00:00:00.000Z',
    'created_at_lt' => '2021-03-30T23:59:59.999Z',
);
try {
    do {
        $params['cursor'] = $cursor;
        $refunds = $client->getRefunds($params);
        foreach ($refunds->getItems() as $refund) {
            echo $refund->getCreatedAt()->format('Y-m-d H:i:s') . ' - ' .
                 $refund->getStatus() . ' - ' .
                 $refund->getId() . "\n";
        }
    } while ($cursor = $refunds->getNextCursor());
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Запрос на создание сделки
try {
    $response = $client->createDeal(
        array(
            'type' => \YooKassa\Model\Deal\DealType::SAFE_DEAL,
            'fee_moment' => \YooKassa\Model\Deal\FeeMoment::PAYMENT_SUCCEEDED,
            'metadata' => array(
                'order_id' => '37',
            ),
            'description' => 'SAFE_DEAL 123554642-2432FF344R',
        ),
        uniqid('', true)
    );
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить информацию о сделке
try {
    $response = $client->getDealInfo('dl-2909e77d-1022-5003-8004-0c37205b3208');
    echo $response->getStatus();
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);

// Получить список сделок с фильтрацией
$cursor = null;
$params = array(
    'limit' => 30,
    'status' => \YooKassa\Model\Deal\DealStatus::OPENED,
    'full_text_search' => 'DEAL',
    'created_at_gte' => '2021-10-01T00:00:00.000Z',
    'created_at_lt' => '2021-11-01T23:59:59.999Z',
);
try {
    do {
        $params['cursor'] = $cursor;
        $deals = $client->getDeals($params);
        foreach ($deals->getItems() as $deal) {
            $res = array(
                $deal->getCreatedAt()->format('Y-m-d H:i:s'),
                $deal->getBalance()->getValue() . ' ' . $deal->getBalance()->getCurrency(),
                $deal->getPayoutBalance()->getValue() . ' ' . $deal->getBalance()->getCurrency(),
                $deal->getStatus(),
                $deal->getId(),
            );
            echo implode(' - ', $res) . "\n";
        }
    } while ($cursor = $deals->getNextCursor());
} catch (\Exception $e) {
    $response = $e;
    var_dump($response);
}

// Создание выплаты
$request = array(
    'amount' => array(
        'value' => '80.00',
        'currency' => 'RUB',
    ),
    'payout_destination_data' => array(
        'type' => \YooKassa\Model\PaymentMethodType::YOO_MONEY,
        'accountNumber' => '4100116075156746',
    ),
    'description' => 'Выплата по заказу №37',
    'metadata' => array(
        'order_id' => '37'
    ),
    'deal' => array(
        'id' => 'dl-2909e77d-0022-5000-8000-0c37205b3208',
    ),
);
$idempotenceKey = uniqid('', true);
try {
    $idempotenceKey = uniqid('', true);
    $result = $client->createPayout($request, $idempotenceKey
    );
} catch (\Exception $e) {
    $result = $e;
}

var_dump($result);

// Получить информацию о выплате
$payoutId = 'po-285c0ab7-0003-5000-9000-0e1166498fda';
try {
    $response = $client->getPayoutInfo($payoutId);
} catch (\Exception $e) {
    $response = $e;
}

var_dump($response);
