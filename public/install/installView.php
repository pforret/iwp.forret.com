<?php

function printHeader(){
  ?>
  <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="robots" content="noindex">
  <title>InfiniteWP</title>
  <?php
    $min = '';
    if(file_exists(APP_ROOT."/_env.php")){
      @include_once(APP_ROOT."/_env.php");
      if (!defined("DISABLE_MINIFY")) {
        $min = '.min';
      }
    }
  ?>
  <link href='http://fonts.googleapis.com/css?family=Droid+Sans:400,700' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="../css/core<?php echo $min; ?>.css" />
  <link rel="stylesheet" type="text/css" href="../css/pwd.min.css" />
  <link rel="stylesheet" href="../css/nanoscroller.min.css" type="text/css" />
  <link rel="stylesheet" type="text/css" href="css/installStyle<?php echo $min; ?>.css">
  <link rel="stylesheet" type="text/css" href="../css/font-awesome-4.6.3/css/font-awesome.min.css">
  <script src="../js/jquery.min.js" type="text/javascript" charset="utf-8"></script>
  <script src="../js/apps<?php echo $min; ?>.js" type="text/javascript" charset="utf-8"></script>
  <script src="../js/jquery.nanoscroller.min.js" type="text/javascript"></script>
  <script src="../js/strength.min.js" type="text/javascript"></script>
  <script type="text/javascript" src="js/installApps<?php echo $min; ?>.js"></script>
  </head> <?php
}

function printCheckRequirementHTML(){
  $check = checkPHPRequirements();
  ?>
     <div class="iwp_installtion_content check_requirement">
     <div id="checkRequirementsSuccess" style=" position: absolute;z-index: 1;margin-left: 254px;margin-top: 232px;background: url(../images/loading_w.gif) no-repeat center center;padding-top: 60px; display:none;">Checking server requirements</div>
      <div class="tr" >
        <div class="req_txt float-left">
          <div class="req_title">PHP INFORMATION</div>
          <div class="req_descr">You can view the current state of PHP.</div>
        </div>
        <a href="info.php" target="_blank" class="float-left" style="margin:10px 43px;">View PHP Info</a>
        <div class="clear-both"></div>
      </div>
       <div class="tr <?php checkAvailable('PHP_VERSION', 'errorClass');?>">
        <div class="req_txt float-left">
          <div class="req_title">PHP VERSION</div>
          <?php 
            if(version_compare($check['available']['PHP_VERSION'], '8.0.0', '>=' )){ ?>
              <div class="req_descr">InfiniteWP is not compatible with PHP 8 yet. We are working on it! Please downgrade the PHP version to less than 8.</div>
           <?php }else{ ?>
             <div class="req_descr">InfiniteWP requires PHP version <?php echo $check['required']['PHP_VERSION']; ?> or higher.</div>
          <?php } ?>
          
        </div>
        <div class="req_result float-left"><?php echo $check['available']['PHP_VERSION']; ?></div>
        <div class="icon_result float-left <?php checkFinal('PHP_VERSION'); ?>"></div>
        <div class="clear-both"></div>
      </div>
       <div class="tr <?php checkAvailable('PHP_WITH_MYSQL', 'errorClass');?>">
         <div class="req_txt float-left">
          <div class="req_title">MYSQL SUPPORT</div>
          <div class="req_descr">PHP is required to be compiled with <span class="droid700">Mysql or Mysqli</span> support.</div>
        </div>
        <div class=" req_result float-left">
          <?php checkAvailable('PHP_WITH_MYSQL', 'status'); ?>
        </div>
        <div class="icon_result float-left <?php checkFinal('PHP_WITH_MYSQL'); ?>"></div>
        <div class="clear-both"></div>
      </div>
      <div class="tr <?php checkAvailable('PHP_SAFE_MODE', 'errorClass');?>">
        <div class="req_txt float-left">
          <div class="req_title">SAFE MODE</div>
          <div class="req_descr">PHP safe mode is required to be <span class="droid700">disabled</span>.</div>
        </div>
        <div class=" req_result float-left">
          <?php checkAvailable('PHP_SAFE_MODE', 'status'); ?>
        </div>
        <div class="icon_result float-left <?php checkFinal('PHP_SAFE_MODE'); ?>"></div>
        <div class="clear-both"></div>
      </div>
        <div class="tr <?php checkAvailable('PHP_WITH_OPEN_SSL', 'errorClass');?>">
      <div class="req_txt float-left">
          <div class="req_title">OPEN SSL</div>
          <div class="req_descr">Enabling Open SSL makes it secure. However, this is <span class="droid700">optional</span>.</div>
        </div>
        <div class=" req_result float-left">
          <?php checkAvailable('PHP_WITH_OPEN_SSL', 'status'); ?>
        </div>
        <div class="icon_result float-left <?php checkFinal('PHP_WITH_OPEN_SSL'); ?>"></div>
        <div class="clear-both"></div>
      </div>
        <div class="tr <?php checkAvailable('PHP_FILE_UPLOAD', 'errorClass');?>">
        <div class="req_txt float-left">
          <div class="req_title">FILE UPLOADS</div>
          <div class="req_descr">PHP file uploads option is required to be <span class="droid700">enabled</span>.</div>
        </div>
        <div class=" req_result float-left">
          <?php checkAvailable('PHP_FILE_UPLOAD', 'status'); ?>
        </div>
        <div class="icon_result float-left  <?php checkFinal('PHP_FILE_UPLOAD'); ?>"></div>
        <div class="clear-both"></div>
      </div>
     <div class="tr <?php checkAvailable('PHP_WITH_CURL', 'errorClass');?>">
          <div class="req_txt float-left">
          <div class="req_title">CURL SUPPORT</div>
          <div class="req_descr">It is required for all communications between the client plugin and the admin panel.</div>
        </div>
        <div class=" req_result float-left">
          <?php checkAvailable('PHP_WITH_CURL', 'status'); ?>
        </div>
        <div class="icon_result float-left <?php checkFinal('PHP_WITH_CURL'); ?>"></div>
        <div class="clear-both"></div>
      </div>

      <div class="tr <?php checkAvailable('PHP_MAX_EXECUTION_TIME_CONFIGURABLE', 'errorClass');?>">
       <div class="req_txt float-left">
          <div class="req_title">CONFIGURABLE MAX EXECUTION TIME</div>
          <div class="req_descr">The max execution time should be configurable.</div>
        </div>
        <div class=" req_result float-left">
          <?php checkAvailable('PHP_MAX_EXECUTION_TIME_CONFIGURABLE', 'status'); ?>
        </div>
        <div class="icon_result float-left <?php checkFinal('PHP_MAX_EXECUTION_TIME_CONFIGURABLE'); ?>"></div>
        <div class="clear-both"></div>
      </div>
    </div>
    <?php
}


