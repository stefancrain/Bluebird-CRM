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
<div class="form-item">
{if $form.postal_code.html}
    <div class="postal-code-search">
    {$form.postal_code.label}<br />
    {$form.postal_code.html}&nbsp;{ts}OR{/ts}<br />
     <br />
     <label>{ts}Postal Code{/ts}</label>
            {$form.postal_code_low.label|replace:'-':'<br />'}<br />
            {$form.postal_code_low.html|crmReplace:class:six}<br />
            {$form.postal_code_high.label}<br />
    		{$form.postal_code_high.html|crmReplace:class:six}
     </div>
     <script>
         cj('.postal-code-search').appendTo('form#Advanced #locationSection:empty');    
    </script> 				
{/if}

    <table class="form-layout">
	<tr>
        <td>        
		{$form.location_type.label}<br />
        {$form.location_type.html} 
        <div class="description" >
            {ts}Location search uses the PRIMARY location for each contact by default.{/ts}<br /> 
            {ts}To search by specific location types (e.g. Home, Work...), check one or more boxes above.{/ts}
        </div>
        <div style="float:left; margin-top: 15px;">
            {$form.street_address.label}<br />
            {$form.street_address.html|crmReplace:class:big}
        </div>
        <div style="float:left; margin-left:10px; margin-top: 15px;">
            {$form.city.label}<br />
            {$form.city.html|crmReplace:class:big}
  	    </div>
		<div style="clear:left;">
        	{$form.state_province.label}<br />
            {$form.state_province.html}
        </div>
        </td>

    {if $addressGroupTree}
	    <td width="50%">
	        {include file="CRM/Custom/Form/Search.tpl" groupTree=$addressGroupTree showHideLinks=true}
        </td>
    {/if}
    </tr>
    </table>
</div>

