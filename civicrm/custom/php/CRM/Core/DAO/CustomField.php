<?php
/*
+--------------------------------------------------------------------+
| CiviCRM version 4.4                                                |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2013                                |
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
 * @copyright CiviCRM LLC (c) 2004-2013
 *
 * Generated from xml/schema/CRM/Core/CustomField.xml
 * DO NOT EDIT.  Generated by GenCode.php
 */
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Core_DAO_CustomField extends CRM_Core_DAO
{
  /**
   * static instance to hold the table name
   *
   * @var string
   * @static
   */
  static $_tableName = 'civicrm_custom_field';
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  /**
   * static instance to hold the keys used in $_fields for each field.
   *
   * @var array
   * @static
   */
  static $_fieldKeys = null;
  /**
   * static instance to hold the FK relationships
   *
   * @var string
   * @static
   */
  static $_links = null;
  /**
   * static instance to hold the values that can
   * be imported
   *
   * @var array
   * @static
   */
  static $_import = null;
  /**
   * static instance to hold the values that can
   * be exported
   *
   * @var array
   * @static
   */
  static $_export = null;
  /**
   * static value to see if we should log any modifications to
   * this table in the civicrm_log table
   *
   * @var boolean
   * @static
   */
  static $_log = true;
  /**
   * Unique Custom Field ID
   *
   * @var int unsigned
   */
  public $id;
  /**
   * FK to civicrm_custom_group.
   *
   * @var int unsigned
   */
  public $custom_group_id;
  /**
   * Variable name/programmatic handle for this group.
   *
   * @var string
   */
  public $name;
  /**
   * Text for form field label (also friendly name for administering this custom property).
   *
   * @var string
   */
  public $label;
  /**
   * Controls location of data storage in extended_data table.
   *
   * @var enum('String', 'Int', 'Float', 'Money', 'Memo', 'Date', 'Boolean', 'StateProvince', 'Country', 'File', 'Link', 'ContactReference')
   */
  public $data_type;
  /**
   * HTML types plus several built-in extended types.
   *
   * @var enum('Text', 'TextArea', 'Select', 'Multi-Select', 'AdvMulti-Select', 'Radio', 'CheckBox', 'Select Date', 'Select State/Province', 'Select Country', 'Multi-Select Country', 'Multi-Select State/Province', 'File', 'Link', 'RichTextEditor', 'Autocomplete-Select')
   */
  public $html_type;
  /**
   * Use form_options.is_default for field_types which use options.
   *
   * @var string
   */
  public $default_value;
  /**
   * Is a value required for this property.
   *
   * @var boolean
   */
  public $is_required;
  /**
   * Is this property searchable.
   *
   * @var boolean
   */
  public $is_searchable;
  /**
   * Is this property range searchable.
   *
   * @var boolean
   */
  public $is_search_range;
  /**
   * Controls field display order within an extended property group.
   *
   * @var int
   */
  public $weight;
  /**
   * Description and/or help text to display before this field.
   *
   * @var text
   */
  public $help_pre;
  /**
   * Description and/or help text to display after this field.
   *
   * @var text
   */
  public $help_post;
  /**
   * Optional format instructions for specific field types, like date types.
   *
   * @var string
   */
  public $mask;
  /**
   * Store collection of type-appropriate attributes, e.g. textarea  needs rows/cols attributes
   *
   * @var string
   */
  public $attributes;
  /**
   * Optional scripting attributes for field.
   *
   * @var string
   */
  public $javascript;
  /**
   * Is this property active?
   *
   * @var boolean
   */
  public $is_active;
  /**
   * Is this property set by PHP Code? A code field is viewable but not editable
   *
   * @var boolean
   */
  public $is_view;
  /**
   * number of options per line for checkbox and radio
   *
   * @var int unsigned
   */
  public $options_per_line;
  /**
   * field length if alphanumeric
   *
   * @var int unsigned
   */
  public $text_length;
  /**
   * Date may be up to start_date_years years prior to the current date.
   *
   * @var int
   */
  public $start_date_years;
  /**
   * Date may be up to end_date_years years after the current date.
   *
   * @var int
   */
  public $end_date_years;
  /**
   * date format for custom date
   *
   * @var string
   */
  public $date_format;
  /**
   * time format for custom date
   *
   * @var int unsigned
   */
  public $time_format;
  /**
   *  Number of columns in Note Field
   *
   * @var int unsigned
   */
  public $note_columns;
  /**
   *  Number of rows in Note Field
   *
   * @var int unsigned
   */
  public $note_rows;
  /**
   * Name of the column that holds the values for this field.
   *
   * @var string
   */
  public $column_name;
  /**
   * For elements with options, the option group id that is used
   *
   * @var int unsigned
   */
  public $option_group_id;
  /**
   * Stores Contact Get API params contact reference custom fields. May be used for other filters in the future.
   *
   * @var string
   */
  public $filter;
  /**
   * class constructor
   *
   * @access public
   * @return civicrm_custom_field
   */
  function __construct()
  {
    $this->__table = 'civicrm_custom_field';
    parent::__construct();
  }
  /**
   * return foreign keys and entity references
   *
   * @static
   * @access public
   * @return array of CRM_Core_EntityReference
   */
  static function getReferenceColumns()
  {
    if (!self::$_links) {
      self::$_links = array(
        new CRM_Core_EntityReference(self::getTableName() , 'custom_group_id', 'civicrm_custom_group', 'id') ,
      );
    }
    return self::$_links;
  }
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields()
  {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
        ) ,
        'custom_group_id' => array(
          'name' => 'custom_group_id',
          'type' => CRM_Utils_Type::T_INT,
          'required' => true,
          'FKClassName' => 'CRM_Core_DAO_CustomGroup',
          'pseudoconstant' => array(
            'table' => 'civicrm_custom_group',
            'keyColumn' => 'id',
            'labelColumn' => 'title',
          )
        ) ,
        'name' => array(
          'name' => 'name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Name') ,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
        ) ,
        'label' => array(
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Label') ,
          'required' => true,
          'maxlength' => 1020,//NYSS 9784
          'size' => CRM_Utils_Type::HUGE,
        ) ,
        'data_type' => array(
          'name' => 'data_type',
          'type' => CRM_Utils_Type::T_ENUM,
          'title' => ts('Data Type') ,
          'required' => true,
          'enumValues' => 'String, Int, Float, Money, Memo, Date, Boolean, StateProvince, Country, File, Link, ContactReference',
        ) ,
        'html_type' => array(
          'name' => 'html_type',
          'type' => CRM_Utils_Type::T_ENUM,
          'title' => ts('Html Type') ,
          'required' => true,
          'enumValues' => 'Text, TextArea, Select, Multi-Select, AdvMulti-Select, Radio, CheckBox, Select Date, Select State/Province, Select Country, Multi-Select Country, Multi-Select State/Province, File, Link, RichTextEditor, Autocomplete-Select',
        ) ,
        'default_value' => array(
          'name' => 'default_value',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Default Value') ,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
        ) ,
        'is_required' => array(
          'name' => 'is_required',
          'type' => CRM_Utils_Type::T_BOOLEAN,
        ) ,
        'is_searchable' => array(
          'name' => 'is_searchable',
          'type' => CRM_Utils_Type::T_BOOLEAN,
        ) ,
        'is_search_range' => array(
          'name' => 'is_search_range',
          'type' => CRM_Utils_Type::T_BOOLEAN,
        ) ,
        'weight' => array(
          'name' => 'weight',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Weight') ,
          'required' => true,
          'default' => '1',
        ) ,
        'help_pre' => array(
          'name' => 'help_pre',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => ts('Help Pre') ,
        ) ,
        'help_post' => array(
          'name' => 'help_post',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => ts('Help Post') ,
        ) ,
        'mask' => array(
          'name' => 'mask',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Mask') ,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
        ) ,
        'attributes' => array(
          'name' => 'attributes',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Attributes') ,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
        ) ,
        'javascript' => array(
          'name' => 'javascript',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Javascript') ,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
        ) ,
        'is_active' => array(
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
        ) ,
        'is_view' => array(
          'name' => 'is_view',
          'type' => CRM_Utils_Type::T_BOOLEAN,
        ) ,
        'options_per_line' => array(
          'name' => 'options_per_line',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Options Per Line') ,
        ) ,
        'text_length' => array(
          'name' => 'text_length',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Text Length') ,
        ) ,
        'start_date_years' => array(
          'name' => 'start_date_years',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Start Date Years') ,
        ) ,
        'end_date_years' => array(
          'name' => 'end_date_years',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('End Date Years') ,
        ) ,
        'date_format' => array(
          'name' => 'date_format',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Date Format') ,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
        ) ,
        'time_format' => array(
          'name' => 'time_format',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Time Format') ,
        ) ,
        'note_columns' => array(
          'name' => 'note_columns',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Note Columns') ,
        ) ,
        'note_rows' => array(
          'name' => 'note_rows',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Note Rows') ,
        ) ,
        'column_name' => array(
          'name' => 'column_name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Column Name') ,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
        ) ,
        'option_group_id' => array(
          'name' => 'option_group_id',
          'type' => CRM_Utils_Type::T_INT,
        ) ,
        'filter' => array(
          'name' => 'filter',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Filter') ,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
        ) ,
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the arary key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys()
  {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'custom_group_id' => 'custom_group_id',
        'name' => 'name',
        'label' => 'label',
        'data_type' => 'data_type',
        'html_type' => 'html_type',
        'default_value' => 'default_value',
        'is_required' => 'is_required',
        'is_searchable' => 'is_searchable',
        'is_search_range' => 'is_search_range',
        'weight' => 'weight',
        'help_pre' => 'help_pre',
        'help_post' => 'help_post',
        'mask' => 'mask',
        'attributes' => 'attributes',
        'javascript' => 'javascript',
        'is_active' => 'is_active',
        'is_view' => 'is_view',
        'options_per_line' => 'options_per_line',
        'text_length' => 'text_length',
        'start_date_years' => 'start_date_years',
        'end_date_years' => 'end_date_years',
        'date_format' => 'date_format',
        'time_format' => 'time_format',
        'note_columns' => 'note_columns',
        'note_rows' => 'note_rows',
        'column_name' => 'column_name',
        'option_group_id' => 'option_group_id',
        'filter' => 'filter',
      );
    }
    return self::$_fieldKeys;
  }
  /**
   * returns the names of this table
   *
   * @access public
   * @static
   * @return string
   */
  static function getTableName()
  {
    return CRM_Core_DAO::getLocaleTableName(self::$_tableName);
  }
  /**
   * returns if this table needs to be logged
   *
   * @access public
   * @return boolean
   */
  function getLog()
  {
    return self::$_log;
  }
  /**
   * returns the list of fields that can be imported
   *
   * @access public
   * return array
   * @static
   */
  static function &import($prefix = false)
  {
    if (!(self::$_import)) {
      self::$_import = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('import', $field)) {
          if ($prefix) {
            self::$_import['custom_field'] = & $fields[$name];
          } else {
            self::$_import[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_import;
  }
  /**
   * returns the list of fields that can be exported
   *
   * @access public
   * return array
   * @static
   */
  static function &export($prefix = false)
  {
    if (!(self::$_export)) {
      self::$_export = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['custom_field'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
  /**
   * returns an array containing the enum fields of the civicrm_custom_field table
   *
   * @return array (reference)  the array of enum fields
   */
  static function &getEnums()
  {
    static $enums = array(
      'data_type',
      'html_type',
    );
    return $enums;
  }
  /**
   * returns a ts()-translated enum value for display purposes
   *
   * @param string $field  the enum field in question
   * @param string $value  the enum value up for translation
   *
   * @return string  the display value of the enum
   */
  static function tsEnum($field, $value)
  {
    static $translations = null;
    if (!$translations) {
      $translations = array(
        'data_type' => array(
          'String' => ts('String') ,
          'Int' => ts('Int') ,
          'Float' => ts('Float') ,
          'Money' => ts('Money') ,
          'Memo' => ts('Memo') ,
          'Date' => ts('Date') ,
          'Boolean' => ts('Boolean') ,
          'StateProvince' => ts('StateProvince') ,
          'Country' => ts('Country') ,
          'File' => ts('File') ,
          'Link' => ts('Link') ,
          'ContactReference' => ts('ContactReference') ,
        ) ,
        'html_type' => array(
          'Text' => ts('Text') ,
          'TextArea' => ts('TextArea') ,
          'Select' => ts('Select') ,
          'Multi-Select' => ts('Multi-Select') ,
          'AdvMulti-Select' => ts('AdvMulti-Select') ,
          'Radio' => ts('Radio') ,
          'CheckBox' => ts('CheckBox') ,
          'Select Date' => ts('Select Date') ,
          'Select State/Province' => ts('Select State/Province') ,
          'Select Country' => ts('Select Country') ,
          'Multi-Select Country' => ts('Multi-Select Country') ,
          'Multi-Select State/Province' => ts('Multi-Select State/Province') ,
          'File' => ts('File') ,
          'Link' => ts('Link') ,
          'RichTextEditor' => ts('RichTextEditor') ,
          'Autocomplete-Select' => ts('Autocomplete-Select') ,
        ) ,
      );
    }
    return $translations[$field][$value];
  }
  /**
   * adds $value['foo_display'] for each $value['foo'] enum from civicrm_custom_field
   *
   * @param array $values (reference)  the array up for enhancing
   * @return void
   */
  static function addDisplayEnums(&$values)
  {
    $enumFields = & CRM_Core_DAO_CustomField::getEnums();
    foreach($enumFields as $enum) {
      if (isset($values[$enum])) {
        $values[$enum . '_display'] = CRM_Core_DAO_CustomField::tsEnum($enum, $values[$enum]);
      }
    }
  }
}