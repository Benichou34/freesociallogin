/*
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
*/

function addLoginButton(htmlButton)
{
	if($("#create-account_form").length > 0)
		$("#create-account_form").after(htmlButton);
	else
		$("#login_form").after(htmlButton);
}

function displayAjaxErrors(jsonData, onClosed)
{
	if (!jsonData.hasError)
		return false;

	if (onClosed === undefined) onClosed = null;

	var errors = '';
	for(var error in jsonData.errors)
		//IE6 bug fix
		if(error !== 'indexOf')
			errors += $('<div />').html(jsonData.errors[error]).text() + "\n";
	if (!!$.prototype.fancybox)
		$.fancybox.open([
			{
				type: 'inline',
				autoScale: true,
				minHeight: 30,
				afterClose : function() { if (onClosed) onClosed(jsonData); },
				content: '<p class="fancybox-error">' + errors + '</p>'
			}
		], {
			padding: 0
		});
	else
	{
		alert(errors);
		if (onClosed)
			onClosed(jsonData);
	}

	return true;
}

function displayRequestErrors(XMLHttpRequest, textStatus, errorThrown)
{
	if (textStatus !== 'abort')
	{
		error = "TECHNICAL ERROR:\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus;
		if (!!$.prototype.fancybox)
			$.fancybox.open([
				{
					type: 'inline',
					autoScale: true,
					minHeight: 30,
					content: '<p class="fancybox-error">' + error + '</p>'
				}
			], {
				padding: 0
			});
		else
			alert(error);
	}
}

function postAjaxRequest(requestUrl, postData, cbSuccess, cbError, aSync)
{
	if (postData === undefined) postData = "";
	if (cbSuccess === undefined) cbSuccess = displayAjaxErrors;
	if (cbError === undefined) cbError = displayRequestErrors;
	if (aSync === undefined) aSync = true;

	$.ajax(
	{
		type: 'POST',
		headers: { "cache-control": "no-cache" },
		url: requestUrl,
		async: aSync,
		cache: false,
		data: postData,
		dataType : "json",
		success: cbSuccess,
		error: cbError
	});
}

// Send the login request silently
function sendSocialLoginState(provider, token)
{
	postAjaxRequest(
		fsl_login_url, 
		"provider=" + provider + "&accessToken=" + token, 
		function(jsonData)
		{
			if (!jsonData.hasError && jsonData.customer)
			{
				customer_logged = true;
				$(".free_social_login").hide("fast");
			}
		}, 
		null
	); 
}

// Send the login and create a user accout if neeeded
function sendSocialLoginRequest(provider, accessToken, rerequest, callback)
{
	var postData = "provider=" + provider + "&accessToken=" + accessToken + "&createAccount=true";
	if (rerequest === true)
		postData += "&rerequest=true";

	postAjaxRequest(
		fsl_login_url, 
		postData, 
		function(jsonData)
		{
			if (!displayAjaxErrors(jsonData, callback) && callback)
				callback(jsonData);
			if (jsonData.redirect)
				window.location.href = jsonData.redirect;
		}
	);
}

// Send the revoke request
function sendSocialRevokeRequest(provider, userId, accessToken, callback)
{
	var postData = "provider=" + provider + "&user_id=" + userId;
	if (accessToken === undefined || !accessToken)
		postData += "&notAuthorized=true";
	else
		postData += "&accessToken=" + accessToken;

	postAjaxRequest(
		fsl_revoke_url, 
		postData, 
		function(jsonData)
		{
			if (!displayAjaxErrors(jsonData, callback) && callback)
				callback(jsonData);
			if (jsonData.redirect)
				window.location.href = jsonData.redirect;
		}
	);
}
