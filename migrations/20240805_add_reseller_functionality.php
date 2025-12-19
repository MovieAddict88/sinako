<?php

$sql = "ALTER TABLE `users`
        ADD COLUMN `reseller_id` INT(11) DEFAULT NULL,
        MODIFY COLUMN `role` ENUM('admin','user','reseller') NOT NULL DEFAULT 'user';";
