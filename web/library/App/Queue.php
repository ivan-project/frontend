<?php

//require_once __DIR__.'/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class App_Queue 
{
	private $exchange = 'ivan';
	private $queue = 'tasks';
	private $connection;
	private $channel;

	public function __construct() {
		$this->connection = new AMQPConnection('localhost', 5672, 'guest', 'guest', '/');
		$this->channel = $this->connection->channel();

		$this->channel->exchange_declare($this->exchange, 'direct', false, true, false);
		$this->channel->queue_bind($this->queue, $this->exchange);
	}
	public function queueFile($id) {
		$msg = [
		    "type" => "plaintext",
		    "documentId" => ''.$id,
		    "payload" => []
		];

		$toSend = new AMQPMessage(json_encode($msg), array('message_id'=>sha1(mt_rand())));
		return $this->channel->basic_publish($toSend, $this->exchange);
	}
	public function __destruct() {
		$this->channel->close();
		$this->connection->close();
	}
}

?>