<?php
require_once(dirname(__FILE__).'/users/users.php');

// get user if logged in or require user to login
$user = User::get();
#$user = User::require_login();

$fetched_groups = array();

if (!is_null($user)) {
	// You can work with users, but it's recommended to tie your data to accounts, not users
	$current_account = Account::getCurrentAccount($user);

	$creds = $user->getUserCredentials('meetup');

	$page = 0; // requesting first page
	$keep_going = true;

	while($keep_going) {
		$result = $creds->makeOAuthRequest(
			'http://api.meetup.com/2/groups?order=name&member_id=self',
			'GET'
		);
		if ($result['code'] == 200) {
			$group_data = json_decode($result['body'], true);

			foreach ($group_data['results'] as $group) {
				$fetched_groups[] = array(
					'name' => $group['name'],
					'link' => $group['link'],
					'logo' => $group['group_photo']['thumb_link'],
					'members' => $group['members']
				);
			}

			// keep going while next meta parameter is set
			$keep_going = $group_data['meta']['next'] !== '';

			if ($keep_going) {	
				$page++;
			}
		} else {
			$keep_going = false;
		}
	}
}
?>
<html>
<head>
	<title>Sample page</title>
	<link rel="stylesheet" type="text/css" href="meetup.css"/>
</head>
<body>
<div style="float: right"><?php include(dirname(__FILE__).'/users/navbox.php'); ?></div>
<?php

if (!is_null($user)) {
?>
<h1>Welcome, <?php echo $user->getName() ?>!</h1>

<?php

	usort($fetched_groups, function($a, $b) {
		return $a['members'] > $b['members'] ? -1 : 1;
	});

	if (count($fetched_groups)) {
?>
<h3>Your groups:</h3>
<ul id="groups">
<?php
		foreach ($fetched_groups as $group) {
			?><li>
				<div class="logo"><img src="<?php echo $group['logo'] ?>" /></div>
				<a href="<?php echo $group['link'] ?>"><?php echo $group['name'] ?></a><br/>
				<?php echo $group['members'] ?> members
				<div class="clb"/>
			</li><?php
		}
?>
</ul>
<?php
	} else { ?>
		<p>You still didn't join any groups?!</p>
		<p><a href="http://www.meetup.com/find/">Find a group and join immediately!</a></p>
	<?php
	}
}
else
{
?>
<h1>Welcome!</h1>

<?php
	$meetup_module = AuthenticationModule::get('meetup');
	?><p><?php $meetup_module->renderRegistrationForm(); ?></p><?php
}
?> 
</body>
</html>
