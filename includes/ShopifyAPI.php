<?php
/**
 * Shopify API Wrapper Class
 * Handles all interactions with Shopify API
 */

class ShopifyAPI {
    private $api_key;
    private $api_secret;
    private $shop_name;
    private $access_token;
    private $base_url;

    /**
     * Constructor
     * @param string $api_key Shopify API Key
     * @param string $api_secret Shopify API Secret
     * @param string $shop_name Shopify Store Name
     * @param string $access_token OAuth Access Token (optional)
     */
    public function __construct($api_key, $api_secret, $shop_name, $access_token = null) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
        $this->shop_name = $shop_name;
        $this->access_token = $access_token;
        $this->base_url = "https://{$shop_name}.myshopify.com/admin/api/2024-01";
    }

    /**
     * Get OAuth authorization URL
     * @param string $redirect_uri Redirect URI after authorization
     * @param array $scopes Required API scopes
     * @return string Authorization URL
     */
    public function getAuthorizationUrl($redirect_uri, $scopes = ['read_products', 'write_products']) {
        $scope_string = implode(',', $scopes);
        return "https://{$this->shop_name}.myshopify.com/admin/oauth/authorize?" . http_build_query([
            'client_id' => $this->api_key,
            'scope' => $scope_string,
            'redirect_uri' => $redirect_uri,
            'state' => bin2hex(random_bytes(16))
        ]);
    }

    /**
     * Exchange authorization code for access token
     * @param string $code Authorization code
     * @return array Response with access token
     */
    public function getAccessToken($code) {
        $url = "https://{$this->shop_name}.myshopify.com/admin/oauth/access_token";
        
        $data = [
            'client_id' => $this->api_key,
            'client_secret' => $this->api_secret,
            'code' => $code
        ];

        $response = $this->makeRequest('POST', $url, $data, false);
        if (isset($response['access_token'])) {
            $this->access_token = $response['access_token'];
        }
        return $response;
    }

    /**
     * Get all products from Shopify store
     * @return array Products array
     */
    public function getProducts() {
        return $this->makeRequest('GET', "{$this->base_url}/products.json");
    }

    /**
     * Get product by ID
     * @param int $product_id Product ID
     * @return array Product details
     */
    public function getProduct($product_id) {
        return $this->makeRequest('GET', "{$this->base_url}/products/{$product_id}.json");
    }

    /**
     * Create a new product
     * @param array $product Product data
     * @return array Created product
     */
    public function createProduct($product) {
        $data = ['product' => $product];
        return $this->makeRequest('POST', "{$this->base_url}/products.json", $data);
    }

    /**
     * Update a product
     * @param int $product_id Product ID
     * @param array $product Product data to update
     * @return array Updated product
     */
    public function updateProduct($product_id, $product) {
        $data = ['product' => $product];
        return $this->makeRequest('PUT', "{$this->base_url}/products/{$product_id}.json", $data);
    }

    /**
     * Delete a product
     * @param int $product_id Product ID
     * @return array Response
     */
    public function deleteProduct($product_id) {
        return $this->makeRequest('DELETE', "{$this->base_url}/products/{$product_id}.json");
    }

    /**
     * Get all orders
     * @param array $params Query parameters
     * @return array Orders array
     */
    public function getOrders($params = []) {
        $query = !empty($params) ? '?' . http_build_query($params) : '';
        return $this->makeRequest('GET', "{$this->base_url}/orders.json{$query}");
    }

    /**
     * Get order by ID
     * @param int $order_id Order ID
     * @return array Order details
     */
    public function getOrder($order_id) {
        return $this->makeRequest('GET', "{$this->base_url}/orders/{$order_id}.json");
    }

    /**
     * Get all customers
     * @return array Customers array
     */
    public function getCustomers() {
        return $this->makeRequest('GET', "{$this->base_url}/customers.json");
    }

    /**
     * Get shop information
     * @return array Shop details
     */
    public function getShopInfo() {
        return $this->makeRequest('GET', "{$this->base_url}/shop.json");
    }

    /**
     * Get inventory levels
     * @param int $product_id Product ID
     * @return array Inventory data
     */
    public function getInventory($product_id) {
        return $this->makeRequest('GET', "{$this->base_url}/products/{$product_id}/variants.json");
    }

    /**
     * Make HTTP request to Shopify API
     * @param string $method HTTP method
     * @param string $url API endpoint URL
     * @param array $data Request data
     * @param bool $use_token Use access token for authentication
     * @return array Response data
     */
    private function makeRequest($method, $url, $data = [], $use_token = true) {
        $headers = [
            'Content-Type: application/json'
        ];

        if ($use_token && $this->access_token) {
            $headers[] = "X-Shopify-Access-Token: {$this->access_token}";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if (!empty($data) && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);
        
        return [
            'status' => $http_code,
            'data' => $result,
            'errors' => isset($result['errors']) ? $result['errors'] : null
        ];
    }

    /**
     * Verify webhook signature
     * @param string $data Webhook data
     * @param string $hmac_header HMAC header from webhook
     * @return bool True if valid
     */
    public function verifyWebhook($data, $hmac_header) {
        $calculated_hmac = base64_encode(
            hash_hmac('sha256', $data, $this->api_secret, true)
        );
        return hash_equals($calculated_hmac, $hmac_header);
    }
}
?>
