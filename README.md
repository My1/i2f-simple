# i2f-simple
simple, smaller version of the instant2FA library

I was pretty intrested when I first saw Instant 2FA (I usually abbreviate it just with I2F) but as soon as I saw the PHP library, I just thought "well, no, this needs work".

I mean we have a library which is on itself less than 10kb, which is pretty nice, BUT: This thing pulls 11.7 MB, OVER A THOUSAND TIMES of its own size via composer, and that's not enough. This thing literally throws one exception after another, heck even a user not having 2 Factor enabled is handled as an exception, like seriously? IMHO, exceptions if they should ever be used (I dont like them, I rather put a small army of ifs before) they should be used for EXCEPTIONAL stuff, not for some joke like "the user doesnt have 2FA enabled".

so I made it my mission to reverse the API and write small and SIMPLE PHP scripts for this thing, because it is a lot easier understanding a few small files all from the same dev with no outside dependencies rather than an 11 Megabyte behemoth from scripts of all kinds of devs, which may or may not be old, and with holes, but I wont assert anything on that but it IS possible.

**as always with my stuff, I release this under my [Open Source License](https://github.com/My1/My1-OSL/blob/master/My1-OSL.md)**

I know that the start of this thing will be rough and there wont be much error handling, but first this thing needs to work, handling errors is something I can do after the hard word.

Any Help, like Issues and Pull requests are welcome.

(also sorry if my commit count is a bit large, I am using browser GitHub)

## Installation and Requirements
I stay true to my words that this thing doesnt need much outside dependencies.
First things first. We don't need no composer or anything like that.
This thing has pretty much no requirements, but you do need cURL (because you need to be able to call the API).
Also you need to either have a CA-Bundle of your trusted Root CAs ready (of course containing at least one cert of the [API](https://api.instant2fa.com/) Certificate Chain, you can also kick cert validation right into the bucket, but that's a BAD IDEA because it defeats the issue of using HTTPS for the API in the first place.
Just download the Files (e.g. by using the Download option in the Browser

Putting the Requirements of the technical side aside, there is something which is pretty obviously needed: a means to Identify the user, a `Distinct ID` as it's called by I2F. I recommend NOT to use any sensitive data like the E-Mail Address or anything like that. it is better to just use some generic data like for example a numeric User ID which is usually used as a means to identify the user anyway.
Why I recommend generic data? Simple. The Distinct ID sent to the I2F API, and together with The IP Address and possibly other data that the Javascript from the I2F Form (like the URL), it is possible to associate the user to your site.
Also considering I2F sits in the US and they dont really have the best privacy laws, it's generally not a good Idea (if it's even legal where you live) to transfer sensitive Data without a way to opt out.

## Usage
The Usage is fairly simple. While the Basics are explained at the [I2F Docs](http://docs.instant2fa.com/docs), there are some changes to what to do on the PHP side of things, but that doesnt change that you have to include a settings panel somewhere in the user area (the docs telling to throw it right below a password reset option is honestly one of the weirdest Ideas I saw, I mean you dont even have a user id at that time) so better put it close to other account security options wherea user can change their password and other stuff.

after you decided where to put your forms and figured out the basic code, you just need the URL for the settings. This is something you can get by including `getsettings.php` and running `geti2fsettings` with the disctinct ID of the user.
it just returns the Settings URL which you can include into your frontend snippet or false on an error (at least once I take care of error handling for that file.

to actually Login a user we also follow the basic steps of the documentation and let the user login first, and get the Distinct ID.
then we include `getlogin.php` and run `i2flogin`, yet again with the Distinct ID as parameter.
This function tries to get a URL for authenticating the User via 2FA.

if returns an empty string, (check with === because this function can also return false) it means that there is no URL, simply because the user doesnt have 2FA enabled.
in contrast, if it retuns `false` it means there was an error somewhere, either with the specific request or with cURL itself.
(I wrote it so it just silently logs the error in the file `i2f-log.txt` and returns false, but as per the license, you can change that)

Now we get to the Intresting part. If we get a string with contents we have a URL for 2FA'ing the user. now you need to save the Distince ID of the user somewhere (possibly the `$_SESSION` because we need it later for further verification). Then we redirect the User to the 2FA page and make sure he cannot get anywhere in the logged in area. This can be done by either caging the user to the page so he cant get anywhere, not not fully establishing the session in a way it recognizes the User is logged in.

after the User has entered his I2F credentials, we need to verify those. this isnt written yet, but I can explain how it basically works.
As said before you need the Distinct ID, and also you need the token I2F gives you.

You then need to include the PHP for the verification.
There are 3 Possible main Scenarios that can play out.
* Everything is nice, which means the user can get his session properly established.
* The validation went out fine, but the Distinct ID That I2F returns with the token doesnt match the one we recorded. This probably means that someone manipulated the login by using a token he got from another account which does validate properly but throws out a different user.
* The validation fails. According to the I2F Docs this can happen because the user entered his code wrong too many times, but it also may just mean some kind of possibly random error.
* Of Course there's also scenario 4, with an error in other things (like the server is down, or curl having a problem, These will probably be logged quietly again.

Well forget what I said back then we have two more, one which I couldnt know beforehand.
* Of course there's the random error from I2f (like for example wrong key and whatnot)
* Aside from that I2F also returns the time of the request, meaning that if the request comes earlier than the specified tolerance (in seconds) it says no.

well now that we have the validation, using it is as simple as the name suggests. you just include the validate.php file and then you call the `i2fverify` function with the distinct ID and the token you got from I2F.

## Examples
### Display Settings page
```php
<?php
require "getsettings.php";
$id=getuseridfromsomewhere();
$url=geti2fsettings($id);
if($url) {
  echo <<<END
<iframe src="$url"></iframe>
END;
}
else {
echo "something is clearly wrong here."
}
?>
```
### Handle the login
```php
<?php
require("getlogin.php");
dosomethingtologin($user,$password);
$id=getuseridfromsomewhere();
session_start();
$_SESSION["id"]=$id
$url=i2flogin($id);
if($url) {
echo <<<END
<div class="instant2fa"></div>
<script src="https://js.instant2fa.com/hosted.js"></script>
<script>
    Instant2FAPage.configure({
        element: document.querySelector('.instant2fa'),
        uri: `$url` // this is the URL previously retrieved from the server
    }, function(event) {
        if (event.type === "verificationSuccess") {
            var token = event.payload.token;
            console.log("Verification token is: " + token);
            
            $.ajax({
                method: 'POST',
                url: 'localhost/i2f/i2f-logon2.php',
                data: {
                    instant2faToken: event.payload.token
                }
            });
        }
    });
</script>
END;
}
else {
  dorestoflogin();
}
?>
```
### Check the validity of the I2F Token
```php
reqire "validate.php";
session_start();
$id=$_SESSION["id"];
$result=i2fverify($id,$_POST["instant2faToken"]);
if($result==true) {
  unset $_SESSION["id"];
  dorestoflogin();
}
else {
  tryagainwithnewchallenge();
}
```
