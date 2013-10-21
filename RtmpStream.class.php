<?php
class RtmpStream
{
	private $_index = 0;
	private $_data;
	
	public function __construct($data = "")
	{
		$this->_data = $data;
	}
	
	public function reset()
	{
		$this->_index = 0;
	}
	
	public function flush($length = -1)
	{
		if($length == -1)
		{
			$d = $this->_data;
			$this->_data = "";
		}
		else
		{
			$d = substr($this->_data,0,$length);
			$this->_data = substr($this->_data,$length);
		}
		$this->_index = 0;
		return $d;
	}
	public function dump()
	{
		return $this->_data;
	}
	public function begin()
	{
		$this->_index = 0;
		return $this;
	}
	public function move($pos)
	{
		$this->_index = max(array(0,min(array($pos,strlen($data)))));
		return $this;
	}
	public function end()
	{
		$this->_index = strlen($this->_data);
		return $this;
	}
	public function push($data)
	{
		$this->_data .= $data;
		return $this;
	}
	//--------------------------------
	//		Writer
	//--------------------------------
	
	public function writeByte($value)
	{
		$this->_data .= is_int($value)?chr($value):$value;
		$this->_index++;
	}
	
	public function writeInt16($value)
	{
		$this->_data .= pack("s",$value);
		$this->_index += 2;
	}
	public function writeInt24($value)
	{
		$this->_data .= substr(pack("N",$value),1);
		$this->_index += 3;
	}
	public function writeInt32($value)
	{
		$this->_data .= pack("N",$value);
		$this->_index += 4;
	}
	public function writeInt32LE($value)
	{
		$this->_data .= pack("V",$value);
		$this->_index += 4;
	}
	public function write($value)
	{
		$this->_data .= $value;
		$this->_index += strlen($value);
	}
	//-------------------------------
	//		Reader
	//-------------------------------
	
	public function readByte()
	{
		return ($this->_data[$this->_index++]);
	}
	public function readTinyInt()
	{
		return ord($this->readByte());
	}
	public function readInt16()
	{
		return $this->read("s",2);
	}
	public function readInt24()
	{
		$m = unpack("N","\x00".substr($this->_data,$this->_index,3));
		$this->_index += 3;
		return $m[1];
	}
	public function readInt32()
	{
		return $this->read("N",4);
	}
	public function readInt32LE()
	{
		return $this->read("V",4);
	}
	
	public function readRaw($length = 0)
	{
		if($length == 0)
			$length = strlen($this->_data) - $this->_index;
		$datas = substr($this->_data,$this->_index,$length);
		$this->_index += $length;
		return $datas;
	}
	private function read($type, $size)
	{
		$m = unpack("$type",substr($this->_data,$this->_index,$size));
		$this->_index += $size;
		return $m[1];
	}
	

}
