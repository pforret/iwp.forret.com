<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 $item = 'item';
if ($d['update'] >1) {
	$item = ' items';
}
//subject starts here
?> <?php echo $d['update']; echo $item; ?> updated in 1 site in <?php echo round($d['time']);?> seconds. Woah! <?php

//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here

?>

Hey <?php echo $d['firstName']; ?>,<br><br>

Looks like someone's on a roll! You just saved hours of manual toiling, logging into individual websites and updating items all day long. That's so 2017. 😉 And you know what they say... Time is money. 💵 <br><br>

And I also noticed that you backed up your websites before updating them. That was a smart move. 👍 Updates can break your websites and it's handy to have latest backups to restore your websites from, you know. Just in case.<br><br>
 
Thanks,<br>
David.<br><br>

P.S.: As always, if you have any questions, just reply to this email and we'll get back to you asap.


