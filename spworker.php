<?php

define('SLEEP', 3);
/*
 * Simple Pirate worker
 * Connects REQ socket to tcp://*:5556
 * Implements worker part of LRU queueing
 *
 * @author Ian Barber <ian(dot)barber(at)gmail(dot)com>
 */
include 'zmsg.php';

$worker = new Worker(
	sprintf ("%04X-%04X", rand(0, 0x10000), rand(0, 0x10000)),
	new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ) 
);
$worker->run();

class Worker {
	private $worker;
	private $identity;
	
	public function __construct($identity, $worker) {
		$this->identity = $identity;
		$this->worker = $worker;
	}
	
	private function init() {
		//  Set random identity to make tracing easier
		$this->worker->setSockOpt(ZMQ::SOCKOPT_IDENTITY, $this->identity);
		$this->worker->connect('tcp://localhost:5556');

		//  Tell queue we're ready for work
		printf ("I: (%s) worker ready%s", $this->identity, PHP_EOL);
		$this->worker->send("READY");
	}
	
	public function run() {
		$this->init();
		
		$cycles = 0;
		while (true) {
			$zmsg = new Zmsg($this->worker);
			$zmsg->recv();
			$cycles++;

			$requestMessage = $zmsg->body();
			
			printf ("I: (%s) Request (%s)%s", $this->identity, $requestMessage, PHP_EOL);

			$responseMessage = strtoupper($requestMessage);
			sleep(SLEEP); // Do some heavy work

			printf ("I: (%s) Response (%s)%s", $this->identity, $responseMessage, PHP_EOL);
			
			$zmsg->body_set($responseMessage);
			$zmsg->send();
		}
	}
}


