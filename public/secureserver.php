<?php

namespace MyEnvoySecure;

include '../core/Index.class.php';
include '../core/Config.class.php';
include '../core/MyEnvoySecure.class.php';
include '../core/Firewall.class.php';

if (!isset($_GET['callback']) || !isset($_GET['state'])) {
    exit();
}

$state = filter_input(INPUT_GET, 'state');
$callback = filter_input(INPUT_GET, 'callback');

$result = '';

switch ($state) {
    case 'new':
        $hash = filter_input(INPUT_POST, 'hash');

        if ($hash != '' && Firewall::canCreateChat()) {
            do {
                $channel = hash('sha512', uniqid('Channel', TRUE));
                $success = MyEnvoySecure::newChannel($channel, $hash);
            } while (!$success);

            Firewall::recordNewChat();

            $result = json_encode(array('status' => \TRUE, 'channel' => $channel));
        } else {
            $result = json_encode(array('status' => \FALSE));
        }
        break;

    case 'auth':
        $channel = filter_input(INPUT_POST, 'channel');
        $hash = filter_input(INPUT_POST, 'hash');

        $result = json_encode(array('status' => MyEnvoySecure::auth($channel, $hash)));
        break;

    case 'get':
        $channel = filter_input(INPUT_POST, 'channel');
        $hash = filter_input(INPUT_POST, 'hash');
        $lastID = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        $result = json_encode(MyEnvoySecure::get($channel, $hash, $lastID));
        break;

    case 'send':
        $channel = filter_input(INPUT_POST, 'channel');
        $hash = filter_input(INPUT_POST, 'hash');
        $data = filter_input(INPUT_POST, 'data');

        $result = json_encode(array('status' => MyEnvoySecure::save($channel, $hash, $data)));
        break;
}

printf('%s(%s)', $callback, $result);
