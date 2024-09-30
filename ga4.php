<script>
jQuery(document).ready(function($) {
    // Функция для отправки данных в dataLayer
    function sendRemoveToDataLayer(productName, productPrice) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'remove_from_cart',
            'ecommerce': {
                'items': [{
                    'item_name': productName,
                    'price': productPrice
                }]
            }
        });

        console.log('DataLayer event pushed: remove_from_cart', {
            'item_name': productName,
            'price': productPrice
        });
    }

    // Отслеживание клика на кнопке удаления товара
    $('body').on('click', '.remove-item.remove', function(e) {
        e.preventDefault(); // Останавливаем стандартное поведение ссылки

        // Ищем родительский элемент TR
        var $parentTR = $(this).closest('tr.cart-table-item');

        // Получаем нужные данные
        var productName = $parentTR.find('.title.product-name a').text().trim();
        var productPrice = $parentTR.find('.product-price .woocommerce-Price-amount').text().trim();

        // Отправляем данные в dataLayer
        sendRemoveToDataLayer(productName, productPrice);

        // После этого можно выполнить редирект для удаления товара или отправку формы
        window.location.href = $(this).attr('href');
    });
});
</script>

<?php
if ( class_exists( 'WooCommerce' ) ) :
global $product, $woocommerce;
?>

<script>
window.dataLayer = window.dataLayer || [];
</script>

<?php
// 1. Показы товаров на главной странице и в категориях (view_item_list)
if (is_shop() || is_product_category()) : ?>
    <script>
    window.dataLayer.push({
      'event': 'view_item_list',
      'ecommerce': {
        'items': [
          <?php
          // Цикл для получения данных о товарах на странице
          if ( have_posts() ) : 
              while ( have_posts() ) : the_post(); 
                  global $product; 
                  $product_id = $product->get_id();
                  $product_name = $product->get_name();
                  $product_price = $product->get_price();
                  $product_brand = $product->get_attribute('pa_brand');
                  $product_category = get_the_terms($product_id, 'product_cat')[0]->name;
          ?>
          {
            'item_name': '<?php echo esc_js($product_name); ?>',
            'item_id': '<?php echo esc_js($product_id); ?>',
            'price': '<?php echo esc_js($product_price); ?>',
            'item_brand': '<?php echo esc_js($product_brand); ?>',
            'item_category': '<?php echo esc_js($product_category); ?>',
			  'google_business_vertical': 'retail',
            'quantity': 1
          },
          <?php endwhile; endif; ?>
        ]
      }
    });
    </script>
<?php endif; ?>

<?php
// 2. Клики по товарам (select_item) - это должно обрабатываться через JS/jQuery при клике
if (is_shop() || is_product_category()) : ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Функция для отправки данных в dataLayer
    function sendProductToDataLayer(eventType, productName, productPrice) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': eventType,
            'ecommerce': {
                'items': [{
                    'item_name': productName,
                    'price': productPrice
                }]
            }
        });

        console.log('DataLayer event pushed:', eventType, {
            'item_name': productName,
            'price': productPrice
        });
    }

    // Отслеживание кликов на карточки товаров
    document.querySelectorAll('.product-card').forEach(function(card) {
        card.addEventListener('click', function() {
            // Извлекаем данные о продукте
            var productName = card.querySelector('.product-card__content-title a').textContent.trim();
            var productPrice = card.querySelector('.product-card__content-price').textContent.trim();

            // Отправляем данные в dataLayer с событием select_item
            sendProductToDataLayer('select_item', productName, productPrice);
        });
    });
});

    </script>
<?php endif; ?>

<?php
// 3. Просмотр карточки товара (view_item)
if (is_product()) : 
    $product_id = $product->get_id();
    $product_name = $product->get_name();
    $product_price = $product->get_price();
    $product_brand = $product->get_attribute('pa_brand');
    $product_category = get_the_terms($product_id, 'product_cat')[0]->name;
?>
    <script>
    window.dataLayer.push({
      'event': 'view_item',
      'ecommerce': {
        'items': [{
          'item_name': '<?php echo esc_js($product_name); ?>',
          'item_id': '<?php echo esc_js($product_id); ?>',
          'price': '<?php echo esc_js($product_price); ?>',
          'item_brand': '<?php echo esc_js($product_brand); ?>',
          'item_category': '<?php echo esc_js($product_category); ?>',
			'google_business_vertical': 'retail',
          'quantity': 1
        }]
      }
    });
    </script>
<?php endif; ?>

<?php
// 5. Просмотр корзины (view_cart)
if (is_cart()) : ?>
    <script>
    window.dataLayer.push({
      'event': 'view_cart',
      'ecommerce': {
        'items': [
          <?php
          foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
              $product = $cart_item['data'];
              $product_name = $product->get_name();
              $product_id = $product->get_id();
              $product_price = $product->get_price();
              $product_brand = $product->get_attribute('pa_brand');
              $product_category = get_the_terms($product_id, 'product_cat')[0]->name;
              $quantity = $cart_item['quantity'];
          ?>
          {
            'item_name': '<?php echo esc_js($product_name); ?>',
            'item_id': '<?php echo esc_js($product_id); ?>',
            'price': '<?php echo esc_js($product_price); ?>',
            'item_brand': '<?php echo esc_js($product_brand); ?>',
            'item_category': '<?php echo esc_js($product_category); ?>',
            'quantity': '<?php echo esc_js($quantity); ?>'
          },
          <?php } ?>
        ]
      }
    });
    </script>
<?php endif; ?>

