<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 

//subject starts here
?> Did you know you can send your backups to off-site locations? <?php

//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here

?>

Hey <?php echo $d['firstName']; ?>,<br><br>

I'm sure you know how important it is to backup your websites regularly. But more often than not, these backups are stored in the WP server itself. This is not advisable because there is a very real possibility of the server crashing or the site becoming inaccessible. And when that happens, you might lose access to the backup files right when you need them to restore your site.<br><br>

To avoid this, you can set your backups to be stored in popular cloud apps or even other servers via FTP.<br><br>

<a href="<?php echo APP_URL; ?>">Set up off-site backup locations now -></a><br><br>

Thanks,<br>
David.<br><br>

P.S.: As always, if you have any questions, just reply to this email and we'll get back to you asap.

