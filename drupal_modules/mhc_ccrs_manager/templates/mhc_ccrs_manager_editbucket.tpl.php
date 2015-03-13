<div class="clearfix"></div>
<?php echo render($EditbucketForm); ?>

<div class="clearfix spacer"></div>

<?php
// Bending the no-business-logic-in-templates rule slightly to prevent these tables from loading for the Create Bucket view
if($bucketID) { ?>

<h2>Receivables <a title="Add new receivable" href="edit_receivable?bucketID=<?php echo $bucketID; ?>">+</a></h2>
<div id="receivables_table">
<?php echo render($BucketReceivablesTable); ?>
</div>

<h2>Payables <a title="Add new payable" href="edit_payable?bucketID=<?php echo $bucketID; ?>">+</a></h2>
<div id="payables_table">
<?php echo render($BucketPayablesTable); ?>
</div>
<?php } ?>