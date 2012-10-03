<?php

$strPageTitle = QApplication::Translate('List All') . ' Jobs';

require('top_html_nomenu.inc.php');

	if(isset($_GET['IsActive'])) $this->IsActive=$_GET['IsActive'];
	else $this->IsActive = 1;
?> 
		<div style="padding:10px 0px ; float:left ; width:100%">
			<?php $this->RenderBegin() ?>
				
			<?php $this->lblStatusMessage->Render(); ?>
			<?php $this->dtgJob->Render() ?>
		</div>
		
	<?php $this->RenderEnd() ?>
	
<?php //require(__INCLUDES__ . '/footer_list.inc.php'); ?>