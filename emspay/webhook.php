<?php
include dirname(__FILE__).'/../../config/config.inc.php';

$payment_method_mapping = array(
    "bank-transfer" => "Bank Transfer",
    "ideal" => "iDEAL",
    "credit-card" => "Creditcard",
    "bancontact" => "Bancontact",
    "klarna-pay-later" => "Klarna Pay Later",
    "klarna-pay-now" => "Klarna Pay Now",
    "paypal" => "PayPal",
    "payconiq" => "Payconiq",
    "afterpay" => "Afterpay",
    "applepay" => "Applepay",
    "amex" => "American Express",
    "tikkie-payment-request" => "Tikkie Payment Request",
    "wechat" => "WeChat",
);


$input = json_decode(file_get_contents("php://input"), true);
$ginger_order_id = $input['order_id'];
echo("WEBHOOK: Starting for ginger_order_id: ".htmlentities($ginger_order_id) . "\n");

if (!in_array($input['event'], array("status_changed"))) {
    die("Only work to do if the status changed");
}

$row = Db::getInstance()->getRow(
    sprintf(
        'SELECT * FROM `%s` WHERE `%s` = \'%s\'',
        _DB_PREFIX_.'emspay',
        'ginger_order_id',
        pSQL($ginger_order_id)
    )
);

if (!$row) {
    die("WEBHOOK: Error - No row found for ginger_order_id: ".htmlentities($ginger_order_id));
}

echo "WEBHOOK: Payment method: " . $row['payment_method'] . "\n";

include dirname(__FILE__).'/../'.$row['payment_method'].'/'.$row['payment_method'].'.php';

$emspay = new $row['payment_method']();

$order_details = $emspay->ginger->getOrder($ginger_order_id);

echo "WEBHOOK: Found status: ".$order_details['status']."\n";

if ($order_details['status']) {

    if (!empty($row['id_order'])) {
        echo "WEBHOOK: id_order was not empty but: ".$row['id_order']."\n";

        if (empty(Context::getContext()->link)) {
            Context::getContext()->link = new link();
        } // workaround a prestashop bug so email is sent
        $order = new Order((int) $row['id_order']);

        // only update order state if differs
        if ($order->current_state != (int) Configuration::get('PS_OS_PAYMENT')) {
            echo "WEBHOOK: updating status, old status was: ".$order->current_state."\n";

            $new_history = new OrderHistory();
            $new_history->id_order = (int) $order->id;
            $order_status = (int) Configuration::get('PS_OS_PAYMENT');
            $new_history->changeIdOrderState((int) $order_status, $order, true);
            $new_history->addWithemail(true);
        }
    } else {
        echo "WEBHOOK: id_order is empty\n";

        // check if the cart id already is an order
        if ($id_order = intval(Order::getOrderByCartId((int) ($row['id_cart'])))) {
            echo "WEBHOOK: cart was already promoted to order\n";

        } else {
            echo "WEBHOOK: promote cart to order\n";

            $emspay->validateOrder($row['id_cart'], Configuration::get('PS_OS_PAYMENT'), $order_details['amount'] / 100,
                $payment_method_mapping[$order_details['transactions'][0]['payment_method']], null,
                array("transaction_id" => $order_details['transactions'][0]['id']), null, false, $row['key']);
            $id_order = $emspay->currentOrder;
        }
        echo "WEBHOOK: update database; set id_order to: ".$id_order."\n";

        Db::getInstance()->update('emspay', array("id_order" => $id_order),
            '`ginger_order_id` = "'.Db::getInstance()->escape($ginger_order_id).'"');

        $emspay->ginger->updateOrder($id_order,$order_details);
    }
}
