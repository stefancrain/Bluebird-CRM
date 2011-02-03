{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.3                                                |
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
<div id="demographics" class="form-item">
    <table class="form-layout">
       <tr>
        <td>
            {$form.birth_date_low.label|replace:'-':'<br />'}&nbsp;&nbsp; 
	        {include file="CRM/common/jcalendar.tpl" elementName=birth_date_low}&nbsp;&nbsp;&nbsp;
            {$form.birth_date_high.label}&nbsp;&nbsp;
            {include file="CRM/common/jcalendar.tpl" elementName=birth_date_high}
        </td>
       </tr>
       {*NYSS 2693*}
       <tr>
       	<td>
        	{$form.is_deceased.label}<br />
            {$form.is_deceased.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('is_deaceased', 'Advanced'); return false;" >{ts}clear{/ts}</a>)</span>
        </td>
       </tr>
       {*NYSS end*}
      <tr>
        <td>
           {$form.deceased_date_low.label|replace:'-':'<br />'}&nbsp;&nbsp;
           {include file="CRM/common/jcalendar.tpl" elementName=deceased_date_low}&nbsp;&nbsp;&nbsp;
           {$form.deceased_date_high.label}&nbsp;&nbsp;
           {include file="CRM/common/jcalendar.tpl" elementName=deceased_date_high}
        </td>    
      </tr>
      <tr>
         <td>
            {$form.gender.label}<br />
            {$form.gender.html}<span class="crm-clear-link">(<a href="#" title="unselect" onclick="unselectRadio('gender', 'Advanced'); return false;" >{ts}clear{/ts}</a>)</span>
         </td>
      </tr>
    </table>            
</div>

