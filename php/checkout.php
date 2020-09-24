<?php

/**
 *
 * Example
 * Capturing Checkout data
 * from Convertpack Webhook POST
 *
 * convertpack.io
 *
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') :

    $raw_payload = file_get_contents('php://input');
    $convertpack_data = json_decode($raw_payload, true);

    if (is_array($convertpack_data)) :

        /**
         * Webhook info
         */
        // What triggered the webhook
        // ie. transaction_rejected
        $webhook_event = $convertpack_data['event'];

        // When the webhook was triggered (ISO 8601)
        // ie. 2020-12-20T10:05:10-03:00
        // Do not use as reference to transaction latest update time!
        $webhook_triggered_at = $convertpack_data['triggered_at'];

        /**
         * Customer personal data
         */
        // Customer name
        $customer_first_name = $convertpack_data['customer']['first_name'];
        $customer_last_name = $convertpack_data['customer']['last_name'];
        $customer_full_name = $customer_first_name . ' ' . $customer_last_name;

        // Identification document (ID) number
        // ie. Brazil: 86252818003
        $customer_document_number = $convertpack_data['customer']['document']['number'];

        // Identification document (ID) type
        // ie. Brazil: CPF
        $customer_document_type = $convertpack_data['customer']['document']['type'];

        // E-mail
        $customer_email = $convertpack_data['customer']['email'];

        // Phone without DDI (International phone code), only numbers
        // ie. USA: (541) 754-3010 => 7543010
        // ie. Brazil: (11) 9-9999-1111 => 11999991111
        $customer_phone = $convertpack_data['customer']['phone']['number'];

        // Phone DDI
        // ie. USA: 1
        // ie. Brazil: 55
        $customer_phone_ddi = $convertpack_data['customer']['phone']['ddi'];

        // Phone with DDI
        // ie. USA: 15417543010
        // ie. Brazil: 5511999991111
        $customer_phone_with_ddi = $customer_phone_ddi . $customer_phone;

        /**
         * Customer address
         *
         * Data only available if there is a physical product on transaction.
         * Will return `null` if not available.
         */
        /**
         * Convertpack offers two formats of addresses:
         * - international format (address 1 + address 2)
         * - destination format (most common format in user country)
         *
         * By default we use `international_format`
         * if you want to change to destination format, you need to
         * create your own variables. Below we show an example of
         * the format used in Brazil.
         */

        // Can be: `international_format` or `destination_format`
        $customer_address_format = 'international_format';

        if ($customer_address_format === 'international_format') :
            // Model for `international_format` (default)

            $address_data = $convertpack_data['customer']['address']['international_format'];

            $customer_address_1 = $address_data['address_1'];           // ie. 2815 Directors Row
            $customer_address_2 = $address_data['address_2'];           // ie. Ste 100 Office 546
            $customer_city = $address_data['city'];                     // ie. Orlando
            $customer_state = $address_data['state'];                   // ie. FL
            $customer_zip_code = $address_data['zip_code'];             // ie. 32809
            $customer_country = $address_data['country'];               // ie. United States of America
            $customer_country_code = $address_data['country_code'];     // ie. US

        else :
            // Model for `destination_format`
            // Using Brazil format.
            // For another country, create your own variables.

            $address_data = $convertpack_data['customer']['address']['destination_format'];

            $customer_address_street_name = $address_data['street_name'];       // ie. Av Paulista
            $customer_address_street_number = $address_data['street_number'];   // ie. 100
            $customer_address_complement = $address_data['complement'];         // ie. Apto 305
            $customer_city = $address_data['city'];                             // ie. Sao Paulo
            $customer_federal_unit = $address_data['federal_unit'];             // ie. SP
            $customer_zip_code = $address_data['zip_code'];                     // ie. 01310200
            $customer_country = $address_data['country'];                       // ie. Brasil
            $customer_country_code = $address_data['country_code'];             // ie. BR

        endif;

        /**
         * Transaction data
         */
        // Transaction creation time (ISO 8601)
        // ie. 2020-12-20T10:05:10-03:00
        $transaction_created_at = $convertpack_data['transaction']['created_at'];

        // Transaction latest update time (ISO 8601)
        // ie. 2020-12-20T10:05:15-03:00
        $transaction_updated_at = $convertpack_data['transaction']['updated_at'];

        // Transaction ID
        // ie. CPK-191178870
        $transaction_id = $convertpack_data['transaction']['id'];

        // Payment method
        // ie. credit_card
        $transaction_method = $convertpack_data['transaction']['method'];

        // Transaction status
        // ie. rejected
        $transaction_status = $convertpack_data['transaction']['status'];

        // Transaction currency
        // ie. USA: USD
        // ie. Brazil: BRL
        $transaction_currency = $convertpack_data['transaction']['currency'];

        // Gateway used
        // ie. USA: stripe
        // ie. Brazil: mercado_pago
        $transaction_gateway_name = $convertpack_data['transaction']['gateway']['name'];

        // Gateway account used
        // ie. your-email@domain.com
        $transaction_gateway_account = $convertpack_data['transaction']['gateway']['account'];

        /**
         * Products
         */
        // Array of products
        $products = $convertpack_data['transaction']['products'];

        // Loop to check all products on this transaction
        // Just an example. Change to fit your system.
        $products_keys = array_keys($products);
        $products_count = count($products);

        for ($i = 0; $i < $products_count; $i++) {
            $product = $products[$products_keys[$i]];

            $product_is_upsell = $product['is_upsell'];
            $product_is_order_bump = $product['is_order_bump'];
            $product_quantity = $product['quantity'];
            $product_name = $product['name'];
            $product_type = $product['type'];
            $product_id = $product['id'];
            $product_unit_price = $product['unit_price'];
            $product_sku = $product['sku'];
        }

        // Products subtotal
        $products_subtotal = $convertpack_data['transaction']['products_subtotal'];

        // Progressive discount based on product quantity
        // Only available if you activated for a product
        $volume_discount = $convertpack_data['transaction']['volume_discount'];

        /**
         * Coupon
         */
        // Was a coupon used on this transaction?
        // Can be: `true` or `false`
        $coupon_used = $convertpack_data['transaction']['coupon']['used'];

        // Coupon code used by customer
        $coupon_code = $convertpack_data['transaction']['coupon']['code'];

        // Amount discounted of products subtotal
        $coupon_amount = $convertpack_data['transaction']['coupon']['amount'];

        // Type
        // Can be:
        // - `fixed` for fixed value
        // - `percentage` for percentage value
        $coupon_type = $convertpack_data['transaction']['coupon']['type'];

        // Discount
        // ie. Fixed value = 5 => US$ 5.00
        // ie. Percentage value = 5 => 5%
        $coupon_amount = $convertpack_data['transaction']['coupon']['amount'];

        /**
         * Shipping
         */
        // Amount
        $shipping_amount = $convertpack_data['transaction']['shipping']['amount'];

        // Shipping method name
        $shipping_method_name = $convertpack_data['transaction']['shipping']['name'];

        // Shipping method id
        $shipping_method_id = $convertpack_data['transaction']['shipping']['id'];

        /**
         * Total
         */
        // Discount for selected gateway
        // Only available if you offer discount for this gateway
        $gateway_discount_amount = $convertpack_data['transaction']['gateway_discount'];

        // Order total (amount paid by customer)
        $transaction_total_amount = $convertpack_data['transaction']['total_amount'];

        /**
         * Boleto bancário data
         * (PAYMENT METHOD ONLY FOR BRAZIL)
         */
        // Boleto URL
        $boleto_url = $convertpack_data['boleto']['url'];

        // Boleto barcode
        $boleto_barcode = $convertpack_data['boleto']['barcode'];

        /**
         * Gateway response
         */
        // Method ID (on gateway, not Convertpack)
        $gateway_response_method_id = $convertpack_data['transaction']['gateway_response']['method_id'];

        // Payment ID (on gateway, not Convertpack)
        $gateway_response_method_id = $convertpack_data['transaction']['gateway_response']['payment_id'];

        // Status detail (on gateway, not Convertpack)
        // ie. Brazil: cc_rejected_bad_filled_card_number
        $gateway_response_method_id = $convertpack_data['transaction']['gateway_response']['status_detail'];

        /**
         * Transaction origin
         */
        // URL where the transaction was placed
        // ie: https://website.com/ebook/special-offer?ref=email
        $transaction_origin_url = $convertpack_data['checkout']['origin']['url'];

        // Array with parameters of the URL
        // ie: [ 'ref' => 'email' ]
        $transaction_origin_params = $convertpack_data['checkout']['origin']['params'];

        // Checkout mode
        // 'modal', 'inline' or 'self'
        $transaction_origin_checkout_style = $convertpack_data['checkout']['mode'];

        /**
         * Tracking codes
         *
         * Only available if there is at least one tracking
         * code added to the transaction
         */
        if ($webhook_event === 'tracking_code_added') :
            // Array of tracking codes
            $tracking_codes = $convertpack_data['order_tracking'];

            // Loop to check all tracking codes on this transaction
            // Just an example. Change to fit your system.
            $tracking_codes_keys = array_keys($tracking_codes);
            $tracking_codes_count = count($tracking_codes_keys);

            for ($i = 0; $i < $tracking_codes_count; $i++) {
                $tracking_code = $tracking_codes[$tracking_codes_keys[$i]];

                // Courier (shipping company)
                // ie. USA: fedex
                // ie. Brasil: correios
                $tracking_code_courier = $tracking_code['courier'];

                // Tracking code
                // ie. LO111111111CN
                $tracking_code = $tracking_code['tracking_code'];
            }
        endif;

        /**
         * HTTP success
         */
        http_response_code(200);

        /**
         * Print success
         */
        $success = [
            "status" => "success",
            "transaction_id" => $transaction_id,
        ];

        echo json_encode($success);

    else :

        /**
         * HTTP error
         */
        http_response_code(500);

        /**
         * Print error
         */
        $invalid_payload = [
            "status" => "error",
            "description" => "invalid_payload",
        ];

        echo json_encode($invalid_payload);

    endif;
else :

    /**
     * HTTP error
     */
    http_response_code(500);

    /**
     * Print error
     */
    $invalid_request = [
        "status" => "error",
        "description" => "invalid_request",
    ];

    echo json_encode($invalid_request);

endif;
