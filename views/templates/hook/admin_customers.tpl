{*
* The MIT License (MIT)
*
* Copyright (c) 2015 Benichou
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*
*  @author    Benichou <benichou.software@gmail.com>
*  @copyright 2015 Benichou
*  @license   http://opensource.org/licenses/MIT  The MIT License (MIT)
*}

<div class="col-lg-6">
	<div class="panel">
		<div class="panel-heading"><img src="{$module_dir|escape:'htmlall':'UTF-8'}logo.gif" width="16" height="16"/>
			{l s='Free social login' mod='freesociallogin'} <span class="badge">{count($sub_templates)|intval}</span>
		</div>
		<table class="table">
			<thead>
				<tr>
					<th><span class="title_box">{l s='Network' mod='freesociallogin'}</span></th>
					<th><span class="title_box">{l s='Picture' mod='freesociallogin'}</span></th>
					<th><span class="title_box">{l s='Name' mod='freesociallogin'}</span></th>
					<th><span class="title_box">{l s='Like' mod='freesociallogin'}</span></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$sub_templates item=template}
					{include file="$template"}
				{/foreach}
			</tbody>
		</table>
	</div>
</div>
