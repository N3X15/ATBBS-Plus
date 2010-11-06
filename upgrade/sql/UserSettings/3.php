<?php
// Add usrThemes, set default to 'atbbs'.
DB::Execute("ALTER TABLE {P}UserSettings ADD `usrTheme` VARCHAR(10) NOT NULL DEFAULT 'atbbs'");
DB::SetTableRevision('UserSettings',3);