function printEnterDBDetailsHTML(){
  $config = manageCookies::cookieGet('DBcreds');
  ?>
  <form <?php if(defined("PLUGIN_INSTALLER")){ echo 'onsubmit="doValidateDBCredsCall(1); return false;"';} else { echo 'onsubmit="doValidateDBCredsCall(0); return false;"';}?> id="databasec" method="POST">
      <div class="iwp_installtion_content db_login">
        <div class="form" style="margin: auto;">
          <div class="form_title">Create a new DB and enter its details below <?php if(defined("PLUGIN_INSTALLER")){ echo '<br><br><span style="font-weight: normal;text-transform: none;">We have auto-filled DB details from the WordPress\'s config.php file. If you do not want to use that DB, update the details here.</span>';} ?></div>
          <div class="cf">
            <div style="float:left;  width: 220px;  margin-right: 20px;">
        <div class="label">DB HOST</div>
        <input name="dbHost" type="text" id="dbHost" value="<?php if(isset($config['dbHost'])) { echo $config['dbHost']; } else if(defined('PLUGIN_INSTALLER')){ echo DB_HOST; } else { echo 'localhost'; } ?>" tabindex="1">
        <div class="label">DB NAME</div>
        <input name="dbName" type="text" id="dbName" value="<?php if(isset($config['dbName'])) { echo $config['dbName']; } else if(defined('PLUGIN_INSTALLER')){ echo DB_NAME; } ?>" tabindex="3">
        <div class="label">DB USERNAME</div>
        <input name="dbUser" type="text" id="dbUser" value="<?php if(isset($config['dbUser'])) { echo $config['dbUser']; } else if(defined('PLUGIN_INSTALLER')){ echo DB_USER; } ?>" tabindex="5">
            </div>
            <div style="float:left;  width: 220px;">
        <div class="label">DB PORT</div>
        <input name="dbPort" type="text" id="dbPort" value="<?php if(isset($config['dbPort'])) { echo $config['dbPort']; } else { echo '3306'; } ?>" tabindex="2">
        <div class="label">DB TABLE NAME PREFIX</div>
        <input name="dbTableNamePrefix" type="text"  id="dbTableNamePrefix" value="<?php if(isset($config['dbTableNamePrefix'])) { echo $config['dbTableNamePrefix']; } else { echo 'iwp_'; } ?>" tabindex="4">
        <div style="position:relative">
          <div class="label">DB PASSWORD</div>
          <a class="show_password" style="position: absolute;right: 3px;top: 25px;">Show</a>
          <input name="dbPass" type="password" id="dbPass" value="<?php if(isset($config['dbPass'])) { echo $config['dbPass']; } else if(defined('PLUGIN_INSTALLER')){ echo DB_PASSWORD; } ?>" class="passwords" style="padding: 6px 41px 6px 5px;width: 177px;"  tabindex="6">
              </div>
            </div>
          </div>
          <section style='display:none' id='errorDatabase'>
        <div class="error_cont" id='detailedError' style="text-align: left;"></div>
        <div style="text-align:center; padding-top: 10px;">You can also <a href="https://infinitewp.com/cpanel-installation/" target="_blank">auto-install InfiniteWP via cPanel</a>.</div>
        </section>
        </div>
      <input type="submit" name="step" value="createLogin" style="display:none;" />
      </div>
    </form>
  <?php
}

