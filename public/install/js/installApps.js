function doValidateDBCredsCall(type){
	$("input").css('background-color', '');
	var dbHost=$("#dbHost").val();
	var dbPort=$("#dbPort").val();
	var dbName=$("#dbName").val();
	var dbUser=$("#dbUser").val();
	var dbPass=$("#dbPass").val();
	var dbTableNamePrefix=$("#dbTableNamePrefix").val();
	if (!dbHost) {
		$("#dbHost").css('background-color','#EFDEDE');
	}
	if (!dbPort) {
		$("#dbPort").css('background-color','#EFDEDE');
	}
	if (!dbName) {
		$("#dbName").css('background-color','#EFDEDE');
	}
	if (!dbUser) {
		$("#dbUser").css('background-color','#EFDEDE');
	}
	if (!dbTableNamePrefix) {
		$("#dbTableNamePrefix").css('background-color','#EFDEDE');
	}
	if (!(dbHost && dbPort && dbName && dbUser && dbTableNamePrefix)) {
		return '';
	}
	$('.btn_next_step').css('width',  '110px');
	$('.continueLink ').append("<span class='loading'></span>");
	$('.btn_next_step.float-right.rep_sprite').addClass('disabled');
	$('.continueLink').addClass('linkDisabled');
	$.ajax({
		type: "GET",
		url: 'index.php',
		dataType: 'json',
		data: {dbHost: dbHost, dbPort: dbPort, dbName: dbName, dbUser: dbUser, dbPass: dbPass, dbTableNamePrefix: dbTableNamePrefix},
		success: function (obj) {
			$('.btn_next_step').css('width',  '');
			$('.continueLink ').find('span').remove();
			$('.btn_next_step.float-right.rep_sprite').removeClass('disabled');
			$('.continueLink').removeClass('linkDisabled');
			if (typeof obj != 'undefined' && obj != null)
			{
				if (typeof obj.error != 'undefined' ) {
					$("#detailedError").html(obj.error);
				} else {
					$("#detailedError").html(obj);
				}
				$('#errorDatabase').css( 'display','block');
			}
			else{
				$('#errorDatabase').css( 'display','none');
				$('#databasec').removeAttr('onsubmit')
				if (type == 1) {
					$('#databasec').attr('action', 'index.php?step=createLogin&pluginInstaller');
				} else {
					$('#databasec').attr('action', 'index.php?step=createLogin');
				}
				$("#databasec").submit();
			}
		},
		error: function(obj){
			$('.btn_next_step').css('width',  '');
			$('.continueLink ').find('span').remove();
			$('.btn_next_step.float-right.rep_sprite').removeClass('disabled');
			$('.continueLink').removeClass('linkDisabled');
			$("#detailedError").html(obj.responseText);
			$('#errorDatabase').css( 'display','block');
		}
	});
}

function createLoginCheck(type){
	$("#loginError").hide();
	$("#email_subscribe").css('top','-9px');
	var password 	= $("#myPassword").val();
	var email 		= $("#email").val();
	if (!email) {
		$("#email").css('background-color','#EFDEDE')
	} else if(!validateEmail(email)){
		$("#loginError").html("Please enter a valid email");
		$("#loginError").show();
		$("#email_subscribe").css('top','-52px');
	}else if (password.length < 6) {
		$("#loginError").html("Password should have minimum 6 characters");
		$("#loginError").show();
		$("#email_subscribe").css('top','-52px');
	} else {
		$('#loginCredsForm').removeAttr('onsubmit')
		if (type == 1) {
			$('#loginCredsForm').attr('action', 'index.php?step=createInfinitewpLogin&pluginInstaller');
		}else if (type == 2) {
			$('#loginCredsForm').attr('action', 'index.php?step=install');
		} else {
			$('#loginCredsForm').attr('action', 'index.php?step=createInfinitewpLogin');
		}
		if ($("#email_subscribe").hasClass('active')){
			$("#emailSubscribe").val("1");
		} else {
			$("#emailSubscribe").val("0");
		}
		$("#loginCredsForm").submit();
	}
}

