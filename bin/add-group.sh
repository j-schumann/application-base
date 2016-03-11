#!/usr/bin/env php
<?php
/**
 * Creates a new user group.
 * First argument is the new groups name, second (optional) argument is the
 * parent groups name.
 *
 * We do not use a console route for this as BjyAuthorize / the ACL will
 * fail when not all required roles for all rules exist.
 */

require_once 'initApplication.php';

if (empty($argv[1])) {
    die("group name must be given as first argument!\n");
}

$um = $application->getServiceManager()->get(Vrok\Service\UserManager::class);
$repo = $um->getGroupRepository();

$name = $argv[1];
$pGroup = null;

if (!empty($argv[2])) {
    $parent = $argv[2];
    echo "trying to use $parent as parent group\n";

    $pGroup = $repo->findOneBy(['name' => $parent]);
    if (!$pGroup) {
        die("parent group does not exist!\n");
    }
}


if ($repo->findOneBy(['name' => $name])) {
    die("group already exists!\n");
}


$group = $um->createGroup(['name' => $name]);
if ($pGroup) {
    $group->setParent($pGroup);
    $um->getEntityManager()->flush();
}

echo "created group $name!\n";