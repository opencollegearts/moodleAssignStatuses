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
 * Assignment statuses block — lists course assignments with submission and grading status.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block class for assignment statuses.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_assignstatuses extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_assignstatuses');
    }

    /**
     * This block is only useful on course pages.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['course' => true];
    }

    /**
     * Allow multiple instances of this block.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Build block content.
     *
     * @return stdClass
     */
    public function get_content() {
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $course = $this->page->course;
        if (empty($course) || empty($course->id) || $course->id == SITEID) {
            return $this->content;
        }

        $assignments = \block_assignstatuses\local\helper::get_assignments_for_user($course, $USER->id);
        $renderable = new \block_assignstatuses\output\assignments_list($assignments);
        $renderer = $this->page->get_renderer('block_assignstatuses');
        $this->content->text = $renderer->render($renderable);

        return $this->content;
    }
}
