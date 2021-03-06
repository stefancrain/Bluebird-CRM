{*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.4                                                |
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

<div id="newsFeeds">
	
    {foreach from=$newsfeed key=newskey item=newsitem}
         {if $smarty.get.context != 'dashletFullscreen'}
              {assign var="desc" value=$newsitem.encoded|truncate:100}
         {else}
              {assign var="desc" value=$newsitem.encoded}
         {/if}
         <div class="newsFeed">
              <div class="newsTitle"><a href="{$newsitem.link}" target="_blank">{$newsitem.title}</a></div>
              <div class="newsDesc">{$desc}</div>
              <div class="created-date">{$newsitem.pubDate}</div>
         </div>
    {/foreach}
    &raquo;&nbsp;<a href="http://senateonline.senate.state.ny.us/BluebirdNews.nsf/" target="_blank" class="more_news">View more news items</a>
</div>
