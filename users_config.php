<?php
/* including local app config */
require_once(dirname(__FILE__).'/config.php');

/**
 * You must fill it in with some random string
 * this protects some of your user's data when sent over the network
 * and must be different from other sites
 */
UserConfig::$SESSION_SECRET = $randomness;

/**
 * Database connectivity 
 */
UserConfig::$mysql_db = $mysql_db;
UserConfig::$mysql_user = $mysql_user;
UserConfig::$mysql_password = $mysql_password;
UserConfig::$mysql_host = isset($mysql_host) ? $mysql_host : 'localhost';
UserConfig::$mysql_port = isset($mysql_port) ? $mysql_port : 3306;
UserConfig::$mysql_socket = isset($mysql_port) ? $mysql_socket : null;

/*
 * Name of your application to be used in UI and emails to users
 */
UserConfig::$appName = 'Sample Meetup Application';

/**
 * User IDs of admins for this instance (to be able to access dashboard at /users/admin/)
 */
UserConfig::$admins[] = 1; // usually first user has ID of 1

/*
 * Uncomment next line to enable debug messages in error_log
 */
#UserConfig::$DEBUG = true;

/**
 * Email configuration
 */
UserConfig::$supportEmailFromName = 'Sample App Support';
UserConfig::$supportEmailFromEmail = 'support@startupapi.com';
UserConfig::$supportEmailReplyTo = 'support@startupapi.com';

if ($amazonSMTPHost && $amazonSMTPUserName && $amazonSMTPPassword) {
  UserConfig::$mailer = Swift_Mailer::newInstance(
    Swift_SmtpTransport::newInstance($amazonSMTPHost, 587, 'tls')
      ->setUsername($amazonSMTPUserName)
      ->setPassword($amazonSMTPPassword)
  );
}

/**
 * Meetup Authentication configuration
 * Register your app here: http://www.meetup.com/meetup_api/oauth_consumers/
 * Click red "Register OAuth Consumer" button on the right and enter your site's name and URL
 * And then uncomment two lines below and copy API Key and App Secret
 */
UserConfig::loadModule('meetup');
new MeetupAuthenticationModule($meetup_OAuth_consumer_key, $meetup_OAuth_consumer_secret);

/**
 * Set these to point at your header and footer or leave them commented out to use default ones
 */
#UserConfig::$header = dirname(__FILE__).'/header.php';
#UserConfig::$footer = dirname(__FILE__).'/footer.php';

/**
 * Username and password registration configuration
 * just have these lines or comment them out if you don't want regular form registration
 */
#UserConfig::loadModule('usernamepass');
#new UsernamePasswordAuthenticationModule();
