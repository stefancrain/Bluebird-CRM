<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 */
class CRM_Report_Form_Mailing_Bounce extends CRM_Report_Form {

  protected $_summary = NULL;

  protected $_emailField = FALSE;

  protected $_phoneField = FALSE;

  protected $_customGroupExtends = array(
    'Contact',
    'Individual',
    'Household',
    'Organization',
  );

  protected $_charts = array(
    '' => 'Tabular',
    'barChart' => 'Bar Chart',
    'pieChart' => 'Pie Chart',
  );

  /**
   * This report has not been optimised for group filtering.
   *
   * The functionality for group filtering has been improved but not
   * all reports have been adjusted to take care of it. This report has not
   * and will run an inefficient query until fixed.
   *
   * CRM-19170
   *
   * @var bool
   */
  protected $groupFilterNotOptimised = TRUE;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->_columns = array();

    $this->_columns['civicrm_contact'] = array(
      'dao' => 'CRM_Contact_DAO_Contact',
      'fields' => array(
        'id' => array(
          'title' => ts('Contact ID'),
          'required' => TRUE,
        ),
        'sort_name' => array(
          'title' => ts('Contact Name'),
          'required' => TRUE,
        ),
      ),
      'filters' => array(
        'sort_name' => array(
          'title' => ts('Contact Name'),
        ),
        'source' => array(
          'title' => ts('Contact Source'),
          'type' => CRM_Utils_Type::T_STRING,
        ),
        'id' => array(
          'title' => ts('Contact ID'),
          'no_display' => TRUE,
        ),
      ),
      'order_bys' => array(
        'sort_name' => array(
          'title' => ts('Contact Name'),
          'default' => TRUE,
          'default_order' => 'ASC',
        ),
      ),
      'grouping' => 'contact-fields',
    );

    $this->_columns['civicrm_mailing'] = array(
      'dao' => 'CRM_Mailing_DAO_Mailing',
      'fields' => array(
        'mailing_name' => array(
          'name' => 'name',
          'title' => ts('Mailing Name'),//NYSS
          'default' => TRUE,
        ),
        'mailing_name_alias' => array(
          'name' => 'name',
          'required' => TRUE,
          'no_display' => TRUE,
        ),
        //NYSS 4935
        'mailing_subject' => array(
          'name' => 'subject',
          'title' => ts('Mailing Subject'),
          'default' => true ),
      ),
      'filters' => array(
        'mailing_id' => array(
          'name' => 'id',
          'title' => ts('Mailing Name'),//NYSS
          'operatorType' => CRM_Report_Form::OP_MULTISELECT,
          'type' => CRM_Utils_Type::T_INT,
          'options' => CRM_Mailing_BAO_Mailing::getMailingsList(),
          'operator' => 'like',
        ),
        //NYSS 4935
        'mailing_subject' => array(
          'name' => 'subject',
          'title' => ts('Mailing Subject'),
          'type'=> CRM_Utils_Type::T_STRING,
          'operator' => 'like',
        ),
      ),
      'order_bys' => array(
        'mailing_name' => array(
          'name' => 'name',
          'title' => ts('Mailing Name'),
        ),
		'mailing_subject' =>
        array(
          'name' => 'subject',
          'title' => ts( 'Mailing Subject' ) 
        ),
      ),
      'grouping' => 'mailing-fields',
    );

    $this->_columns['civicrm_mailing_event_bounce'] = array(
      'dao' => 'CRM_Mailing_DAO_Mailing',
      'fields' => array(
        'bounce_reason' => array(
          'title' => ts('Bounce Reason'),
        ),
        'time_stamp' => array(
          'title' => ts('Bounce Date'),
        ),
      ),
      'filters' => array(
        'bounce_reason' => array(
          'title' => ts('Bounce Reason'),
          'type' => CRM_Utils_Type::T_STRING,
        ),
        'time_stamp' => array(
          'title' => ts('Bounce Date'),
          'operatorType' => CRM_Report_Form::OP_DATE,
          'type' => CRM_Utils_Type::T_DATE,
        ),
      ),
      'order_bys' => array(
        'bounce_reason' => array(
          'title' => ts('Bounce Reason'),
        ),
        'time_stamp' => array(
          'title' => ts('Bounce Date'),
        ),
      ),
      'grouping' => 'mailing-fields',
    );

