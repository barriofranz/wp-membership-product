<div class="wrap">
    <div id="gpm-analytics-div" class="md-panel">
        <h2 class="title">
            Analytics
        </h2>
        <div class="gpm-body">
            <div class="analytics-filter-div">

                <div class="md-row">

                    <div class="md-col-30 daterange-div">
                        <div class="md-row md-inline">
                            <label class="md-form-label" for="analytics_from">From:</label>
                            <input type="date" id="analytics_from" class="md-input " >
                        </div>
                        <div class="md-row md-inline">
                            <label class="md-form-label " for="analytics_to">To:</label>
                            <input type="date" id="analytics_to" class="md-input " >
                        </div>
                    </div>

                    <div class="md-col-30 md-inline memberships-div">
                        <!-- <span class="md-form-label">Membership:</span> -->
                        <select class="md-input" id="analytics_id_membership" data-allow_clear="true" data-placeholder="Search">
                        <option value="0">All Memberships</option>
                		<?php
                		foreach ($memberships as $memb) {
                			if ( $memb->post_status == 'publish' ) {
                				echo '<option value="' . esc_attr( intval( $memb->ID ) ) . '" >' . esc_html( $memb->post_title ) . '</option>' . "\n";
                			}
                		}
                		?>
                		</select>
                    </div>

                    <div class="md-col-30 md-inline products-div">
                        <!-- <span class="md-form-label">Product:</span> -->
                        <select class="md-input" id="analytics_id_product" data-allow_clear="true" >
                            <!-- <option value="0">Select product...</option> -->
                            <option value="0">All Products</option>
                		<?php
                			foreach ($products as $prod) {
                				if ( $prod->get_status() == 'publish' ) {
                					echo '<option value="' . esc_attr( intval( $prod->get_id() ) ) . '" >' . esc_html( $prod->get_name() ) . '</option>' . "\n";
                				}
                			}
                		?>
                		</select>
                    </div>

                    <div class="md-col-10 md-inline filter-btn-div">
                        <input id="filter_analytics" type="submit" value="Filter" class="button-primary md-input">
                    </div>

                </div>

            </div>

            <div id="gpm-chart"></div>
            <div id="gpm-chart-notice"></div>
        </div>
    </div>
</div>
