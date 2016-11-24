# i2f-simple
simple, smaller version of the instant2FA library

I was pretty intrested when I first saw Instant 2FA (I usually abbreviate it just with I2F) but as soon as I saw the PHP library, I just thought "well, no, this needs work".

I mean we have a library which is on itself less than 10kb, which is pretty nice, BUT: This thing pulls 11.7 MB, OVER TEN THOUSAND TIMES of its own size via composer, and that's not enough. This thing literally throws one exception after another, heck even a user not having 2 Factor enabled is handled as an exception, like seriously? IMHO, exceptions if they should ever be used (I dont like them, I rather put a small army of ifs before) they should be used for EXCEPTIONAL stuff, not for some joke like "the user doesnt have 2FA enabled".

so I made it my mission to reverse the API and write small and SIMPLE PHP scripts for this thing, because it is a lot easier understanding a few small files all from the same dev with no outside dependencies than an 11 Megabyte behemoth from scripts of all kinds of devs.

as always with my stuff, I release this under my [Open Source License](https://github.com/My1/My1-OSL/blob/master/My1-OSL.md)

I know that the start of this thing will be rough and there wont be much error handling, but first this thing needs to work, handling errors is something I can do after the hard word.

Any Help, like Issues and Pull requests are welcome.

(also sorry if my commit count is a bit large, I am using browser GitHub)
