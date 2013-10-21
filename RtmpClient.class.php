<?php

require "RtmpPacket.class.php";
require "RtmpStream.class.php";
require "RtmpMessage.class.php";
require "RtmpOperation.class.php";
require "RtmpSocket.class.php";

class RTMPClient
{
	
	const RTMP_SIG_SIZE = 1536;
	
	/**
	 * Socket object
	 *
	 * @var RtmpSocket
	 */
	private $socket;
	
	private $host;
	private $application;
	private $port;
	
	private $chunkSizeR = 128, $chunkSizeW = 128;
	
	private $operations = array();
	
	private $connected = false;

	private $client;
	
	public function setClient($client)
	{
		if(is_object($client) || is_null($client))
			$this->client = $client;
	}
	
	/**
	 * Connect
	 *
	 * @param string $host
	 * @param string $application
	 * @param int $port
	 */
	public function connect($host,$application,$port = 1935, $connectParams=null)
	{
		$this->close();
		
		$this->host = $host;
		$this->application = $application;
		$this->port = $port;
		
		
		
		if($this->initSocket())
		{
			$aReadSockets = array($this->socket);
			$this->handshake();
			$this->send_ConnectPacket($connectParams);
		}
	}
	/**
	 * Close connection
	 *
	 */
	public function close()
	{
		$this->socket && $this->socket->close();
		$this->chunkSizeR = $this->chunkSizeW = 128;
	}
	/**
	 * Call remote procedure (RPC)
	 *
	 * @param string $procedureName
	 * @param array $args array of arguments, null if not args
	 * @param callback $handler
	 * 
	 * @return mixed result of RPC
	 */
	public function call($procedureName,array $args = null,$handler = null)
	{
		return $this->sendOperation(new RtmpOperation(new RtmpMessage($procedureName,null,$args), $handler));
	}
	
	public function __call($name, $arguments)
	{
		return $this->call($name, $arguments);
	}
	
	
	//------------------------------------
	//		Socket
	//------------------------------------
	private function initSocket()
	{
		$this->socket = new RtmpSocket();
		return $this->socket->connect($this->host, $this->port);
	}
	private function socketRead($length)
	{
		return $this->socket->read($length);
	}
	private function socketWrite(RtmpStream $data, $n = -1)
	{
		return $this->socket->write($data, $n);
	}
	
	//-------------------------------------
	
	private $listening = false;
	/**
	 * listen socket
	 *
	 * @return mixed last result
	 */
	private function listen()
	{
		if($this->listening)
			return;
		if(!$this->socket)
			return;
		$this->listening = true;
		$stop = false;
		$return = null;
		while (!$stop)
		{
			if($p = $this->readPacket())
			{
				switch($p->type)
				{
					case 0x01; //Chunk size
						$this->handle_setChunkSize($p);
						break;
					case 0x03: //Bytes Read
						
						break;
					case 0x04: //Ping
						unset($this->operations[$p->chunkStreamId]);
						break;
					case 0x05: //Window Acknowledgement Size
						$this->handle_windowAcknowledgementSize($p);
						break;
					case 0x06: //Peer BW
						$this->handle_setPeerBandwidth($p);
						break;
					case 0x08: //Audio Data
						
						break;
					case 0x09: //Video Data
						
						break;
					case 0x12: //Notify
						
						break;
					
					case RtmpPacket::TYPE_INVOKE_AMF0:
					case RtmpPacket::TYPE_INVOKE_AMF3: //Invoke
						$return = $this->handle_invoke($p);
						if(sizeof($this->operations) == 0)
							$stop = true;
						break;
					case 0x16:
						
						break;
					case 0x36: //agregate
						
						break;
					default:
						
						break;
				}
			}
			usleep(1);
		}
		$this->listening = false;
		return $return;
	}
	/**
	 * Previous packet
	 * @internal 
	 *
	 * @var RtmpPacket
	 */
	private $prevReadingPacket = array();
	/**
	 * Read packet
	 *
	 * @return RtmpPacket
	 */
	private function readPacket()
	{
		$p = new RtmpPacket();
		
		$header = $this->socketRead(1)->readTinyInt();
		
		$p->chunkType = (($header & 0xc0) >> 6);
		$p->chunkStreamId = $header & 0x3f;
		
		switch($p->chunkStreamId)
		{
			case 0: //range of 64-319, second byte + 64
				$p->chunkStreamId = 64 + $this->socketRead(1)->readTinyInt();
				break;
			case 1: //range of 64-65599,thrid byte * 256 + second byte + 64
				$p->chunkStreamId = 64 + $this->socketRead(1)->readTinyInt() + $this->socketRead(1)->readTinyInt()*256;
				break;
			case 2:
				break;
			default: //range of 3-63
				// complete stream ids
		}
		switch($p->chunkType)
		{
			case RtmpPacket::CHUNK_TYPE_3:
				$p->timestamp = $this->prevReadingPacket[$p->chunkStreamId]->timestamp;
			case RtmpPacket::CHUNK_TYPE_2:
				$p->length = $this->prevReadingPacket[$p->chunkStreamId]->length;
				$p->type = $this->prevReadingPacket[$p->chunkStreamId]->type;
			case RtmpPacket::CHUNK_TYPE_1:
				$p->streamId = $this->prevReadingPacket[$p->chunkStreamId]->streamId;
			case RtmpPacket::CHUNK_TYPE_0:
				break;
		}
		$this->prevReadingPacket[$p->chunkStreamId] = $p;
		$headerSize = RtmpPacket::$SIZES[$p->chunkType];
		 
		if($headerSize == RtmpPacket::MAX_HEADER_SIZE)
			$p->hasAbsTimestamp = true;
		
		//If not operation exists, create it
		if(!isset($this->operations[$p->chunkStreamId]))
			$this->operations[$p->chunkStreamId] = new RtmpOperation();
		
		if($this->operations[$p->chunkStreamId]->getResponse())
		{	
			//Operation chunking....
			$p = $this->operations[$p->chunkStreamId]->getResponse()->getPacket();
			$headerSize = 0; //no header
		}
		else
		{
			//Create response from packet
			$this->operations[$p->chunkStreamId]->createResponse($p);
		}
		
		
		$headerSize--;
		$header;
		if($headerSize>0)
			$header = $this->socketRead($headerSize);

		if($headerSize >= 3)
			$p->timestamp = $header->readInt24();
		if($headerSize >= 6)
		{
			$p->length = $header->readInt24();
			
			$p->bytesRead = 0;
			$p->free();
		}
		if($headerSize > 6)
			$p->type = $header->readTinyInt();
			
		if($headerSize == 11)
			$p->streamId = $header->readInt32LE();
		

		$nToRead = $p->length - $p->bytesRead;
		$nChunk = $this->chunkSizeR;
		if($nToRead < $nChunk)
			$nChunk = $nToRead;

		if($p->payload == null)
			$p->payload = "";
		$p->payload .= $this->socketRead($nChunk)->flush();
		if($p->bytesRead + $nChunk != strlen($p->payload))
			throw new Exception("Read failed, have read ".strlen($p->payload)." of ".($p->bytesRead + $nChunk));
		$p->bytesRead += $nChunk;
		
		if($p->isReady())
			return $p;
		
		return null;

		
	}
	
