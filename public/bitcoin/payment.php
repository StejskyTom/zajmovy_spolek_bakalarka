<?php
require "header.php";


// Pripojeni Composer
require_once '../../vendor/autoload.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Pripojeni knihovny pro qr kody
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\Writer\PngWriter;

// Vycesteni cache aby uzuvatel se ne vratil na stranku platby bez povtorniho vypelneni formulare
//header("Cache-Control: no-cache, no-store, must-revalidate");
//header("Pragma: no-cache");
//header("Expires: 0");


$binanceAPIURL = 'https://api.binance.com';

// Funkce pro pozadavek k API a overeni chyb
function get_btc_rate($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Ошибка запроса: ' . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Overeni jestli data obdrzene metodou POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Diklarace promenych a zjisteni kurzu BTC pomoci API
        $amount = $_POST['amountUSD'];
        $mail = $_POST['mail'];
        $data = get_btc_rate($binanceAPIURL . '/api/v3/ticker/price?symbol=BTCUSDT');
        $amountBTC = $amount / $data['price'];
        $yourBitcoinAddress = '#######LYiZxBGwQUYJFrb';

        // Vytvoreni a ulozeni QR kodu
        $qrCodeData = 'bitcoin:' . $yourBitcoinAddress . '?amount=' . round($amountBTC, 6);
        $writer = new PngWriter();
        $qrCode = QrCode::create($qrCodeData)
            ->setEncoding(new Encoding('UTF-8'))
            ->setSize(300)
            ->setMargin(10)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $result = $writer->write($qrCode);
        $result->saveToFile(__DIR__ . '/qrcode.png');

        // Zobrazeni informace pro platbu
        echo '<p class="text-center">Donate BTC address: ' . $yourBitcoinAddress . '</p>';
        echo '<p class="text-center">Donate amount: ' . $amount . ' USD</p>';
        echo '<p class="text-center">Donate amount in BTC: ' . round($amountBTC, 6) . ' BTC</p>';
        echo '<img src="qrcode.png" alt="QR Code" class="mx-auto d-block mb-5">';

        // Potvrzeni platby a presmerovani na odeselani mailu
        echo '<form action="mail.php" method="post">
                    <input type="hidden" name="mail" value="' . $mail . '">
                    <button type="submit" class="btn btn-lg btn-success mx-auto d-block" name="send"> Confirm payment </button>
                  </form>';
    } catch (Exception $e) {
        echo 'Error' . $e->getMessage();
    }
} else {
    echo 'Error';
}

require "footer.php";