function printEnterUserDetailsHTML(){
  $config = manageCookies::cookieGet('config');

  if ($_GET['step'] == 'createLogin' && !empty($_POST['decode'])) {
    $_POST['dbPass'] = base64_decode($_POST['dbPass']);
    $_POST['dbUser'] = base64_decode($_POST['dbUser']);
    $config['password'] = base64_decode($config['password']); 
    // $config['sitePassword'] = base64_decode($config['sitePassword']);
  }
  if (!empty($config['password'])) {
    if ( base64_encode(base64_decode($config['password'], true)) === $config['password']){
        $config['password'] = base64_decode($config['password']); 
    }
  }

  ?>
  <form <?php if(defined("PLUGIN_INSTALLER")){ echo 'onsubmit="createLoginCheck(1); return false;"';} else { echo 'onsubmit="createLoginCheck(0); return false;"';}?>  id="loginCredsForm" method="POST">
    <div class="iwp_installtion_content db_login">
      <div class="form" style="margin-left: 66px;margin-top: 50px;">
        <div style="display:none">
        <input id='instalPath' value="<?php if(isset($_POST['instalPath'])) { echo $_POST['instalPath']; } elseif($config['instalPath']){ echo $config['instalPath']; } ?>" name="instalPath">
        <input id='dbHost' value="<?php if(isset($_POST['dbHost'])) { echo $_POST['dbHost']; } elseif($config['dbHost']){ echo $config['dbHost'];} ?>" name="dbHost">
        <input id='dbUser' value="<?php if(isset($_POST['dbUser'])) { echo base64_encode($_POST['dbUser']); } elseif($config['dbUser']){ echo base64_encode($config['dbUser']);} ?>" name="dbUser">
        <input id='dbPass' value="<?php if(isset($_POST['dbPass'])) { echo base64_encode($_POST['dbPass']); } elseif($config['dbPass']){ echo base64_encode($config['dbPass']);} ?>" name="dbPass">
        <input id='dbName' value="<?php if(isset($_POST['dbName'])) { echo $_POST['dbName']; } elseif($config['dbName']){ echo $config['dbName'];} ?>" name="dbName">
        <input id='dbPort' value="<?php if(isset($_POST['dbPort'])) { echo $_POST['dbPort']; } elseif($config['dbPort']){ echo $config['dbPort'];} ?>" name="dbPort">
        <input id='dbTableNamePrefix' value="<?php if(isset($_POST['dbTableNamePrefix'])) { echo $_POST['dbTableNamePrefix']; } elseif($config['dbTableNamePrefix']){ echo $config['dbTableNamePrefix'];} ?>" name="dbTableNamePrefix">
      </div>
    <div class="form_title">CREATE LOGIN CREDENTIALS</div>
    <div class="label">EMAIL</div>
    <?php includeWPConfigFile(); ?>
    <input name="email" id="email" value="<?php if(isset($config['email'])) { echo $config['email']; } else if(defined('PLUGIN_INSTALLER')){ echo pluginInstallerGetAdminEmail(); }?>" type="text">
    <div id="myform">
    <div class="label">PASSWORD <span style="text-transform:lowercase">(min. 6 characters)</span></div>
    <input id="myPassword" name="password" value="<?php if(isset($config['password'])) { echo $config['password']; } ?>" type="password" class="iwp_compatibility">
          <div class="error_cont" id='loginError' style=" display:none; text-align: justify;  margin-top:60px; text-justify: inter-word;">Password should have minimum 6 characters</div>
    </div>
   <input type="submit" name="step" value="install" style="display:none;"  />
      </div>
      </div>
    </form>
  <?php
}