<?php
// 6. Удаление товаров из корзины (remove_from_cart)
add_action('woocommerce_remove_cart_item', 'remove_from_cart_event');
function remove_from_cart_event($cart_item_key) {
    $cart = WC()->cart->get_cart_item( $cart_item_key );
    $product = wc_get_product($cart['product_id']);
    $product_name = $product->get_name();
    $product_id = $product->get_id();
    $product_price = $product->get_price();
    $product_brand = $product->get_attribute('pa_brand');
    $product_category = get_the_terms($product_id, 'product_cat')[0]->name;
    ?>
    <script>
    window.dataLayer.push({
      'event': 'remove_from_cart',
      'ecommerce': {
        'items': [{
          'item_name': '<?php echo esc_js($product_name); ?>',
          'item_id': '<?php echo esc_js($product_id); ?>',
          'price': '<?php echo esc_js($product_price); ?>',
          'item_brand': '<?php echo esc_js($product_brand); ?>',
          'item_category': '<?php echo esc_js($product_category); ?>',
          'quantity': 1
        }]
      }
    });
    </script>
    <?php
}
?>
<?php
add_action('woocommerce_after_cart_item_quantity_update', 'track_quantity_change', 10, 2);

function track_quantity_change($cart_item_key, $cart_item) {
    // Получаем информацию о товаре
    $product_id = $cart_item['product_id'];
    $product_name = get_the_title($product_id);
    $product_price = wc_get_price_to_display(wc_get_product($product_id));
    $quantity = $cart_item['quantity'];

    // Отправляем данные в dataLayer через JavaScript
    ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'quantity_change',
            'ecommerce': {
                'items': [{
                    'item_name': '<?php echo esc_js($product_name); ?>',
                    'item_id': '<?php echo esc_js($product_id); ?>',
                    'price': '<?php echo esc_js($product_price); ?>',
                    'quantity': <?php echo intval($quantity); ?>
                }]
            }
        });
        console.log('Событие quantity_change отправлено в dataLayer', {
            'item_name': '<?php echo esc_js($product_name); ?>',
            'item_id': '<?php echo esc_js($product_id); ?>',
            'price': '<?php echo esc_js($product_price); ?>',
            'quantity': <?php echo intval($quantity); ?>
        });
    </script>
    <?php
}

?>
<?php
// 7. Оформление покупки (begin_checkout)
if (is_checkout() && !is_order_received_page()) : ?>
    <script>
    window.dataLayer.push({
      'event': 'begin_checkout',
      'ecommerce': {
        'items': [
          <?php
          foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
              $product = $cart_item['data'];
              $product_name = $product->get_name();
              $product_id = $product->get_id();
              $product_price = $product->get_price();
              $product_brand = $product->get_attribute('pa_brand');
              $product_category = get_the_terms($product_id, 'product_cat')[0]->name;
              $quantity = $cart_item['quantity'];
          ?>
          {
            'item_name': '<?php echo esc_js($product_name); ?>',
            'item_id': '<?php echo esc_js($product_id); ?>',
            'price': '<?php echo esc_js($product_price); ?>',
            'item_brand': '<?php echo esc_js($product_brand); ?>',
            'item_category': '<?php echo esc_js($product_category); ?>',
            'quantity': '<?php echo esc_js($quantity); ?>'
          },
          <?php } ?>
        ]
      }
    });
    </script>
<?php endif; ?>

<?php
// 8. Покупка (purchase)
if (is_order_received_page()) :
    $order_id = get_query_var('order-received');
    $order = wc_get_order($order_id);
?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var orderId = '<?php echo esc_js($order->get_id()); ?>';

        // Проверяем, был ли этот заказ уже отправлен
        var isOrderProcessed = localStorage.getItem('order_' + orderId);

        if (!isOrderProcessed) {
            // Отправляем данные в dataLayer
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'purchase',
                'ecommerce': {
                    'transaction_id': orderId,
                    'affiliation': 'Online Store',
                    'value': '<?php echo esc_js($order->get_total()); ?>',
                    'currency': '<?php echo esc_js(get_woocommerce_currency()); ?>',
                    'tax': '<?php echo esc_js($order->get_total_tax()); ?>',
                    'shipping': '<?php echo esc_js($order->get_shipping_total()); ?>',
                    'items': [
                      <?php
                      foreach ($order->get_items() as $item_id => $item) {
                          $product = $item->get_product();
                          $product_name = $product->get_name();
                          $product_id = $product->get_id();
                          $product_price = $product->get_price();
                          $product_brand = $product->get_attribute('pa_brand');
                          $product_category = get_the_terms($product_id, 'product_cat')[0]->name;
                          $quantity = $item->get_quantity();
                      ?>
                      {
                        'item_name': '<?php echo esc_js($product_name); ?>',
                        'item_id': '<?php echo esc_js($product_id); ?>',
                        'price': '<?php echo esc_js($product_price); ?>',
                        'item_brand': '<?php echo esc_js($product_brand); ?>',
                        'item_category': '<?php echo esc_js($product_category); ?>',
                        'google_business_vertical': 'retail',
                        'quantity': '<?php echo esc_js($quantity); ?>'
                      },
                      <?php } ?>
                    ]
                }
            });

            // Сохраняем в localStorage информацию о том, что заказ был обработан
            localStorage.setItem('order_' + orderId, 'processed');
        } else {
            console.log('Order already processed, dataLayer event not sent.');
        }
    });
    </script>

<?php endif; ?>


<?php endif; // End if WooCommerce active ?>




