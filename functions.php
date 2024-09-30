
add_action('wp_footer', 'enqueue_custom_cart_tracking_script');
function enqueue_custom_cart_tracking_script() {
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded and parsed');

    // Функция для отправки данных в dataLayer
    function sendQuantityChangeToDataLayer(eventType, productName, productPrice, isIncrease) {
        let itemData = {
            'item_name': productName,
            'price': productPrice
        };

        // Если это увеличение количества товара, добавляем google_business_vertical
        if (isIncrease) {
            itemData['google_business_vertical'] = 'retail';
        }

        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': eventType,
            'ecommerce': {
                'items': [itemData]
            }
        });

        console.log('DataLayer event pushed:', eventType, itemData);
    }

    // Функция для получения данных о товаре
    function getProductData(element) {
        var productRow = element.closest('tr.cart-table-item');
        if (productRow) {
            var productName = productRow.querySelector('.title.product-name a')?.textContent.trim() || 'Unknown';
            var productPrice = productRow.querySelector('.product-price .woocommerce-Price-amount')?.textContent.trim() || '0';
            return { productName, productPrice };
        }
        return { productName: '', productPrice: '' };
    }

    // Функция для обработки кликов
    function handleButtonClick(e) {
        if (e.target && (e.target.matches('.wac-btn-inc') || e.target.matches('.wac-btn-sub'))) {
            e.preventDefault(); // Останавливаем стандартное поведение

            console.log('Button clicked:', e.target);

            var button = e.target;
            var { productName, productPrice } = getProductData(button);

            if (button.classList.contains('wac-btn-inc')) {
                console.log('Increase button clicked');
                // Отправляем данные в dataLayer с событием item_plus и добавляем google_business_vertical
                sendQuantityChangeToDataLayer('add_to_cart', productName, productPrice, true);
            } else if (button.classList.contains('wac-btn-sub')) {
                console.log('Decrease button clicked');
                // Отправляем данные в dataLayer с событием item_minus без google_business_vertical
                sendQuantityChangeToDataLayer('remove_from_cart', productName, productPrice, false);
            }
        }
    }

    // Привязываем обработчики к элементам, уже присутствующим на странице
    document.querySelectorAll('.wac-btn-inc, .wac-btn-sub').forEach(btn => {
        btn.removeEventListener('click', handleButtonClick); // Убираем предыдущие обработчики, если есть
        btn.addEventListener('click', handleButtonClick);
    });

    // Настройка MutationObserver для отслеживания изменений в DOM
    const observer = new MutationObserver((mutations) => {
        mutations.forEach(mutation => {
            if (mutation.addedNodes.length > 0) {
                // Перепривязываем обработчики для новых элементов
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        node.querySelectorAll('.wac-btn-inc, .wac-btn-sub').forEach(btn => {
                            btn.removeEventListener('click', handleButtonClick); // Убираем предыдущие обработчики, если есть
                            btn.addEventListener('click', handleButtonClick);
                        });
                    }
                });
            }
        });
    });

    // Наблюдаем за изменениями в документе
    observer.observe(document.body, { childList: true, subtree: true });

});

    </script>
    <?php
}
