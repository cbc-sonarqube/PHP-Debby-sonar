<?php

namespace alsvanzelf\debby\channel;

interface channel {

/**
 * setup a connection with the channel
 * 
 * @param array $options from general options.json `notify_[channel_name]`
 *                       contents variate per channel type
 */
public function __construct(array $options=[]);

/**
 * send the updatable packages to the channel
 * 
 * @param  array<package> $packages as returned by debby->check()
 * @return void
 */
public function send(array $packages);

}
