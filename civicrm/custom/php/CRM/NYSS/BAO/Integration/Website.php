<?php

/*
 * Project: BluebirdCRM
 * Authors: Brian Shaughnessy
 * Organization: New York State Senate
 * Date: 2015-04-10
 */

class CRM_NYSS_BAO_Integration_Website
{
  /*
   * given a website user Id, conduct a lookup to get the contact Id
   * if none, return empty
   */
  static function getContactId($userId)
  {
    $cid = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_contact
      WHERE web_user_id = {$userId}
    ");

    return $cid;
  } //getContactId()

  /*
   * given a contact ID, conduct a lookup to get the web ID
   * if none, return empty
   */
  static function getWebId($contactId)
  {
    $cid = CRM_Core_DAO::singleValueQuery("
      SELECT web_user_id
      FROM civicrm_contact
      WHERE id = {$contactId}
    ");

    return $cid;
  } //getContactId()

  /*
   * build contact params from row; we now need to look in multiple places.
   * 1. check for table columns (first level row elements)
   * 2. check in msg_info->user_info
   * 3. check in msg_info->form_info
   *
   * in each case, we will look for the existence of first/last name
   */
  static function getContactParams($row) {
    $params = json_decode($row->msg_info);
    $user_info = $params->user_info;
    $form_info = $params->form_info;
    //CRM_Core_Error::debug_var('getContactParams $params', $params);
    //CRM_Core_Error::debug_var('getContactParams $user_info', $user_info);
    //CRM_Core_Error::debug_var('getContactParams $form_info', $form_info);

    $contactParams = array();
    if (!empty($row->first_name) || !empty($row->last_name)) {
      $contactParams = array(
        'web_user_id' => $row->user_id,
        'first_name' => $row->first_name,
        'last_name' => $row->last_name,
        'email' => $row->email_address,
        'street_address' => $row->address1,
        'supplemental_addresss_1' => $row->address2,
        'city' => $row->city,
        'state' => $row->state,
        'postal_code' => $row->zip,
      );
    }
    elseif (!empty($user_info->first_name) || !empty($user_info->last_name)) {
      $contactParams = array(
        'web_user_id' => $user_info->id,
        'first_name' => $user_info->first_name,
        'last_name' => $user_info->last_name,
        'email' => $user_info->email,
        'street_address' => $user_info->address,
        'city' => $user_info->city,
        'state' => $user_info->state,
        'postal_code' => $user_info->zipcode,
      );
    }
    elseif (!empty($form_info->first_name) || !empty($form_info->last_name)) {
      $contactParams = array(
        'first_name' => $form_info->first_name,
        'last_name' => $form_info->last_name,
        'email' => $form_info->user_email,
        'street_address' => $form_info->user_address,
        'city' => $form_info->user_city,
        'state' => $form_info->user_state,
        'postal_code' => $form_info->user_zipcode,
      );
    }

    //if we have address fields, pass them through SAGE so we correct any mispellings
    if (!empty($contactParams['state'])) {
      //match params format required by SAGE checkAddress
      $contactParams['state_province'] = $contactParams['state'];
    }
    CRM_Utils_SAGE::checkAddress($contactParams);

    return $contactParams;
  }//getContactParams

  /*
   * attempt to match the record with existing contacts
   */
  static function matchContact($params) {
    //CRM_Core_Error::debug_var('matchContact $params', $params);

    //format params to pass to dedupe tool
    $dedupeParams = array(
      'civicrm_contact' => array(
        'first_name' => $params['first_name'],
        'last_name' => $params['last_name'],
        'birth_date' => $params['birth_date'],
        'gender_id' => $params['gender_id'],
      ),
      'civicrm_address' => array(
        'street_address' => $params['street_address'],
        'city' => $params['city'],
        'postal_code' => $params['postal_code'],
      ),
    );

    if (!empty($params['email'])) {
      $dedupeParams['civicrm_email']['email'] = $params['email'];
    }

    $dedupeParams = CRM_Dedupe_Finder::formatParams($dedupeParams, 'Individual');
    $dedupeParams['check_permission'] = 0;

    //get indiv unsupervised rule
    $ruleTitle = CRM_Core_DAO::singleValueQuery("
      SELECT title
      FROM civicrm_dedupe_rule_group
      WHERE id = 1
    ");

    $o = new stdClass();
    $o->title = $ruleTitle;
    $o->params = $dedupeParams;
    $o->noRules = FALSE;
    $tableQueries = array();
    nyss_dedupe_civicrm_dupeQuery($o, 'table', $tableQueries);
    $sql = $tableQueries['civicrm.custom.5'];
    $sql = "
      SELECT contact.id
      FROM civicrm_contact as contact JOIN ($sql) as dupes
      WHERE dupes.id1 = contact.id AND contact.is_deleted = 0
    ";
    //CRM_Core_Error::debug_var('$sql', $sql);
    $r = CRM_Core_DAO::executeQuery($sql);

    $dupeIDs = array();
    while ($r->fetch()) {
      $dupeIDs[] = $r->id;
    }
    //CRM_Core_Error::debug_var('dupeIDs', $dupeIDs);

    //if dupe found, return id
    if (!empty($dupeIDs)) {
      $cid = $dupeIDs[0];
    }
    else {
      //if not found, create new contact
      $cid = self::createContact($params);
    }

    //set user id
    if (!empty($cid) && !empty($params['web_user_id'])) {
      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_contact
        SET web_user_id = {$params['web_user_id']}
        WHERE id = {$cid}
      ");

      return $cid;
    }
    elseif (!empty($cid)) {
      return $cid;
    }
    else {
      return null;
    }
  } // matchContact()

  /*
   * create a new contact
   */
  static function createContact($params)
  {
    $params['custom_60'] = 'Website Account';
    $params['contact_type'] = 'Individual';
    $params['api.address.create'] = array(
      'street_address' => $params['street_address'],
      'supplemental_addresss_1' => $params['supplemental_addresss_1'],
      'city' => $params['city'],
      'state_province' => $params['state'],
      'postal_code' => $params['postal_code'],
      'location_type_id' => 1,
    );
    self::cleanContactParams($params);
    //CRM_Core_Error::debug_var('createContact params', $params);

    $contact = civicrm_api3('contact', 'create', $params);
    //CRM_Core_Error::debug_var('contact', $contact);

    return $contact['id'];
  } //createContact()

  /*
   * because we're getting data from the web, some of it could be junk
   * initially we will just concern ourselves with field length, but in time
   * this can be a common function used for cleaning data
   */
  static function cleanContactParams(&$params) {
    $contactFields = civicrm_api3('contact', 'getfields', array('sequential' => 1, 'api_action' => 'create'));
    $addressFields = civicrm_api3('address', 'getfields', array('sequential' => 1, 'api_action' => 'create'));

    //cycle through contact fields and truncate if necessary
    foreach ($contactFields['values'] as $field) {
      if (array_key_exists($field['name'], $params) && !empty($field['maxlength'])) {
        if (is_string($params[$field['name']]) &&
          strlen(utf8_decode($params[$field['name']])) > $field['maxlength']
        ) {
          $params[$field['name']] = truncate_utf8($params[$field['name']], $field['maxlength']);
        }
      }
    }

    //cycle through address fields and truncate if necessary
    foreach ($addressFields['values'] as $field) {
      if (array_key_exists($field['name'], $params['api.address.create']) && !empty($field['maxlength'])) {
        if (is_string($params['api.address.create'][$field['name']]) &&
          strlen(utf8_decode($params['api.address.create'][$field['name']])) > $field['maxlength']
        ) {
          $params['api.address.create'][$field['name']] =
            truncate_utf8($params['api.address.create'][$field['name']], $field['maxlength']);
        }
      }
    }

    /*Civi::log()->debug('cleanContactParams', array(
      'contactFields' => $contactFields,
      'addressFields' => $addressFields,
      'params' => $params,
    ));*/
  }//cleanContactParams

  //TODO when a user moves to a different district, need to reset web_user_id

  static function processIssue($contactId, $action, $params)
  {
    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Issues'
        AND is_tagset = 1
    ");

    //find tag name
    $tagName = self::getTagName($params, 'issue_name');
    if (empty($tagName)) {
      CRM_Core_Error::debug_var('processIssue: unable to identify tag name in $params', $params, true, true, 'integration');
      return false;
    }

    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = {$parentId}
    ", array(1 => array($tagName, 'String')));
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', array(
        'version' => 3,
        'name' => $tagName,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s'),
        'description' => '',//TODO store link back to website
      ));
      //CRM_Core_Error::debug_var('$tag', $tag);

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', array('cache_clear' => 1));

    $apiAction = ($action == 'follow') ? 'create' : 'delete';
    $et = civicrm_api('entity_tag', $apiAction, array(
      'version' => 3,
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contactId,
      'tag_id' => $tagId,
    ));

    return $et;
  } //processIssue()


