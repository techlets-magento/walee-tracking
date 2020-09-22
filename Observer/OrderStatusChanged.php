<?php

namespace Walee\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\ObjectManager;
// Testttttttt
class OrderStatusChanged implements ObserverInterface
{
 protected $logger;

 public function __construct(LoggerInterface$logger) {
 $this->logger = $logger;
 }

 public function sendCurlPost($url, $vars){
    $vars['installed_version'] = '1.0.0';
    $vars['type'] = 'magento';
    $vars['domain'] = $_SERVER['SERVER_NAME'];
    $payload = json_encode( $vars );
    $postUrl = "https://influencersofpakistan.com".$url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$postUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $server_output = curl_exec($ch);
    curl_close ($ch);
}


 public function saveOrder($observer) {
    $objectManager = ObjectManager::getInstance();
    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
    $connection = $resource->getConnection();
    $saleTable = $resource->getTableName('wt_sales');
    $saleLineTable = $resource->getTableName('wt_sale_line');
    $matchDataTable = $resource->getTableName('wt_match_data');

    $order = $observer->getEvent()->getOrder();
    $order_id = $order->getId();
    $order_id2 = $order->getIncrementId();
    // for new order use $order_id2
    $customer = $observer->getEvent()->getCustomer();
    $shipAddress =  $observer->getOrder()->getShippingAddress();
    $phone = $observer->getOrder()->getBillingAddress()->getTelephone();
    $phone2 = $observer->getOrder()->getShippingAddress()->getTelephone();
    $order_status = $order->getState();
    $total = $order->getGrandTotal();
    // $total = number_format($total);
 
    $orderIdForUse = null;
    if($order_id2){
      $orderIdForUse = $order_id2;
    }
    if($order_id){
      $orderIdForUse = $order_id;
    }
    $phoneForUse = null;
    if($phone){
      $phoneForUse = $phone;
    }
    if($phone2){
      $phoneForUse = $phone2;
    }
  
   
    $totalItems = 0;
    $itemNames = '';
    foreach ($order->getAllItems() as $item) {
        $totalItems++;
        $itemNames .= $item->getName().' - ';
    }

    $ip = $this->get_client_ipp();

    $sql = "SELECT referrer FROM $matchDataTable 
            WHERE ip = '$ip' AND ip != '' AND createdOn > DATE_ADD(NOW(), INTERVAL -7 DAY) 
            AND referrer is not null ORDER BY createdOn DESC LIMIT 1;";
    $result = $connection->fetchAll($sql);
    if($order_status == 'new'){
        if($result && isset($result[0]) && isset($result[0]['referrer'])){
            $sql = "INSERT INTO $saleTable (referrer, orderId, userPhone, currency, paymentMethod, userMail, totalItems, totalPrice, order_status)
            VALUES ('".$result[0]['referrer']."', '$orderIdForUse', '$phoneForUse', 'PKR', 'payMethod', 'email', '$totalItems', '$total', '$order_status')";
            $connection->query($sql);
    
            $hookArr = [
                'orderId' => $orderIdForUse, 
                'referrer' => $result[0]['referrer'],
                'hookType' => 'Sales',
                'userPhone' => $phoneForUse,
                'userMail' => 'email',
                'totalItems' => $totalItems,
                'totalPrice' => $total,
                'order_status' => $order_status,
                'currency' => 'PKR',
                'paymentMethod' => 'DontKnowNow',
                'orderLine' => [],
                'foriegn_id' => $connection->lastInsertId()
            ];
            $this->sendCurlPost("/api/tracking/newWordPressHook", $hookArr);
        }
    } else {
        $sql = "SELECT id, referrer FROM $saleTable WHERE orderId = '$orderIdForUse';";
        $result = $connection->fetchAll($sql);
        
        if($result && isset($result[0]) && isset($result[0]['id'])){
            $hookArr = [
                'order_status' => $order_status,
                'hookType' => 'Sales',
                'referrer' => $result[0]['referrer'],
                'foriegn_id' => $result[0]['id']
            ];
            $this->sendCurlPost("/api/tracking/orderStatusUpdated", $hookArr);
        }
    }
 }

 public function get_client_ipp() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    return $ipaddress;
}

 public function execute(\Magento\Framework\Event\Observer $observer)
 {
    try {
        $this->saveOrder($observer);
    }catch (\Exception $e) {
        file_put_contents('tempWaleeFileError', $e->getMessage() );
        $this->logger->info($e->getMessage());
    }
 }
}