    $this->_columns['civicrm_mailing_bounce_type'] = array(
      'dao' => 'CRM_Mailing_DAO_BounceType',
      'fields' => array(
        'bounce_name' => array(
          'name' => 'name',
          'title' => ts('Bounce Type'),
        ),
      ),
      'filters' => array(
        'bounce_type_name' => array(
          'name' => 'name',
          'title' => ts('Bounce Type'),
          'operatorType' => CRM_Report_Form::OP_MULTISELECT, //NYSS 4943
          'type' => CRM_Utils_Type::T_STRING,
          'options' => self::bounce_type(),
          'operator' => 'like',
        ),
      ),
      'order_bys' => array(
        'bounce_name' => array(
          'name' => 'name',
          'title' => ts('Bounce Type'),
        ),
      ),
      'grouping' => 'mailing-fields',
    );

    $this->_columns['civicrm_email'] = array(
      'dao' => 'CRM_Core_DAO_Email',
      'fields' => array(
        'email' => array(
          'title' => ts('Email'),
          'no_repeat' => TRUE,
        ),
        'on_hold' => array(
          'title' => ts('On hold'),
        ),
        'hold_date' => array(
          'title' => ts('Hold date'),
        ),
        'reset_date' => array(
          'title' => ts('Hold reset date'),
        ),
      ),
      'filters' => array(
        'on_hold' => array(
          'title' => ts('On hold'),
        ),
        'hold_date' => array(
          'title' => ts('Hold date'),
          'operatorType' => CRM_Report_Form::OP_DATE,
          'type' => CRM_Utils_Type::T_DATE,
        ),
        'reset_date' => array(
          'title' => ts('Hold reset date'),
          'operatorType' => CRM_Report_Form::OP_DATE,
          'type' => CRM_Utils_Type::T_DATE,
        ),
      ),
      'order_bys' => array(
        'email' => array('title' => ts('Email'), 'default_order' => 'ASC'),
      ),
      'grouping' => 'contact-fields',
    );

    $this->_columns['civicrm_phone'] = array(
      'dao' => 'CRM_Core_DAO_Phone',
      'fields' => array('phone' => NULL),
      'grouping' => 'contact-fields',
    );

    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  public function preProcess() {
    $this->assign('chartSupported', TRUE);
    parent::preProcess();
  }

