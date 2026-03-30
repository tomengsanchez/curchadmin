<?php
$module = $module ?? 'general';

function help_module_title(string $module): string {
    switch ($module) {
        case 'dashboard': return 'Dashboard';
        case 'profile': return 'Profiles';
        case 'structure': return 'Structure';
        case 'grievance': return 'Grievance';
        case 'grievance-dashboard': return 'Grievance Dashboard';
        case 'grievance-respondents': return 'Grievance Respondent Profiles';
        case 'library': return 'Library';
        case 'system-general': return 'System – General';
        case 'email-settings': return 'Email (SMTP) settings';
        case 'security-settings': return 'Security settings';
        case 'settings': return 'Settings';
        case 'user-management': return 'User Management';
        case 'notifications': return 'Notifications';
        case 'audit-trail': return 'Audit Trail';
        case 'debug-log': return 'Debug Log';
        case 'development': return 'Development';
        case 'account': return 'My Profile / Account';
        case 'account-sessions': return 'Active sessions';
        case 'admin-guide': return 'Administrator Guide';
        default: return 'PAPeR (Overall)';
    }
}

$pageTitle = 'Help - ' . help_module_title($module);
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Help: <?= htmlspecialchars(help_module_title($module)) ?></h2>
        <?php if ($module !== 'general'): ?>
            <p class="text-muted small mb-0">Context-sensitive help based on the page where you clicked Help.</p>
        <?php else: ?>
            <p class="text-muted small mb-0">Overall help for PAPeR.</p>
        <?php endif; ?>
    </div>
    <div>
        <?php if ($module !== 'general'): ?>
            <a href="/help" class="btn btn-outline-secondary btn-sm">View overall help</a>
        <?php endif; ?>
    </div>
</div>

