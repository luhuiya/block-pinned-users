<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Form for editing pinned_users block instances.
 *
 * @package     block_pinned_users
 * @copyright   2017 Sofia
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing block_pinned_users block instances.
 *
 * @package    block_pinned_users
 * @copyright  2017 Sofia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_pinned_users_edit_form extends block_edit_form {


    private function get_users()
    {
        global $DB, $OUTPUT, $PAGE;
        
        $usernames = [];

        if(empty($this->block->config->users)) return [];
        $ids = $this->block->config->users;

        list($uids, $params) = $DB->get_in_or_equal($ids);
        $rs = $DB->get_recordset_select('user', 'id ' . $uids, $params, '', 'id,firstname,lastname,email');

        foreach ($rs as $record) 
        {
            $usernames[$record->id] = fullname($record) . ' ' . $record->email;
        }
        $rs->close();

        return $usernames;
    }

    /**
     * Extends the configuration form for block_pinned_users.
     */
    protected function specific_definition($mform) 
    {

        // Section header title.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Please keep in mind that all elements defined here must start with 'config_'.
        $mform->addElement('text', 'config_title', get_string('pinned_users_title', 'block_pinned_users'));
        $mform->setType('config_title', PARAM_TEXT);

        $usernames = $this->get_users();
        $mform->addElement('autocomplete', 'config_users', get_string('pinned_users_users', 'block_pinned_users'), $usernames, [
        	'multiple' => true,
            'ajax' => 'tool_lp/form-user-selector',  
        ]);
        $mform->addRule('config_users', null, 'required');

    }
}
