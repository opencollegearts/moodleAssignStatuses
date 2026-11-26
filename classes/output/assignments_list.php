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
 * Renderable for the assignment statuses list.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_assignstatuses\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Assignment list renderable.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assignments_list implements renderable, templatable {

    /** @var array Assignment row data. */
    protected $assignments;

    /**
     * Constructor.
     *
     * @param array $assignments Assignment rows from the helper.
     */
    public function __construct(array $assignments) {
        $this->assignments = $assignments;
    }

    /**
     * Export data for the Mustache template.
     *
     * @param renderer_base $output Renderer.
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $rows = [];
        foreach ($this->assignments as $assignment) {
            $submission = $assignment['submission'];
            $grading = $assignment['grading'];

            $row = [
                'name' => $assignment['name'],
                'url' => $assignment['url'],
                'submissionicon' => $submission['icon'],
                'submissioncomponent' => $submission['component'],
                'submissionlabel' => $submission['label'],
                'submissionclass' => $submission['extraclass'],
                'haslate' => !empty($submission['late']),
                'latelabel' => $submission['latelabel'] ?? '',
                'lateicon' => $submission['lateicon'] ?? '',
                'latecomponent' => $submission['latecomponent'] ?? '',
                'gradingicon' => $grading['icon'],
                'gradingcomponent' => $grading['component'],
                'gradinglabel' => $grading['label'],
                'gradingclass' => $grading['extraclass'],
            ];
            $rows[] = $row;
        }

        return [
            'hasassignments' => !empty($rows),
            'assignments' => $rows,
            'noassignments' => get_string('noassignments', 'block_assignstatuses'),
            'headersubmission' => get_string('submission', 'block_assignstatuses'),
            'headergrading' => get_string('grading', 'block_assignstatuses'),
            'headerassignment' => get_string('headerassignment', 'block_assignstatuses'),
        ];
    }
}
