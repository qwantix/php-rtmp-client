<?php
class RtmpPacket
{
	const TYPE_CHUNK_SIZE = 0x01;
	const TYPE_READ_REPORT = 0x03;
	const TYPE_PING = 0x04;
	const TYPE_SERVER_BW = 0x05;
	const TYPE_CLIENT_BW = 0x06;
	const TYPE_AUDIO = 0x08;
	const TYPE_VIDEO = 0x09;
	const TYPE_METADATA = 0x12;
	const TYPE_INVOKE_AMF0 = 0x14;
	const TYPE_INVOKE_AMF3 = 0x11;
	const TYPE_FLV_TAGS = 0x16;
	
	
	const MAX_HEADER_SIZE = 12;
	
	const CHUNK_TYPE_0 = 0; //Large type
	const CHUNK_TYPE_1 = 1; //Medium 
	const CHUNK_TYPE_2 = 2;	//Small
	const CHUNK_TYPE_3 = 3; //Minimal
	
	
	
	public static $SIZES = array(12, 8, 4, 1);
	
	public $chunkType = 0;
	public $chunkStreamId = 0;
	public $timestamp = 0;
	public $length = 0;
	public $type = 0;
	public $streamId = 0;
	public $hasAbsTimestamp = false;
	public $bytesRead = 0;
	public $payload = null;
	
	public function reset()
	{
		$this->chunkType = 0;
		$this->chunkStreamId = 0;
		$this->timestamp = 0;
		$this->length = 0;
		$this->type = 0;
		$this->streamId = 0;
		$this->hasAbsTimestamp = false;
		$this->bytesRead = 0;
		$this->payload = null;
	}

	public function free()
	{
		$this->payload = null;
	}
	
	public function isReady()
	{
		return $this->bytesRead == $this->length;
	}
}

