<?php

declare(strict_types=1);

namespace CLADevs\Minion\database\utils;

interface BinaryStringParserInstance{

	public function encode(string $string) : string;

	public function decode(string $string) : string;
}