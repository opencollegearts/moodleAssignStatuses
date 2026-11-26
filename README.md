# Assignment Statuses (Moodle block)

Moodle 4.5+ course block that lists every Assignment in the current course, shows the **current user’s** submission and grading status with visual icons, and links through to each activity.

## Features

- Lists all visible Assignment activities in the course
- Shows submission status (no submission, draft, submitted, reopened, overdue, submitted late)
- Shows grading status (not graded, graded, or marking workflow state)
- Status icons with accessible labels
- Each assignment name links to the activity
- Course pages only

## Requirements

- Moodle **4.5** or later

## Installation

1. Download or clone this repository.
2. Copy the plugin folder into your Moodle site as:

   ```text
   blocks/assignstatuses
   ```

   The folder must be named `assignstatuses` (not `moodleAssignStatuses`).

   Example:

   ```bash
   git clone https://github.com/opencollegearts/moodleAssignStatuses.git blocks/assignstatuses
   ```

3. Log in as an administrator and visit **Site administration → Notifications** to install the plugin.
4. Open a course, turn editing on, and add the **Assignment statuses** block.

## Usage

Once added to a course, the block shows each assignment with:

| Column | Meaning |
|--------|---------|
| Assignment name | Link to the Assignment activity |
| Submission icon | Current user’s submission status |
| Grading icon | Current user’s grading / workflow status |

Hover an icon (or use a screen reader) to see the status label.

## License

GNU GPL v3 or later. See [LICENSE](LICENSE).
