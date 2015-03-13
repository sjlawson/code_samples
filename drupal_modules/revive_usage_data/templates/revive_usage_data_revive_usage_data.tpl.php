<?php echo render($ReviveUsageDataForm); ?>
<div style="clear: both;"></div>
<div class="revive-internal-process-table">
<div class="revive-internal-filter-results">
     <label>Total processes filtered: <?php echo $dataCount; ?></label>
     <label>Total successful revives: <?php echo $successfulRevives; ?></label>
</div>
<?php echo render($reviveUsageDataTable); ?>
</div>
