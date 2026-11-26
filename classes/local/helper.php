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
 * Helper to gather assignment status rows for the current user.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_assignstatuses\local;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/locallib.php');

/**
 * Collects visible assignments and current-user submission/grading status.
 *
 * @package    block_assignstatuses
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Build a list of assignment status rows for a user in a course.
     *
     * @param \stdClass $course Course record.
     * @param int $userid User id.
     * @return array[] List of assignment row data for the template.
     */
    public static function get_assignments_for_user(\stdClass $course, int $userid): array {
        $modinfo = get_fast_modinfo($course, $userid);
        $assigns = $modinfo->get_instances_of('assign');
        if (empty($assigns)) {
            return [];
        }

        $rows = [];
        $now = time();

        foreach ($assigns as $cm) {
            if (!$cm->uservisible) {
                continue;
            }

            $context = \context_module::instance($cm->id);
            $assignment = new \assign($context, $cm, $course);
            $assignment->update_effective_access($userid);

            $instance = $assignment->get_instance($userid);
            $flags = $assignment->get_user_flags($userid, false);

            $duedate = (int) $instance->duedate;
            if (!empty($flags->extensionduedate) && (int) $flags->extensionduedate > $duedate) {
                $duedate = (int) $flags->extensionduedate;
            }

            if (!empty($instance->teamsubmission)) {
                $submission = $assignment->get_group_submission($userid, 0, false);
            } else {
                $submission = $assignment->get_user_submission($userid, false);
            }

            $submissionstatus = '';
            $timemodified = 0;
            if (!empty($submission) && !empty($submission->status)) {
                $submissionstatus = $submission->status;
                $timemodified = (int) ($submission->timemodified ?? 0);
            }

            $gradingstatus = $assignment->get_grading_status($userid);

            $overdue = false;
            $late = false;
            if ($duedate > 0) {
                $isopen = empty($submissionstatus)
                    || $submissionstatus === ASSIGN_SUBMISSION_STATUS_NEW
                    || $submissionstatus === ASSIGN_SUBMISSION_STATUS_DRAFT
                    || $submissionstatus === ASSIGN_SUBMISSION_STATUS_REOPENED;
                if ($isopen && $now > $duedate) {
                    $overdue = true;
                }
                if ($submissionstatus === ASSIGN_SUBMISSION_STATUS_SUBMITTED && $timemodified > $duedate) {
                    $late = true;
                }
            }

            $submissioninfo = self::map_submission_status($submissionstatus, $overdue, $late);
            $gradinginfo = self::map_grading_status($gradingstatus);

            $rows[] = [
                'cmid' => $cm->id,
                'name' => $cm->get_formatted_name(),
                'url' => (new \moodle_url('/mod/assign/view.php', ['id' => $cm->id]))->out(false),
                'duedate' => $duedate,
                'submission' => $submissioninfo,
                'grading' => $gradinginfo,
            ];
        }

        return $rows;
    }

    /**
     * Map a submission status string to icon/label template data.
     *
     * @param string $status Assign submission status constant value.
     * @param bool $overdue Whether the assignment is overdue and not submitted.
     * @param bool $late Whether the submission was made after the due date.
     * @return array
     */
    public static function map_submission_status(string $status, bool $overdue, bool $late): array {
        if ($overdue) {
            return [
                'key' => 'overdue',
                'icon' => 'i/overdue',
                'component' => 'block_assignstatuses',
                'label' => get_string('statusoverdue', 'block_assignstatuses'),
                'extraclass' => 'block-assignstatuses-status-overdue',
            ];
        }

        switch ($status) {
            case ASSIGN_SUBMISSION_STATUS_DRAFT:
                $data = [
                    'key' => 'draft',
                    'icon' => 'i/draft',
                    'component' => 'block_assignstatuses',
                    'label' => get_string('submissionstatus_draft', 'assign'),
                    'extraclass' => 'block-assignstatuses-status-draft',
                ];
                break;
            case ASSIGN_SUBMISSION_STATUS_SUBMITTED:
                $data = [
                    'key' => 'submitted',
                    'icon' => 'i/submitted',
                    'component' => 'block_assignstatuses',
                    'label' => get_string('submissionstatus_submitted', 'assign'),
                    'extraclass' => 'block-assignstatuses-status-submitted',
                ];
                break;
            case ASSIGN_SUBMISSION_STATUS_REOPENED:
                $data = [
                    'key' => 'reopened',
                    'icon' => 'i/reopened',
                    'component' => 'block_assignstatuses',
                    'label' => get_string('submissionstatus_reopened', 'assign'),
                    'extraclass' => 'block-assignstatuses-status-reopened',
                ];
                break;
            case ASSIGN_SUBMISSION_STATUS_NEW:
            default:
                $data = [
                    'key' => 'nosubmission',
                    'icon' => 'i/nosubmission',
                    'component' => 'block_assignstatuses',
                    'label' => get_string('submissionstatus_', 'assign'),
                    'extraclass' => 'block-assignstatuses-status-nosubmission',
                ];
                break;
        }

        if ($late) {
            $data['late'] = true;
            $data['latelabel'] = get_string('statuslate', 'block_assignstatuses');
            $data['lateicon'] = 'i/late';
            $data['latecomponent'] = 'block_assignstatuses';
            $data['extraclass'] .= ' block-assignstatuses-status-late';
        }

        return $data;
    }

    /**
     * Map a grading status / workflow state to icon/label template data.
     *
     * @param string $status Result of assign::get_grading_status().
     * @return array
     */
    public static function map_grading_status(string $status): array {
        switch ($status) {
            case ASSIGN_GRADING_STATUS_GRADED:
            case ASSIGN_MARKING_WORKFLOW_STATE_RELEASED:
                return [
                    'key' => 'graded',
                    'icon' => 'i/graded',
                    'component' => 'block_assignstatuses',
                    'label' => get_string('graded', 'assign'),
                    'extraclass' => 'block-assignstatuses-status-graded',
                ];
            case ASSIGN_MARKING_WORKFLOW_STATE_NOTMARKED:
                return [
                    'key' => 'notgraded',
                    'icon' => 'i/notgraded',
                    'component' => 'block_assignstatuses',
                    'label' => get_string('markingworkflowstatenotmarked', 'assign'),
                    'extraclass' => 'block-assignstatuses-status-notgraded',
                ];
            case ASSIGN_MARKING_WORKFLOW_STATE_INMARKING:
            case ASSIGN_MARKING_WORKFLOW_STATE_READYFORREVIEW:
            case ASSIGN_MARKING_WORKFLOW_STATE_INREVIEW:
            case ASSIGN_MARKING_WORKFLOW_STATE_READYFORRELEASE:
                $stringid = 'markingworkflowstate' . $status;
                return [
                    'key' => 'workflow',
                    'icon' => 'i/inmarking',
                    'component' => 'block_assignstatuses',
                    'label' => get_string($stringid, 'assign'),
                    'extraclass' => 'block-assignstatuses-status-workflow',
                ];
            case ASSIGN_GRADING_STATUS_NOT_GRADED:
            default:
                return [
                    'key' => 'notgraded',
                    'icon' => 'i/notgraded',
                    'component' => 'block_assignstatuses',
                    'label' => get_string('notgraded', 'assign'),
                    'extraclass' => 'block-assignstatuses-status-notgraded',
                ];
        }
    }
}
