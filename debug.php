<?php
///------------------------------
//	Debug
///------------------------------
function dumpHex($value)
{
	
	print "\n === DUMP Hex === \n";
	for($i = 0; $i<strlen($value); $i++)
	{
		printf("%02X ",ord($value{$i}));
		if(($i+1) % 16 == 0)
			print "\n";
	}
	print "\n===\n";
}
function dumpBin($value)
{
	print "\n === DUMP Bin === \n";
	for($i = 0; $i<strlen($value); $i++)
	{
		printf("%08b ",ord($value{$i}));
		if(($i+1) % 16 == 0)
			print "\n";
	}
	print "\n===\n";
}