<?php if ($module === 'profile'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-2">
                <strong>Profiles</strong> are records for project-affected people (PAPs): demographics, project linkage,
                structure ownership, and related details. This is separate from your login account (see <a href="/help?from=account">My Profile / Account</a>).
            </p>
            <p class="mb-0 text-muted">
                Profiles connect to <strong>Structures</strong> and to <strong>Grievances</strong> where complainants are linked to PAPs.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>Profile</strong> from the navigation to see the list of PAP profiles.</li>
                <li>Use search and sorting to find a profile; use <strong>Select Columns</strong> (when available) to customize the table.</li>
                <li>Click <strong>Add Profile</strong> to create a record, or open an existing row to view or edit (based on permissions).</li>
                <li>Use <strong>Import</strong> when you have a bulk file in the expected format.</li>
                <li>Use <strong>Export</strong> to download CSV using your current filters and column selection.</li>
                <li>From a profile, manage or review linked structures and attachments as needed.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Profile list</strong> – Searchable table with optional column picker and export.</li>
                <li><strong>Profile detail / form</strong> – Full PAP fields, project context, and structure-related flags.</li>
                <li><strong>Import</strong> – Bulk load of profiles (administrators or roles with import capability).</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>I can’t create or edit profiles.</strong> – Your role needs capabilities such as <code>add_profiles</code> or <code>edit_profiles</code>.</li>
                <li><strong>Why is my dashboard empty for profiles?</strong> – Data may be scoped to linked projects, or there is no recent activity yet.</li>
                <li><strong>How does this differ from “My Profile” in the user menu?</strong> – That page is your <em>user account</em>; this module is for <em>PAP</em> records.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'account'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-2">
                <strong>My Profile</strong> (under the account menu) shows your user information (username, display name, email, role)
                and the projects that are linked to your account.
            </p>
            <p class="mb-0 text-muted">
                Use this page to review your access and, when allowed, jump to the Users module to edit your details.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open the account menu (top-right) and click <strong>My Profile</strong>.</li>
                <li>Review your username, display name, email, and role.</li>
                <li>Check the list of linked projects to see which projects your access is scoped to.</li>
                <li>If you need changes and you have permission, click <strong>Edit</strong> to go to the Users module; otherwise contact an administrator.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Header</strong> – Shows the page title and an Edit button (when you have <code>edit_users</code> capability).</li>
                <li><strong>User details</strong> – Username, display name, email, and role.</li>
                <li><strong>Linked projects</strong> – List of projects your account is associated with, or <em>None</em> if not yet linked.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Why don’t I see an Edit button?</strong> – Your role does not include permission to edit users. Contact an administrator.</li>
                <li><strong>Why are my linked projects empty?</strong> – Your account has not been linked to any project yet, or links were removed during cleanup.</li>
                <li><strong>Why is my email required?</strong> – Email is used for notifications and features like 2FA.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'account-sessions'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview</h5>
            <p class="mb-0">
                <strong>Active sessions</strong> lists browsers and devices where you are currently signed in to PAPeR.
                Use it to review activity and sign out sessions you no longer trust or need.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open the account menu and choose <strong>Active sessions</strong>.</li>
                <li>Review each row: approximate device or browser, IP address, sign-in time, and last activity.</li>
                <li>To end every session except the one you are using now, click <strong>Sign out other devices</strong> and confirm.</li>
                <li>To end a single session, use its <strong>Sign out</strong> action (when shown).</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Device / browser</strong> – Derived from the session’s user agent (best-effort label).</li>
                <li><strong>Current session</strong> – Usually marked so you do not accidentally lock yourself out.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>I signed out other devices but one still shows.</strong> – The other browser may refresh and create a new session; sign it out again or change your password if the device is not yours.</li>
                <li><strong>Why don’t I see this menu item?</strong> – Session tracking may be disabled or your account may not use this feature; contact an administrator.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'structure'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Structure module manages physical structures associated with profiles (e.g., houses, establishments)
                and their related images.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Use the list to search or filter existing structures.</li>
                <li>Optionally click <strong>Select Columns</strong> (when available) to customize which fields are shown in the table.</li>
                <li>Click <strong>Create</strong> to add a new structure, filling in required details.</li>
                <li>Attach images and link the structure to the correct profile when needed.</li>
                <li>Use <strong>Edit</strong> to correct or update structure information.</li>
                <li>Use <strong>Export</strong> to download a CSV of the current list based on your filters and selected columns.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Filters / search</strong> – Narrow down the list of structures.</li>
                <li><strong>Table of structures</strong> – Shows key fields like ID, profile, location, and status; can be customized via <em>Select Columns</em> in list pages.</li>
                <li><strong>Actions</strong> – View, edit, or delete a structure (based on your permissions).</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>I can’t edit a structure.</strong> – Your role might be read-only for this module.</li>
                <li><strong>Images are not loading.</strong> – Check that uploads are allowed and that the file type is supported (image or PDF where applicable).</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'grievance-dashboard'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the dashboard</h5>
            <p class="mb-0">
                The Grievance Dashboard shows filtered totals, breakdowns, and charts for grievances. All widgets
                respect the date range and project filters at the top, as well as your allowed projects.
            </p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Filters</h5>
            <ul class="mb-0">
                <li><strong>Date From / To</strong> – Limits all cards, tables, and charts to grievances whose <em>date recorded</em> falls within the selected range.</li>
                <li><strong>Project</strong> – Limits data to a single project. For non-admin users, only projects you are linked to are available; other project IDs are ignored.</li>
                <li><strong>User project scope</strong> – Even with “All projects”, non-admins only see grievances for projects linked to their account.</li>
            </ul>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">How the widgets and charts are calculated</h5>
            <ul class="mb-0">
                <li>
                    <strong>Total Grievances</strong> – Count of all grievances in the filtered set (project + date range + your allowed projects).
                </li>
                <li>
                    <strong>By Status</strong> – Counts how many grievances are <em>Open</em>, <em>In Progress</em>, and <em>Closed</em>
                    in the filtered set. The “Status distribution” doughnut chart uses the same numbers.
                </li>
                <li>
                    <strong>This Month vs Last Month</strong> –
                    Uses the month part of <em>date recorded</em> to count how many grievances were recorded in
                    the current month and the previous month, then shows the percentage change.
                </li>
                <li>
                    <strong>Grievances over time</strong> –
                    Groups grievances by month (based on <em>date recorded</em>) to build a trend of counts per month.
                    If you set a date range, only months inside that range are shown; otherwise it shows the last 12 months.
                </li>
                <li>
                    <strong>By project (chart and table)</strong> –
                    Groups grievances by project and counts how many belong to each project in the filtered set.
                    “No project” rows represent grievances not linked to a project.
                </li>
                <li>
                    <strong>By Category / By Type</strong> –
                    Uses the grievance’s stored categories and types (JSON arrays) and counts how many grievances
                    include each category or type, within the current project and date filters.
                </li>
                <li>
                    <strong>In progress by stage (chart and list)</strong> –
                    Counts grievances with status <em>In Progress</em>, grouped by their current progress level (stage).
                </li>
                <li>
                    <strong>Needs escalation / closure</strong> –
                    For each in-progress stage that has a configured <em>days to address</em>, compares how many days
                    have passed since the grievance entered that stage. If the limit is exceeded, it is counted under
                    “needs escalation” (for intermediate stages) or “needs to close” (for the final stage).
                </li>
                <li>
                    <strong>Recent grievances</strong> –
                    Shows the 10 most recently recorded grievances in the filtered set, including status, date,
                    and a quick link to the detail page.
                </li>
            </ul>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Customization</h5>
            <ul class="mb-0">
                <li>
                    <strong>Customize dashboard</strong> – Opens a dialog where you can choose which widgets (cards,
                    tables, charts) appear on your grievance dashboard.
                </li>
                <li>
                    <strong>Trend chart type</strong> – Inside the customization dialog, you can change the
                    “Grievances over time” chart between a bar chart and a line chart.
                </li>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Why is the dashboard empty?</strong> – There may be no grievances that match your date range and project filters, or your account has no linked projects.</li>
                <li><strong>Why do counts not match the Grievance list?</strong> – Make sure you are using the same date range and project filters on both the dashboard and the list.</li>
                <li><strong>Why do I not see a specific project in the filter?</strong> – You may not be linked to that project; ask an administrator to link your account if needed.</li>
                <li><strong>What does “needs escalation / needs to close” mean?</strong> – The grievance has stayed longer in that stage than the configured number of days to address.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'grievance-respondents'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview</h5>
            <p class="mb-0">
                <strong>Grievance Respondent Profiles</strong> aggregates people who appear as respondents on grievances
                (name, contact details, PAPS flag, optional link to a PAP profile). It helps you see repeat respondents
                and open their grievances in one click.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Under <strong>Grievance</strong>, open <strong>Respondent Profiles</strong>.</li>
                <li>Enter a search (name, mobile, or email) and submit, or clear to see the full paginated list.</li>
                <li>Review columns such as grievance count, latest grievance date, and whether the respondent is flagged as PAPS.</li>
                <li>When a row shows <em>Linked PAPS</em>, use the link to open the related PAP profile in a new tab.</li>
                <li>Click <strong>View Grievances</strong> to jump to the grievance list filtered to that respondent.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Search</strong> – Filters the respondent table; pagination applies after filtering.</li>
                <li><strong>Respondent table</strong> – One row per distinct respondent profile used on grievances.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Why is someone missing?</strong> – They may not yet be stored as a respondent on any grievance, or spelling differs from your search.</li>
                <li><strong>How is this different from PAP Profiles?</strong> – PAP Profiles are the main people registry; respondents are grievance-specific identities that may be linked to a PAP.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'grievance'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Grievance module records, tracks, and manages grievances including their status, categories, and history.
                It includes a dashboard, list view, detailed view, respondent profiles, and an options library.
            </p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>
                    From the navigation, open <strong>Grievance</strong> and choose
                    <em>Dashboard</em>, <em>Grievances</em>, or <em>Respondent Profiles</em> (aggregated respondents across cases).
                </li>
                <li>
                    On the <strong>Grievances</strong> list, use filters (status, project, date, etc.) to find existing grievances,
                    or click <strong>Create</strong> to log a new grievance.
                </li>
                <li>
                    Use <strong>Select Columns</strong> (when available) to choose which fields appear in the table,
                    for both on-screen viewing and export.
                </li>
                <li>
                    Click <strong>Export</strong> to download a CSV of grievances using your current filters and column selection.
                </li>
                <li>
                    When creating a grievance, fill in complainant details, choose the correct category, channel, and
                    preferred language, and describe the issue clearly.
                </li>
                <li>
                    Attach any relevant files (e.g., documents, images) if the form allows it.
                </li>
                <li>
                    As work progresses, open the grievance detail page to:
                    update status, add notes or history entries, and record escalations when required.
                </li>
                <li>
                    Administrators or users with configuration rights can open the
                    <strong>Options Library</strong> to maintain lookup values such as vulnerabilities,
                    respondent types, GRM channels, preferred languages, grievance types, categories,
                    and in-progress stages.
                </li>
                <li>
                    Use the <strong>Dashboard</strong> to monitor overall grievance trends and workload
                    (by status, category, etc.).
                </li>
            </ol>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li>
                    <strong>Dashboard</strong> – High-level counts and charts for grievances, showing open/in-progress/closed
                    and other key metrics.
                </li>
                <li>
                    <strong>Grievance list</strong> – Searchable, filterable table of grievances with quick access
                    to view or edit, depending on your permissions; supports <em>Select Columns</em> and CSV <em>Export</em>.
                </li>
                <li>
                    <strong>Grievance detail</strong> – Full information for a single grievance, including history and
                    attachments, where you can change status and add updates.
                </li>
                <li>
                    <strong>Respondent Profiles</strong> – Searchable list of grievance respondents with counts and a shortcut
                    to filtered grievances; see also <a href="/help?from=grievance-respondents">help for Respondent Profiles</a>.
                </li>
                <li>
                    <strong>Options Library</strong> – Configuration screens for lookup data:
                    vulnerabilities, respondent types, GRM channels, preferred languages, grievance types,
                    categories, and in-progress stages.
                </li>
            </ul>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li>
                    <strong>I can’t see the Options Library.</strong> –
                    You need the <code>manage_grievance_options</code> capability to configure lookup values.
                </li>
                <li>
                    <strong>Why can’t I delete a grievance?</strong> –
                    Deletion may be restricted for audit reasons. Instead, close the grievance with the appropriate status.
                </li>
                <li>
                    <strong>Why is the dashboard empty?</strong> –
                    There may be no grievances matching your linked projects or you may lack permission to view them.
                </li>
                <li>
                    <strong>Why can’t I change the status?</strong> –
                    Your role might be read-only for this stage, or the grievance may already be closed.
                </li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'library'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Library module manages project records, which are then linked to profiles, grievances, and users.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>Library</strong> &rarr; <em>Project</em> from the navigation.</li>
                <li>Use filters or search to find an existing project.</li>
                <li>Optionally use <strong>Select Columns</strong> (when available) to customize which project fields are shown in the table.</li>
                <li>Click <strong>Create</strong> to add a new project and fill in the required details.</li>
                <li>Use <strong>Edit</strong> to update project information when names or statuses change.</li>
                <li>Use <strong>Export</strong> to download a CSV of projects based on your current filters and selected columns.</li>
                <li>Confirm that projects are correctly linked to profiles and users where applicable.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Filters and search</strong> – Narrow down projects by keywords or other fields.</li>
                <li><strong>Project list</strong> – Shows project identifiers, names, and statuses; can be customized via <em>Select Columns</em> and exported to CSV.</li>
                <li><strong>Actions</strong> – View, edit, or delete (if allowed) individual projects.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Why do I see no projects?</strong> – You may not have permission to view projects or none have been created yet.</li>
                <li><strong>Can I rename a project?</strong> – Yes, if you have edit rights; use the Edit action on the project list.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'system-general'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview</h5>
            <p class="mb-0">
                <strong>System &rarr; General</strong> configures organization-wide branding and default region/timezone.
                It applies to everyone using this PAPeR installation (not per-user).
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>System</strong> &rarr; <strong>General</strong> from the navigation.</li>
                <li>Under <strong>Branding</strong>, set the app name, company or organization name, and upload a logo if desired (also used as the favicon).</li>
                <li>Set <strong>Region</strong> and <strong>Timezone</strong> so dates and times display correctly for your operations.</li>
                <li>Click <strong>Save</strong> and confirm the header/title and timestamps reflect your changes.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Related settings</h5>
            <ul class="mb-0">
                <li><a href="/help?from=email-settings">Email (SMTP) settings</a> – outbound mail</li>
                <li><a href="/help?from=security-settings">Security settings</a> – passwords, 2FA, throttling</li>
                <li><a href="/help?from=settings">Settings</a> – your personal UI and notification preferences</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>My branding changes did not appear.</strong> – Save again, hard-refresh the browser, and check that you are not viewing a cached copy.</li>
                <li><strong>Why don’t I see this menu?</strong> – Only administrators can change system general settings.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'email-settings'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview</h5>
            <p class="mb-0">
                <strong>SMTP / Email settings</strong> configure how PAPeR sends outbound mail (notifications, test messages).
                Typical fields include host, port, encryption, username, and password.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>System</strong> &rarr; <strong>SMTP settings</strong> (or the Email settings page from your navigation).</li>
                <li>Enter the SMTP server details provided by your IT team or provider.</li>
                <li>Save, then use <strong>Test email</strong> to send a message to yourself and confirm delivery.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Test email fails.</strong> – Verify host, port, TLS/SSL, credentials, and that the server allows relay from the PAPeR host.</li>
                <li><strong>I can’t access this page.</strong> – Your role may not include email configuration; ask an administrator.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'security-settings'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview</h5>
            <p class="mb-0">
                <strong>Security settings</strong> control password rules, optional two-factor authentication (2FA),
                session behaviour, and login throttling to reduce abuse.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>Settings</strong> &rarr; <strong>Security</strong> (or <strong>System</strong> &rarr; <strong>Security</strong> depending on layout).</li>
                <li>Review each policy (password length/complexity, 2FA requirements, idle logout, etc.).</li>
                <li>Save changes and communicate new rules to users before enforcing stricter policies.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>I can’t see Security settings.</strong> – Only roles with the appropriate capability (e.g. security administrators) can edit them.</li>
                <li><strong>Users are locked out after changes.</strong> – Confirm 2FA and password rules; admins may need to reset accounts.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'settings'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The <strong>Settings</strong> page (General under Settings in the sidebar) saves <em>your</em> preferences:
                color theme, navigation layout, mobile-friendly mode, and which in-app notifications you want to receive.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>Settings</strong> &rarr; <strong>General</strong> from the navigation.</li>
                <li>Adjust <strong>Custom UI</strong> (theme, layout, optional mobile-friendly navigation).</li>
                <li>Under <strong>Notifications</strong>, toggle which events should generate notifications for your account, then save.</li>
                <li>For server-wide branding, timezone defaults, SMTP, or security policies, use the System menu instead (see links below).</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Custom UI</strong> – Per-user theme, sidebar vs top navigation, and related display options.</li>
                <li><strong>Notifications</strong> – Per-user toggles for profile/grievance notification types.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Related (administrators)</h5>
            <ul class="mb-0">
                <li><a href="/help?from=general">System – General</a> – branding, region, timezone</li>
                <li><a href="/help?from=email-settings">Email (SMTP) settings</a></li>
                <li><a href="/help?from=security-settings">Security settings</a></li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>My theme reset.</strong> – Ensure you clicked save; clearing cookies may reset session-related preferences.</li>
                <li><strong>I don’t receive notifications.</strong> – Check toggles here and SMTP under System; also confirm your role can see the related records.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'user-management'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The User Management module manages user accounts, roles, and capabilities that control what each user
                can see and do in PAPeR.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>User Management</strong> and choose either <em>Users</em> or <em>User Roles &amp; Capabilities</em>.</li>
                <li>In <strong>Users</strong>, search for an existing user or click <strong>Create</strong> to add a new account.</li>
                <li>Optionally use <strong>Select Columns</strong> (when available) on the Users list to show the fields you care about most.</li>
                <li>Use <strong>Export</strong> on the Users list to download a CSV of users based on your filters and selected columns.</li>
                <li>Assign an appropriate role and, if needed, link the user to specific projects.</li>
                <li>In <strong>User Roles &amp; Capabilities</strong>, adjust which modules and actions each role is allowed to access.</li>
                <li>Save changes and confirm that users can access the intended modules (and not more than necessary).</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Users</strong> – List and manage individual user accounts (login, name, email, role, status), with optional <em>Select Columns</em> and CSV <em>Export</em>.</li>
                <li><strong>User Roles &amp; Capabilities</strong> – Configure which actions and modules each role can use.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Why can’t I delete my own account?</strong> – For safety, users cannot delete themselves.</li>
                <li><strong>A user cannot access a module.</strong> – Check their role, capabilities, and linked projects.</li>
                <li><strong>Why am I seeing too many options?</strong> – Your role might be too powerful; consider splitting roles by responsibility.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'notifications'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Notifications page and the bell icon help you see recent events that require your attention,
                such as new grievances, profile updates, or system messages.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Click the bell icon in the header or top navigation to open recent notifications.</li>
                <li>Click on an item in the dropdown to navigate directly to the related record.</li>
                <li>To see all notifications, use the <strong>View all notifications</strong> link or open the <strong>Notifications</strong> page from the navigation.</li>
                <li>Use filters on the Notifications page (if available) to focus on unread or specific types of notifications.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Bell icon</strong> – Shows a badge when there are new notifications.</li>
                <li><strong>Dropdown list</strong> – Quick view of the most recent items with one-click navigation.</li>
                <li><strong>Notifications page</strong> – Full list of notifications, often with filters and pagination.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>The badge never clears.</strong> – Some items may be considered “unread” until you open the related record.</li>
                <li><strong>I don’t get notifications for some actions.</strong> – Those events may not be configured to generate notifications or you may lack permission for the related module.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'audit-trail'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Audit Trail shows a history of important actions taken in PAPeR, helping with accountability,
                troubleshooting, and compliance.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>System</strong> &rarr; <strong>Audit Trail</strong> from the navigation.</li>
                <li>Use filters (date range, user, action, module) to focus on the records you need.</li>
                <li>Click on specific entries when a detail view is available (e.g., to see field-level changes).</li>
                <li>Export or copy relevant rows if you need to share them for investigation or reporting.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Filters</strong> – Narrow the audit log to a particular period, user, or module.</li>
                <li><strong>Audit table</strong> – Lists who did what, when, and (often) where in the system.</li>
                <li><strong>Details</strong> – Depending on implementation, may show before/after values.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Why is an action missing?</strong> – Not all operations may be logged; check system configuration and version notes.</li>
                <li><strong>Can I delete audit entries?</strong> – Typically, deleting audit logs is restricted to preserve history; consult your administrator.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'debug-log'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Debug Log shows technical error and debug messages useful for developers and administrators
                to diagnose issues.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>System</strong> &rarr; <strong>Debug Log</strong> from the navigation.</li>
                <li>Filter or search logs (if available) by date, level, or keyword related to the error.</li>
                <li>Review stack traces and messages to identify misconfiguration, code errors, or external issues (e.g., SMTP failures).</li>
                <li>After fixing an issue, clear or rotate the log if appropriate and supported.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Log entries</strong> – Timestamped records including severity, message, and sometimes stack trace.</li>
                <li><strong>Filters/search</strong> – Quickly narrow down to relevant log lines.</li>
                <li><strong>Maintenance actions</strong> – Options to clear or download logs if implemented.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>I see repeated errors.</strong> – An underlying configuration or code issue is likely still present; address the root cause instead of just clearing the log.</li>
                <li><strong>Some errors mention external services.</strong> – Check connectivity and credentials for databases, SMTP, or other integrations.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'development'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Development page is meant for administrators and developers to configure development-time options,
                such as simulated dates and performance diagnostics.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open <strong>System</strong> &rarr; <strong>Development</strong> from the navigation.</li>
                <li>Review current development settings, such as simulated time and status checks.</li>
                <li>Adjust settings only in coordination with your technical team, then save.</li>
                <li>Verify in the footer/system status that performance and simulated date behave as expected.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Simulated time</strong> – Allows testing how the app behaves on a different date/time.</li>
                <li><strong>Status checks</strong> – Controls whether system status information is displayed.</li>
                <li><strong>Other dev toggles</strong> – Additional switches used during development or troubleshooting.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Users see “Simulated” in the header.</strong> – The app date is being simulated for testing; turn off simulated time after use.</li>
                <li><strong>Why don’t I see Development?</strong> – Access is typically limited to administrators or developers.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'admin-guide'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview</h5>
            <p class="mb-2">
                The <strong>Administrator Guide</strong> is a long-form reference for people who deploy, configure,
                and operate PAPeR (architecture, RBAC, backups, grievance operations, troubleshooting).
            </p>
            <p class="mb-0">
                It appears in your account menu only when you have administrator access.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Open the account menu (top-right) and choose <strong>Admin Guide</strong>, or go to <a href="/admin-guide">/admin-guide</a>.</li>
                <li>Read sections in order for onboarding, or jump to the topic you need (users, roles, system settings, audit, etc.).</li>
                <li>Use this Help page for shorter, screen-specific guidance; use the Admin Guide for operational runbooks and technical detail.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">What it covers</h5>
            <ul class="mb-0">
                <li>System architecture, hosting, configuration files, and logs</li>
                <li>Users, roles, capabilities, and project linking</li>
                <li>Grievance workflow concepts and options library</li>
                <li>Maintenance, backups, and common support scenarios</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>I don’t see Admin Guide in the menu.</strong> – It is limited to administrators; use contextual Help or ask an admin.</li>
                <li><strong>Does the Admin Guide replace this Help page?</strong> – No. Help is end-user and screen-oriented; the Admin Guide is operator-focused.</li>
            </ul>
        </div>
    </div>

<?php elseif ($module === 'dashboard'): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of the module</h5>
            <p class="mb-0">
                The Dashboard gives a summary of activity across Profiles, Structures, Grievances, and Users,
                limited to the projects linked to your account.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Review each section (Profile, Structure, Grievance, Users) for recent counts and trends.</li>
                <li>Hover on charts where supported to see exact values.</li>
                <li>Use the <strong>View All</strong> buttons to jump into the detailed module screens.</li>
                <li>If a section shows “No data”, confirm your project links and permissions.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Profile section</strong> – New and updated profiles, plus structures added to profiles.</li>
                <li><strong>Structure section</strong> – Structures created, updated, and images added.</li>
                <li><strong>Grievance section</strong> – Counts by status and activity, with charts.</li>
                <li><strong>Users section</strong> – Active users per role.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>Why do I see “No data”?</strong> – You may not have permission to that module or no activity exists yet for your linked projects.</li>
                <li><strong>Numbers look wrong.</strong> – Confirm the date range and that your projects are correctly linked to the underlying records.</li>
            </ul>
        </div>
    </div>

<?php else: ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Overview of PAPeR</h5>
            <p class="mb-0">
                PAPeR is used to manage project-affected profiles and their grievances, including structures,
                projects, users, notifications, and system configuration.
            </p>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Features and module help</h5>
            <p class="mb-2">
                Open contextual help for a specific area (your menu may hide items you do not have permission to use):
            </p>
            <ul class="mb-0">
                <li><a href="/help?from=dashboard">Dashboard</a> – Home summary for profiles, structures, grievances, and users.</li>
                <li><a href="/help?from=profile">Profiles</a> – Project-affected people (PAP) records.</li>
                <li><a href="/help?from=structure">Structure</a> – Physical structures linked to profiles.</li>
                <li><a href="/help?from=grievance-dashboard">Grievance Dashboard</a> – Charts and KPIs for grievances.</li>
                <li><a href="/help?from=grievance-list">Grievances</a> – List, create, track, and export grievances.</li>
                <li><a href="/help?from=grievance-respondents">Grievance Respondent Profiles</a> – Aggregated respondents and links to their grievances.</li>
                <li><a href="/help?from=library">Library (Projects)</a> – Project master data.</li>
                <li><a href="/help?from=settings">Settings</a> – UI preferences and notification toggles (plus email/security where your role allows).</li>
                <li><a href="/help?from=users">User Management</a> – Users and roles/capabilities.</li>
                <li><a href="/help?from=notifications">Notifications</a> – In-app notification list and bell menu.</li>
                <li><a href="/help?from=account">My Profile / Account</a> – Your login identity and linked projects.</li>
                <li><a href="/help?from=account-sessions">Active sessions</a> – Where you are signed in; sign out other devices.</li>
                <li><a href="/help?from=general">System – General</a> – Branding, region, timezone (administrators).</li>
                <li><a href="/help?from=email-settings">SMTP / Email settings</a> – Outbound mail configuration.</li>
                <li><a href="/help?from=security-settings">Security settings</a> – Password policy, 2FA, throttling.</li>
                <li><a href="/help?from=audit-trail">Audit Trail</a> – Record of important changes.</li>
                <li><a href="/help?from=debug-log">Debug Log</a> – Technical errors for administrators.</li>
                <li><a href="/help?from=development">Development</a> – Simulated time and diagnostics (administrators).</li>
                <li><a href="/help?from=admin-guide">Administrator Guide</a> – Full operator reference (administrators).</li>
            </ul>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Steps</h5>
            <ol class="mb-0">
                <li>Start at the Dashboard to get an overview of your data.</li>
                <li>Use the navigation for Profiles, Structure, Grievance (including Respondent Profiles and Options Library), Library, Settings, and System.</li>
                <li>Use your account menu for My Profile, Active sessions, Notifications, Help, Admin Guide (if administrator), and Logout.</li>
            </ol>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="mb-2">Parts</h5>
            <ul class="mb-0">
                <li><strong>Main navigation</strong> – Access modules according to your role.</li>
                <li><strong>Account menu</strong> – My Profile, Active sessions, Notifications, Admin Guide (admins), Help, Logout.</li>
                <li><strong>Notifications</strong> – Bell icon and full notifications page for recent events.</li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-2">FAQs</h5>
            <ul class="mb-0">
                <li><strong>I don’t see some menu items.</strong> – Menus are filtered by your role and capabilities.</li>
                <li><strong>My data looks incomplete.</strong> – Check that your account is linked to the correct projects.</li>
                <li><strong>I see an error message.</strong> – Capture a screenshot or the exact text and share it with your administrator.</li>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <h5 class="mb-2">Overall help for PAPeR</h5>
        <p class="mb-2">
            Regardless of which module you are using, the following tips apply across the system:
        </p>
        <ul class="mb-2">
            <li><strong>Permissions</strong> – What you can see and do depends on your role and capabilities.</li>
            <li><strong>Projects</strong> – Most data is scoped to the projects linked to your account.</li>
            <li><strong>Audit trail</strong> – Many actions are recorded for accountability; avoid deleting data unless necessary.</li>
        </ul>
        <p class="mb-1">
            If you still need help, contact your system administrator or PAPeR focal person.
        </p>
        <p class="mb-0 text-muted">
            Include what you were doing, the exact time, the module, and any error messages so they can assist faster.
        </p>
    </div>
</div>
<?php
$content = ob_get_clean();
$currentPage = '';
require __DIR__ . '/../layout/main.php';

