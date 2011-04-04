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
{if (!$chartEnabled || !$chartSupported )&& $rows}
    {if $pager and $pager->_response and $pager->_response.numPages > 1}
        <div class="report-pager">
            {include file="CRM/common/pager.tpl" location="top" noForm=0}
        </div>
    {/if}
    {include file="CRM/common/jsortable.tpl"}
    <table class="report-layout display" id="logTable">
        <thead class="sticky">
        <tr> 
            {foreach from=$columnHeaders item=header key=field}
                {assign var=class value=""}
                {if $header.type eq 1024 OR $header.type eq 1}
        		    {assign var=class value="class='reports-header-right'"}
                {else}
                    {assign var=class value="class='reports-header'"}
                {/if}
                {if !$skip}
                   {if $header.colspan}
                       <th colspan={$header.colspan}>{$header.title}</th>
                      {assign var=skip value=true}
                      {assign var=skipCount value=`$header.colspan`}
                      {assign var=skipMade  value=1}
                   {else}
                       <th {$class}>{$header.title}</th> 
                   {assign var=skip value=false}
                   {/if}
                {else} {* for skip case *}
                   {assign var=skipMade value=`$skipMade+1`}
                   {if $skipMade >= $skipCount}{assign var=skip value=false}{/if}
                {/if}
            {/foreach}
        </tr>          
        </thead>
       
        {foreach from=$rows item=row key=rowid}
        {*<pre>{$row|@print_r}</pre>*}
            <tr  class="{cycle values="odd-row,even-row"} crm-report" id="crm-report_{$rowid}">
                {foreach from=$columnHeaders item=header key=field}
                    {assign var=fieldLink value=$field|cat:"_link"}
                    {assign var=fieldHover value=$field|cat:"_hover"}
                    {assign var=hideTouched value=""}
                    
                    <td class="crm-report-{$field}{if $header.type eq 1024 OR $header.type eq 1} report-contents-right{elseif $row.$field eq 'Subtotal'} report-label{/if}" id="{$field}_{$rowid}" >
                    	
                        {if $row.$fieldLink}
                            <a title="{$row.$fieldHover}" href="{$row.$fieldLink}">
                        {/if}
                        
                        {if $row.$field eq 'Subtotal'}
                            {$row.$field}
                        {elseif $header.type & 4 OR $header.type & 256}   
                            {if $header.group_by eq 'MONTH' or $header.group_by eq 'QUARTER'}
                                {$row.$field|crmDate:$config->dateformatPartial}
                            {elseif $header.group_by eq 'YEAR'}	
                                {$row.$field|crmDate:$config->dateformatYear}
                            {else}	
                                {if $header.type & 4}	
                                   {$row.$field|truncate:10:''|crmDate}
                                {else}
                                   {$row.$field|crmDate}
                                {/if}
                            {/if} 
                        {elseif $header.type eq 1024}
                            <span class="nowrap">{$row.$field|crmMoney}</span>
                        {else}
                            {$row.$field}
                        {/if}
                        
                        {if $row.$fieldLink}</a>{/if}
                        
                        {*NYSS add contact details*}
                        {if $field eq 'civicrm_contact_touched_display_name_touched' && $row.$field}
                        	{assign var=cPhone value="civicrm_contact_touched_phone"}
                            {assign var=cEmail value="civicrm_contact_touched_email"}
                            {assign var=cAddress value="civicrm_contact_touched_address"}
                            {assign var=cDemographics value="civicrm_contact_touched_demographics"}
                            <br />
                            <table class="logContactDetails">
                            	<tr>
                                	<td>
                                    	{$row.$cAddress}
                                    </td>
                                    <td>
                                    	{$row.$cPhone}
                                        {$row.$cEmail}
                                        {$row.$cDemographics}
                                    </td>
                                </tr>
                            </table>
                        {elseif $field eq 'civicrm_contact_touched_display_name_touched' && $row.civicrm_activity_targets_list}
                        	{*NYSS add activity target list*}
                            {$row.civicrm_activity_targets_list}
                        {/if}
                    </td>
                    {if $field eq 'civicrm_contact_touched_display_name_touched' && $row.hideTouched}
                    <script type="text/javascript">
   						document.getElementById('civicrm_contact_touched_display_name_touched_{$rowid}').innerHTML=""
					</script>
                    {/if}
                {/foreach}
            </tr>
        {/foreach}
        
        {if $grandStat}
            {* foreach from=$grandStat item=row*}
            <tr class="total-row">
                {foreach from=$columnHeaders item=header key=field}
                    <td class="report-label">
                        {if $header.type eq 1024}
                            {$grandStat.$field|crmMoney}
                        {else}
                            {$grandStat.$field}
                        {/if}
                    </td>
                {/foreach}
            </tr>
            {* /foreach*}
        {/if}
    </table>
    {if $pager and $pager->_response and $pager->_response.numPages > 1}
        <div class="report-pager">
            {include file="CRM/common/pager.tpl"  noForm=0}
        </div>
    {/if}
{/if}        