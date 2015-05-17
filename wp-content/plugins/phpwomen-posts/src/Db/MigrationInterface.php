<?php

namespace PHPWomen\Posts\Db;

use \WPDB;

interface MigrationInterface
{
    public static function update(WPDB $wpdb);
    public static function rollback(WPDB $wpdb);
}