function printEnterInfiniteWPDetailsHTML(){
    $config = manageCookies::cookieGet('config');
    ?>
    <div class="iwp_installtion_content check_requirement">
    <div id="checkingIWPRequest" style=" position: absolute;z-index: 1;margin-left: 254px;margin-top: 85px;background: url(../images/loading_w.gif) no-repeat center center;padding-top: 60px; display:none;">Connecting INFINITEWP.COM</div>
    </div>
    <form <?php if(defined("PLUGIN_INSTALLER")){ echo 'onsubmit="createInfinitewpLoginCheck(1); return false;"';} else { echo 'onsubmit="createInfinitewpLoginCheck(0); return false;"';}?>  id="loginCredsForm" method="POST">
      <div class="iwp_installtion_content db_login iwp_login">
        <div class="form" style="margin-left: 66px;margin-top: 50px;">
          <div style="display:none">
        <input id='instalPath' value="<?php if(isset($_POST['instalPath'])) { echo $_POST['instalPath']; } elseif($config['instalPath']){ echo $config['instalPath']; } ?>" name="instalPath">
        <input id='dbHost' value="<?php if(isset($_POST['dbHost'])) { echo $_POST['dbHost']; } elseif($config['dbHost']){ echo $config['dbHost'];} ?>" name="dbHost">
        <input id='dbUser' value="<?php if(isset($_POST['dbUser'])) { echo ($_POST['dbUser']); } elseif($config['dbUser']){ echo base64_encode($config['dbUser']);} ?>" name="dbUser">
        <input id='dbPass' value="<?php if(isset($_POST['dbPass'])) { echo ($_POST['dbPass']); } elseif($config['dbPass']){ echo base64_encode($config['dbPass']);} ?>" name="dbPass">
        <input id='dbName' value="<?php if(isset($_POST['dbName'])) { echo $_POST['dbName']; } elseif($config['dbName']){ echo $config['dbName'];} ?>" name="dbName">
        <input id='dbPort' value="<?php if(isset($_POST['dbPort'])) { echo $_POST['dbPort']; } elseif($config['dbPort']){ echo $config['dbPort'];} ?>" name="dbPort">
        <input id='dbTableNamePrefix' value="<?php if(isset($_POST['dbTableNamePrefix'])) { echo $_POST['dbTableNamePrefix']; } elseif($config['dbTableNamePrefix']){ echo $config['dbTableNamePrefix'];} ?>" name="dbTableNamePrefix">
        <input id='email' value="<?php if(isset($_POST['email'])) { echo $_POST['email']; } elseif($config['email']){ echo $config['email'];} ?>" name="email">
        <input id='password' value="<?php if(isset($_POST['password'])) { echo base64_encode($_POST['password']); } elseif($config['password']){ echo base64_encode($config['password']);} ?>" name="password">
        <input id='processInfiniteWP' value="processInfiniteWP" name="process">
        <input id='processInfiniteWPAction' value="create" name="infinitewpAction">
        <input id='infinitewpLogin' value="<?php if(isset($config['infinitewpLogin'])) {echo $config['infinitewpLogin'];}else{ echo '0';} ?>" name="infinitewpLogin">
      </div>
      <div class="form_title">CREATE INFINITEWP.COM ACCOUNT</div>
      <div class="iwp_login_form">
      <div class="label">EMAIL</div>
      <?php includeWPConfigFile(); ?>
      <input name="siteEmail" id="siteEmail" value="<?php if(isset($config['siteEmail'])) { echo $config['siteEmail']; }else if(isset($_POST['email'])){echo $_POST['email'];} else if(defined('PLUGIN_INSTALLER')){ echo pluginInstallerGetAdminEmail(); } ?>" type="text">
      <div id="myform" class="showIWPPassword" style="display: none;margin-bottom: 42px;">
      <div class="label">PASSWORD</div>
      <input id="mySitePassword" name="sitePassword" value="<?php if(isset($config['sitePassword'])) { echo $config['sitePassword']; } ?>" type="password" class="iwp_compatibility">
      </div>
      <p class="lost_password"> <a id="haveIWPAccount" style="display: block;" class="a_href_red">Already have account?</a></p>
      <p class="lost_password" style="margin-bottom: 5px;"> <a id="createIWPAccount" style="display: none; margin-bottom: 5px;" class="a_href_red">Create new account?</a></p>
      <p class="lost_password"> <a id="lostIWPAccount" href="https://infinitewp.com/lost-your-password/" style="display: none;" target="_blank" class="a_href_red">Lost your password?</a></p>

      <div class="error_cont" id='loginError' style=" display:none; text-align: left;  margin-top:15px; text-justify: inter-word; line-height: 1.4;">Password should have minimum 6 characters</div>
     <br><div>As a security measure, we will delete the [IWP Admin Panel]/install folder after successful installation.</div>
     <input type="submit" name="step" value="install" style="display:none;"  />
        </div>
        </div>
      </div>
      </form>
    <?php
}

