<?php

class RtmpSocket
{
	
	
	private $host;
	private $port;
	private $socket;
	
	public $timeout = 15;
	
	public function __construct()
	{
		
	}
	
	/**
	 * Init socket
	 *
	 * @return bool
	 */
	public function connect($host, $port)
	{
		$this->close();
		$this->host = $host;
		$this->port = $port;
		if (($this->socket = socket_create(AF_INET, SOCK_STREAM, 0)) == false)
			throw new Exception("Unable to create socket.");
	    if (!socket_connect($this->socket, $this->host, $this->port))
			throw new Exception("Could not connect to $this->host:$this->port");
	    return $this->socket != null;
	}
	/**
	 * Close socket
	 *
	 */
	public function close()
	{
		$this->socket && socket_close($this->socket);
	}
	/**
	 * Read socket
	 *
	 * @param int $length
	 * @return RtmpStream
	 */
	public function read($length)
	{
		$buff = "";
		$t = time();
		do
		{ 
			$recv = "";
			$recv = socket_read($this->socket, $length - strlen($buff), PHP_BINARY_READ); 
			if($recv === false)
				throw new Exception("Could not read socket");
			
			if($recv != "")
				$buff .= $recv;
			
			if(time() > $t + $this->timeout)
				throw new Exception("Timeout, could not read socket");
		}
		while($recv != "" && strlen($buff) < $length);
		$this->recvBuffer = substr($buff,$length);
		return new RtmpStream(substr($buff,0,$length));
	}
	/**
	 * Write data 
	 *
	 * @param RtmpStream $data
	 * @param int $n
	 * @return bool
	 */
	public function write(RtmpStream $data, $n = -1)
	{
		$buffer = $data->flush($n);
		$n = strlen($buffer);
		while($n>0)
		{
			$nBytes = socket_write($this->socket,$buffer,$n);
			if($nBytes === false)
			{
				$this->close();
				return false;
			}
			
			if($nBytes == 0)
				break;
			
			$n -= $nBytes;
			$buffer = substr($buffer, $nBytes);
		}
		return true;
	}
}
