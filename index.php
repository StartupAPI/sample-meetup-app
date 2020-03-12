<?php
require_once(dirname(__FILE__).'/users/users.php');

$user = StartupAPI::requireLogin();

$fetched_groups_organizer = array();
$fetched_groups_member = array();

// You can work with users, but it's recommended to tie your data to accounts, not users
$current_account = Account::getCurrentAccount($user);

$creds = $user->getUserCredentials('meetup');
$meetup_info = $creds->getUserInfo();
$meetup_id = $meetup_info['id'];

$page = 0; // requesting first page
$keep_going = true;
$max_pages = 20;

while($keep_going && $page <= $max_pages) {
	$result = $creds->makeOAuth2Request(
		'https://api.meetup.com/2/groups?order=name&member_id=self',
		'GET'
	);

	$group_data = json_decode(utf8_encode($result), true);

	foreach ($group_data['results'] as $group) {
		$group_info = array(
			'name' => $group['name'],
			'link' => $group['link'],
			'members' => $group['members']
		);

		if (array_key_exists('group_photo', $group)) {
			$group_info['logo'] = $group['group_photo']['thumb_link'];
		}

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
}

// start with global template data needed for Startup API menus and stuff
$template_info = StartupAPI::getTemplateInfo();

$template_info['name'] = $user->getName();

usort($fetched_groups_organizer, function($a, $b) {
	return $a['members'] > $b['members'] ? -1 : 1;
});

usort($fetched_groups_member, function($a, $b) {
	return $a['members'] > $b['members'] ? -1 : 1;
});

$template_info['fetched_groups_organizer'] = $fetched_groups_organizer;
$template_info['fetched_groups_member'] = $fetched_groups_member;

StartupAPI::$template->getLoader()->addPath(__DIR__ . '/templates', 'app');
StartupAPI::$template->display('@app/index.html.twig', $template_info);