  static function processCommittee($contactId, $action, $params)
  {
    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Committees'
        AND is_tagset = 1
    ");

    //find tag name
    $tagName = self::getTagName($params, 'committee_name');
    if (empty($tagName)) {
      CRM_Core_Error::debug_var('processCommittee: unable to identify tag name in $params', $params, true, true, 'integration');
      return false;
    }

    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = {$parentId}
    ", array(1 => array($tagName, 'String')));
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', array(
        'version' => 3,
        'name' => $tagName,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s'),
        'description' => ''//TODO store link back to website
      ));
      //CRM_Core_Error::debug_var('$tag', $tag);

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', array('cache_clear' => 1));

    $apiAction = ($action == 'follow') ? 'create' : 'delete';
    $et = civicrm_api('entity_tag', $apiAction, array(
      'version' => 3,
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contactId,
      'tag_id' => $tagId,
    ));

    return $et;
  } //processCommittee()


  static function processBill($contactId, $action, $params) {
    //CRM_Core_Error::debug_var('processBill $params', $params, true, true, 'integration');

    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Bills'
        AND is_tagset = 1
    ");

    $tagName = $tagNameBase = self::buildBillName($params);
    $tagNameOpposite = '';

    //construct tag name and determine action
    switch ($action) {
      case 'follow':
        $apiAction = 'create';
        break;
      case 'unfollow':
        $apiAction = 'delete';
        break;
      case 'aye':
        $apiAction = 'create';
        $tagName .= ': SUPPORT';
        $tagNameOpposite = $tagNameBase.': OPPOSE';
        break;
      case 'nay':
        $apiAction = 'create';
        $tagName .= ': OPPOSE';
        $tagNameOpposite = $tagNameBase.': SUPPORT';
        break;
      default:
        return array(
          'is_error' => 1,
          'error_message' => 'Unable to determine bill action',
          'contactId' => $contactId,
          'action' => $action,
          'params' => $params,
        );
    }

    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = $parentId
    ", array(1 => array($tagName, 'String')));
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', array(
        'version' => 3,
        'name' => $tagName,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s')
      ));
      //CRM_Core_Error::debug_var('$tag', $tag);

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', array('cache_clear' => 1));

    $et = civicrm_api('entity_tag', $apiAction, array(
      'version' => 3,
      'entity_table' => 'civicrm_contact',
      'entity_id' => $contactId,
      'tag_id' => $tagId,
    ));

    //see if the opposite tag exists and if so, remove it
    if (!empty($tagNameOpposite)) {
      $tagIdOpp = CRM_Core_DAO::singleValueQuery("
        SELECT id
        FROM civicrm_tag
        WHERE name = %1
          AND parent_id = $parentId
      ", array(1 => array($tagNameOpposite, 'String')));

      //if the tag doesn't even exist, it's never been used on the site and we can skip the check
      if ($tagIdOpp) {
        $et = civicrm_api('entity_tag', 'delete', array(
          'version' => 3,
          'entity_table' => 'civicrm_contact',
          'entity_id' => $contactId,
          'tag_id' => $tagIdOpp,
        ));
      }
    }

    return $et;
  } //processBill()


  static function processPetition($contactId, $action, $params) {
    //find out if tag exists
    $parentId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = 'Website Petitions'
        AND is_tagset = 1
    ");

    //find tag name
    $tagName = self::getTagName($params, 'petition_name');
    if (empty($tagName)) {
      CRM_Core_Error::debug_var('processPetition: unable to identify tag name in $params', $params, true, true, 'integration');
      return false;
    }

    $tagId = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_tag
      WHERE name = %1
        AND parent_id = {$parentId}
    ", array(1 => array($tagName, 'String')));
    //CRM_Core_Error::debug_var('tagId', $tagId);

    if (!$tagId) {
      $tag = civicrm_api('tag', 'create', array(
        'version' => 3,
        'name' => $tagName,
        'parent_id' => $parentId,
        'is_selectable' => 0,
        'is_reserved' => 1,
        'used_for' => 'civicrm_contact',
        'created_date' => date('Y-m-d H:i:s'),
        'description' => '',//TODO store link back to website
      ));
      //CRM_Core_Error::debug_var('$tag', $tag);

      if ($tag['is_error']) {
        return $tag;
      }

      $tagId = $tag['id'];
    }

    //clear tag cache; entity_tag sometimes fails because newly created tag isn't recognized by pseudoconstant
    civicrm_api3('Tag', 'getfields', array('cache_clear' => 1));

    $apiAction = (in_array($action, array('sign', 'signature update'))) ? 'create' : 'delete';
    try {
      $etID = CRM_Core_DAO::singleValueQuery("
        SELECT id
        FROM civicrm_entity_tag
        WHERE entity_table = 'civicrm_contact'
          AND entity_id = %1
          AND tag_id = %2
        LIMIT 1
      ", array(1 => array($contactId, 'Integer'), 2 => array($tagId, 'Integer')));

      if (!$etID || $action == 'delete') {
        $et = civicrm_api3('entity_tag', $apiAction, array(
          'entity_table' => 'civicrm_contact',
          'entity_id' => $contactId,
          'tag_id' => $tagId,
        ));
      }
      else {
        //get existing value so we can return it
        $et = civicrm_api3('entity_tag', 'get', array(
          'entity_table' => 'civicrm_contact',
          'entity_id' => $contactId,
          'tag_id' => $tagId,
        ));
      }
    }
    catch (CiviCRM_API3_Exception $e) {
      CRM_Core_Error::debug_var('CRM_NYSS_BAO_Integration_Website::processPetition $e', $e);
    }

    return $et;
  } //processPetition()


  /*
   * process account records in the custom nyss_web_account table
   */
  static function processAccount($contactId, $action, $params, $created_date)
  {
    switch ($action) {
      case 'account created':
      case 'account deleted':
      case 'login':
      case 'logout':
        $sql = "
          INSERT INTO nyss_web_account
          (contact_id, action, created_date)
          VALUES
          ({$contactId}, '{$action}', '{$created_date}')
        ";
        CRM_Core_DAO::executeQuery($sql);
        break;

      default:
        return array(
          'is_error' => 1,
          'error_message' => 'Unable to determine account action',
          'contactId' => $contactId,
          'action' => $action,
          'params' => $params,
        );
    }

    return array('is_error' => 0, 'version' => 3);
  } //processAccount()


  static function processProfile($contactId, $action, $params, $row)
  {
    //CRM_Core_Error::debug_var('processProfile $row', $row);

    //only available action is account edited
    if ($action != 'account edited') {
      return array(
        'is_error' => 1,
        'error_message' => 'Unknown action type for profile: '.$action,
        'params' => $params,
      );
    }

    $status = ($params->status) ? $params->status : 'edited';

    $profileParams = array(
      'entity_id' => $contactId,
      'custom_65' => $row->first_name,
      'custom_66' => $row->last_name,
      'custom_67' => $row->address1,
      'custom_68' => $row->address2,
      'custom_69' => $row->city,
      'custom_70' => $row->state,
      'custom_71' => $row->zip,
      'custom_72' => $row->email_address,
      'custom_73' => ($row->dob) ? date('Ymd', $row->dob) : '',//dob comes as timestamp
      'custom_74' => $row->gender,
      'custom_75' => $row->contact_me,
      'custom_76' => $row->top_issue,
      'custom_77' => $status,
      'custom_78' => $row->user_is_verified,
      'custom_79' => date('YmdHis', $row->created_at),
    );
    //CRM_Core_Error::debug_var('profileParams', $profileParams);

    try {
      $result = civicrm_api3('custom_value', 'create', $profileParams);
      //CRM_Core_Error::debug_var('update profile result', $result);
    }
    catch (CiviCRM_API3_Exception $e) {
      // handle error here
      $errorMessage = $e->getMessage();
      $errorCode = $e->getErrorCode();
      $errorData = $e->getExtraParams();

      return array(
        'is_error' => 1,
        'error_message' => $errorMessage,
        'error_code' => $errorCode,
        'error_data' => $errorData
      );
    }

    //9581 update contact record if data missing
    $contact = civicrm_api3('contact', 'getsingle', array('id' => $contactId));

    $updateParams = array(
      'id' => $contactId,
    );
    $update = false;

    if (empty($contact['email']) && !empty($row->email_address)) {
      $updateParams['api.email.create'] = array(
        'email' => $row->email_address,
        'location_type_id' => 1,
      );
      $update = true;
    }

    if (empty($contact['gender']) && !empty($row->gender)) {
      switch ($row->gender) {
        case 'male':
          $updateParams['gender_id'] = 2;
          break;
        case 'female':
          $updateParams['gender_id'] = 1;
          break;
        case 'other':
          $updateParams['gender_id'] = 4;
          break;
        default:
      }
      $update = true;
    }

    if (empty($contact['birth_date']) && !empty($row->dob)) {
      $updateParams['birth_date'] = date('Ymd', $row->dob);
      $update = true;
    }

    if (empty($contact['street_address']) && !empty($row->address1)) {
      $updateParams['api.address.create'] = array(
        'street_address' => $row->address1,
        'supplemental_addresss_1' => $row->address2,
        'city' => $row->city,
        'state_province' => $row->state,
        'postal_code' => $row->zip,
        'location_type_id' => 1,
      );
      $update = true;
    }

    //CRM_Core_Error::debug_var('$updateParams', $updateParams);
    if ($update) {
      civicrm_api3('contact', 'create', $updateParams);
    }

    return $result;
  } //processProfile()


  /*
   * process communication and contextual messages as notes
   */
  static function processCommunication($contactId, $action, $params, $type)
  {
    if ($type == 'DIRECTMSG') {
      $entity_table = 'nyss_directmsg';
      $subject = 'Direct Message';
      $note = $params->message;

      if (!empty($params->subject)) {
        $note = "Subject: {$params->subject}\n{$note}";
      }

      if (empty($note)) {
        $note = '[no message]';
      }
    }
    else {
      $entity_table = 'nyss_contextmsg';
      $subject = 'Contextual Message';

      $note = $params->message;
      if (!empty($params->bill_number)) {
        //TODO create link to openleg?
        $note = "{$params->message}\n\n
          Bill Number: {$params->bill_number}\n
          Bill Year: {$params->bill_year}
        ";
      }

      if (!empty($params->subject)) {
        $note = "Subject: {$params->subject}\n{$note}";
      }
    }

    $params = array(
      'entity_table' => $entity_table,
      'entity_id' => $contactId,
      'note' => $note,
      'contact_id' => $contactId,
      'modified_date' => date('Y-m-d H:i:s'),
      'subject' => "Website {$subject}",
    );

    try {
      $result = civicrm_api3('note', 'create', $params);
      //CRM_Core_Error::debug_var('processCommunication result', $result);
    }
    catch (CiviCRM_API3_Exception $e) {
      // handle error here
      $errorMessage = $e->getMessage();
      $errorCode = $e->getErrorCode();
      $errorData = $e->getExtraParams();

      return array(
        'is_error' => 1,
        'error_message' => $errorMessage,
        'error_code' => $errorCode,
        'error_data' => $errorData
      );
    }

    return $result;
  } // processCommunication()


  /*
   * handle surveys (questionnaire response) with
   */
  static function processSurvey($contactId, $action, $params)
  {
    //check if survey exists; if not, construct fields
    if (!$flds = self::surveyExists($params)) {
      $flds = self::buildSurvey($params);
    }

    if (empty($flds)) {
      return array(
        'is_error' => 1,
        'error_message' => 'Unable to build survey'
      );
    }

    //build array for activity
    $actParams = array(
      'subject' => $params->form_title,
      'date' => date('Y-m-d H:i:s'),
      'activity_type_id' => CRM_Core_OptionGroup::getValue('activity_type', 'Website Survey', 'name'),
      'target_contact_id' => $contactId,
      'source_contact_id' => civicrm_api3('uf_match', 'getvalue', array(
        'uf_id' => 1,
        'return' => 'contact_id',
      )),
    );
    //CRM_Core_Error::debug_var('actParams', $actParams);

    //wrap activity and custom data in a transaction
    $transaction = new CRM_Core_Transaction();

    $act = civicrm_api3('activity', 'create', $actParams);
    if ($act['is_error']) {
      return $act;
    }

    $custParams = array(
      'entity_id' => $act['id'],
      'custom_80' => $params->form_title,
      'custom_81' => $params->form_id,
    );

    foreach ($params->form_values as $k => $f) {
      //CRM_Core_Error::debug_var("field $k", $f);

      //some surveys are constructed with duplicate field names, so need to make
      //sure we don't overwrite or skip
      if (isset($flds[$f->field]) && !isset($custParams[$flds[$f->field]])) {
        $custParams[$flds[$f->field]] = $f->value;
      }
      else {
        //try alternate field label (if duplicate)
        $custParams[$flds["{$f->field} ({$k})"]] = $f->value;
      }
    }
    //CRM_Core_Error::debug_var('actParams', $actParams);
    $cf = civicrm_api3('custom_value', 'create', $custParams);

    $transaction->commit();

    if (!empty($cf) && empty($cf['is_error'])) {
      return $cf;
    }

    return array(
      'is_error' => 1,
      'details' => 'Unable to store survey',
      'form_id' => $params->form_id,
      'contact_id' => $contactId,
    );
  } //processSurvey()


  /*
   * get web account history for a contact
   */
  static function getAccountHistory($cid)
  {
    $sql = "
      SELECT *
      FROM nyss_web_account
      WHERE contact_id = {$cid}
      ORDER BY created_date DESC
      LIMIT 50
    ";
    $r = CRM_Core_DAO::executeQuery($sql);

    $rows = array();
    while ($r->fetch()) {
      $rows[] = array(
        'action' => $r->action,
        'created' => date('F jS, Y g:i A', strtotime($r->created_date)),
      );
    }

    return $rows;
  } // getAccountHistory()


  /*
   * get web messages for a contact
   */
  static function getMessages($cid)
  {
    $sql = "
      SELECT *
      FROM civicrm_note
      WHERE entity_id = {$cid}
        AND entity_table IN ('nyss_contextmsg', 'nyss_directmsg')
      ORDER BY modified_date DESC
      LIMIT 50
    ";
    $r = CRM_Core_DAO::executeQuery($sql);

    $rows = array();
    while ($r->fetch()) {
      $rows[] = array(
        'subject' => $r->subject,
        'modified_date' => date('F jS, Y', strtotime($r->modified_date)),
        'note' => nl2br($r->note),
      );
    }

    return $rows;
  } // getMessages()


  /*
   * check if survey already exists; if so, return fields by label
   * else return false
   */
  function surveyExists($params)
  {
    if (empty($params->form_id)) {
      return false;
    }

    //see if any activity records exist with the survey id
    $act = CRM_Core_DAO::singleValueQuery("
      SELECT count(id)
      FROM civicrm_value_website_survey_10
      WHERE survey_id_81 = {$params->form_id}
    ");

    //see if custom set exists
    $cs = CRM_Core_DAO::singleValueQuery("
      SELECT *
      FROM civicrm_custom_group
      WHERE name LIKE 'Survey_{$params->form_id}'
    ");

    //CRM_Core_Error::debug_var('act', $act);
    //CRM_Core_Error::debug_var('cs', $cs);

    if (!$act && !$cs) {
      return false;
    }

    //get custom fields for this set
    $cf = civicrm_api3('custom_field', 'get', array('custom_group_id' => $cs));
    //CRM_Core_Error::debug_var('$cf', $cf);

    //check to see if existing fields count equals params field count
    //if not, need to rebuild fields
    if (count($cf['values']) != count($params->form_values)) {
      $fields = self::buildSurvey($params);
      //CRM_Core_Error::debug_var('$fields', $fields);
    }

    $fields = array();
    foreach ($cf['values'] as $id => $f) {
      $fields[$f['label']] = "custom_{$id}";
    }
    //CRM_Core_Error::debug_var('surveyExists $fields', $fields);

    return $fields;
  } //surveyExists()


  /*
   * create custom data set and fields for survey
   */
  function buildSurvey($data)
  {
    if (empty($data->form_id)) {
      return false;
    }

    //create custom group if it doesn't exist
    $csID = CRM_Core_DAO::singleValueQuery("
      SELECT id
      FROM civicrm_custom_group
      WHERE name LIKE 'Survey_{$data->form_id}'
    ");

    if (!$csID) {
      $weight = CRM_Core_DAO::singleValueQuery("
        SELECT max(weight)
        FROM civicrm_custom_group
      ");
      $params = array(
        'name' => "Survey_{$data->form_id}",
        'title' => "Survey: {$data->form_title} [{$data->form_id}]",
        'table_name' => "civicrm_value_surveydata_{$data->form_id}",
        'extends' => array('0' => 'Activity'),
        'extends_entity_column_value' => CRM_Core_OptionGroup::getValue('activity_type', 'Website Survey', 'name'),
        'collapse_display' => 1,
        'collapse_adv_display' => 1,
        'style' => 'Inline',
        'is_active' => 1,
        'weight' => $weight++,
      );
      $cg = civicrm_api3('custom_group', 'create', $params);
      $csID = $cg['id'];
    }

    //get existing fields for this custom data set
    $existingFieldsList = array();
    $existingFields = civicrm_api3('custom_field', 'get', array(
      'custom_group_id' => $csID,
      'options' => array(
        'limit' => 0,
      ),
    ));
    //CRM_Core_Error::debug_var('existingFields', $existingFields);
    //CRM_Core_Error::debug_var('$data->form_values', $data->form_values);

    foreach ($existingFields['values'] as $ef) {
      $existingFieldsList[$ef['id']] = $ef['label'];
    }

    $fields = array();
    $weight = 0;
    $fieldCreated = false;
    foreach ($data->form_values as $k => $f) {
      //check to see if field has already been created; if so, set to fields and skip
      if (in_array($f->field, $existingFieldsList)) {
        $efKey = array_search($f->field, $existingFieldsList);
        $fields[$f->field] = "custom_{$efKey}";
        continue;
      }

      //make sure label is unique
      $label = $f->field;
      if (array_key_exists($f->field, $fields)) {
        $label = "{$f->field} ({$k})";
      }
      $params = array(
        'custom_group_id' => $csID,
        'label' => $label,
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_searchable' => 1,
        'is_active' => 1,
        'is_view' => 1,
        'weight' => $weight++,
      );
      //CRM_Core_Error::debug_var('fields $params', $params);
      $cf = civicrm_api3('custom_field', 'create', $params);

      $fields[$f->field] = "custom_{$cf['id']}";

      $fieldCreated = true;
    }
    //CRM_Core_Error::debug_var('final $fields', $fields);
    //CRM_Core_Error::debug_var('$fieldCreated', $fieldCreated);

    if ($fieldCreated) {
      $logging = new CRM_Logging_Schema;
      $logging->fixSchemaDifferencesForAll();
    }

    return $fields;
  } //buildSurvey()

  static function buildBillName($params) {
    //get data pieces from possible locations
    $bill_number = (!empty($params->event_info->bill_number)) ?
      $params->event_info->bill_number : $params->bill_number;
    $bill_year = (!empty($params->event_info->bill_year)) ?
      $params->event_info->bill_year : $params->bill_year;
    $bill_sponsor = (!empty($params->event_info->sponsors)) ?
      $params->event_info->sponsors : $params->bill_sponsor;

    //build bill value text
    $billName = $bill_number.'-'.$bill_year;

    if (!empty($bill_sponsor)) {
      $sponsor = strtoupper($bill_sponsor);
    }
    else {
      require_once 'CRM/NYSS/BAO/Integration/OpenLegislation.php';
      $sponsor = CRM_NYSS_BAO_Integration_OpenLegislation::getBillSponsor($billName);
    }
    
    return "{$billName} ({$sponsor})";
  }//buildBillName

  /*
   * get the four types of website tagset tags
   * return hierarchal array by tagset
   */
  static function getTags($cid)
  {
    $parentNames = CRM_Core_BAO_Tag::getTagSet('civicrm_contact');
    //CRM_Core_Error::debug_var('$parentNames', $parentNames);

    $tags = array(
      'Website Bills' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Bills', $parentNames), $cid),
      'Website Committees' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Committees', $parentNames), $cid),
      'Website Issues' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Issues', $parentNames), $cid),
      'Website Petitions' =>
        CRM_Core_BAO_EntityTag::getChildEntityTagDetails(array_search('Website Petitions', $parentNames), $cid),
    );

    //CRM_Core_Error::debug_var('$tags', $tags);
    return $tags;
  } //getTags()


  /*
   * get activity stream for contact
   */
  static function getActivityStream()
  {
    //CRM_Core_Error::debug_var('getActivityStream $_REQUEST', $_REQUEST);

    $contactID = CRM_Utils_Type::escape($_REQUEST['cid'], 'Integer', false);
    //CRM_Core_Error::debug_var('getActivityStream $contactID', $contactID);
    $contactIDSql = ($contactID) ? "contact_id = {$contactID}" : '(1)';

    $type = CRM_Utils_Type::escape($_REQUEST['atype'], 'String', false);
    //CRM_Core_Error::debug_var('getActivityStream $type', $type);
    $typeSql = ($type) ? "AND type = '{$type}'" : '';

    $sortMapper = array(
      0 => 'sort_name',
      1 => 'type',
      2 => 'created_date',
      3 => 'details',
    );

    $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_REQUEST;
    if ($sort && $sortOrder) {
      $params['sortBy'] = $sort . ' ' . $sortOrder;
    }

    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    if ($contactID) {
      $params['contact_id'] = $contactID;
    }

    //CRM_Core_Error::debug_var('getActivityStream $params', $params);

    $orderBy = ($params['sortBy']) ? $params['sortBy'] : 'created_date desc';

    $activity = array();
    $sql = "
      SELECT SQL_CALC_FOUND_ROWS a.*, c.sort_name, c.id as cid
      FROM nyss_web_activity a
      JOIN civicrm_contact c
        ON a.contact_id = c.id
      WHERE $contactIDSql
        {$typeSql}
      ORDER BY {$orderBy}
      LIMIT {$rowCount} OFFSET {$offset}
    ";
    //CRM_Core_Error::debug_var('getActivityStream $sql', $sql);
    $dao = CRM_Core_DAO::executeQuery($sql);
    $totalRows = CRM_Core_DAO::singleValueQuery('SELECT FOUND_ROWS()');
    //CRM_Core_Error::debug_var('getActivityStream $totalRows', $totalRows);

    while ($dao->fetch()) {
      $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->cid}");

      $additionalDetails = '';
      if (in_array($dao->type, array('Direct Message', 'Context Message')) &&
        !empty($dao->data)
      ) {
        $data = json_decode($dao->data, true);
        if (!empty($data['note_id'])) {
          $note = CRM_Core_DAO::singleValueQuery("
            SELECT note
            FROM civicrm_note
            WHERE id = {$data['note_id']}
          ");
          $additionalDetails = " <a href='#' onclick='displayNote({$data['note_id']}); return false;'>[view message]</a><div title='Message Text' style='display:none;' id='msg-{$data['note_id']}'>{$note}</div>";
        }
      }

      $activity[$dao->id] = array(
        'sort_name' => "<a href='{$url}'>{$dao->sort_name}</a>",
        'type' => $dao->type,
        'created_date' => date('m/d/Y g:i A', strtotime($dao->created_date)),
        'details' => $dao->details.$additionalDetails,
      );
    }
    //CRM_Core_Error::debug_var('getActivityStream $activity', $activity);

    $iFilteredTotal = $iTotal = $params['total'] = $totalRows;
    $selectorElements = array(
      'sort_name', 'type', 'created_date', 'details',
    );

    echo CRM_Utils_JSON::encodeDataTableSelector($activity, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  } //getActivityStream()


  /*
   * store basic details about the event in the activity log
   */
  static function storeActivityLog($cid, $type, $date, $details, $data)
  {
    //CRM_Core_Error::debug_var('storeActivityLog', $type);

    $params = array(1 => array($details, 'String'));
    CRM_Core_DAO::executeQuery("
      INSERT INTO nyss_web_activity
      (contact_id, type, created_date, details, data)
      VALUES
      ({$cid}, '{$type}', '{$date}', %1, '{$data}')
    ", $params);
  } //storeActivityLog()


  /*
   * archive the accumulator record and then delete from accumulator
   */
  static function archiveRecord($db, $type, $row, $params, $date, $success = true)
  {
    //CRM_Core_Error::debug_var('archiveRecord $type', $type);
    //CRM_Core_Error::debug_var('archiveRecord $row', $row);
    //CRM_Core_Error::debug_var('archiveRecord $params', $params);
    //CRM_Core_Error::debug_var('archiveRecord $date', $date);

    //wrap in a transaction so we store archive and delete from accumulator together
    $transaction = new CRM_Core_Transaction();

    //extra fields by type
    $extraFields = array(
      'bill' => array(
        'bill_number',
        'bill_year',
      ),
      'issue' => array(
        'issue_name',
      ),
      'committee' => array(
        'committee_name',
      ),
      'contextmsg' => array(
        'bill_number',
      ),
      'petition' => array(
        'petition_id',
      ),
      'survey' => array(
        'form_id',
      ),
    );

    //setup fields for common archive table insert
    $fields = array_keys(get_object_vars($row));
    //remove object properties
    foreach ($fields as $k => $f) {
      if (strpos($f, '_') === 0 || $f == 'N') {
        unset($fields[$k]);
      }
    }
    $fields[] = 'archive_date';
    $fieldList = implode(', ', $fields);
    //CRM_Core_Error::debug_var('archiveRecord $fields', $fields);

    //setup data
    $data = array();
    foreach ($row as $f => $v) {
      if (in_array($f, $fields)) {
        $data[] = CRM_Core_DAO::escapeString($v);
      }
    }

    //add date stamp
    $data[] = $date;

    $dataList = implode("', '", $data);
    //CRM_Core_Error::debug_var('archiveRecord $data', $data);

    $mainArchiveTable = ($success) ? 'archive' : 'archive_error';

    $sql = "
      INSERT IGNORE INTO {$db}.{$mainArchiveTable}
      ({$fieldList})
      VALUES
      ('{$dataList}')
    ";
    //CRM_Core_Error::debug_var('archiveRecord $sql', $sql);
    CRM_Core_DAO::executeQuery($sql);

    //setup any additional fields
    if (array_key_exists($type, $extraFields)) {
      $fields = array_merge(array('archive_id'), $extraFields[$type]);
      $fieldList = implode(', ', $fields);

      $data = array($row->id);
      foreach ($extraFields[$type] as $f) {
        $data[] = CRM_Core_DAO::escapeString($params->$f);
      }
      $dataList = implode("', '", $data);

      $sql = "
      INSERT INTO {$db}.archive_{$type}
      ({$fieldList})
      VALUES
      ('{$dataList}')
    ";
      //CRM_Core_Error::debug_var('archiveRecord extra $sql', $sql);
      CRM_Core_DAO::executeQuery($sql);
    }

    //now delete record from accumulator
    CRM_Core_DAO::executeQuery("
      DELETE FROM {$db}.accumulator
      WHERE id = {$row->id}
    ");

    $transaction->commit();
  } // archiveRecord()

  /*
   * get recently created contacts
   */
  static function getNewContacts()
  {
    //CRM_Core_Error::debug_var('getNewContacts $_REQUEST', $_REQUEST);

    $sortMapper = array(
      0 => 'contact',
      1 => 'date',
      2 => 'email',
      3 => 'address',
      4 => 'city',
      5 => 'source',
    );

    $sEcho = CRM_Utils_Type::escape($_REQUEST['sEcho'], 'Integer');
    $offset = isset($_REQUEST['iDisplayStart']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayStart'], 'Integer') : 0;
    $rowCount = isset($_REQUEST['iDisplayLength']) ? CRM_Utils_Type::escape($_REQUEST['iDisplayLength'], 'Integer') : 25;
    $sort = isset($_REQUEST['iSortCol_0']) ? CRM_Utils_Array::value(CRM_Utils_Type::escape($_REQUEST['iSortCol_0'], 'Integer'), $sortMapper) : NULL;
    $sortOrder = isset($_REQUEST['sSortDir_0']) ? CRM_Utils_Type::escape($_REQUEST['sSortDir_0'], 'String') : 'asc';

    $params = $_REQUEST;
    if ($sort && $sortOrder) {
      $params['sortBy'] = $sort . ' ' . $sortOrder;
    }

    $params['page'] = ($offset / $rowCount) + 1;
    $params['rp'] = $rowCount;

    //CRM_Core_Error::debug_var('getActivityStream $params', $params);

    //source field sql
    $source = CRM_Utils_Type::escape($_REQUEST['source'], 'String', false);
    $sourceSql = '';
    if ($source == 'Website') {
      $sourceSql = "AND contact_source_60 = 'Website Account'";
    }
    elseif ($source == 'Bluebird') {
      $sourceSql = "AND (contact_source_60 != 'Website Account' OR contact_source_60 IS NULL)";
    }

    //date fields sql
    $dateType = CRM_Utils_Type::escape($_REQUEST['date_relative'], 'String', false);
    //CRM_Core_Error::debug_var('getNewContacts $dateType', $dateType);
    $dateSql = '';

    if (!empty($dateType) && $dateType !== '0') {
      //relative date
      //CRM_Core_Error::debug_log_message('relative date processing...');
      CRM_Contact_BAO_Query::fixDateValues($_REQUEST['date_relative'], $_REQUEST['date_low'], $_REQUEST['date_high']);
      //CRM_Core_Error::debug_var('getNewContacts relative date $_REQUEST', $_REQUEST);
    }

    $date_low = ($_REQUEST['date_low']) ? date('Y-m-d H:i:s', strtotime($_REQUEST['date_low'])) : '';
    $date_high = ($_REQUEST['date_high']) ? date('Y-m-d H:i:s', strtotime($_REQUEST['date_high'])) : '';
    //CRM_Core_Error::debug_var('getNewContacts $date_low', $date_low);
    //CRM_Core_Error::debug_var('getNewContacts $date_high', $date_high);

    if ($date_low) {
      $dateSql .= "AND created_date >= '{$date_low}'";
    }
    if ($date_high) {
      $dateSql .= "AND created_date <= '{$date_high}'";
    }

    $orderBy = 'created_date desc';
    if ($params['sortBy']) {
      //CRM_Core_Error::debug_var('getNewContacts $params[sortBy]', $params['sortBy']);
      //column values don't directly match field names so we must convert
      switch ($params['sortBy']) {
        case 'source asc':
          $orderBy = 'contact_source_60 asc';
          break;
        case 'source desc':
          $orderBy = 'contact_source_60 desc';
          break;
        case 'address asc':
          $orderBy = 'street_address asc';
          break;
        case 'address desc':
          $orderBy = 'street_address desc';
          break;
        case 'date asc':
          $orderBy = 'created_date asc';
          break;
        case 'date desc':
          $orderBy = 'created_date desc';
          break;
        case 'contact asc':
          $orderBy = 'sort_name asc';
          break;
        case 'contact desc':
          $orderBy = 'sort_name desc';
          break;

        default:
          $orderBy = $params['sortBy'];
      }
    }

    $newcontacts = array();
    $sql = "
      SELECT SQL_CALC_FOUND_ROWS c.*, ci.contact_source_60, e.email, a.street_address, a.city
      FROM civicrm_contact c
      LEFT JOIN civicrm_value_constituent_information_1 ci
        ON ci.entity_id = c.id
      LEFT JOIN civicrm_email e
        ON e.contact_id = c.id
        AND e.is_primary = 1
      LEFT JOIN civicrm_address a
        ON a.contact_id = c.id
        AND a.is_primary = 1
      WHERE (1)
        {$sourceSql}
        {$dateSql}
      ORDER BY {$orderBy}
      LIMIT {$rowCount} OFFSET {$offset}
    ";
    //CRM_Core_Error::debug_var('getNewContacts $sql', $sql);
    $dao = CRM_Core_DAO::executeQuery($sql);
    $totalRows = CRM_Core_DAO::singleValueQuery('SELECT FOUND_ROWS()');
    //CRM_Core_Error::debug_var('getNewContacts $totalRows', $totalRows);

    while ($dao->fetch()) {
      $url = CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->id}");
      $newcontacts[$dao->id] = array(
        'sort_name' => "<a href='{$url}'>{$dao->sort_name}</a>",
        'date' => date('m/d/Y g:i A', strtotime($dao->created_date)),
        'email' => $dao->email,
        'address' => $dao->street_address,
        'city' => $dao->city,
        'source' => ($dao->contact_source_60 == 'Website Account') ? 'Website' : 'Bluebird',
      );
    }
    //CRM_Core_Error::debug_var('getActivityStream $activity', $activity);

    $iFilteredTotal = $iTotal = $params['total'] = $totalRows;
    $selectorElements = array(
      'sort_name', 'date', 'email', 'address', 'city', 'source',
    );

    echo CRM_Utils_JSON::encodeDataTableSelector($newcontacts, $sEcho, $iTotal, $iFilteredTotal, $selectorElements);
    CRM_Utils_System::civiExit();
  } //getActivityStream()

  /*
   * helper to get tag name as it could be passed in different ways
   * $params the parameter object passed to the record processing function
   * $alternate the alternate column name/param name where we may need to look
   *   for backwards compatibility
   */
  static function getTagName($params, $alternate) {
    $tagName = '';
    if (!empty($params->event_info->name)) {
      $tagName = $params->event_info->name;
    }
    elseif (!empty($params->event_info->$alternate)) {
      $tagName = $params->event_info->$alternate;
    }
    elseif (!empty($params->$alternate)) {
      $tagName = $params->$alternate;
    }

    return $tagName;
  }//getTagName

  /*
   * we want to make sure we store the email address, regardless of whether we
   * have created the contact or found an existing one.
   * given a contact ID, we determine if the email address already exists;
   * if so, continue with no action. if it does not exist, add it and set it as
   * the primary email for the contact
   */
  static function updateEmail($cid, $row) {
    //email reside in one of three places
    $params = json_decode($row->msg_info);
    $email = null;

    if (!empty($params->user_info->email)) {
      $email = $params->user_info->email;
    }
    elseif (!empty($params->form_info->user_email)) {
      $email = $params->form_info->user_email;
    }
    elseif (!empty($row->email_address)) {
      $email = $row->email_address;
    }

    if (empty($email)) {
      return;
    }

    //determine if email already exists for contact
    $exists = CRM_Core_DAO::singleValueQuery("
      SELECT e.id
      FROM civicrm_email e
      JOIN civicrm_contact c 
        ON e.contact_id = c.id
        AND c.is_deleted != 1
      WHERE contact_id = %1
        AND email = %2
      LIMIT 1
    ", array(
      1 => array($cid, 'Integer'),
      2 => array($email, 'String')
    ));

    if (!$exists) {
      try {
        civicrm_api3('email', 'create', array(
          'contact_id' => $cid,
          'email' => $email,
          'is_primary' => true,
          'location_type_id' => 1,
        ));
      }
      catch (CiviCRM_API3_Exception $e) {}
    }
  }
}//end class
