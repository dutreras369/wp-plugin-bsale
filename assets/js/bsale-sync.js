jQuery(document).ready(function ($) {
    console.log('bsale-sync.js loaded successfully!');

    // Componentes de interacción
    const components = {
        showLoading: function () {
            $('#bsale-sync-results').html(`
                <div class="spinner"></div>
                <p>${bsaleSyncAjax.loadingMessage}</p>
            `);
        },
        renderResults: function (results) {
            let html = '<h3>Synchronization Results</h3><ul>';

            results.success.forEach((product) => {
                html += `<li style="color: green;">✔️ ${product}</li>`;
            });

            results.failed.forEach((failure) => {
                html += `<li style="color: red;">❌ ${failure.name}: ${failure.error}</li>`;
            });

            html += '</ul>';
            $('#bsale-sync-results').html(html);
        },
        renderError: function (message) {
            $('#bsale-sync-results').html(`
                <p style="color: red;">Error: ${message}</p>
            `);
        },
        renderAjaxError: function (error) {
            $('#bsale-sync-results').html(`
                <p style="color: red;">AJAX Error: ${error}</p>
            `);
        },
        logDebug: function (response) {
            if (bsaleSyncAjax.isDevMode) {
                console.log('Debug Information:', response);
            }
        }
    };

    // Función de sincronización AJAX
    const syncProducts = function () {
        components.showLoading();

        $.ajax({
            url: bsaleSyncAjax.ajaxUrl,
            method: 'POST',
            data: {
                action: 'bsale_sync_products',
                nonce: bsaleSyncAjax.nonce,
            },
            success: function (response) {
                if (response.success) {
                    components.renderResults(response.data.results);
                    components.logDebug(response);
                } else {
                    components.renderError(response.data.message);
                    components.logDebug(response);
                }
            },
            error: function (xhr, status, error) {
                components.renderAjaxError(error);
                console.error('AJAX Error:', status, error, xhr.responseText);
            },
        });
    };

    // Manejar clic en el botón de sincronización
    $('#bsale-sync-button').on('click', function (e) {
        e.preventDefault();
        syncProducts();
    });
});