  public function select() {
    $select = array();
    $this->_columnHeaders = array();

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (!empty($field['required']) ||
            !empty($this->_params['fields'][$fieldName])
          ) {
            if ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
            }
            elseif ($tableName == 'civicrm_phone') {
              $this->_phoneField = TRUE;
            }

            $select[] = "{$field['dbAlias']} as {$tableName}_{$fieldName}";
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['type'] = CRM_Utils_Array::value('type', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['no_display'] = CRM_Utils_Array::value('no_display', $field);
            $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = CRM_Utils_Array::value('title', $field);
          }
        }
      }
    }

    if (!empty($this->_params['charts'])) {
      $select[] = "COUNT({$this->_aliases['civicrm_mailing_event_bounce']}.id) as civicrm_mailing_bounce_count";
      $this->_columnHeaders["civicrm_mailing_bounce_count"]['title'] = ts('Bounce Count');
    }

    $this->_selectClauses = $select;
    $this->_select = "SELECT " . implode(', ', $select) . " ";
  }

  /**
   * @param $fields
   * @param $files
   * @param $self
   *
   * @return array
   */
  public static function formRule($fields, $files, $self) {
    $errors = $grouping = array();
    return $errors;
  }

  public function from() {
    $this->_from = "
        FROM civicrm_contact {$this->_aliases['civicrm_contact']} {$this->_aclFrom}";
    // LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
    // ON ({$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND
    // {$this->_aliases['civicrm_address']}.is_primary = 1 ) ";

    $this->_from .= "
        INNER JOIN civicrm_mailing_event_queue
          ON civicrm_mailing_event_queue.contact_id = {$this->_aliases['civicrm_contact']}.id
        LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
          ON civicrm_mailing_event_queue.email_id = {$this->_aliases['civicrm_email']}.id
        INNER JOIN civicrm_mailing_event_bounce {$this->_aliases['civicrm_mailing_event_bounce']}
          ON {$this->_aliases['civicrm_mailing_event_bounce']}.event_queue_id = civicrm_mailing_event_queue.id
        LEFT JOIN civicrm_mailing_bounce_type {$this->_aliases['civicrm_mailing_bounce_type']}
          ON {$this->_aliases['civicrm_mailing_event_bounce']}.bounce_type_id = {$this->_aliases['civicrm_mailing_bounce_type']}.id
        INNER JOIN civicrm_mailing_job
          ON civicrm_mailing_event_queue.job_id = civicrm_mailing_job.id
        INNER JOIN civicrm_mailing {$this->_aliases['civicrm_mailing']}
          ON civicrm_mailing_job.mailing_id = {$this->_aliases['civicrm_mailing']}.id
      ";

    if ($this->_phoneField) {
      $this->_from .= "
            LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']}
                   ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_phone']}.contact_id AND
                      {$this->_aliases['civicrm_phone']}.is_primary = 1 ";
    }
  }

  public function where() {

    $clauses = array();

    // Exclude SMS mailing type
    $clauses[] = "{$this->_aliases['civicrm_mailing']}.sms_provider_id IS NULL";

    // Build date filter clauses
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('type', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($this->_aliases[$tableName] . '.' . $field['name'], $relative, $from, $to, $field['type']);
          }
          else {
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);

            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }

          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }

    $this->_where = "WHERE " . implode(' AND ', $clauses);
  }

  public function groupBy() {
    if (!empty($this->_params['charts'])) {
      $groupBy = "{$this->_aliases['civicrm_mailing']}.id";
    }
    else {
      $groupBy = "{$this->_aliases['civicrm_mailing_event_bounce']}.id";
    }
    $this->_groupBy = CRM_Contact_BAO_Query::getGroupByFromSelectColumns($this->_selectClauses, $groupBy);
  }

  public function postProcess() {
    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);

    $sql = $this->buildQuery(TRUE);

    $rows = $graphRows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  /**
   * @param $rows
   */
  public function buildChart(&$rows) {
    if (empty($rows)) {
      return;
    }

    $chartInfo = array(
      'legend' => ts('Mail Bounce Report'),
      'xname' => ts('Mailing'),
      'yname' => ts('Bounce'),
      'xLabelAngle' => 20,
      'tip' => ts('Mail Bounce: %1', array(1 => '#val#')),
    );
    foreach ($rows as $row) {
      $chartInfo['values'][$row['civicrm_mailing_mailing_name_alias']] = $row['civicrm_mailing_bounce_count'];
    }

    // build the chart.
    CRM_Utils_OpenFlashChart::buildChart($chartInfo, $this->_params['charts']);
    $this->assign('chartType', $this->_params['charts']);
  }

  /**
   * @return array
   */
  public function bounce_type() {

    $data = array('' => ts('--Please Select--'));

    $bounce_type = new CRM_Mailing_DAO_BounceType();
    $query = "SELECT name FROM civicrm_mailing_bounce_type";
    $bounce_type->query($query);

    while ($bounce_type->fetch()) {
      $data[$bounce_type->name] = $bounce_type->name;
    }

    return $data;
  }

  /**
   * Alter display of rows.
   *
   * Iterate through the rows retrieved via SQL and make changes for display purposes,
   * such as rendering contacts as links.
   *
   * @param array $rows
   *   Rows generated by SQL, with an array for each row.
   */
  public function alterDisplay(&$rows) {

    $config = CRM_Core_Config::Singleton();

    $entryFound = FALSE;
    foreach ($rows as $rowNum => $row) {

      // If the email address has been deleted
      if (array_key_exists('civicrm_email_email', $row)) {
        if (empty($rows[$rowNum]['civicrm_email_email'])) {
          $rows[$rowNum]['civicrm_email_email'] = '<del>Email address deleted</del>';
        }
        $entryFound = TRUE;
      }

      // make count columns point to detail report
      // convert display name to links
      if (array_key_exists('civicrm_contact_sort_name', $row) &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url('civicrm/contact/view',
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_sort_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_sort_name_hover'] = ts("View Contact details for this contact.");
        $entryFound = TRUE;
      }

      // Handle on_hold boolean display
      if (array_key_exists('civicrm_email_on_hold', $row)) {
        $rows[$rowNum]['civicrm_email_on_hold'] = (!empty($row['civicrm_email_on_hold'])) ? 'Yes' : 'No';
        $entryFound = TRUE;
      }

      // Convert datetime values to custom date and time format
      $dateFields = array(
        'civicrm_mailing_event_bounce_time_stamp',
        'civicrm_email_hold_date',
        'civicrm_email_reset_date',
      );

      foreach ($dateFields as $dateField) {
        if (array_key_exists($dateField, $row)) {
          if (!empty($rows[$rowNum][$dateField])) {
            $rows[$rowNum][$dateField] = CRM_Utils_Date::customFormat($row[$dateField], $config->dateformatDatetime);
          }
          $entryFound = TRUE;
        }
      }

      // skip looking further in rows, if first row itself doesn't
      // have the column we need
      if (!$entryFound) {
        break;
      }
    }
  }
}