function printInstallHTML(){
  $config = manageCookies::cookieGet('config');
  $email = urlencode($config['email']);
  $password = urlencode(base64_decode($config['password']));

  $URL = "email=".$email."&password=".$password;

  ?>
  <div class="iwp_installtion_content install_final">
    <div style=" text-align:center; margin-top: 100px;" id="installNote">Installing your admin panel...</div>
    <div class="install_progress"><div id='progress' style="width:1px"></div></div>
    <div class="error_cont" style="display:none"></div>
    <div class="install_folder_msg" style="display:none"></div>
    <div class="success_area" style="display:none"></div>
    <div id="expertsInstallation" style="text-align:center; padding-top: 10px; display :none">If you need help, our experts can install it for you. <a href="https://support.infinitewp.com/support/tickets/new" target="_blank">Get in touch.</a> </div>
    <div id="openAdminPanel" style="display:none; position:relative ; top:-70px" >
      <div style="text-align: center;margin-top: 100px;">Installed Successfully :)</div>
      <a href="../login.php?<?php echo $URL ?>" <?php if(defined("PLUGIN_INSTALLER")){ echo "target='_blank'";} ?> class="open_panel">Open my admin panel</a>
    </div>
    <div  id="somethingWentWrong" style="display:none; position:relative">
      <div style="text-align: center;margin-top: 100px;">Try again :(</div>
    </div>
  </div>
   <?php
}


function printLicenceHTML(){ ?>
  <div class="iwp_installtion_content license_agreement">
        <div class="tr">
          <div style="height:400px; overflow:auto; padding: 0 10px 0 20px;">
            <div class="nano">
              <div class="content" style="padding: 10px 10px 10px 0px; text-align:justify" >
                <?php include('../license.html'); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php
}