	/**
	 * Previous packet
	 * @internal 
	 *
	 * @var RtmpPacket
	 */
	private $prevSendingPacket = array();
	/**
	 * Send packet
	 *
	 * @param RtmpPacket $packet
	 * @return bool
	 */
	private function sendPacket(RtmpPacket $packet)
	{
		
		if(!$packet->length)
			$packet->length = strlen($packet->payload);
		if(isset($this->prevSendingPacket[$packet->chunkStreamId]))
		{
			if($packet->length == $this->prevSendingPacket[$packet->chunkStreamId]->length)
				$packet->chunkType = RtmpPacket::CHUNK_TYPE_2;
			else
				$packet->chunkType = RtmpPacket::CHUNK_TYPE_1;
		}
		if($packet->chunkType > 3) //sanity
			throw new Exception("sanity failed!! tring to send header of type: 0x%02x");
		
		$this->prevSendingPacket[$packet->chunkStreamId] = $packet;
		
		$headerSize = RtmpPacket::$SIZES[$packet->chunkType];
		//Initialize header
		$header = new RtmpStream();
		$header->writeByte($packet->chunkType << 6 | $packet->chunkStreamId);
		if($headerSize > 1)
		{
			$packet->timestamp = time();
			$header->writeInt24($packet->timestamp);
		}	
		if($headerSize > 4)
		{
			$header->writeInt24($packet->length);
			$header->writeByte($packet->type);
		}
		if($headerSize > 8)
			$header->writeInt32LE($packet->streamId);

		// Send header
		$this->socketWrite($header);
		
		$headerSize = $packet->length;
		$buffer = new RtmpStream($packet->payload);
		
		
		while($headerSize)
		{
			$chunkSize = $packet->type == RtmpPacket::TYPE_INVOKE_AMF0 || $packet->type == RtmpPacket::TYPE_INVOKE_AMF3 ? $this->chunkSizeW : $packet->length;
			if($headerSize < $this->chunkSizeW)
				$chunkSize = $headerSize;
			
			if(!$this->socketWrite($buffer, $chunkSize))
				throw new Exception("Socket write error (write : $chunkSize)");
			
			$headerSize -= $chunkSize;
			//$buffer = substr($buffer,$chunkSize);
			
			if($headerSize > 0)
			{
				$sep = (0xc0 | $packet->chunkStreamId);
				if(!$this->socketWrite(new RtmpStream(chr($sep)),1))
					return false;
			}
			
		}
		return true;
		
	}
	
