<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *                                   ATTENTION!
 * If you see this message in your browser (Internet Explorer, Mozilla Firefox, Google Chrome, etc.)
 * this means that PHP is not properly installed on your web server. Please refer to the PHP manual
 * for more details: http://php.net/manual/install.php 
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */


    include_once dirname(__FILE__) . '/' . 'components/utils/check_utils.php';
    CheckPHPVersion();
    CheckTemplatesCacheFolderIsExistsAndWritable();


    include_once dirname(__FILE__) . '/' . 'phpgen_settings.php';
    include_once dirname(__FILE__) . '/' . 'database_engine/mysql_engine.php';
    include_once dirname(__FILE__) . '/' . 'components/page.php';


    function GetConnectionOptions()
    {
        $result = GetGlobalConnectionOptions();
        $result['client_encoding'] = 'utf8';
        GetApplication()->GetUserAuthorizationStrategy()->ApplyIdentityToConnectionOptions($result);
        return $result;
    }

    
    
    // OnBeforePageExecute event handler
    
    
    
    class peoplePage extends Page
    {
        protected function DoBeforeCreate()
        {
            $this->dataset = new TableDataset(
                new MyConnectionFactory(),
                GetConnectionOptions(),
                '`people`');
            $field = new StringField('fname');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('lname');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new IntegerField('extension');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new StringField('mobile');
            $this->dataset->AddField($field, false);
            $field = new StringField('email');
            $this->dataset->AddField($field, false);
            $field = new StringField('group');
            $this->dataset->AddField($field, false);
            $field = new StringField('department');
            $this->dataset->AddField($field, false);
            $field = new StringField('warehouse');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, false);
            $field = new IntegerField('lastupdate');
            $field->SetIsNotNull(true);
            $this->dataset->AddField($field, true);
        }
    
        protected function CreatePageNavigator()
        {
            return null;
        }
    
        public function GetPageList()
        {
            $currentPageCaption = $this->GetShortCaption();
            $result = new PageList($this);
            
            if ( HasAdminPage() && GetApplication()->HasAdminGrantForCurrentUser() )
              $result->AddPage(new PageLink($this->GetLocalizerCaptions()->GetMessageString('AdminPage'), 'phpgen_admin.php', $this->GetLocalizerCaptions()->GetMessageString('AdminPage'), false, true));
            return $result;
        }
    
        protected function CreateRssGenerator()
        {
            return null;
        }
    
        protected function CreateGridSearchControl(Grid $grid)
        {
            $grid->UseFilter = true;
            $grid->SearchControl = new SimpleSearch('peoplessearch', $this->dataset,
                array('fname', 'lname', 'extension', 'mobile', 'email', 'group', 'department', 'warehouse'),
                array($this->RenderText('First name'), $this->RenderText('Last name'), $this->RenderText('Extension'), $this->RenderText('Mobile'), $this->RenderText('Email'), $this->RenderText('Group'), $this->RenderText('Department'), $this->RenderText('Warehouse')),
                array(
                    '=' => $this->GetLocalizerCaptions()->GetMessageString('equals'),
                    '<>' => $this->GetLocalizerCaptions()->GetMessageString('doesNotEquals'),
                    '<' => $this->GetLocalizerCaptions()->GetMessageString('isLessThan'),
                    '<=' => $this->GetLocalizerCaptions()->GetMessageString('isLessThanOrEqualsTo'),
                    '>' => $this->GetLocalizerCaptions()->GetMessageString('isGreaterThan'),
                    '>=' => $this->GetLocalizerCaptions()->GetMessageString('isGreaterThanOrEqualsTo'),
                    'ILIKE' => $this->GetLocalizerCaptions()->GetMessageString('Like'),
                    'STARTS' => $this->GetLocalizerCaptions()->GetMessageString('StartsWith'),
                    'ENDS' => $this->GetLocalizerCaptions()->GetMessageString('EndsWith'),
                    'CONTAINS' => $this->GetLocalizerCaptions()->GetMessageString('Contains')
                    ), $this->GetLocalizerCaptions(), $this, 'CONTAINS'
                );
        }
    
        protected function CreateGridAdvancedSearchControl(Grid $grid)
        {
            $this->AdvancedSearchControl = new AdvancedSearchControl('peopleasearch', $this->dataset, $this->GetLocalizerCaptions(), $this->GetColumnVariableContainer(), $this->CreateLinkBuilder());
            $this->AdvancedSearchControl->setTimerInterval(1000);
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('fname', $this->RenderText('First name')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('lname', $this->RenderText('Last name')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('extension', $this->RenderText('Extension')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('mobile', $this->RenderText('Mobile')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('email', $this->RenderText('Email')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('group', $this->RenderText('Group')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('department', $this->RenderText('Department')));
            $this->AdvancedSearchControl->AddSearchColumn($this->AdvancedSearchControl->CreateStringSearchInput('warehouse', $this->RenderText('Warehouse')));
        }
    
        protected function AddOperationsColumns(Grid $grid)
        {
            $actionsBandName = 'actions';
            $grid->AddBand($actionsBandName, $this->GetLocalizerCaptions()->GetMessageString('Actions'), true);
            if ($this->GetSecurityInfo()->HasEditGrant())
            {
                $column = new InlineEditRowColumn('Edit', $this->dataset,
                    $this->GetLocalizerCaptions()->GetMessageString('Edit'),
                    $this->GetLocalizerCaptions()->GetMessageString('Cancel'),
                    $this->GetLocalizerCaptions()->GetMessageString('Commit'),
                    false);
                $grid->AddViewColumn($column, $actionsBandName);
                $column->OnShow->AddListener('ShowInlineEditButtonHandler', $this);
            }
            if ($this->GetSecurityInfo()->HasDeleteGrant())
            {
                $column = new RowOperationByLinkColumn($this->GetLocalizerCaptions()->GetMessageString('Delete'), OPERATION_DELETE, $this->dataset);
                $grid->AddViewColumn($column, $actionsBandName);
                $column->OnShow->AddListener('ShowDeleteButtonHandler', $this);
            $column->SetAdditionalAttribute("data-modal-delete", "true");
            $column->SetAdditionalAttribute("data-delete-handler-name", $this->GetModalGridDeleteHandler());
            }
            if ($this->GetSecurityInfo()->HasAddGrant())
            {
                $column = new ModalDialogCopyRowColumn(
                    $this->GetLocalizerCaptions()->GetMessageString('Copy'), $this->dataset,
                    $this->GetLocalizerCaptions()->GetMessageString('Copy'),
                    $this->GetModalGridCopyHandler());
                $grid->AddViewColumn($column, $actionsBandName);
            }
        }
    
        protected function AddFieldColumns(Grid $grid)
        {
            //
            // View column for fname field
            //
            $column = new TextViewColumn('fname', 'First name', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('fname_handler');
            
            /* <inline edit column> */
            //
            // Edit column for fname field
            //
            $editor = new TextAreaEdit('fname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Fname', 'fname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for fname field
            //
            $editor = new TextAreaEdit('fname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Fname', 'fname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('45px');
            $grid->AddViewColumn($column);
            
            //
            // View column for lname field
            //
            $column = new TextViewColumn('lname', 'Last name', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('lname_handler');
            
            /* <inline edit column> */
            //
            // Edit column for lname field
            //
            $editor = new TextAreaEdit('lname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Lname', 'lname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for lname field
            //
            $editor = new TextAreaEdit('lname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Lname', 'lname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('45px');
            $grid->AddViewColumn($column);
            
            //
            // View column for extension field
            //
            $column = new TextViewColumn('extension', 'Extension', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for extension field
            //
            $editor = new TextEdit('extension_edit');
            $editColumn = new CustomEditColumn('Extension', 'extension', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for extension field
            //
            $editor = new TextEdit('extension_edit');
            $editColumn = new CustomEditColumn('Extension', 'extension', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('20px');
            $grid->AddViewColumn($column);
            
            //
            // View column for mobile field
            //
            $column = new TextViewColumn('mobile', 'Mobile', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('mobile_handler');
            
            /* <inline edit column> */
            //
            // Edit column for mobile field
            //
            $editor = new TextAreaEdit('mobile_edit', 50, 8);
            $editColumn = new CustomEditColumn('Mobile', 'mobile', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for mobile field
            //
            $editor = new TextAreaEdit('mobile_edit', 50, 8);
            $editColumn = new CustomEditColumn('Mobile', 'mobile', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('35px');
            $grid->AddViewColumn($column);
            
            //
            // View column for email field
            //
            $column = new TextViewColumn('email', 'Email', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for email field
            //
            $editor = new TextEdit('email_edit');
            $editor->SetSize(45);
            $editor->SetMaxLength(45);
            $editColumn = new CustomEditColumn('Email', 'email', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for email field
            //
            $editor = new TextEdit('email_edit');
            $editor->SetSize(45);
            $editor->SetMaxLength(45);
            $editColumn = new CustomEditColumn('Email', 'email', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('45px');
            $grid->AddViewColumn($column);
            
            //
            // View column for group field
            //
            $column = new TextViewColumn('group', 'Group', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('group_handler');
            
            /* <inline edit column> */
            //
            // Edit column for group field
            //
            $editor = new TextAreaEdit('group_edit', 50, 8);
            $editColumn = new CustomEditColumn('Group', 'group', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for group field
            //
            $editor = new TextAreaEdit('group_edit', 50, 8);
            $editColumn = new CustomEditColumn('Group', 'group', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('45px');
            $grid->AddViewColumn($column);
            
            //
            // View column for department field
            //
            $column = new TextViewColumn('department', 'Department', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('department_handler');
            
            /* <inline edit column> */
            //
            // Edit column for department field
            //
            $editor = new TextAreaEdit('department_edit', 50, 8);
            $editColumn = new CustomEditColumn('Department', 'department', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for department field
            //
            $editor = new TextAreaEdit('department_edit', 50, 8);
            $editColumn = new CustomEditColumn('Department', 'department', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('45px');
            $grid->AddViewColumn($column);
            
            //
            // View column for warehouse field
            //
            $column = new TextViewColumn('warehouse', 'Warehouse', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('warehouse_handler');
            
            /* <inline edit column> */
            //
            // Edit column for warehouse field
            //
            $editor = new TextAreaEdit('warehouse_edit', 50, 8);
            $editColumn = new CustomEditColumn('Warehouse', 'warehouse', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for warehouse field
            //
            $editor = new TextAreaEdit('warehouse_edit', 50, 8);
            $editColumn = new CustomEditColumn('Warehouse', 'warehouse', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $column->SetDescription($this->RenderText(''));
            $column->SetFixedWidth('45px');
            $grid->AddViewColumn($column);
        }
    
        protected function AddSingleRecordViewColumns(Grid $grid)
        {
            //
            // View column for mobile field
            //
            $column = new TextViewColumn('mobile', 'Mobile', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('mobile_handler');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for email field
            //
            $column = new TextViewColumn('email', 'Email', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for group field
            //
            $column = new TextViewColumn('group', 'Group', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('group_handler');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for department field
            //
            $column = new TextViewColumn('department', 'Department', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('department_handler');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for warehouse field
            //
            $column = new TextViewColumn('warehouse', 'Warehouse', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(75);
            $column->SetFullTextWindowHandlerName('warehouse_handler');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for fname field
            //
            $column = new TextViewColumn('fname', 'Fname', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(45);
            $column->SetFullTextWindowHandlerName('fname_handler');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for lname field
            //
            $column = new TextViewColumn('lname', 'Lname', $this->dataset);
            $column->SetOrderable(true);
            $column->SetMaxLength(45);
            $column->SetFullTextWindowHandlerName('lname_handler');
            $grid->AddSingleRecordViewColumn($column);
            
            //
            // View column for extension field
            //
            $column = new TextViewColumn('extension', 'Extension', $this->dataset);
            $column->SetOrderable(true);
            $grid->AddSingleRecordViewColumn($column);
        }
    
        protected function AddEditColumns(Grid $grid)
        {
            //
            // Edit column for fname field
            //
            $editor = new TextAreaEdit('fname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Fname', 'fname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for lname field
            //
            $editor = new TextAreaEdit('lname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Lname', 'lname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for extension field
            //
            $editor = new TextEdit('extension_edit');
            $editColumn = new CustomEditColumn('Extension', 'extension', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for mobile field
            //
            $editor = new TextAreaEdit('mobile_edit', 50, 8);
            $editColumn = new CustomEditColumn('Mobile', 'mobile', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for email field
            //
            $editor = new TextEdit('email_edit');
            $editor->SetSize(45);
            $editor->SetMaxLength(45);
            $editColumn = new CustomEditColumn('Email', 'email', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for group field
            //
            $editor = new TextAreaEdit('group_edit', 50, 8);
            $editColumn = new CustomEditColumn('Group', 'group', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for department field
            //
            $editor = new TextAreaEdit('department_edit', 50, 8);
            $editColumn = new CustomEditColumn('Department', 'department', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
            
            //
            // Edit column for warehouse field
            //
            $editor = new TextAreaEdit('warehouse_edit', 50, 8);
            $editColumn = new CustomEditColumn('Warehouse', 'warehouse', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddEditColumn($editColumn);
        }
    
        protected function AddInsertColumns(Grid $grid)
        {
            //
            // Edit column for fname field
            //
            $editor = new TextAreaEdit('fname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Fname', 'fname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for lname field
            //
            $editor = new TextAreaEdit('lname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Lname', 'lname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for extension field
            //
            $editor = new TextEdit('extension_edit');
            $editColumn = new CustomEditColumn('Extension', 'extension', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for mobile field
            //
            $editor = new TextAreaEdit('mobile_edit', 50, 8);
            $editColumn = new CustomEditColumn('Mobile', 'mobile', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for email field
            //
            $editor = new TextEdit('email_edit');
            $editor->SetSize(45);
            $editor->SetMaxLength(45);
            $editColumn = new CustomEditColumn('Email', 'email', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for group field
            //
            $editor = new TextAreaEdit('group_edit', 50, 8);
            $editColumn = new CustomEditColumn('Group', 'group', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for department field
            //
            $editor = new TextAreaEdit('department_edit', 50, 8);
            $editColumn = new CustomEditColumn('Department', 'department', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            
            //
            // Edit column for warehouse field
            //
            $editor = new TextAreaEdit('warehouse_edit', 50, 8);
            $editColumn = new CustomEditColumn('Warehouse', 'warehouse', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $grid->AddInsertColumn($editColumn);
            if ($this->GetSecurityInfo()->HasAddGrant())
            {
                $grid->SetShowAddButton(false);
                $grid->SetShowInlineAddButton(true);
            }
            else
            {
                $grid->SetShowInlineAddButton(false);
                $grid->SetShowAddButton(false);
            }
        }
    
        protected function AddPrintColumns(Grid $grid)
        {
    
        }
    
        protected function AddExportColumns(Grid $grid)
        {
    
        }
    
        public function GetPageDirection()
        {
            return null;
        }
    
        protected function ApplyCommonColumnEditProperties(CustomEditColumn $column)
        {
            $column->SetShowSetToNullCheckBox(false);
    		$column->SetVariableContainer($this->GetColumnVariableContainer());
        }
    
        function GetCustomClientScript()
        {
            return ;
        }
        
        function GetOnPageLoadedClientScript()
        {
            return ;
        }
        public function ShowInlineEditButtonHandler(&$show)
        {
            if ($this->GetRecordPermission() != null)
                $show = $this->GetRecordPermission()->HasEditGrant($this->GetDataset());
        }
        public function ShowDeleteButtonHandler(&$show)
        {
            if ($this->GetRecordPermission() != null)
                $show = $this->GetRecordPermission()->HasDeleteGrant($this->GetDataset());
        }
        
        public function GetModalGridDeleteHandler() { return 'people_modal_delete'; }
        protected function GetEnableModalGridDelete() { return true; }
        
        public function GetModalGridCopyHandler() { return 'people_inline_edit'; }
        protected function GetEnableModalGridCopy() { return true; }
    
        protected function CreateGrid()
        {
            $result = new Grid($this, $this->dataset, 'peopleGrid');
            if ($this->GetSecurityInfo()->HasDeleteGrant())
               $result->SetAllowDeleteSelected(false);
            else
               $result->SetAllowDeleteSelected(false);   
            
            ApplyCommonPageSettings($this, $result);
            
            $result->SetUseImagesForActions(false);
            $result->SetUseFixedHeader(false);
            
            $result->SetShowLineNumbers(false);
            
            $result->SetHighlightRowAtHover(true);
            $result->SetWidth('');
            $this->CreateGridSearchControl($result);
            $this->CreateGridAdvancedSearchControl($result);
    
            $this->AddFieldColumns($result);
            $this->AddSingleRecordViewColumns($result);
            $this->AddEditColumns($result);
            $this->AddInsertColumns($result);
            $this->AddPrintColumns($result);
            $this->AddExportColumns($result);
            $this->AddOperationsColumns($result);
            $this->SetShowPageList(false);
            $this->SetHidePageListByDefault(false);
            $this->SetExportToExcelAvailable(false);
            $this->SetExportToWordAvailable(false);
            $this->SetExportToXmlAvailable(false);
            $this->SetExportToCsvAvailable(false);
            $this->SetExportToPdfAvailable(false);
            $this->SetPrinterFriendlyAvailable(false);
            $this->SetSimpleSearchAvailable(true);
            $this->SetAdvancedSearchAvailable(false);
            $this->SetFilterRowAvailable(false);
            $this->SetVisualEffectsEnabled(false);
            $this->SetShowTopPageNavigator(false);
            $this->SetShowBottomPageNavigator(false);
    
            //
            // Http Handlers
            //
            //
            // View column for fname field
            //
            $column = new TextViewColumn('fname', 'First name', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for fname field
            //
            $editor = new TextAreaEdit('fname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Fname', 'fname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for fname field
            //
            $editor = new TextAreaEdit('fname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Fname', 'fname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'fname_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for lname field
            //
            $column = new TextViewColumn('lname', 'Last name', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for lname field
            //
            $editor = new TextAreaEdit('lname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Lname', 'lname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for lname field
            //
            $editor = new TextAreaEdit('lname_edit', 50, 8);
            $editColumn = new CustomEditColumn('Lname', 'lname', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'lname_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for mobile field
            //
            $column = new TextViewColumn('mobile', 'Mobile', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for mobile field
            //
            $editor = new TextAreaEdit('mobile_edit', 50, 8);
            $editColumn = new CustomEditColumn('Mobile', 'mobile', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for mobile field
            //
            $editor = new TextAreaEdit('mobile_edit', 50, 8);
            $editColumn = new CustomEditColumn('Mobile', 'mobile', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'mobile_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for group field
            //
            $column = new TextViewColumn('group', 'Group', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for group field
            //
            $editor = new TextAreaEdit('group_edit', 50, 8);
            $editColumn = new CustomEditColumn('Group', 'group', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for group field
            //
            $editor = new TextAreaEdit('group_edit', 50, 8);
            $editColumn = new CustomEditColumn('Group', 'group', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'group_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for department field
            //
            $column = new TextViewColumn('department', 'Department', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for department field
            //
            $editor = new TextAreaEdit('department_edit', 50, 8);
            $editColumn = new CustomEditColumn('Department', 'department', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for department field
            //
            $editor = new TextAreaEdit('department_edit', 50, 8);
            $editColumn = new CustomEditColumn('Department', 'department', $editor, $this->dataset);
            $editColumn->SetAllowSetToNull(true);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'department_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for warehouse field
            //
            $column = new TextViewColumn('warehouse', 'Warehouse', $this->dataset);
            $column->SetOrderable(true);
            
            /* <inline edit column> */
            //
            // Edit column for warehouse field
            //
            $editor = new TextAreaEdit('warehouse_edit', 50, 8);
            $editColumn = new CustomEditColumn('Warehouse', 'warehouse', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetEditOperationColumn($editColumn);
            /* </inline edit column> */
            
            /* <inline insert column> */
            //
            // Edit column for warehouse field
            //
            $editor = new TextAreaEdit('warehouse_edit', 50, 8);
            $editColumn = new CustomEditColumn('Warehouse', 'warehouse', $editor, $this->dataset);
            $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $this->RenderText($editColumn->GetCaption())));
            $editor->GetValidatorCollection()->AddValidator($validator);
            $this->ApplyCommonColumnEditProperties($editColumn);
            $column->SetInsertOperationColumn($editColumn);
            /* </inline insert column> */
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'warehouse_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);//
            // View column for mobile field
            //
            $column = new TextViewColumn('mobile', 'Mobile', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'mobile_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for group field
            //
            $column = new TextViewColumn('group', 'Group', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'group_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for department field
            //
            $column = new TextViewColumn('department', 'Department', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'department_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for warehouse field
            //
            $column = new TextViewColumn('warehouse', 'Warehouse', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'warehouse_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for fname field
            //
            $column = new TextViewColumn('fname', 'Fname', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'fname_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            //
            // View column for lname field
            //
            $column = new TextViewColumn('lname', 'Lname', $this->dataset);
            $column->SetOrderable(true);
            $handler = new ShowTextBlobHandler($this->dataset, $this, 'lname_handler', $column);
            GetApplication()->RegisterHTTPHandler($handler);
            return $result;
        }
        
        public function OpenAdvancedSearchByDefault()
        {
            return false;
        }
    
        protected function DoGetGridHeader()
        {
            return '';
        }
    }



    try
    {
        $Page = new peoplePage("index.php", "people", GetCurrentUserGrantForDataSource("people"), 'UTF-8');
        $Page->SetShortCaption('');
        $Page->SetHeader(GetPagesHeader());
        $Page->SetFooter(GetPagesFooter());
        $Page->SetCaption('ADMIN edit mode');
        $Page->SetRecordPermission(GetCurrentUserRecordPermissionsForDataSource("people"));
        GetApplication()->SetEnableLessRunTimeCompile(GetEnableLessFilesRunTimeCompilation());
        GetApplication()->SetCanUserChangeOwnPassword(
            !function_exists('CanUserChangeOwnPassword') || CanUserChangeOwnPassword());
        GetApplication()->SetMainPage($Page);
        GetApplication()->Run();
    }
    catch(Exception $e)
    {
        ShowErrorPage($e->getMessage());
    }
	
