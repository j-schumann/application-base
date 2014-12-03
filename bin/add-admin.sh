#!/usr/bin/env php
<?php
/**
 * Creates a new user and adds him to the admin and userAdmin groups.
 *
 * Sends a mail to the given address with the random password, make sure the
 * necessary translations are imported so the password is included in the mail!
 *
 * We do not use a console route for this as BjyAuthorize / the ACL will
 * eventually fail when not all required roles exist.
 */

require_once 'initApplication.php';

if (empty($argv[1])) {
    die("user email must be given as first argument!\n");
}

$email = $argv[1];

$um = $application->getServiceManager()->get('UserManager');
if ($um->getUserByIdentity($email)) {
    die("User with that email already exists!\n");
}

$adminGroup = $um->getGroupRepository()->findOneBy(['name' => 'admin']);
if (!$adminGroup) {
    die("'admin' group does not exist!\n");
}
$userAdminGroup = $um->getGroupRepository()->findOneBy(['name' => 'userAdmin']);
if (!$userAdminGroup) {
    die("'userAdmin' group does not exist!\n");
}

$user = $um->createUser(['email' => $email]);
$user->setIsValidated(true);
$user->setIsActive(true);

$adminGroup->addMember($user);
$userAdminGroup->addMember($user);
$um->sendRandomPassword($user);

echo "created user $email!\n";