function printSideBarHTML(){ ?>
  <body>
  <div id="site_cont" style="width: 852px;">
    <div id="logo_signin" style="margin-top:50px;"></div>
    <div style="text-transform: uppercase; color: #434E51; font-size: 12px; margin-bottom: 20px; text-align: center; font-weight: 700; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);">Manage all your WordPress sites</div>
    <div class="dialog_cont iwp_installation cf">
      <div class="th rep_sprite">
        <div class="title droid700">INFINITEWP INSTALLATION</div>
      </div>
      <div class="cf">
        <div class="th_sub rep_sprite left_stack"
        <?php if(empty($_GET['step'])){ ?> style="height:400px;" <?php }?>
        <?php if($_GET['step'] == 'checkRequirement'){ ?> style="height:475px;" <?php }?>
        <?php if($_GET['step'] == 'enterDetails'){  ?> style="height:370px;" <?php }?>
        <?php if($_GET['step'] == 'createLogin'){  ?> style="height:351px;" <?php }?>
        <?php if($_GET['step'] == 'createInfinitewpLogin'){  ?> style="height:351px;" <?php }?>
        <?php if($_GET['step'] == 'install'){  ?> style="height:441px;" <?php }?>>
          <ul>
            <li><a class="<?php indexPagesClass(''); ?>" href="index.php<?php if(defined("PLUGIN_INSTALLER")){ echo "?pluginInstaller";}?>">License Agreement</a></li>
            <li><a class="<?php indexPagesClass('checkRequirement'); ?>" href="index.php?step=checkRequirement<?php pluginInstaller();?>">Server Requirements</a></li>
            <li><a class="<?php indexPagesClass('enterDetails'); ?>" href="index.php?step=enterDetails<?php pluginInstaller();?>">DB Details</a></li>
            <li> <?php if($_GET['step'] == 'install'){ ?>
            <form id="installToCreateUser" method="post" action="index.php?step=createLogin<?php pluginInstaller();?>">
              <div style="display:none">
                <input id='instalPath' value="<?php echo $_POST['instalPath'] ?>" name="instalPath">
                <input id='dbHost' value="<?php echo $_POST['dbHost'] ?>" name="dbHost">
                <input id='dbUser' value="<?php echo $_POST['dbUser'] ?>" name="dbUser">
                <input id='dbPass' value="<?php echo $_POST['dbPass'] ?>" name="dbPass">
                <input id='dbName' value="<?php echo $_POST['dbName'] ?>" name="dbName">
                <input id='dbPort' value="<?php echo $_POST['dbPort'] ?>" name="dbPort">
                <input id='decode' value="1" name="decode">
                <input id='dbTableNamePrefix' value="<?php echo $_POST['dbTableNamePrefix'] ?>" name="dbTableNamePrefix">
              </div>
            <a class="<?php indexPagesClass('createLogin'); ?>" href="index.php?step=createLogin<?php pluginInstaller();?>" onclick="$('#installToCreateUser').submit(); return false;">Create Panel Login</a> </form>
              <?php } else { ?>
                <a class="<?php indexPagesClass('createLogin'); ?>" href="index.php?step=createLogin<?php pluginInstaller();?>"> Create Panel Login</a> 
              <?php }?>
            </li>
            <?php if (!manageCookies::cookieGet('softaculous')): ?>
                <li><a class="<?php indexPagesClass('createInfinitewpLogin'); ?>" href="index.php?step=createInfinitewpLogin<?php pluginInstaller();?>">Create infinitewp.com Account</a></li>
            <?php endif ?>
            <li><a class="<?php indexPagesClass('install'); ?>" href="index.php?step=install<?php pluginInstaller();?>">Installation</a></li>
          </ul>
        </div> <?php
}

function validateRequirements(){
  $pageComingFrom = $_SERVER['HTTP_REFERER'];
  $keyword = 'step';
  $dontRedirect = strpos($pageComingFrom, $keyword);
  $check = checkPHPRequirements();
  manageCookies::cookieSet('isRequirementMet', false,array('expire'=> COOKIE_EXPIRE_LIMIT ));
  if ( !$dontRedirect && isRequirementsSatisfied() == true) {
    manageCookies::cookieSet('isRequirementMet', true,array('expire'=> COOKIE_EXPIRE_LIMIT ));
    if (defined("PLUGIN_INSTALLER")) {
      $redirectURL = "index.php?step=enterDetails&pluginInstaller";
    } else{
      $redirectURL = "index.php?step=enterDetails";
    }
    ?>
    <script type="text/javascript">
      window.onload = function () {
        $("#checkRequirementsSuccess").show();
        $(".dialog_cont.iwp_installation .iwp_installtion_content.check_requirement .tr").css('opacity','0.4')
      }
      window.setTimeout(function(){
        window.location.href = "<?php echo $redirectURL;?>"; }, 1000);
    </script><?php
  } else if(!$dontRedirect){ ?>
      <script>
        window.onload = function () {
          $('.btn_next_step.float-right.rep_sprite').addClass('disabled');
          $('.continueLink').addClass('linkDisabled');
        }
      </script><?php
  }
}

