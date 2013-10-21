<?php
class RtmpOperation
{
	private $chunkStreamID;
	private $call;
	private $response;
	
	private $handler;
	
	public function __construct(RtmpMessage $call = null, $handler = null)
	{
		if($call)
		{
			$this->call = $call;
			$call->encode();
			$this->chunkStreamID = $call->getPacket()->chunkStreamId;
		}
		$this->handler = $handler;
	}
	
	public function getChunkStreamID()
	{
		return $this->chunkStreamID;
	}
	
	/**
	 * getCall
	 *
	 * @return RtmpMessage
	 */
	public function getCall()
	{
		return $this->call;
	}
	
	/**
	 * getResponse
	 *
	 * @return RtmpMessage
	 */
	public function getResponse()
	{
		return $this->response;
	}
	public function clearResponse()
	{
		$this->response = null;
	}
	/**
	 * CReate response from packet
	 *
	 * @param RtmpPacket $packet
	 */
	public function createResponse(RtmpPacket $packet)
	{
		$this->response = new RtmpMessage();
		$this->response->setPacket($packet);
	}
	
	public function invokeHandler()
	{
		is_callable($this->handler) && call_user_func($this->handler,$this);
	}
}
