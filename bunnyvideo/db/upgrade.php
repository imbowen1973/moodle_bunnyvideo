<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for mod_bunnyvideo.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool success
 */
function xmldb_bunnyvideo_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // 2025060100: Add bunnyvideo_tracking table
    if ($oldversion < 2025060100) {
        $table = new xmldb_table('bunnyvideo_tracking');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('bunnyvideoid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('watchtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('maxwatched', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('percentcomplete', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('completed', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('bunnyvideo_user_unique', XMLDB_KEY_UNIQUE, ['bunnyvideoid', 'userid']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_mod_savepoint(true, 2025060100, 'bunnyvideo');
    }

    // 2025060401: Add completionvideo and completionpercent to bunnyvideo table
    if ($oldversion < 2025060401) {
        $table = new xmldb_table('bunnyvideo');

        // Add completionvideo field
        $field1 = new xmldb_field('completionvideo', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'posterurl');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        // Add completionpercent field
        $field2 = new xmldb_field('completionpercent', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '80', 'completionvideo');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        upgrade_mod_savepoint(true, 2025060401, 'bunnyvideo');
    }

    return true;
}
