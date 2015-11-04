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

var auth2; // The Sign-In client object.

// Load the SDK asynchronously
(function() {
	var po = document.createElement('script');
	po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/api:client.js?onload=googleAsyncInit';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(po, s);
})();

function sendGoogleLoginState(token)
{
	if(!token.access_token)
		return;

	sendSocialLoginState("google", JSON.stringify(token));
}

function googleAsyncInit()
{
	gapi.load('auth2', function()
	{
		// Retrieve the singleton for the GoogleAuth library and set up the client.
		auth2 = gapi.auth2.init({
			client_id: google_appid,
			scope: google_scope
		});

		auth2.then(function()
		{
			if(!customer_logged && auth2.isSignedIn.get() == true)
				sendGoogleLoginState(auth2.currentUser.get().getAuthResponse());
		});
	});
}

// Send the login and create a user accout if neeeded
function sendGoogleLoginRequest(token)
{
	if(!token.access_token)
		return;

	sendSocialLoginRequest("google", JSON.stringify(token));
}

// Send the revoke request
function sendGoogleRevokeRequest(googleId, token)
{
	sendSocialRevokeRequest("google", googleId, (token === undefined || !token.access_token) ? null : JSON.stringify(token));
}

// This function is called when someone click to a Login Button.
function googleLogin()
{
	// Sign the user in.
	auth2.signIn().then(function()
	{
		sendGoogleLoginRequest(auth2.currentUser.get().getAuthResponse());
	});
}

function googleRevoke(google_id)
{
	if (confirm(confirm_revoke))
	{
		if (auth2.isSignedIn.get() == true)
		{
			var user = auth2.currentUser.get();
			sendGoogleRevokeRequest(
				user.getId(),
				user.getAuthResponse()
			);
		}
		else
		{
			auth2.signIn().then(function()
			{
				var user = auth2.currentUser.get();
				sendGoogleRevokeRequest(
					user.getId(),
					user.getAuthResponse()
				);
			});
		}
	}
}