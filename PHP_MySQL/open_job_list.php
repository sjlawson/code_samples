<?php
	// Include prepend.inc to load Qcodo
	require('../includes/prepend.inc.php');		

	// Include the classfile for JobListFormBase
	require(__FORMBASE_CLASSES__ . '/JobListFormBase.class.php');

	// Security check for ALLOW_REMOTE_ADMIN
	// To allow access REGARDLESS of ALLOW_REMOTE_ADMIN, simply remove the line below
	QApplication::CheckRemoteAdmin();

	/**
	 * 
	 * @package 10til2
	 * @author sjlawson@freeshell.org
	 * @subpackage FormDraftObjects
	 * 
	 */
	class OpenJobListForm extends JobListFormBase {
		
		protected $lblStatusMessage;
		
		//add the custom column vars
		protected $colViewColumn;
		protected $colRAColumn;
		protected $colInterests;
		protected $colDeactivateColumn;
		protected $colFranchise;
		protected $colComputerSkills;
		protected $SectionPerms;
		protected $CurrUser;
		protected $FranchisePerms;
		protected $lstPageJump;
		protected $intItemCount;
		protected $colActivity;
		protected $IsActive;
		protected $ActivityPerms;
	
		
		
		// Override Form Event Handlers as Needed
//		protected function Form_Run() {}

//		protected function Form_Load() {}
		
		//Customize Form_Create
		protected function Form_Create() {
			
			$this->SectionPerms = explode('#',QApplication::GetSectionAccess(__JOBS_MODULE__, $_SESSION['AccessList']));
			$this->ActivityPerms = explode('#',QApplication::GetSectionAccess(6,$_SESSION['AccessList']));
			$this->FranchisePerms = explode('#',QApplication::GetSectionAccess(__FRANCHISES_MODULE__,$_SESSION['AccessList']));
		
		
			
			$this->CurrUser = QApplication::$Login;
			
			//create the status message
			$this->lblStatusMessage_Create();
						
			// Setup DataGrid Columns
			$this->colEditLinkColumn = new QDataGridColumn(QApplication::Translate(''), '<?= $_FORM->dtgJob_EditLinkColumn_Render($_ITEM) ?>');
			$this->colEditLinkColumn->HtmlEntities = false;
			//$this->colEditLinkColumn->Width = "10px";
			$this->colEditLinkColumn->CssClass = "dtgColumn-little";
			
				$this->colFranchise = new QDataGridColumn(QApplication::Translate('Franchise'), '<?= $_FORM->FranchiseColumn_Render($_ITEM); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->Client->FranchiseId), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->Client->FranchiseId, false)));
				$this->colFranchise->HtmlEntities = false;
			
			
			$this->colJobId = new QDataGridColumn(QApplication::Translate('Id'), '<?= $_FORM->dtgJob_IdColumn_Render($_ITEM) ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->JobId), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->JobId, false)));
			$this->colJobId->HtmlEntities = false;
			//$this->colJobId->Width = "10px";
			$this->colJobId->CssClass = "dtgColumn-little";
			
			$this->colViewColumn = new QDataGridColumn(QApplication::Translate(''), '<?= $_FORM->dtgJob_ViewColumn_Render($_ITEM) ?>');
			$this->colViewColumn->HtmlEntities = false;
			//$this->colViewColumn->Width = "10px";
			$this->colViewColumn->CssClass = "dtgColumn-little";
			
			$this->colRAColumn = new QDataGridColumn(QApplication::Translate(''), '<?= $_FORM->dtgJob_RAColumn_Render($_ITEM) ?>');
			$this->colRAColumn->HtmlEntities = false;
			//$this->colRAColumn->Width = "10px";
			$this->colRAColumn->CssClass = "dtgColumn-little";
			
			$this->colDeactivateColumn = new QDataGridColumn(QApplication::Translate(''), '<?= $_FORM->dtgJob_DeactivateColumn_Render($_ITEM) ?>');
			$this->colDeactivateColumn->HtmlEntities = false;
			//$this->colDeactivateColumn->Width = "10px";
			$this->colDeactivateColumn->CssClass = "dtgColumn-little";
			
			$this->colAdditionalRequirements = new QDataGridColumn(QApplication::Translate('Additional Requirements'), '<?= QString::Truncate($_ITEM->AdditionalRequirements, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->AdditionalRequirements), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->AdditionalRequirements, false)));
			
			$this->colClientId = new QDataGridColumn(QApplication::Translate('Client'), '<?= $_FORM->ClientColumn_Render($_ITEM); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->Client->Name), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->Client->Name, false)));
			$this->colClientId->HtmlEntities = false;
			
			$this->colPositionTitle = new QDataGridColumn(QApplication::Translate('Position Title'), '<?= QString::Truncate($_ITEM->PositionTitle, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->PositionTitle), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->PositionTitle, false)));
			
			$this->colJobStatusId = new QDataGridColumn(QApplication::Translate('Job Status'), '<?= $_FORM->dtgJob_JobStatus_Render($_ITEM); ?>',array('OrderByClause' => QQ::OrderBy(QQN::Job()->JobStatusId), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->JobStatusId, false)));
			
			$this->colPositionTypeId = new QDataGridColumn(QApplication::Translate('Position Type'), '<?= $_FORM->dtgJob_PositionType_Render($_ITEM); ?>');
			$this->colPerformBackgroundCheck = new QDataGridColumn(QApplication::Translate('Perform Background Check'), '<?= ($_ITEM->PerformBackgroundCheck) ? "true" : "false" ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->PerformBackgroundCheck), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->PerformBackgroundCheck, false)));
			
			$this->colInterests = new QDataGridColumn(QApplication::Translate('Interested Candidates'), '<?= $_FORM->InterestsColumn_Render($_ITEM); ?>');
			$this->colInterests->HtmlEntities = false;
			/*
			//$this->colJobDescription = new QDataGridColumn(QApplication::Translate('Job Description'), '<?= $_FORM->DescriptionColumn_Render($_ITEM); ?>', 
			//$this->colJobDescription->HtmlEntities = false;
			*/
			
			$this->colAltContactName = new QDataGridColumn(QApplication::Translate('Alt Contact Name'), '<?= QString::Truncate($_ITEM->AltContactName, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->AltContactName), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->AltContactName, false)));
			$this->colAltContactOfcPhone = new QDataGridColumn(QApplication::Translate('Alt Contact Ofc Phone'), '<?= QString::Truncate($_ITEM->AltContactOfcPhone, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->AltContactOfcPhone), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->AltContactOfcPhone, false)));
			$this->colAltContactCellphone = new QDataGridColumn(QApplication::Translate('Alt Contact Cellphone'), '<?= QString::Truncate($_ITEM->AltContactCellphone, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->AltContactCellphone), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->AltContactCellphone, false)));
			$this->colAltContactOtherPhone = new QDataGridColumn(QApplication::Translate('Alt Contact Other Phone'), '<?= QString::Truncate($_ITEM->AltContactOtherPhone, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->AltContactOtherPhone), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->AltContactOtherPhone, false)));
			$this->colAltContactEmail = new QDataGridColumn(QApplication::Translate('Alt Contact Email'), '<?= QString::Truncate($_ITEM->AltContactEmail, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->AltContactEmail), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->AltContactEmail, false)));
			$this->colJobCity = new QDataGridColumn(QApplication::Translate('Job City'), '<?= QString::Truncate($_ITEM->JobCity, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->JobCity), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->JobCity, false)));
			$this->colHoursPerWeek = new QDataGridColumn(QApplication::Translate('Hours Per Week'), '<?= $_ITEM->HoursPerWeek; ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->HoursPerWeek), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->HoursPerWeek, false)));
			
			$this->colClientBillRate = new QDataGridColumn(QApplication::Translate('Client Bill Rate'), '<?= $_FORM->BillRate_Render($_ITEM); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->ClientBillRate), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->ClientBillRate, false)));
			$this->colClientBillRate->HtmlEntities = false;
						
			$this->colPayRate = new QDataGridColumn(QApplication::Translate('Pay Rate'), '<?= $_ITEM->PayRate; ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->PayRate), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->PayRate, false)));
			$this->colJobNotes = new QDataGridColumn(QApplication::Translate('Job Notes'), '<?= QString::Truncate($_ITEM->JobNotes, 200); ?>', array('OrderByClause' => QQ::OrderBy(QQN::Job()->JobNotes), 'ReverseOrderByClause' => QQ::OrderBy(QQN::Job()->JobNotes, false)));
			
			$this->colActivity = new QDataGridColumn(QApplication::Translate(''), '<?= $_FORM->ActivityLinkColumn_Render($_ITEM) ?>');
			$this->colActivity->HtmlEntities = false;
			$this->colActivity->Width = "20px";
			
			// Setup DataGrid
			$this->dtgJob = new QDataGrid($this);
			
			// Datagrid Paginator
			$this->dtgJob->Paginator = new QPaginator($this->dtgJob);
			$this->dtgJob->ItemsPerPage = 10;

			// Specify Whether or Not to Refresh using Ajax
			$this->dtgJob->UseAjax = true;
			
			$this->dtgJob->CssClass = "grid_list";
			
			$this->dtgJob->SortColumnIndex = 0;
			$this->dtgJob->SortDirection = 1;

			// Specify the local databind method this datagrid will use
			$this->dtgJob->SetDataBinder('dtgJob_Bind');

			
			$this->dtgJob->AddColumn($this->colJobId);
			$this->dtgJob->AddColumn($this->colClientId);
		if(in_array(__VIEWPERM__,$this->FranchisePerms)){
				$this->dtgJob->AddColumn($this->colFranchise);
			}
			$this->dtgJob->AddColumn($this->colPositionTitle);
			//$this->dtgJob->AddColumn($this->colJobStatusId);
			$this->dtgJob->AddColumn($this->colJobCity);
			//$this->dtgJob->AddColumn($this->colComputerSkills);
			//$this->dtgJob->AddColumn($this->colJobDescription); 
			$this->dtgJob->AddColumn($this->colInterests);
			$this->dtgJob->AddColumn($this->colHoursPerWeek);
			$this->dtgJob->AddColumn($this->colPayRate);
			
			if(in_array(__EDITPERM__,$this->ActivityPerms))
			{
				$this->dtgJob->AddColumn($this->colActivity);
			}
			/*
			$this->dtgJob->AddColumn($this->colRAColumn);
			
			if(in_array(__EDITPERM__,$this->SectionPerms))
				$this->dtgJob->AddColumn($this->colEditLinkColumn);
			
			if(in_array(__VIEWPERM__,$this->SectionPerms))
				$this->dtgJob->AddColumn($this->colViewColumn);
			*/
			if(in_array(__DEACTIVATEPERM__,$this->SectionPerms)) {
				$this->dtgJob->AddColumn($this->colDeactivateColumn);
				
			}
			else
				
			$this->lstPageJump_Create();
			
		}
		
		public function FranchiseColumn_Render(Job $obj) {
			return sprintf('<div align="center"><a target="_TOP" href="franchise/franchise_view.php?intFranchiseId=%s" title="Franchise">
					%s </a>',	
				$obj->Client->FranchiseId, $obj->Client->Franchise->ShortName);
		}
		
		//customize rendering of Edit Column
		public function ActivityLinkColumn_Render(Job $obj) {
			return sprintf('<div align="center"><a target="_TOP" href="activity/activity_edit.php?intJobId=%s&intClientId=%s" title="Activity">'.
					'<img src="'.__WEBROOT__.'/admin/images/act_icon.gif" height="16px" width="16px" border="0" /> '.
					'</a></div>',
				$obj->JobId,
				$obj->ClientId);
		}
		
		public function lstPageJump_Create()
		{
			$this->lstPageJump = new QListBox($this);
			$intTotalPages = $this->intItemCount;
			
			$this->lstPageJump->AddItem(new QListItem('-- Jump to Page ('.$intTotalPages.') --',''));
			for($i=1;$i<=$intTotalPages;$i++)
			{
				$this->lstPageJump->AddItem(new QListItem(_t($i),$i));
			}
			
			
		}
		
		
		
		//revise the display of bill rate quoted
		public function BillRate_Render(Job $obj)
		{
			if($obj->ClientBillRate)
				return "$".$obj->ClientBillRate;
			else
				return "---";
		}
		
		
		
		public function ClientColumn_Render(Job $obj)
		{
			$ClientPerms = explode('#',QApplication::GetSectionAccess(__CLIENTS_MODULE__,$_SESSION['AccessList']));
			
			if(in_array(__VIEWPERM__, $ClientPerms))
			{
				return sprintf('<a href="'.__ADMINROOT__.'/client/client_view.php?intClientId=%s" target="_TOP">%s</a>',
					$obj->ClientId,
					$obj->Client
					);
			}
			else
			{
				return $obj->Client;
			}
		}
		
		public function InterestsColumn_Render(Job $obj)
		{
			$objActivities = Activity::QueryArray(QQ::AndCondition(
			QQ::Like(QQN::Activity()->Description, '%expressed interest%'),
			QQ::Equal(QQN::Activity()->JobId, $obj->JobId) ), QQ::Clause(QQ::GroupBy(QQN::Activity()->JobseekerId), QQ::OrderBy(QQN::Activity()->ActivityDate, false)));
			$interests = "";
			foreach ($objActivities as $objActivity){
				if(is_object($objActivity->Jobseeker)){
				$interests .= "<a target='_TOP' href='".__ADMINROOT__."/jobseeker/jobseeker_view.php?intJobseekerId=";
				$interests .= $objActivity->JobseekerId."'>".$objActivity->Jobseeker->FirstName."_".$objActivity->Jobseeker->LastName."</a>(".$objActivity->ActivityDate."), ";
				}
			}		
			return $interests;
		}
		
		public function DescriptionColumn_Render(Job $obj)
		{
			
			/* old code for job description
			//$s = preg_replace('/[<>()!#$%\^&=+~`*"\'�������������\.�������������������]/', '', $s);
			$strDisplay = "";
			if(strlen($obj->JobDescription) > __DESC_LEN__)
			{
				$strArray = str_split($obj->JobDescription, __DESC_LEN__);
				$strDisplay = ereg_replace("[^ A-Za-z0-9.\-]", "", $strArray[0]).'...[more]';
			}
			else
			{
				$strDisplay = ereg_replace("[^ A-Za-z0-9.\-]", "", $obj->JobDescription);
			
			}
			
			if(in_array(__VIEWPERM__, $this->SectionPerms))
			{
				//if($obj->JobId == 357)
				//	die(preg_replace('/�/', '',$strDisplay));
					
				return sprintf('<a href="client/job_view.php?intJobId=%s" target="_TOP">%s</a>',
					$obj->JobId,
					 $strDisplay
					);
			}
			else
			{
				//if($obj->JobId == 357)
				//	die('x.'.preg_replace('/�/', '',$strDisplay));
				return $obj->JobDescription;	
			}
			*/
		}
		
		//customize the rendering of the Computer Skills column
		public function dtgJob_ComputerSkills_Render(Job $objJob) {
			
			$strToDisplay = "";
			$objAssociatedArray = $objJob->GetComputerSkillArray(); //lAsTechnology
			
			$strComputerSkills = implode(', ',$objAssociatedArray);
			
			if(strlen($strComputerSkills) > __DESC_LEN__)
			{
				$strArray = str_split($strComputerSkills,__DESC_LEN__);
				if(in_array(__VIEWPERM__,$this->SectionPerms))
					$strToDisplay = $strArray[0].'...<a href="client/job_view.php?intJobId='.$objJob->JobId.'"target="_TOP">[more]</a>';
				else
					$strToDisplay = $strArray[0].'...[more]';
			}
			else
			{
				$strToDisplay = $strComputerSkills;
			}
			
			return $strToDisplay;
			
		}
		
		//customize rendering of Edit Column
		public function dtgJob_EditLinkColumn_Render(Job $objJob) {
			return sprintf('<a href="client/job_edit.php?intJobId=%s" target="_TOP" title="Edit">'.
					'<img src="'.__WEBROOT__.'//admin/images/edit_f2.gif" height="16px" width="16px" border="0" /> '.
					'</a>',
				$objJob->JobId);
		}
		
		//add the view function to the Id column
		public function dtgJob_IdColumn_Render(Job $objJob) {
			return sprintf('<a title="View" href="client/job_view.php?intJobId=%s" target="_TOP">'.
				'%s</a>',
				$objJob->JobId,
				$objJob->JobId);	
		}
		
		//add the view column
		public function dtgJob_RAColumn_Render(Job $objJob) {
			return sprintf('<a title="Requirements Agreement" href="client/job_ra_edit.php?intJobId=%s" target="_TOP">'.
			'<img src="'.__WEBROOT__.'/admin/images/generic.png" height="20px" width="20px" border="0" /> '.
			'</a>',
				$objJob->JobId);	
				
		}
		
		//add the view column
		public function dtgJob_ViewColumn_Render(Job $objJob) {
			return sprintf('<a title="View" href="client/job_view.php?intJobId=%s" target="_TOP">'.
			'<img src="'.__WEBROOT__.'/admin/images/mark_f2.gif" height="17px" width="13px" border="0" /> '.
			'</a>',
				$objJob->JobId);	
				
		}
		
		//add the deactivation column
		public function dtgJob_DeactivateColumn_Render(Job $objJob) {
			
			//set the control id
			$strControlId = 'deacLink'.$objJob->JobId;
			
			$lnkDeac = $this->GetControl($strControlId);
			if(!$this->IsActive){
				if(!$lnkDeac){
					$lnkDeac = new QImageButton($this->dtgJob,$strControlId);
					//format the button
					$lnkDeac->ImageUrl = __WEBROOT__.'/admin/images/act_icon.gif';
					$lnkDeac->Height = "14px";
					$lnkDeac->Width = "14px";
					$lnkDeac->Cursor = QCursor::Pointer;
					$lnkDeac->ToolTip = QApplication::Translate("un-delete");
					$lnkDeac->ActionParameter = $objJob->JobId;
					$lnkDeac->AddAction(new QClickEvent(), new QConfirmAction(sprintf(QApplication::Translate('Are you SURE you want to un-delete this %s?'), 'Job')));
					$lnkDeac->AddAction(new QClickEvent(), new QServerAction('lnkAct_clicked'));
				}
			}
			if(!$lnkDeac){
				$lnkDeac = new QImageButton($this->dtgJob,$strControlId);
				//format the button
				$lnkDeac->ImageUrl = __WEBROOT__.'/admin/images/cancel_f2.gif';
				$lnkDeac->Height = "14px";
				$lnkDeac->Width = "14px";
				$lnkDeac->Cursor = QCursor::Pointer;
				$lnkDeac->ToolTip = QApplication::Translate("Delete");
				$lnkDeac->ActionParameter = $objJob->JobId;
				$lnkDeac->AddAction(new QClickEvent(), new QConfirmAction(sprintf(QApplication::Translate('Are you SURE you want to Delete this %s?'), 'Job')));
				$lnkDeac->AddAction(new QClickEvent(), new QServerAction('lnkDeac_clicked'));
			}
			
			return $lnkDeac->Render(false);
		}
		
		protected function lnkAct_Clicked ($strFormId, $strControlId, $strParameter) {
			$intJobId = $strParameter;
			
			$objJob = Job::Load($intJobId);
			$objJob->Activate();
			$this->RedirectToListPageWithStatus($objJob);
		}
		
		protected function lnkDeac_Clicked ($strFormId, $strControlId, $strParameter) {
			$intJobId = $strParameter;
			
			$objJob = Job::Load($intJobId);
			$objJob->Deactivate();
			$this->RedirectToListPageWithStatus($objJob);
		}
		
		protected function RedirectToListPage() {
			QApplication::Redirect('index.php');
		}
		
		
		protected function dtgJob_Bind() {
			
			// Remember!  We need to first set the TotalItemCount, which will affect the calcuation of LimitClause below
			
			
				if($this->CurrUser->UserName == __ADMINACCT__ || in_array(__CORPORATEPERM__,$this->SectionPerms))
				{
					$this->dtgJob->TotalItemCount = Job::CountAllOpen();
					$this->intItemCount = Job::CountAllOpen();
				}
				else
				{	
					$this->dtgJob->TotalItemCount = Job::CountAllOpen($this->CurrUser->FranchiseId);
					$this->intItemCount = Job::CountAllOpen($this->CurrUser->FranchiseId);
				}
	
				// Setup the $objClauses Array
				$objClauses = array();
	
				// If a column is selected to be sorted, and if that column has a OrderByClause set on it, then let's add
				// the OrderByClause to the $objClauses array
				if ($objClause = $this->dtgJob->OrderByClause)
					array_push($objClauses, $objClause);
	
				// Add the LimitClause information, as well
				if ($objClause = $this->dtgJob->LimitClause)
					array_push($objClauses, $objClause);
	
				// Set the DataSource to be the array of all Job objects, given the clauses above
				if($this->CurrUser->UserName == __ADMINACCT__ || in_array(__CORPORATEPERM__,$this->SectionPerms))
					$this->dtgJob->DataSource = Job::LoadAllOpen($objClauses);
				else
					$this->dtgJob->DataSource = Job::LoadAllOpen($objClauses,$this->CurrUser->FranchiseId);
					
				
		}
		
		//status message
		protected function lblStatusMessage_Create(){
			$this->lblStatusMessage = new QLabel($this);
			$this->lblStatusMessage->ForeColor = "#0000FF";
			$this->lblStatusMessage->BackColor = "#D1E6E7";
			$this->lblStatusMessage->FontBold = true;
			
			if(isset($_GET['stat']))
				$this->lblStatusMessage->Text = $_GET['stat'];
		}
		
		protected function RedirectToListPageWithStatus(Job $objJob){
			if(!$this->IsActive)
				$stat_msg = __ACTIVATED__.' [Job '.$objJob->__tostring().']';
			else
			$stat_msg = __DEACTIVATED__.' [Job '.$objJob->__tostring().']';
			QApplication::Redirect('index.php?stat='.$stat_msg);
		}

//		protected function Form_PreRender() {}

//		protected function Form_Exit() {}
	}

	// Go ahead and run this form object to generate the page and event handlers, using
	// generated/job_list.tpl.php as the included HTML template file
	OpenJobListForm::Run('OpenJobListForm','open_job_list.tpl.php');
?>
