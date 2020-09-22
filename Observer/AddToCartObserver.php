<?php

namespace Walee\Tracking\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\ObjectManager;

class AddToCartObserver implements ObserverInterface
{
 protected $logger;

 public function __construct(LoggerInterface$logger) {
 $this->logger = $logger;
 }

 public function execute(\Magento\Framework\Event\Observer $observer)
 {
    try {
        $objectManager = ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $matchDataTable = $resource->getTableName('wt_match_data');
        $cartsTable = $resource->getTableName('wt_carts');
        $item = $observer->getEvent()->getData('quote_item');
        $item = ($item->getParentItem() ? $item->getParentItem() : $item);
        $product_sku = $item->getProduct()->getSku();
        $product_name = $item->getProduct()->getName();
        $product_price = $item->getProduct()->getFinalPrice();
        $product_id = $item->getProduct()->getId();
        $ip = $this->get_client_ipp();

        $sql = "SELECT referrer FROM $matchDataTable 
                WHERE ip = '$ip' AND ip != '' AND createdOn > DATE_ADD(NOW(), INTERVAL -7 DAY) 
                AND referrer is not null ORDER BY createdOn DESC LIMIT 1;";
        $result1 = $connection->fetchAll($sql);
        if($result1 && isset($result1[0]) && isset($result1[0]['referrer'])){
            $sql = "INSERT INTO " . $cartsTable . "(referrer, proId, proSku, proName, proPrice) 
                                            VALUES ('".$result1[0]['referrer']."', '$product_id', '$product_sku', '$product_name', '$product_price')";
            $result2 = $connection->query($sql);
            $this->sendCurlPost("/api/tracking/newWordPressHook", [
                'proId' => $product_id, 
                'referrer' => $result1[0]['referrer'],
                'hookType' => 'Add To Cart',
                'proSku' => $product_sku,
                'proName' => $product_name,
                'proPrice' => $product_price,
                'proPriceSale' => '',
                'foriegn_id' => $connection->lastInsertId()
            ]);
        }
        file_put_contents('addToCartLogs',  "something added to cart".PHP_EOL, FILE_APPEND | LOCK_EX);

    }catch (\Exception $e) {
        file_put_contents('tempWaleeFileError', $e->getMessage() );
        $this->logger->info($e->getMessage());
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

 public function sendCurlPost($url, $vars){
    $vars['installed_version'] = '0.0.1';
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
}
