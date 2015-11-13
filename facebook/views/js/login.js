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

window.fbAsyncInit = function()
{
	FB.init({
		appId      : facebook_appid,
		cookie     : true,  // enable cookies to allow the server to access the session
		xfbml      : true,  // parse social plugins on this page
		version    : 'v2.5' // use version 2.5
	});

	if (!customer_logged)
	{
		// Now that we've initialized the JavaScript SDK, we call FB.getLoginStatus().
		// This function gets the state of the person visiting this page and can return one of three states to the callback you provide.
		// They can be:
		// 1. Logged into your app ('connected')
		// 2. Logged into Facebook, but not your app ('not_authorized')
		// 3. Not logged into Facebook and can't tell if they are logged into your app or not.
		//
		// These three cases are handled in the callback function.

		FB.getLoginStatus(function(response)
		{
			if (response.status === 'connected') // Logged into the app and Facebook.
				sendSocialLoginState("facebook", response.authResponse.accessToken);
		});
	}
};

// Load the SDK asynchronously
(function(d, s, id)
{
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// Send the login and create a user accout if neeeded
function sendFacebookLoginRequest(accessToken, rerequest)
{
	sendSocialLoginRequest("facebook", accessToken, rerequest, function(jsonData)
	{
		if (jsonData.rerequest)
			facebookLogin(true); // Retry after error
	});
}

// Send the revoke request
function sendFacebookRevokeRequest(facebookId, accessToken)
{
	sendSocialRevokeRequest("facebook", facebookId, accessToken);
}

// This function is called when someone click to a Login Button.
function facebookLogin(rerequest)
{
	var loginOptions = { scope: facebook_scope };
	if(rerequest === true)
		loginOptions.auth_type = 'rerequest';

	FB.login(function(response)
	{
		if (response.status === 'connected') // Logged into the app and Facebook.
			sendFacebookLoginRequest(response.authResponse.accessToken, rerequest);
	}, loginOptions);
}

function facebookRevoke(facebook_id)
{
	if (confirm(confirm_revoke))
	{
		FB.getLoginStatus(function(response)
		{
			if (response.status === 'connected') // Logged into the app and Facebook.
			{
				sendFacebookRevokeRequest(facebook_id, response.authResponse.accessToken)
			}
			else if (response.status === 'not_authorized')
			{
				// The person is logged into Facebook, but not your app.
				// Revoke access
				sendFacebookRevokeRequest(facebook_id)
			}
			else
			{
				// The person is not logged into Facebook, so we're not sure if they are logged into this app or not.
				FB.login(function(response)
				{
					if (response.status === 'connected')
					{
						// Logged into the app and Facebook.
						sendFacebookLoginRequest(response.authResponse.accessToken);
						facebookRevoke(facebook_id); // Re-call revoke function
					}
				},
				{
					scope: facebook_scope
				});
			}
		});
	}
}