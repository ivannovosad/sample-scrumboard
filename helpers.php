<?php

function avatar_url($user_id, $size = 'tiny') {
  return 'https://files.podio.com/'.$user_id.'/'.$size;
}

function current_user_id() {
	global $api;
	$currentUser = $api->user->get();
	return $currentUser['user_id'];
}

function dev_started($dev_started) {
	return date("d.m.Y H:i", strtotime($dev_started));
}