function printButtonNames(){
  $softaculous = manageCookies::cookieGet('softaculous');
  if($_GET['step']=='enterDetails'){ ?>
    Next, Create Panel Login
  <?php
  } elseif(empty($_GET['step'])) { ?>
    Agree &amp; Install
  <?php
  } elseif($_GET['step'] == 'checkRequirement') { ?>
    Next, DB details
  <?php
  } else if($_GET['step'] == 'createLogin' && empty($softaculous)){ ?>
    Next, Create infinitewp.com Account
  <?php
  }else if($_GET['step'] == 'createLogin' && !empty($softaculous)){ ?>
    Next, Install
  <?php
  }else if($_GET['step'] == 'createInfinitewpLogin'){ ?>
    Next, Install
  <?php
  } else { ?>
   Continue<?php
  }
}

function printFooterBar($continueOnClick, $continueLink, $idDatabase, $continueClass, $continueDivClass){ ?>
  <div class="clear-both"></div>
    <div class="th rep_sprite" style="border-top:1px solid #c6c9ca; height:35px;">
     <?php if($_GET['step']!='install'){ ?>
      <a <?php if(empty($continueOnClick) && $continueLink=='createLogin'){ if(defined("PLUGIN_INSTALLER")){ echo 'onClick="doValidateDBCredsCall(1);"'; } else {echo 'onClick="doValidateDBCredsCall(0);"';} }  else if(empty($continueOnClick)){?> href="index.php?step=<?php echo $continueLink; pluginInstaller(); ?>"<?php }  ?> onClick="<?php echo $continueOnClick; ?>" style="text-decoration:none;" id="<?php echo $idDatabase ?>" class="continueLink <?php echo $continueClass; ?>">
       <div class="btn_next_step float-right rep_sprite <?php echo $continueDivClass; ?>">
        <?php printButtonNames(); ?>
        <div class="taper"></div>
      </div>
      </a>
     <?php } else { ?>
     <script>
        var dbHost = "<?php echo $_POST['dbHost']; ?>";
        var dbUser = "<?php echo $_POST['dbUser']; ?>";
        var dbPass = "<?php echo $_POST['dbPass']; ?>";
        var dbName = "<?php echo $_POST['dbName']; ?>";
        var dbPort = "<?php echo $_POST['dbPort']; ?>";
        var dbTableNamePrefix = "<?php echo $_POST['dbTableNamePrefix']; ?>";
        var email = "<?php echo $_POST['email']; ?>";
        var siteEmail = "<?php echo $_POST['siteEmail']; ?>";
        var password = "<?php echo $_POST['password']; ?>";
        var sitePassword = "<?php echo base64_encode($_POST['sitePassword']); ?>";
        var infinitewpLogin = "<?php echo $_POST['infinitewpLogin']; ?>";

        $(function(){ startInstall(); });<?php  } ?>
     </script>
    </div>
    </div>
  </div>
  </body>
  </html> <?php
}

function redirectIfUserAlreadyCreated(){ 
  $config = manageCookies::cookieGet('config');
  $alreadyHaveCreated = 0;
  if (!empty($config['sitePassword']) && !empty($config['infinitewpLogin']) && $config['infinitewpLogin'] == 1) {
    $alreadyHaveCreated =1;
  }
  ?>

<script>
  var alreadyHaveCreated = "<?php echo $alreadyHaveCreated  ?>";

  if (alreadyHaveCreated == 1) {
    window.setTimeout(function(){
      $("#haveIWPAccount").click();
      $("#loginCredsForm").submit();
    }, 100);
  }
</script>


<?php }

function printAlreadyInstalled(){
  ?>
  <body>
  <div id="site_cont" style="width: 852px;">
    <div id="logo_signin" style="margin-top:50px;"></div>
    <div style="text-transform: uppercase; color: #434E51; font-size: 12px; margin-bottom: 20px; text-align: center; font-weight: 700; text-shadow: 0 1px 0 rgba(255, 255, 255, 0.6);">Manage all your WordPress sites</div>
    <div class="dialog_cont iwp_installation cf">
     <div class="th rep_sprite">
        <div class="title droid700">INFINITEWP INSTALLATION</div>
      </div>
      <div style="padding: 50px; text-align: center;">It looks like the admin panel is already installed here. To re-install, empty the config.php file, save it and retry.</div>
    </div>
</div>
<?php }