function createInfinitewpLoginCheck(type){
	$("#loginError").hide();
	$("#email_subscribe").css('top','-9px');
	var password 	= $("#mySitePassword").val();
	var email 		= $("#siteEmail").val();
	var iwpprocess = $('#processInfiniteWPAction').val();
	if (!email) {
		$("#siteEmail").css('background-color','#EFDEDE')
	} else if(!validateEmail(email) && iwpprocess == 'create'){
		$("#loginError").html("Please enter a valid email");
		$("#loginError").show();
		$("#email_subscribe").css('top','-52px');
	}else if ($('.showIWPPassword').hasClass('active') && password.length < 6) {
		$("#loginError").html("Password should have minimum 6 characters");
		$("#loginError").show();
		$("#email_subscribe").css('top','-52px');
	} else {
		processInfiniteWPRequest(type);
	}
}

function showSuccessItems(){
	if($("#somethingWentWrong").is(":visible")){
		return false;
	}
	$("#openAdminPanel").show();
	$("#installNote").hide();
	$(".install_progress").hide();
}

function showFailureItems(){
	$("#openAdminPanel").hide();
	$(".install_progress").hide();
	$("#installNote").hide();
	$("#somethingWentWrong").show();
}

function changeNewIWPURL(url){
	$(".open_panel").attr('href',url);
}
function successInstallFolderRemoved(){
	$('.success_area').html("We have removed the <strong>install</strong> folder for security reasons.");
	$('.success_area').show();
}

function failedInstallFolderRemove(){
	$('.install_folder_msg').html("We are not able to remove the <strong>install</strong> folder. Please remove or rename it for enhanced security.");
	$('.install_folder_msg').show();
}

function validateEmail(email) {
	var emailReg = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return emailReg.test( email );
}

function progressBar(width){
	$("#progress").css('width', width+"px");
}

function startInstall(){
	$.ajax({
		type: "POST",
		url: 'index.php',
		dataType: 'json',
		data: {process:"startInstall", dbHost:dbHost, dbUser:dbUser, dbName:dbName, dbPort:dbPort, dbTableNamePrefix:dbTableNamePrefix, dbPass:dbPass, email:email, password:password, siteEmail:siteEmail, sitePassword:sitePassword, infinitewpLogin:infinitewpLogin},
		success: function (result) {
			processInstallResult(result);
		}
	});
}

function processInstallResult(result){
	if (typeof result == 'undefined') {
		return;
	}
	if (typeof result.error != 'undefined') {
		$(".error_cont").html(result.error);
		$(".error_cont").show();
		$("#expertsInstallation").show();
	}
	if (typeof result.progressBar != 'undefined') {
		progressBar(result.progressBar);
	}
	if (typeof result.executeSchemaQueries != 'undefined') {
		if (result.executeSchemaQueries != 'completed') {
			continueInstall('executeSchemaQueries', result.executeSchemaQueries);
		} else if(result.executeSchemaQueries == 'completed'){
			continueInstall('modifyConfigFile', '');
		}
	} else if (typeof result.modifyConfigFile != 'undefined' &&  result.modifyConfigFile == 'completed') {
		continueInstall('userCreated', '');
	} else if (typeof result.userCreated != 'undefined' && result.userCreated == 'completed'){
		continueInstall('removeInstallFolder', '');
		showSuccessItems();
	} else if (typeof result.removeInstallFolder != 'undefined'){
		if (result.removeInstallFolder == 'failed') {
			failedInstallFolderRemove();
		} else if(result.removeInstallFolder == 'success'){
			successInstallFolderRemoved();
		}
	}
}

function continueInstall(type, status){
	$.ajax({
		type: "POST",
		url: 'index.php',
		dataType: 'json',
		data: {process:'continueInstall', type:type, status:status, dbHost:dbHost, dbUser:dbUser, dbName:dbName, dbPort:dbPort, dbTableNamePrefix:dbTableNamePrefix, dbPass:dbPass, email:email, password:password, siteEmail:siteEmail, sitePassword:sitePassword},
		success: function (result) {
			processInstallResult(result);
		}
	});
}

function processInfiniteWPRequest(type){
	$('#checkingIWPRequest').show();
	$( ".iwp_login_form" ).css( "opacity", "0.3" );
	$.ajax({
		type: "POST",
		url: 'index.php',
		dataType: 'json',
		data: $('form#loginCredsForm').serialize(),
		success: function (result) {
			$('#checkingIWPRequest').hide();
			$( ".iwp_login_form" ).css( "opacity", "1" );
			processInfiniteWPRequestResult(result, type);
		}	
	});
}

