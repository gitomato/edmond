<?php

define("REQUEST_TIMEOUT", 3500); //  msecs, (> 1000!)
define("REQUEST_RETRIES", 3); //  Before we abandon

$request = isset($argv[1]) ? $argv[1] : 'Hello World';

$context = new ZMQContext();
echo "I: connecting to server...", PHP_EOL;
$client = new ZMQSocket($context,ZMQ::SOCKET_REQ);
$client->connect("tcp://localhost:5555");
//  Configure socket to not wait at close time
$client->setSockOpt(ZMQ::SOCKOPT_LINGER, 0);


//  We send a request, then we work to get a reply
printf ("I: Request (%s)%s", $request, PHP_EOL);
$client->send($request);

//  Poll socket for a reply, with timeout
$poll = new ZMQPoll();
$poll->add($client, ZMQ::POLL_IN);
$read = $write = [];
$events = $poll->poll($read, $write, REQUEST_TIMEOUT);

//  If we got a reply, process it
if ($events > 0) {
	//  We got a reply from the server
	$response = $client->recv();
	printf ("I: Response (%s)%s", $response, PHP_EOL);
	
} else {
	echo "E: server seems to be offline, abandoning", PHP_EOL;
}
