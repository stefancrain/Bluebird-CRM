{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
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
*}
{* tpl for building Organization related fields *}
<table class="form-layout-compressed">
    <tr>
       <td>{$form.organization_name.label}<br/>
        {if $action == 2}
            {include file='CRM/Core/I18n/Dialog.tpl' table='civicrm_contact' field='organization_name' id=$entityID}
        {/if}
       {$form.organization_name.html|crmReplace:class:big}</td>
       </tr><tr>
       <td>{$form.legal_name.label}<br/>
       {$form.legal_name.html|crmReplace:class:big}</td>
       </tr><tr>
       <td>{$form.nick_name.label}<br/>
       {$form.nick_name.html|crmReplace:class:big}</td>
       </tr><tr>
       <td>{$form.sic_code.label}<br/>
       {$form.sic_code.html|crmReplace:class:big}</td>
       </tr><tr>
       <td>{if $action == 1 and $contactSubType}&nbsp;{else}
              {$form.contact_sub_type.label}<br />
              {$form.contact_sub_type.html}
           {/if}
       </td>
     </tr>
</table>
