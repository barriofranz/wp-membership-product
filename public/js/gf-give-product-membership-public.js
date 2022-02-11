(function( $ ) {
	'use strict';

    $(document).ready(function() {

        ajaxDatasource();

		// ['Year', 'Sales', 'Expenses'],
		// ['2004',  1000,      400],
		// ['2005',  1170,      460],
		// ['2006',  660,       1120],
		// ['2007',  1030,      540]
		google.charts.load('current', {'packages':['corechart']});

        function drawChart(title, data) {
			$('#gpm-chart-notice').text("");
            // Create the data table.
            var data = google.visualization.arrayToDataTable(data);

            var options = {
                  title: title,
                  curveType: 'function',
                  legend: { position: 'bottom' }
            };

             // Instantiate and draw our chart, passing in some options.
            var chart = new google.visualization.LineChart(document.getElementById('gpm-chart'));

			// var chart = new google.visualization.ColumnChart(document.getElementById("gpm-chart"));
			google.visualization.events.addListener(chart, 'error', function (googleError) {
				google.visualization.errors.removeError(googleError.id);
				$('#gpm-chart-notice').text("No data available.");
			});

			chart.draw(data, options);
        }


		$(document).on('click', '#filter_analytics', function(e) {
			ajaxDatasource()

	    });

        function ajaxDatasource(){
			$('#filter_analytics').attr('disabled','disabled');
    		var ajaxAction = 'getGpmDatasource';
    		var daterangeFrom = $('#analytics_from').val();
    		var daterangeTo = $('#analytics_to').val();
    		var id_membership = $('#analytics_id_membership').val();
    		var id_product = $('#analytics_id_product').val();
    		var request = $.ajax({
    			url: ajaxArr.ajaxDatasource,
    			type: 'POST',
    			data: 'ajax=1&action=' + ajaxAction +
				'&daterangeFrom=' + daterangeFrom +
				'&daterangeTo=' + daterangeTo +
				'&id_membership=' + id_membership +
				'&id_product=' + id_product,
    			dataType: "text"
    		});

            request.done(function(response) {
				var data = JSON.parse(response);
				$('#filter_analytics').removeAttr('disabled');
		        google.charts.setOnLoadCallback(drawChart('', data));

    		});
        }
    });

	$(document).on('click', '#gf-gpm-do-gift', function(e) {
		e.preventDefault();
		$('#gf-gpm-do-gift').attr('disabled', 'disabled');
		// $('#gf-gpm-do-gift-form').submit();
		var membership_id = $('#gf-gpm-do-gift-form .wc-customer-search').val();
		var products = $('#gf-gpm-do-gift-form .wc-product-search').val();

		var request = $.ajax({
			url: ajaxArr.ajaxDatasource,
			type: 'POST',
			data: 'action=gpm_manual_gift&membership_id='+membership_id+'&products='+products,
			dataType: "text"
		});


		request.done( function(response) {
			console.log(response);
		});
	});

	$(document).on('change', '#gf-gpm-do-gift-form .wc-customer-search, #gf-gpm-do-gift-form .wc-product-search', function(e) {
		var customer = $('#gf-gpm-do-gift-form .wc-customer-search').val();
		var product = $('#gf-gpm-do-gift-form .wc-product-search').val();

		if( customer > 0 && product > 0 ) {
			$('#gf-gpm-do-gift').removeAttr('disabled');
		} else {
			$('#gf-gpm-do-gift').attr('disabled', 'disabled');
		}
	});
})( jQuery );