	protected function sendOperation(RtmpOperation $op)
	{
		$this->operations[$op->getChunkStreamID()] = $op;
		$this->sendPacket($op->getCall()->getPacket());
		return $this->listen();
	}
	
	
	//------------------------------------
	//		RTMP Methods
	//------------------------------------
	/**
	 * Perform handshake
	 *
	 */
	private function handshake()
	{
		///	Send C0, the version
		$stream = new RtmpStream();
		
		$stream->writeByte("\x03"); //"\x03";
		$this->socketWrite($stream);
		
		///	Send C1
		$ctime = time();
		$stream->writeInt32($ctime); //Time
		$stream->write("\x80\x00\x03\x02");	//Zero zone? Flex put : 0x80 0x00 0x03 0x02, maybe new handshake style?
		
		$crandom = "";
		for($i=0; $i<self::RTMP_SIG_SIZE - 8; $i++)
			$crandom .= chr(rand(0,256)); //TODO: better method to randomize
		
		$stream->write($crandom);
		$this->socketWrite($stream);
		
		///Read S0
		$s0 = $this->socketRead(1)->readTinyInt();
		if($s0 != 0x03)
			throw new Exception("Packet version ".$s0." not supported");
		///Read S1
		$s1 = $this->socketRead(self::RTMP_SIG_SIZE);
		
		///Send C2
		$c2 = new RtmpStream();
		$c2->writeInt32($s1->readInt32());
		$s1->readInt32();
		$c2->writeInt32($ctime);
		$raw = $s1->readRaw();
		$c2->write($raw);
		$this->socketWrite($c2);
		
		///Read S2
		$resp = $this->socketRead(self::RTMP_SIG_SIZE);
		
		//TODO check integrity
		
		return true;
		
	}

	private function send_ConnectPacket($connectParams=null)
	{
		$this->sendOperation(
			new RtmpOperation(new RtmpMessage("connect",(object)array(
					"app" => $this->application,
					"flashVer" => "LNX 10,0,32,18",
					"swfUrl" => null,
					"tcUrl" => "rtmp://$this->host:$this->port/$this->application",
					"fpad" => false,
					"capabilities" => 0.0,
					"audioCodecs" => 0x01,
					"videoCodecs" => 0xFF,
					"videoFunction" => 0,
					"pageUrl" => null,
					"objectEncoding" => 0x03
				), $connectParams), array($this,"onConnect"))
			);
	}
	private function send_SetChunkSize()
	{
		
	}
	private function send_AbortMessage()
	{
		
	}
	private function send_Acknowledgement()
	{
		
	}
	private function send_UserControlMessage()
	{
		
	}
	private function send_WindowAcknowledgementSize()
	{
		
	}
	private function send_SetPeerBandwidth()
	{
		
	}
	private function handle_windowAcknowledgementSize(RtmpPacket $p)
	{
		$s = new RtmpStream($p->payload);
		$size = $s->readInt32();
		//TODO
		unset($this->operations[$p->chunkStreamId]);
	}
	private function handle_setPeerBandwidth(RtmpPacket $p)
	{
		$s = new RtmpStream($p->payload);
		$size = $s->readInt32();
		$limitType = $s->readTinyInt();
		//TODO
		unset($this->operations[$p->chunkStreamId]);
	}
	private function handle_setChunkSize(RtmpPacket $p)
	{
		$s = new RtmpStream($p->payload);
		$this->chunkSizeR = $s->readInt32();
		unset($this->operations[$p->chunkStreamId]);
	}
	
	private function handle_invoke(RtmpPacket $p)
	{
		$op = $this->operations[$p->chunkStreamId];
		$op->getResponse()->decode($p);
		
		if($op->getCall() && $op->getResponse()->isResponseCommand() )
		{
			//Result
			unset($this->operations[$p->chunkStreamId]);
			$op->invokeHandler();
			$data = $op->getResponse()->arguments instanceof SabreAMF_AMF3_Wrapper ? $op->getResponse()->arguments->getData() : $op->getResponse()->arguments;
			if($op->getResponse()->isError())
			{
				$data = (object)$data;
				throw new Exception($data->description . (isset($data->application)&&!empty($data->application)?" (Application specific message: {$data->application})":''));
			}
			return $data;
		}
		else
		{
			//Remote invoke from server
			$h = $op->getResponse()->commandName;
			if($this->client)
				$h = array($this->client,$h);
			is_callable($h) && call_user_func_array($h,$op->getResponse()->arguments);
			$op->clearResponse();
			return;
		}
		
		
	}
	
	
	//------------------------------------
	//	Internal handlers
	//------------------------------------
	/**
	 * On connect handler
	 * @internal 
	 * @param RtmpMessage $m
	 * 
	 */
	public function onConnect(RtmpOperation $m)
	{
		$this->connected = true;
		unset($this->prevSendingPacket[$m->getResponse()->getPacket()->chunkStreamId]);
		
	}
}
