<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2012                                |
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
 * @copyright CiviCRM LLC (c) 2004-2012
 * $Id$
 *
 */

/**
 * Form to send test mail
 */
class CRM_Mailing_Form_Test extends CRM_Core_Form {

  /**
   * Function to set variables up before form is built
   *
   * @return void
   * @access public
   */
  public function preProcess() {
    //when user come from search context.
    $ssID = $this->get('ssID');
    $this->assign('ssid',$ssID);
    $this->_searchBasedMailing = CRM_Contact_Form_Search::isSearchContext($this->get('context'));
    if(CRM_Contact_Form_Search::isSearchContext($this->get('context')) && !$ssID){
      $params = array();
      $value = CRM_Core_BAO_PrevNextCache::buildSelectedContactPager($this,$params);
      $result = CRM_Core_BAO_PrevNextCache::getSelectedContacts($value['offset'],$value['rowCount1']);
      $this->assign("value", $result);
    }
  }
  /**
   * This function sets the default values for the form.
   *
   * @access public
   *
   * @return None
   */
  function setDefaultValues() {
    $count = $this->get('count');
    $this->assign('count', $count);
  }

  public function buildQuickForm() {
    $session = CRM_Core_Session::singleton();
    $this->add('text', 'test_email', ts('Send to This Address'));
    $defaults['test_email'] = $session->get('ufUniqID');
    $qfKey = $this->get('qfKey');

    $this->add('select',
      'test_group',
      ts('Send to This Group'),
      array('' => ts('- none -')) + CRM_Core_PseudoConstant::group('Mailing')
    );
    $this->setDefaults($defaults);

    $this->add('submit', 'sendtest', ts('Send a Test Mailing'));
    $name = ts('Next >>');
    if (CRM_Mailing_Info::workflowEnabled()) {
      if (!CRM_Core_Permission::check('schedule mailings') &&
        CRM_Core_Permission::check('create mailings')
      ) {
        $name = ts('Inform Scheduler');
      }
    }

    //NYSS 4448
    //FIXME : currently we are hiding save an continue later when
    //search base mailing, we should handle it when we fix CRM-3876
    /*if ($this->_searchBasedMailing) {
      $buttons = array(
        array('type' => 'back',
          'name' => ts('<< Previous'),
        ),
        array(
          'type' => 'next',
          'name' => $name,
          'spacing' => '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;',
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      );
    }
    else {*/
      $buttons = array(
        array('type' => 'back',
          'name' => ts('<< Previous'),
        ),
        array(
          'type' => 'next',
          'name' => $name,
          'spacing' => '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;',
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'submit',
          'name' => ts('Save & Continue Later'),
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      );
    //}

    $this->addButtons($buttons);

    $mailingID = $this->get('mailing_id');
    $textFile  = $this->get('textFile');
    $htmlFile  = $this->get('htmlFile');

    $this->addFormRule(array('CRM_Mailing_Form_Test', 'testMail'), $this);
    $preview = array();
    if ($textFile) {
      $preview['text_link'] = CRM_Utils_System::url('civicrm/mailing/preview', "type=text&qfKey=$qfKey");
    }
    if ($htmlFile) {
      $preview['html_link'] = CRM_Utils_System::url('civicrm/mailing/preview', "type=html&qfKey=$qfKey");
    }

    $preview['attachment'] = CRM_Core_BAO_File::attachmentInfo('civicrm_mailing',
      $mailingID
    );
    $this->assign('preview', $preview);
    //Token Replacement of Subject in preview mailing
    $options = array();
    $prefix = "CRM_Mailing_Controller_Send_$qfKey";
    if ($this->_searchBasedMailing) {
      $prefix = "CRM_Contact_Controller_Search_$qfKey";
    }
    $session->getVars($options, $prefix);

    $mailing = new CRM_Mailing_BAO_Mailing();
    $mailing->id = $options['mailing_id'];
    $mailing->find(TRUE);
    $fromEmail = $mailing->from_email;
    $replyToEmail = $mailing->replyto_email;

    $attachments = &CRM_Core_BAO_File::getEntityFile('civicrm_mailing',
      $mailing->id
    );

    $returnProperties = $mailing->getReturnProperties();
    $userID           = $session->get('userID');
    $params           = array('contact_id' => $userID);

    $details = CRM_Utils_Token::getTokenDetails($params,
      $returnProperties,
      TRUE, TRUE, NULL,
      $mailing->getFlattenedTokens(),
      get_class($this)
    );

    $allDetails = &$mailing->compose(NULL, NULL, NULL,
      $userID,
      $fromEmail,
      $fromEmail,
      TRUE,
      $details[0][$userID],
      $attachments
    );

    $this->assign('subject', $allDetails->_headers['Subject']);
  }

  /**
   * Form rule to send out a test mailing.
   *
   * @param array $params     Array of the form values
   * @param array $files      Any files posted to the form
   * @param array $self       an current this object
   *
   * @return boolean          true on succesful SMTP handoff
   * @access public
   */
  static
  function testMail($testParams, $files, $self) {
    $error = NULL;

    $urlString = 'civicrm/mailing/send';
    $urlParams = "_qf_Test_display=true&qfKey={$testParams['qfKey']}";

    $ssID = $self->get('ssID');
    if ($ssID && $self->_searchBasedMailing) {
      if ($self->_action == CRM_Core_Action::BASIC) {
        $fragment = 'search';
      }
      elseif ($self->_action == CRM_Core_Action::PROFILE) {
        $fragment = 'search/builder';
      }
      elseif ($self->_action == CRM_Core_Action::ADVANCED) {
        $fragment = 'search/advanced';
      }
      else {
        $fragment = 'search/custom';
      }
      $urlString = 'civicrm/contact/' . $fragment;
    }
    $emails = NULL;
    if (CRM_Utils_Array::value('sendtest', $testParams)) {
      if (!($testParams['test_group'] || $testParams['test_email'])) {
        CRM_Core_Session::setStatus(ts('You did not provide an email address or select a group.  No test mailing has been sent.'));
        $error = TRUE;
      }

      if ($testParams['test_email']) {
        $emailAdd = explode(',', $testParams['test_email']);
        foreach ($emailAdd as $key => $value) {
          $email = trim($value);
          $testParams['emails'][] = $email;
          $emails .= $emails ? ",'$email'" : "'$email'";
          if (!CRM_Utils_Rule::email($email)) {
            CRM_Core_Session::setStatus(ts('Please enter valid email addresses only.'));
            $error = TRUE;
          }
        }
      }

      if ($error) {
        $url = CRM_Utils_System::url($urlString, $urlParams);
        CRM_Utils_System::redirect($url);
        return $error;
      }
    }

    if (CRM_Utils_Array::value('_qf_Test_submit', $testParams)) {
      //when user perform mailing from search context
      //redirect it to search result CRM-3711.
      if ($ssID && $self->_searchBasedMailing) {
        $draftURL = CRM_Utils_System::url('civicrm/mailing/browse/unscheduled', 'scheduled=false&reset=1');
        $status = ts("Your mailing has been saved. You can continue later by clicking the 'Continue' action to resume working on it.<br /> From <a href='%1'>Draft and Unscheduled Mailings</a>.", array(1 => $draftURL));
        CRM_Core_Session::setStatus($status);

        //replace user context to search.
        $context = $self->get('context');
        if (!CRM_Contact_Form_Search::isSearchContext($context)) {
          $context = 'search';
        }
        $urlParams = "force=1&reset=1&ssID={$ssID}&context={$context}&qfKey={$testParams['qfKey']}";

        $url = CRM_Utils_System::url($urlString, $urlParams);
        CRM_Utils_System::redirect($url);
      }
      else {
        $status = ts("Your mailing has been saved. Click the 'Continue' action to resume working on it.");
        CRM_Core_Session::setStatus($status);
        $url = CRM_Utils_System::url('civicrm/mailing/browse/unscheduled', 'scheduled=false&reset=1');
        CRM_Utils_System::redirect($url);
      }
    }

    //NYSS fix redirection when informing scheduler
	/*if ( CRM_Mailing_Info::workflowEnabled( ) ) {
      if (!CRM_Core_Permission::check('schedule mailings') &&
        CRM_Core_Permission::check('create mailings')
      ) {
        $url = CRM_Utils_System::url('civicrm/mailing/browse/unscheduled', 'scheduled=false&reset=1');
        CRM_Utils_System::redirect($url);
      }
	}*/
	//NYSS end

    //NYSS 6373
    if (CRM_Utils_Array::value('_qf_Test_next', $testParams) &&
      $self->get('count') <= 0) {
      return array(
        '_qf_default' =>
        ts("You can not schedule or send this mailing because there are currently no recipients selected. Click 'Previous' to return to the Select Recipients step, OR click 'Save & Continue Later'."),
      );
    }

    if (CRM_Utils_Array::value('_qf_Import_refresh', $_POST) ||
      CRM_Utils_Array::value('_qf_Test_next', $testParams) ||
      !CRM_Utils_Array::value('sendtest', $testParams)
    ) {
      $error = TRUE;
      return $error;
    }

    $job             = new CRM_Mailing_BAO_Job();
    $job->mailing_id = $self->get('mailing_id');
    $job->is_test    = TRUE;
    $job->save();
    $newEmails = NULL;
    $session = CRM_Core_Session::singleton();
    if (!empty($testParams['emails'])) {
      $query = "
                      SELECT id, contact_id, email  
                      FROM civicrm_email  
                      WHERE civicrm_email.email IN ($emails)";

      $dao = CRM_Core_DAO::executeQuery($query);
      $emailDetail = array();
      // fetch contact_id and email id for all existing emails
      while ($dao->fetch()) {
        $emailDetail[$dao->email] = array(
          'contact_id' => $dao->contact_id,
          'email_id' => $dao->id,
        );
      }

      $dao->free();
      foreach ($testParams['emails'] as $key => $email) {
        $email = trim($email);
        $contactId = $emailId = NULL;
        if (array_key_exists($email, $emailDetail)) {
          $emailId = $emailDetail[$email]['email_id'];
          $contactId = $emailDetail[$email]['contact_id'];
        }

        if (!$contactId) {
          //create new contact.
          $params = array(
            'contact_type' => 'Individual',
            'email' => array(
              1 => array('email' => $email,
                'is_primary' => 1,
                'location_type_id' => 1,
              )),
          );
          $contact   = CRM_Contact_BAO_Contact::create($params);
          $emailId   = $contact->email[0]->id;
          $contactId = $contact->id;
          $contact->free();
        }
        $params = array(
          'job_id' => $job->id,
          'email_id' => $emailId,
          'contact_id' => $contactId,
        );
        CRM_Mailing_Event_BAO_Queue::create($params);
      }
    }

    $testParams['job_id'] = $job->id;
    $isComplete = FALSE;
    while (!$isComplete) {
      $isComplete = CRM_Mailing_BAO_Job::runJobs($testParams);
    }

    if (CRM_Utils_Array::value('sendtest', $testParams)) {

      //NYSS 4557/7223
      $testTarget = '';
      if ( $testParams['test_group'] ) {
        $group = CRM_Contact_BAO_Group::getGroups(array('id' => $testParams['test_group']));
        $testTarget = $group[0]->title;
      }
      elseif ( $testParams['test_email'] ) {
        $testTarget = $testParams['test_email'];
      }
      $status = ts("Your test message has been sent to <em>$testTarget</em>.<br />");//NYSS
      if (CRM_Mailing_Info::workflowEnabled()) {
        if ((CRM_Core_Permission::check('schedule mailings') &&
            CRM_Core_Permission::check('create mailings')
          ) ||
          CRM_Core_Permission::check('access CiviMail')
        ) {
          $status .= ts(" Click 'Next' when you are ready to Schedule or Send your live mailing (you will still have a chance to confirm or cancel sending this mailing on the next page).");
        }
      }
      else {
        $status .= ts(" Click 'Next' when you are ready to Schedule or Send your live mailing (you will still have a chance to confirm or cancel sending this mailing on the next page).");
      }

      CRM_Core_Session::setStatus($status);
      $url = CRM_Utils_System::url($urlString, $urlParams);
      CRM_Utils_System::redirect($url);
    }
    $error = TRUE;
    return $error;
  }

  /**
   * Display Name of the form
   *
   * @access public
   *
   * @return string
   */
  public function getTitle() {
    return ts('Test');
  }

  public function postProcess() {}
}

