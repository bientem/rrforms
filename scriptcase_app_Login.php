<?php

if(sc_logged_is_blocked()) { sc_error_exit(); }

$login		= {login};
$pswd		= {pswd};
$options = array(
	'domain_controllers' => array('10.232.2.12'),
    'base_dn' => 'DC=miambiente,DC=interno',
    'account_suffix' => '@MIAMBIENTEINT',
	'admin_username' => $login,
    'admin_password' => $pswd,
	'ad_port' => 389
);
$ldapConn	= sc_ldap_login($options);

if($ldapConn === false)
{
	sc_log_add('Login Fail', {lang_login_fail} . {login});
	sc_logged_in_fail({login});
	sc_error_message({lang_error_login});
}
else
{
	$user_filter = "(|(samaccountname=".$login . ")(uid=".$login.")(userprincipalname=".$login."))";
	$result =  sc_ldap_search($user_filter, array('mail', 'displayname', 'cn', 'givenname', 'samaccountname', 'userprincipalname', 'uid'));
	
echo "<pre>";
print_r($result);
echo "</pre>";
	
	if(!isset($result[0]))
	{
		[_user] = $login;
	}
	else
	{
		[_user] = (isset($result[0]['samaccountname']) ? $result[0]['samaccountname'] : (isset($result[0]['userprincipalname']) ? $result[0]['userprincipalname'] : $result[0]['cn']));
	}
	
	$sql = "SELECT 
				count(*)
	      	FROM
				table_users
	      	WHERE
				(login = '". [_user] ."' OR  login = '". {login} ."')
				AND active = 'Y'";
		
	
	sc_select(rs, $sql);
	if({rs} === false || $rs->fields[0] === '0')
	{
		$rs->Close();
		sc_user_logout('logged_user', 'logout', 'app_Login');
		sc_redir('app_user_not_active', user={login}, 'modal', '', 297, 897);
	}
	$rs->Close();
	[usr_login] = {login};
	[usr_pswd]  = sc_encode({pswd});
	
	if(isset($result[0]['displayname']))
	{
		[usr_name] = $result[0]['displayname'];
	}
	elseif(isset($result[0]['cn']))
	{
		[usr_name] = $result[0]['cn'];
	}
	elseif(isset($result[0]['givenname']))
	{
		[usr_name] = $result[0]['givenname'];
	}
	elseif(isset($result[0]['samaccountname']))
	{
		[usr_name] = $result[0]['samaccountname'];
	}
	[usr_email] = (isset($result[0]['mail']) ? $result[0]['mail'] : '');
    
    
    remember_me_validate();   
    
    
}


?>