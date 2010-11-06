<?php
// Add "flags" field and "name" field.
DB::Execute("ALTER TABLE `{P}Replies` ADD `flags` INT( 11 ) NOT NULL DEFAULT '0'");
DB::Execute("ALTER TABLE `{P}Replies` ADD `name` varchar( 25 ) NOT NULL DEFAULT ''");

// Set flags field
DB::Execute("UPDATE `{P}Replies` SET flags = (0|(edit_mod*1))");

// Remove edit_mod
DB::Execute("ALTER TABLE `{P}Replies` DROP `edit_mod`");

DB::SetTableRevision("Replies",2);
