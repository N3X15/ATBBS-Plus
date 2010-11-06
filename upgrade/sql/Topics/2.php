<?php
// Add "flags" field and "name" field.
DB::Execute("ALTER TABLE `{P}Topics` ADD `flags` INT( 11 ) NOT NULL DEFAULT '0'");
DB::Execute("ALTER TABLE `{P}Topics` ADD `name` VARCHAR(25) NOT NULL DEFAULT ''");

// Set flags field
DB::Execute("UPDATE `{P}Topics` SET flags = (0|(edit_mod*1))");

// Remove edit_mod
DB::Execute("ALTER TABLE `{P}Topics` DROP `edit_mod`");

DB::SetTableRevision("Topics",2);
