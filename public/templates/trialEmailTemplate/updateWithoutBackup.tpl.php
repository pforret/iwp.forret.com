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
?> <?php echo $d['update']; echo " "; echo $item; ?> updated in 1 site in <?php echo round($d['time']);?> seconds. Woah! <?php

//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here

?>

Hey <?php echo $d['firstName']; ?>,<br><br>

Looks like someone's on a roll! You just saved hours of manual toiling, logging into individual websites and updating items all day long. That's so 2017. 😉 And you know what they say... Time is money. 💵 <br><br>

But I also noticed that you didn't backup your websites before updating them. I'm sure you know this is not a good idea. Updates can break your websites and it's handy to have latest backups to restore your websites from, you know. Just in case.<br><br>

<a href="<?php echo IWP_SITE_URL; ?>/docs/backups/?utm_source=trial&utm_medium=email&utm_campaign=update_without_backup">See how to backup your websites -></a><br><br>

Thanks,<br>
David.<br><br>

P.S.: As always, if you have any questions, just reply to this email and we'll get back to you asap.

