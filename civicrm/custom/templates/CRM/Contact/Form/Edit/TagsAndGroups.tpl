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
{if $title}
<div class="crm-accordion-wrapper crm-tagGroup-accordion crm-accordion-closed">
 <div class="crm-accordion-header">
  <div class="icon crm-accordion-pointer"></div> 
	{$title} 
  </div><!-- /.crm-accordion-header -->
  <div class="crm-accordion-body" id="tagGroup">
{/if}
    <table class="form-layout-compressed" style="width:98%">
	<tr>
	    {foreach key=key item=item from=$tagGroup}
		{* $type assigned from dynamic.tpl *}
		{if !$type || $type eq $key }
		<td width={cycle name=tdWidth values="70%","30%"}>
        <span class="label">{if $title}{$form.$key.label}{/if}</span>
		    <div id="tagListWrap">
            <table id="tagGroupTable">
			{foreach key=k item=it from=$form.$key}
			    {if $k|is_numeric}
				<tr class={cycle values="'odd-row','even-row'" name=$key} id="tagRow{$k}">
				    <td>
                    {if $key eq 'tag'}
                    	{if strstr($it.html,'&nbsp;&nbsp;&nbsp;&nbsp;')}
                        	{$it.html|replace:'>&nbsp;&nbsp;&nbsp;&nbsp;':' class="level3">'}
                        {elseif strstr($it.html,'&nbsp;&nbsp;')}
                        	{$it.html|replace:'>&nbsp;&nbsp;':' class="level2">'}
                        {else}
                        	{$it.html}
                        {/if}
                    {else}
						<strong>{$it.html}</strong><br /> {*LCD retain for groups list*}
                    {/if}
                    
					{if $item.$k.description}
					    <div class="description">
						{$item.$k.description}
					    </div>
					{/if}
				    </td>
				</tr>
			    {/if}
			{/foreach}   
		    </table>
            </div>
		</td>
		{/if}
	    {/foreach}
	</tr>
	<tr><td>{include file="CRM/common/Tag.tpl"}</td></tr>
    </table>   
{if $title}
 </div><!-- /.crm-accordion-body -->
</div><!-- /.crm-accordion-wrapper -->

{/if}