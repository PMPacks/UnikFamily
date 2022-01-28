<?php

declare(strict_types=1);

namespace CLADevs\Minion\database;

interface DatabaseStmts{

	public const INIT_PLAYER_VAULTS = "playervaults.init";
	public const LOAD_VAULT = "playervaults.load";
	public const SAVE_VAULT = "playervaults.save";
}