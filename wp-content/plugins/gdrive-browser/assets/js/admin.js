jQuery(document).ready(function ($) {
    // Save credentials button
    $('#gdi-save-credentials').on('click', function () {
        const credentials = $('#gdi_service_account_json').val().trim();
        const statusEl = $('#gdi-connection-status');

        if (!credentials) {
            statusEl.html('<span class="gdi-error">Please enter service account credentials</span>');
            return;
        }
        if (typeof credentials !== "string") {
            console.error("Expected a string but got:", typeof credentials, credentials);
            statusEl.html('<span class="gdi-error">Invalid input type. Expected a JSON string.</span>');
            return;
        }


        try {
            JSON.parse(credentials);
        } catch (e) {
            console.error("Error:", e);
            statusEl.html('<span class="gdi-error">Invalid JSON: ' + e.message + '</span>');
            return;
        }

        statusEl.html('<span>Saving credentials...</span>');

        // Encode the credentials to prevent JSON parsing issues
        const encodedCredentials = encodeURIComponent(credentials);

        $.ajax({
            url: gdiData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'gdi_save_credentials',
                nonce: gdiData.nonce,
                credentials: encodedCredentials
            },
            success: function (response) {
                if (response.success) {
                    statusEl.html('<span class="gdi-success">' + response.data.message + '</span>');
                    // Update the credentials status
                    $('.gdi-credentials-status').html(
                        '<div class="notice notice-success inline">' +
                        '<p>Service account credentials are configured.</p>' +
                        '</div>'
                    );

                    // If the credentials include a client_email, try to extract and set it
                    try {
                        var jsonData = JSON.parse(credentials);
                        if (jsonData.client_email && $('#gdi_service_account_email').val() === '') {
                            $('#gdi_service_account_email').val(jsonData.client_email);
                        }
                    } catch (e) {
                    }

                    // Clear the textarea
                    $('#gdi_service_account_json').val('');
                } else {
                    statusEl.html('<span class="gdi-error">' + escapeHTML(response.data.message) + '</span>');
                }
            },
            error: function (xhr) {
                let errorMsg = 'Connection failed. Please check your network.';
                if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMsg = escapeHTML(xhr.responseJSON.data.message);
                }
                statusEl.html('<span class="gdi-error">' + errorMsg + '</span>');
                console.error('GDI save credentials failed:', xhr);
            }
        });
    });

    // Test connection button
    $('#gdi-test-connection').on('click', function () {
        const credentials = $('#gdi_service_account_json').val();
        const statusEl = $('#gdi-connection-status');

        if (!credentials) {
            statusEl.html('<span class="gdi-error">Please enter service account credentials</span>');
            return;
        }

        // Validate JSON format before sending
        try {
            JSON.parse(credentials);
        } catch (e) {
            statusEl.html('<span class="gdi-error">Invalid JSON format: ' + e.message + '</span>');
            return;
        }

        statusEl.html('<span>Testing connection...</span>');

        // Encode the credentials to prevent JSON parsing issues
        const encodedCredentials = encodeURIComponent(credentials);

        $.ajax({
            url: gdiData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'gdi_test_connection',
                nonce: gdiData.nonce,
                credentials: encodedCredentials
            },
            success: function (response) {
                console.info('Response--->', response);
                if (response.success) {
                    statusEl.html('<span class="gdi-success">' + response.data.message + '</span>');
                } else {
                    statusEl.html('<span class="gdi-error">' + escapeHTML(response.data.message) + '</span>');
                }
            },
            error: function (xhr) {
                let errorMsg = 'Connection failed. Please check your network.';
                if (xhr.responseJSON && xhr.responseJSON.data) {
                    errorMsg = escapeHTML(xhr.responseJSON.data.message);
                }
                statusEl.html('<span class="gdi-error">' + errorMsg + '</span>');
                console.error('GDI connection test failed:', xhr);
            }
        });
    });
});

// Helper function to prevent XSS
function escapeHTML(str) {
    return str.replace(/[&<>"']/g, function (match) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        }[match];
    });
}