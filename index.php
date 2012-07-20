<?php
require_once(dirname(__FILE__).'/users/users.php');

// get user if logged in or require user to login
$user = User::get();
#$user = User::require_login();

$fetched_groups_organizer = array();
$fetched_groups_member = array();

if (!is_null($user)) {
	// You can work with users, but it's recommended to tie your data to accounts, not users
	$current_account = Account::getCurrentAccount($user);

	$creds = $user->getUserCredentials('meetup');
	$meetup_info = $creds->getUserInfo();
	$meetup_id = $meetup_info['id'];

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
				$group_info = array(
					'name' => $group['name'],
					'link' => $group['link'],
					'logo' => $group['group_photo']['thumb_link'],
					'members' => $group['members']
				);

				if ($group['organizer']['member_id'] == $meetup_id) {
					$fetched_groups_organizer[] = $group_info;
				} else {
					$fetched_groups_member[] = $group_info;
				}
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
	<title>Sample Meetup Application</title>
	<link rel="stylesheet" type="text/css" href="meetup.css"/>
</head>
<body>
<div style="float: right"><?php include(dirname(__FILE__).'/users/navbox.php'); ?></div>
<?php

if (!is_null($user)) {
?>
<h1>Welcome, <?php echo $user->getName() ?>!</h1>

<div style="width: 400px; max-width: 100%">
<a href="http://github.com/StartupAPI/users/" target="_blank" style="float: left"><img alt="Octocat.png" src="http://startupapi.org/w/images/thumb/6/61/Octocat.png/50px-Octocat.png" width="50" height="50" border="0" align="top" style="margin-right: 1em"></a>
This is a sample Meetup application powered by Startup API, you can see the <a href="http://github.com/StartupAPI/users/" target="_blank">code on Github</a>.
</div>
<div style="clear: both"></div>

<?php

	usort($fetched_groups_organizer, function($a, $b) {
		return $a['members'] > $b['members'] ? -1 : 1;
	});

	usort($fetched_groups_member, function($a, $b) {
		return $a['members'] > $b['members'] ? -1 : 1;
	});

	if (count($fetched_groups_member) || count($fetched_groups_organizer)) {
		if (count($fetched_groups_organizer)) {
?>
<h3>You organize:</h3>
<ul class="groups">
<?php
			foreach ($fetched_groups_organizer as $group) {
				?><li>
					<div class="logo">
					<?php if ($group['logo'] != '') { ?>
						<img src="<?php echo $group['logo'] ?>" />
					<?php } ?>
					</div>
					<a href="<?php echo $group['link'] ?>"><?php echo $group['name'] ?></a><br/>
					<?php echo $group['members'] ?> members
					<div class="clb"/>
				</li><?php
			}
?>
</ul>
<?php
		}

		if (count($fetched_groups_member)) {
?>
<h3>You're a member:</h3>
<ul class="groups">
<?php
			foreach ($fetched_groups_member as $group) {
				?><li>
					<div class="logo">
					<?php if ($group['logo'] != '') { ?>
						<img src="<?php echo $group['logo'] ?>" />
					<?php } ?>
					</div>
					<a href="<?php echo $group['link'] ?>"><?php echo $group['name'] ?></a><br/>
					<?php echo $group['members'] ?> members
					<div class="clb"/>
				</li><?php
			}
?>
</ul>
<?php
		}
	} else { ?>
		<p>You still didn't join any groups?!</p>
		<p><a href="http://www.meetup.com/find/">Find a group and join immediately!</a></p>
	<?php
	}
}
else
{
?>
<h1>Sample Meetup Application!</h1>

<?php
	$meetup_module = AuthenticationModule::get('meetup');
	?><p><?php $meetup_module->renderRegistrationForm(); ?></p><?php
}
?> 

</body>
</html>