function processInfiniteWPRequestResult(result, type){
	if (typeof result == 'undefined') {
		return;
	}
	if (typeof result.error != 'undefined') {
		$(".error_cont").html(result.error);
		$(".error_cont").show();
	}

	if (typeof result.success != 'undefined' && result.success == '1' && result.extra != 'undefined' && result.extra == 'create' ) {
		$('#loginCredsForm').removeAttr('onsubmit')
		$('#infinitewpLogin').val('1');
		$("#processInfiniteWPAction").remove();
		$("#processInfiniteWP").remove();
		if (type == 1) {
			$('#loginCredsForm').attr('action', 'index.php?step=install&pluginInstaller');
		}else{
			$('#loginCredsForm').attr('action', 'index.php?step=install');
		}
		$('#mySitePassword'). val(result.password);
		$("#loginCredsForm").submit();
	}else if (typeof result.success != 'undefined' && result.success == '1' && result.extra != 'undefined' && result.extra == 'login') {
		$('#loginCredsForm').removeAttr('onsubmit')
		$('#infinitewpLogin').val('1');
		$("#processInfiniteWPAction").remove();
		$("#processInfiniteWP").remove();
		if (type == 1) {
			$('#loginCredsForm').attr('action', 'index.php?step=install&pluginInstaller');
		}else{
			$('#loginCredsForm').attr('action', 'index.php?step=install');
		}
		$("#loginCredsForm").submit();
	}
	
}


$(function(){
	$('a.linkDisabled').live('click', function(e){
		e.preventDefault();
		return false;
	});

	$('#email').live('click',function(){
		$("#email").css('background-color','');
	});

	$(".show_password").live('mousedown', function(e){
		var passwordInp = $(this).next(".passwords").get(0);
		passwordInp.blur();
		passwordInp.type = 'text';
		$(this).text('Hide');
		e.preventDefault();
		e.stopPropagation();
	}).live('mouseup mouseleave', function(e){
		$(this).text('Show');
		$(this).next(".passwords").get(0).type = 'password';
		e.preventDefault();
		e.stopPropagation();
	});

	$(".button_strength").live('click', function(e){
		e.preventDefault();
		e.stopPropagation();
	});

	$(".btn_next_step").live('mousedown',function(){
		 $(this).addClass('pressed');
		}).live('mouseup',function () {
		$(this).removeClass('pressed');
	});

	$('.nano').nanoScroller();

	$("input").live('click',function() {
		$(this).css('background-color','');
	});

	$("#haveIWPAccount").live('click',function() {
		$(this).hide();
		$('.showIWPPassword').show();
		$('.showIWPPassword').addClass('active');
		$('#lostIWPAccount').show();
		$('#createIWPAccount').show();
		$('#processInfiniteWPAction').val('login');
		$('.iwp_login .form_title').text('LOGIN EXISTING INFINITEWP.COM ACCOUNT');
		$('#loginCredsForm').attr('action', 'index.php?step=install&pluginInstaller&createAccount');
	});

	$("#createIWPAccount").live('click',function() {
		$(this).hide();
		$("#haveIWPAccount").show();
		$('.showIWPPassword').hide();
		$('.showIWPPassword').removeClass('active');
		$('#lostIWPAccount').hide();
		$('#createIWPAccount').hide();
		$('#processInfiniteWPAction').val('create');
		$('.iwp_login .form_title').text('CREATE INFINITEWP.COM ACCOUNT');
		$('#loginCredsForm').attr('action', 'index.php?step=install&pluginInstaller');
	});

	$("#email_subscribe").live('click',function() {
		if($(this).hasClass('active')){
			$(this).removeClass('active');
		} else {
			$(this).addClass('active');
		}
	});

	$('#reStartInstall').live('click', function() {
		$(".error_cont").hide();
		$("#expertsInstallation").hide();
		startInstall();
	});

	$('#myPassword').strength({
		strengthClass: 'iwp_compatibility  strength',
		strengthMeterClass: 'strength_meter',
		strengthButtonClass: 'button_strength',
		strengthButtonText: 'Show',
		strengthButtonTextToggle: 'Hide'
	});
});