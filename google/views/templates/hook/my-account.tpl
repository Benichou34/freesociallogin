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

<!-- MODULE FreeSocialLogin -->
<script type="text/javascript">
	var confirm_revoke = "{l s='Are you sure ?' mod='freesociallogin' js=1}";
</script>
<li class="account_social_login">
	{if isset($google_id)}
		<a href="#" title="{l s='Revoke Google access' mod='freesociallogin'}" rel="nofollow" onclick="googleRevoke('{$google_id}');" >
			<img src="{$google_picture_url}" alt="{$google_username}" height="50" width="50" class="google_picture" />
			<span>{l s='Revoke Google access' mod='freesociallogin'}</span>
		</a>
	{else}
		<a href="#" title="{l s='Login with Google' mod='freesociallogin'}" rel="nofollow" onclick="googleLogin();" >
			<i class="icon-google"></i>
			<span>{l s='Login with Google' mod='freesociallogin'}</span>
		</a>
	{/if}
</li>
<!-- END : MODULE FreeSocialLogin -->