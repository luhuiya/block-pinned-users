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
 * Block pinned_users is defined here.
 *
 * @package     block_pinned_users
 * @copyright   2017 Sofia
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * pinned_users block.
 *
 * @package    block_pinned_users
 * @copyright  2017 Sofia
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_pinned_users extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_pinned_users');
    }
    
    private function get_users($ids)
    {
        global $DB, $OUTPUT, $PAGE;
        
        $usernames = [];
        if(empty($ids)) return [];

        list($uids, $params) = $DB->get_in_or_equal($ids);
        $rs = $DB->get_recordset_select('user', 'id ' . $uids, $params, '', 'id,firstname,lastname,email,picture,imagealt,lastnamephonetic,firstnamephonetic,middlename,alternatename');

        foreach ($rs as $record) 
        {
            $record->fullname = fullname($record);
            $record->identity = $record->email;
            $record->hasidentity = true;

            // Get the user picture data - messaging has always shown these to the user.
            $userpicture = new \user_picture($record);
            $userpicture->size = 0; // Size f2.
            $record->profileimageurlsmall = $userpicture->get_url($PAGE)->out(false);

            $usernames[$record->id] = $OUTPUT->render_from_template('tool_lp/form-user-selector-suggestion', $record);
        }
        $rs->close();

        return $usernames;
    }


    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {


        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else 
        {
            $userconfig = null;
            if(!empty($this->config->users))
            {
                $userconfig = $this->config->users;
            }
            $users = $this->get_users($userconfig);
            if(empty($users))
            {
                $this->content->text = get_string('empty', 'block_pinned_users');
            }
            else
            {
                $list = [];
                foreach ($users as $id => $username) 
                {
                    $link = html_writer::link(new moodle_url('/user/profile.php', array('id' => $id)), $username);
                    $list[] = html_writer::tag('li', $link);   
                }
                $this->content->text = html_writer::tag('ul', implode('', $list));
            }
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediatly after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_pinned_users');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Allow multiple instances in a single course?
     *
     * @return bool True if multiple instances are allowed, false otherwise.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'all' => true
        );
    }
}
