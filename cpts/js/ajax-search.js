jQuery(document).ready(function ($) {
    // Handle Search Button Click
    $('#search-cpts-button').on('click', function () {
        console.log('Button clicked');
        let searchQuery = $('#search-cpts').val();
        console.log('Search Query:', searchQuery);

        $.ajax({
            url: cptsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'cpts_ajax_search_handler',
                _ajax_nonce: cptsAjax.nonce,
                query: searchQuery
            },
            success: function (response) {
                if (response.success) {
                    $('#cpts-results').html(response.data);
                } else {
                    $('#cpts-results').html('<tr><td colspan="9">No results found.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });

    // Handle Reset Button Click
    $('#reset-cpts-button').on('click', function () {
        $('#search-cpts').val(''); // Clear the search input

        // Reload the full list of custom post types by triggering a search with an empty query
        $.ajax({
            url: cptsAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'cpts_ajax_search_handler',
                _ajax_nonce: cptsAjax.nonce,
                query: '' // Empty query to retrieve all items
            },
            success: function (response) {
                if (response.success) {
                    $('#cpts-results').html(response.data); // Restore full table content
                } else {
                    $('#cpts-results').html('<tr><td colspan="9">No custom post types found.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });

    $('#search-submit').on('click', function () {

        var formData = $('#search-builder-form').serialize();

        $.ajax({
            url: jax.ajax_url,
            type: 'POST',
            data: {
                action: 'search',
                data: formData
            },
            beforeSend: function () {
                $('#search-results').html('<p>Loading...</p>');
            },
            success: function (response) {
                $('#search-results').html(response);
            },
            error: function () {
                $('#search-results').html('<p>An error occurred.</p>');
            }
        });
    });

    $('#search-reset').on('click', function () {
        // Reset the form fields
        $('#search-builder-form')[0].reset();

        $('#search-results').html('');
    });

    // Intercept form submission
    $('#search-builder-form').on('submit', function (e) {
        e.preventDefault();

        // Serialize form data
        var formData = $(this).serialize();

        // Send AJAX request
        $.ajax({
            url: ajax_url,
            type: 'POST',
            data: {
                action: 'search',
                data: formData,
            },
            beforeSend: function () {
                $('#search-results-container').html('<p>Loading...</p>');
            },
            success: function (response) {
                $('#search-results-container').html(response);
            },
            error: function () {
                $('#search-results-container').html('<p>An error occurred. Please try again.</p>');
            },
        });
    });
});