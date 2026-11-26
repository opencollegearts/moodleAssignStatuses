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
 * Renderer for block_assignstatuses.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_assignstatuses\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

/**
 * Block renderer.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the assignments list.
     *
     * @param assignments_list $list Renderable.
     * @return string HTML
     */
    public function render_assignments_list(assignments_list $list): string {
        return $this->render_from_template(
            'block_assignstatuses/assignments_list',
            $list->export_for_template($this)
        );
    }
}
