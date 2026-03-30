 <?php ob_start();
$g = $grievance ?? null;
$fv = function($k, $d = '') use ($g) { return $g ? ($g->$k ?? $d) : $d; };
$vulnIds = $g ? \App\Models\Grievance::parseJson($g->vulnerability_ids ?? '') : [];
$respIds = $g ? \App\Models\Grievance::parseJson($g->respondent_type_ids ?? '') : [];
$grmIds = $g ? \App\Models\Grievance::parseJson($g->grm_channel_ids ?? '') : [];
$grmId = !empty($grmIds) ? (int)$grmIds[0] : '';
$langIds = $g ? \App\Models\Grievance::parseJson($g->preferred_language_ids ?? '') : [];
$typeIds = $g ? \App\Models\Grievance::parseJson($g->grievance_type_ids ?? '') : [];
$catIds = $g ? \App\Models\Grievance::parseJson($g->grievance_category_ids ?? '') : [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= $g ? 'Edit Grievance' : 'Grievance Registration' ?></h2>
    <a href="/grievance/list" class="btn btn-outline-secondary">Back</a>
</div>
<?php if (!empty($_SESSION['grievance_validation_error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_SESSION['grievance_validation_error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['grievance_validation_error']); endif; ?>
<form method="post" action="<?= $g ? "/grievance/update/{$g->id}" : '/grievance/store' ?>" id="grievanceForm" enctype="multipart/form-data">
    <?= \Core\Csrf::field() ?>
    <!-- Card: Grievance Registration -->
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Grievance Registration</h6></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date Recorded</label>
                    <input type="datetime-local" name="date_recorded" class="form-control" value="<?= $fv('date_recorded') ? date('Y-m-d\TH:i', strtotime($fv('date_recorded'))) : '' ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Grievance Case Number</label>
                    <input type="text" name="grievance_case_number" class="form-control" value="<?= htmlspecialchars($fv('grievance_case_number', '')) ?>" placeholder="Auto-generated if empty">
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Respondents Profile -->
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Respondent's Profile</h6></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_paps" id="isPaps" value="1" <?= !empty($fv('is_paps')) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isPaps">Is PAPS (Project Affected Person)</label>
                </div>
            </div>
            <div id="papsProfileBlock" class="mb-3" style="display:<?= !empty($fv('is_paps')) ? 'block' : 'none' ?>">
                <label class="form-label">Select Profile (PAPS)</label>
                <select name="profile_id" id="profileSelect" class="form-select" style="width:100%">
                    <option value="">-- Search Profile --</option>
                    <?php if (!empty($g->profile_id)): ?><option value="<?= (int)$g->profile_id ?>" selected><?= htmlspecialchars($g->profile_name ?? $g->papsid ?? '') ?></option><?php endif; ?>
                </select>
                <div class="mt-2">
                    <button type="button" id="viewPapsInfoBtn" class="btn btn-sm btn-outline-info" style="display:none">View PAPS Info</button>
                </div>
            </div>
            <div id="fullNameBlock" class="mb-3" style="display:<?= empty($fv('is_paps')) ? 'block' : 'none' ?>">
                <label class="form-label">Respondent Name</label>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <input type="text" name="respondent_first_name" id="respondentFirstName" class="form-control" placeholder="First name" value="<?= htmlspecialchars($fv('respondent_first_name')) ?>" list="respondentFirstNameList" autocomplete="off">
                        <datalist id="respondentFirstNameList"></datalist>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="text" name="respondent_middle_name" id="respondentMiddleName" class="form-control" placeholder="Middle name" value="<?= htmlspecialchars($fv('respondent_middle_name')) ?>" list="respondentMiddleNameList" autocomplete="off">
                        <datalist id="respondentMiddleNameList"></datalist>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input type="text" name="respondent_last_name" id="respondentLastName" class="form-control" placeholder="Last name" value="<?= htmlspecialchars($fv('respondent_last_name')) ?>" list="respondentLastNameList" autocomplete="off">
                        <datalist id="respondentLastNameList"></datalist>
                    </div>
                </div>
                <div id="respondentHistoryBlock" class="mt-3 d-none">
                    <div class="small fw-semibold mb-1">
                        Previous Grievance Records for
                        <span id="respondentHistoryName" class="text-primary"></span>
                    </div>
                    <div class="small text-muted mb-2">Showing most recent 20 records.</div>
                    <div class="table-responsive border rounded">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Grievance Type</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="respondentHistoryBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="mb-3" id="projectBlock">
                <label class="form-label">Project</label>
                <select name="project_id" id="projectSelect" class="form-select" style="width:100%">
                    <option value="">-- Search Project --</option>
                    <?php if (!empty($g->project_id)): ?><option value="<?= (int)$g->project_id ?>" selected><?= htmlspecialchars($g->project_name ?? '') ?></option><?php endif; ?>
                </select>
                <small id="projectFromProfileNote" class="text-muted" style="display:none">Project is set from the selected Profile (PAPS) and cannot be changed.</small>
                <small id="projectAutofillNote" class="text-info" style="display:none"></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Gender</label>
                <div>
                    <?php foreach (['Male','Female','Others','Prefer not to say'] as $opt): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="gender" id="gender<?= $opt ?>" value="<?= htmlspecialchars($opt) ?>" <?= $fv('gender') === $opt ? 'checked' : '' ?>>
                        <label class="form-check-label" for="gender<?= $opt ?>"><?= htmlspecialchars($opt) ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="genderSpecifyBlock" class="mt-2" style="display:<?= $fv('gender') === 'Others' ? 'block' : 'none' ?>">
                    <input type="text" name="gender_specify" class="form-control form-control-sm" placeholder="Specify" value="<?= htmlspecialchars($fv('gender_specify')) ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Valid ID in the Philippines</label>
                    <input type="text" name="valid_id_philippines" class="form-control" value="<?= htmlspecialchars($fv('valid_id_philippines')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">ID Number</label>
                    <input type="text" name="id_number" class="form-control" value="<?= htmlspecialchars($fv('id_number')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Vulnerabilities</label>
                <div class="border rounded p-2">
                    <?php foreach ($vulnerabilities ?? [] as $v): ?>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="vulnerability_ids[]" value="<?= (int)$v->id ?>" id="vuln<?= $v->id ?>" <?= in_array((int)$v->id, $vulnIds) ? 'checked' : '' ?>><label class="form-check-label" for="vuln<?= $v->id ?>"><?= htmlspecialchars($v->name) ?></label></div>
                    <?php endforeach; ?>
                    <?php if (empty($vulnerabilities)): ?><small class="text-muted">No vulnerabilities defined. <a href="/grievance/options/vulnerabilities">Add in Options Library</a>.</small><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Respondent Type</label>
                <?php
                $preselectRespCategory = '';
                if ($g && !empty($respIds)) {
                    foreach ($respondentTypes ?? [] as $rt) {
                        if (in_array((int)$rt->id, $respIds)) { $preselectRespCategory = $rt->type ?? 'Others'; break; }
                    }
                    if (!$preselectRespCategory && !empty(trim($g->respondent_type_other_specify ?? ''))) $preselectRespCategory = 'Others';
                }
                ?>
                <select id="respondentTypeCategory" class="form-select mb-2" style="max-width:280px">
                    <option value="">-- Select category --</option>
                    <option value="Directly Affected" <?= $preselectRespCategory === 'Directly Affected' ? 'selected' : '' ?>>Directly Affected</option>
                    <option value="Indirectly Affected" <?= $preselectRespCategory === 'Indirectly Affected' ? 'selected' : '' ?>>Indirectly Affected</option>
                    <option value="Others" <?= $preselectRespCategory === 'Others' ? 'selected' : '' ?>>Other specify</option>
                </select>
                <div id="respondentTypeCheckboxes" class="border rounded p-2 mb-2" style="display:none">
                    <?php
                    $rtByType = ['Directly Affected' => [], 'Indirectly Affected' => [], 'Others' => []];
                    foreach ($respondentTypes ?? [] as $rt) {
                        $t = $rt->type ?? 'Others';
                        if (!isset($rtByType[$t])) $rtByType[$t] = [];
                        $rtByType[$t][] = $rt;
                    }
                    foreach (['Directly Affected','Indirectly Affected','Others'] as $t):
                        $items = $rtByType[$t] ?? [];
                    ?>
                    <div class="respondent-type-group" data-type="<?= htmlspecialchars($t) ?>" style="display:none">
                        <?php foreach ($items as $rt): ?>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="respondent_type_ids[]" value="<?= (int)$rt->id ?>" id="resp<?= $rt->id ?>" <?= in_array((int)$rt->id, $respIds) ? 'checked' : '' ?>><label class="form-check-label" for="resp<?= $rt->id ?>"><?= htmlspecialchars($rt->name) ?></label></div>
                        <?php endforeach; ?>
                        <?php if (empty($items)): ?><small class="text-muted">No options for this category. <a href="/grievance/options/respondent-types">Add in Options Library</a>.</small><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div id="respondentTypeOtherSpecifyBlock" class="mt-2" style="display:none">
                    <label class="form-label">Specify</label>
                    <input type="text" name="respondent_type_other_specify" class="form-control" value="<?= htmlspecialchars($fv('respondent_type_other_specify')) ?>" placeholder="Specify other respondent type">
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Contact Details -->
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Contact Details</h6></div>
        <div class="card-body">
            <div id="contactAutofillNote" class="alert alert-info py-2 px-3 small d-none mb-3"></div>
            <div class="mb-3">
                <label class="form-label">Home / Business Address</label>
                <input type="text" name="home_business_address" class="form-control" value="<?= htmlspecialchars($fv('home_business_address')) ?>">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile_number" class="form-control" value="<?= htmlspecialchars($fv('mobile_number')) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="text" name="email" class="form-control" value="<?= htmlspecialchars($fv('email')) ?>" placeholder="Email address (optional)">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Others (Specify)</label>
                <input type="text" name="contact_others_specify" class="form-control" value="<?= htmlspecialchars($fv('contact_others_specify')) ?>">
            </div>
        </div>
    </div>

    <!-- Card: GRM Mode -->
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">GRM Mode</h6></div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">GRM Channel <span class="text-danger">*</span></label>
                <div class="border rounded p-2" id="grmChannelGroup">
                    <?php $grmFirst = true; foreach ($grmChannels ?? [] as $gc): ?>
                    <div class="form-check"><input class="form-check-input" type="radio" name="grm_channel_id" value="<?= (int)$gc->id ?>" id="grm<?= $gc->id ?>" <?= (int)$gc->id === $grmId ? 'checked' : '' ?> <?= $grmFirst && count($grmChannels ?? []) > 0 ? 'required' : '' ?>><label class="form-check-label" for="grm<?= $gc->id ?>"><?= htmlspecialchars($gc->name) ?></label></div>
                    <?php $grmFirst = false; endforeach; ?>
                    <?php if (empty($grmChannels)): ?><small class="text-muted">No GRM channels defined. <a href="/grievance/options/grm-channels">Add in Options Library</a>.</small><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Preferred Language of Communication <span class="text-danger">*</span></label>
                <div class="border rounded p-2" id="preferredLanguageGroup" data-required-group="preferred_language_ids[]">
                    <?php foreach ($preferredLanguages ?? [] as $pl): ?>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="preferred_language_ids[]" value="<?= (int)$pl->id ?>" id="lang<?= $pl->id ?>" <?= in_array((int)$pl->id, $langIds) ? 'checked' : '' ?>><label class="form-check-label" for="lang<?= $pl->id ?>"><?= htmlspecialchars($pl->name) ?></label></div>
                    <?php endforeach; ?>
                    <?php if (empty($preferredLanguages)): ?><small class="text-muted">No preferred languages defined. <a href="/grievance/options/preferred-languages">Add in Options Library</a>.</small><?php endif; ?>
                </div>
                <div class="mt-2">
                    <label class="form-label small">Others (Specify)</label>
                    <input type="text" name="preferred_language_other_specify" class="form-control form-control-sm" value="<?= htmlspecialchars($fv('preferred_language_other_specify')) ?>" placeholder="Specify other language">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Type of Grievance <span class="text-danger">*</span></label>
                <div class="border rounded p-2" id="grievanceTypeGroup" data-required-group="grievance_type_ids[]">
                    <?php foreach ($grievanceTypes ?? [] as $gt): ?>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="grievance_type_ids[]" value="<?= (int)$gt->id ?>" id="type<?= $gt->id ?>" <?= in_array((int)$gt->id, $typeIds) ? 'checked' : '' ?>><label class="form-check-label" for="type<?= $gt->id ?>"><?= htmlspecialchars($gt->name) ?></label></div>
                    <?php endforeach; ?>
                    <?php if (empty($grievanceTypes)): ?><small class="text-muted">No types defined. <a href="/grievance/options/types">Add in Options Library</a>.</small><?php endif; ?>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Category of Grievance <span class="text-danger">*</span></label>
                <div class="border rounded p-2" id="grievanceCategoryGroup" data-required-group="grievance_category_ids[]">
                    <?php foreach ($grievanceCategories ?? [] as $gc): ?>
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="grievance_category_ids[]" value="<?= (int)$gc->id ?>" id="cat<?= $gc->id ?>" <?= in_array((int)$gc->id, $catIds) ? 'checked' : '' ?>><label class="form-check-label" for="cat<?= $gc->id ?>"><?= htmlspecialchars($gc->name) ?></label></div>
                    <?php endforeach; ?>
                    <?php if (empty($grievanceCategories)): ?><small class="text-muted">No categories defined. <a href="/grievance/options/categories">Add in Options Library</a>.</small><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Card: Location of Interest -->
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Location of Interest</h6></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="location_same_as_address" id="locationSame" value="1" <?= !empty($fv('location_same_as_address')) || !$g ? 'checked' : '' ?>>
                    <label class="form-check-label" for="locationSame">Same as home/business address</label>
                </div>
            </div>
            <div id="locationSpecifyBlock" class="mb-3" style="display:<?= empty($fv('location_same_as_address')) && $g ? 'block' : 'none' ?>">
                <label class="form-label">If no, Specify</label>
                <input type="text" name="location_specify" class="form-control" value="<?= htmlspecialchars($fv('location_specify')) ?>">
            </div>
        </div>
    </div>

    <!-- Card: Date of Incident -->
    <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0">Date of Incident</h6></div>
        <div class="card-body">
            <div class="mb-3">
                <div class="form-check mb-2">
                    <input class="form-check-input incident-type" type="checkbox" name="incident_one_time" id="incidentOne" value="1" <?= !empty($fv('incident_one_time')) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="incidentOne">One-time occurrence</label>
                </div>
                <div id="incidentOneDateBlock" class="ms-4" style="display:<?= !empty($fv('incident_one_time')) ? 'block' : 'none' ?>">
                    <input type="date" name="incident_date" class="form-control form-control-sm w-auto d-inline-block" value="<?= $fv('incident_date') ? date('Y-m-d', strtotime($fv('incident_date'))) : '' ?>">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check mb-2">
                    <input class="form-check-input incident-type" type="checkbox" name="incident_multiple" id="incidentMultiple" value="1" <?= !empty($fv('incident_multiple')) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="incidentMultiple">Multiple occurrences</label>
                </div>
                <div id="incidentMultipleBlock" class="ms-4" style="display:<?= !empty($fv('incident_multiple')) ? 'block' : 'none' ?>">
                    <input type="text" name="incident_dates" class="form-control form-control-sm" placeholder="Enter dates (e.g. comma-separated)" value="<?= htmlspecialchars($fv('incident_dates')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input incident-type" type="checkbox" name="incident_ongoing" id="incidentOngoing" value="1" <?= !empty($fv('incident_ongoing')) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="incidentOngoing">Ongoing (Currently experiencing the problem)</label>
                </div>
            </div>
        </div>
    </div>

    <!-- Description and Resolution -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Description of the Complaint</label>
                <textarea name="description_complaint" class="form-control" rows="5"><?= htmlspecialchars($fv('description_complaint')) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">What would the complainant like to see happen to resolve the problem?</label>
                <textarea name="desired_resolution" class="form-control" rows="4"><?= htmlspecialchars($fv('desired_resolution')) ?></textarea>
            </div>
        </div>
    </div>

    <!-- Attachments (Title, Description, File per card) -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Attachments</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addAttachmentCard">+ Add attachment</button>
        </div>
        <div class="card-body">
            <input type="hidden" name="attachment_section" value="1">
            <div id="attachmentCards">
                <?php
                $attachments = $attachments ?? [];
                if (empty($attachments)): ?>
                <div class="attachment-card border rounded p-3 mb-3">
                    <input type="hidden" name="attachment_id[]" value="">
                    <div class="mb-2">
                        <label class="form-label small">Title</label>
                        <input type="text" name="attachment_title[]" class="form-control form-control-sm" placeholder="Attachment title">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Description</label>
                        <textarea name="attachment_description[]" class="form-control form-control-sm" rows="2" placeholder="Optional"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">File</label>
                        <input type="file" name="attachment_file[]" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-attachment-card">Remove</button>
                </div>
                <?php endif;
                foreach ($attachments as $idx => $att):
                    $aid = (int)($att->id ?? 0);
                    $atitle = htmlspecialchars($att->title ?? '');
                    $adesc = htmlspecialchars($att->description ?? '');
                ?>
                <div class="attachment-card border rounded p-3 mb-3" data-index="<?= $idx ?>">
                    <input type="hidden" name="attachment_id[]" value="<?= $aid ?>">
                    <div class="mb-2">
                        <label class="form-label small">Title</label>
                        <input type="text" name="attachment_title[]" class="form-control form-control-sm" value="<?= $atitle ?>" placeholder="Attachment title">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Description</label>
                        <textarea name="attachment_description[]" class="form-control form-control-sm" rows="2" placeholder="Optional"><?= $adesc ?></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">File</label>
                        <?php if ($aid && !empty($att->file_path)): ?>
                        <div class="small text-muted mb-1">Current: <?= htmlspecialchars(basename($att->file_path)) ?></div>
                        <?php endif; ?>
                        <input type="file" name="attachment_file[]" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-attachment-card">Remove</button>
                </div>
                <?php endforeach; ?>
            </div>
            <template id="attachmentCardTemplate">
                <div class="attachment-card border rounded p-3 mb-3">
                    <input type="hidden" name="attachment_id[]" value="">
                    <div class="mb-2">
                        <label class="form-label small">Title</label>
                        <input type="text" name="attachment_title[]" class="form-control form-control-sm" placeholder="Attachment title">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Description</label>
                        <textarea name="attachment_description[]" class="form-control form-control-sm" rows="2" placeholder="Optional"></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">File</label>
                        <input type="file" name="attachment_file[]" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-attachment-card">Remove</button>
                </div>
            </template>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><?= $g ? 'Update' : 'Save' ?> Grievance</button>
</form>
<div class="modal fade" id="papsInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">PAPS Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="papsInfoBody">
                <div class="text-muted small">Select a PAPS profile first.</div>
            </div>
        </div>
    </div>
</div>
<?php
$scripts = "
<script>
$(function(){
    var papsModalEl = document.getElementById('papsInfoModal');
    var papsModal = papsModalEl ? new bootstrap.Modal(papsModalEl) : null;
    function escapeHtml(v) { return $('<div>').text(v == null ? '' : String(v)).html(); }
    function yesNo(v) { return v ? 'Yes' : 'No'; }
    function fmt(v) { return (v === null || v === undefined || v === '') ? '-' : escapeHtml(v); }
    function renderAttachmentLinks(items) {
        if (!Array.isArray(items) || !items.length) return '-';
        return items.map(function(it){
            if (!it || !it.url) return '';
            var name = it.name || 'Attachment';
            return '<a href=\"' + escapeHtml(it.url) + '\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"d-inline-block me-2\">' + escapeHtml(name) + '</a>';
        }).filter(Boolean).join('');
    }
    function updatePapsInfoButtonState() {
        var hasProfile = !!$('#profileSelect').val();
        var isPaps = $('#isPaps').is(':checked');
        $('#viewPapsInfoBtn').toggle(isPaps && hasProfile);
    }
    function setDatalistOptions(listId, items) {
        var list = document.getElementById(listId);
        if (!list) return;
        var html = (items || []).map(function(name){
            return '<option value=\"' + escapeHtml(name) + '\"></option>';
        }).join('');
        list.innerHTML = html;
    }
    function loadRespondentSuggestions(url, listId) {
        return $.ajax({ url: url, method: 'GET', dataType: 'json' })
            .done(function(data){ setDatalistOptions(listId, Array.isArray(data) ? data : []); })
            .fail(function(){ setDatalistOptions(listId, []); });
    }
    function clearRespondentHistory() {
        $('#respondentHistoryBody').empty();
        $('#respondentHistoryName').text('');
        $('#respondentHistoryBlock').addClass('d-none');
    }
    function formatHistoryDate(v) {
        if (!v) return '-';
        var dt = new Date(String(v).replace(' ', 'T'));
        if (isNaN(dt.getTime())) return escapeHtml(String(v).slice(0, 16).replace('T', ' '));
        return dt.toLocaleString('en-PH', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    function buildRespondentDisplayName() {
        var first = ($('#respondentFirstName').val() || '').trim();
        var middle = ($('#respondentMiddleName').val() || '').trim();
        var last = ($('#respondentLastName').val() || '').trim();
        return [first, middle, last].filter(Boolean).join(' ');
    }
    function renderRespondentHistory(rows) {
        var body = $('#respondentHistoryBody');
        body.empty();
        if (!Array.isArray(rows) || !rows.length) {
            clearRespondentHistory();
            return;
        }
        $('#respondentHistoryName').text(buildRespondentDisplayName());
        rows.forEach(function(r){
            var dateVal = formatHistoryDate(r.date_recorded);
            var viewUrl = '/grievance/view/' + (parseInt(r.id, 10) || 0);
            var tr = '<tr>'
                + '<td>' + escapeHtml(dateVal) + '</td>'
                + '<td>' + fmt(r.grievance_type) + '</td>'
                + '<td>' + fmt(r.category) + '</td>'
                + '<td>' + fmt(r.status) + '</td>'
                + '<td>' + fmt(r.progress) + '</td>'
                + '<td><a href=\"' + escapeHtml(viewUrl) + '\" class=\"btn btn-sm btn-outline-primary\" target=\"_blank\" rel=\"noopener noreferrer\">View</a></td>'
                + '</tr>';
            body.append(tr);
        });
        $('#respondentHistoryBlock').removeClass('d-none');
    }
    function loadRespondentHistory() {
        if ($('#isPaps').is(':checked')) { clearRespondentHistory(); return; }
        var first = ($('#respondentFirstName').val() || '').trim();
        var last = ($('#respondentLastName').val() || '').trim();
        if (!first || !last) { clearRespondentHistory(); return; }
        var middle = ($('#respondentMiddleName').val() || '').trim();
        var url = '/api/respondents/history?first_name=' + encodeURIComponent(first)
            + '&middle_name=' + encodeURIComponent(middle)
            + '&last_name=' + encodeURIComponent(last);
        $.ajax({ url: url, method: 'GET', dataType: 'json' })
            .done(function(data){ renderRespondentHistory(Array.isArray(data) ? data : []); })
            .fail(function(){ clearRespondentHistory(); });
    }
    function applyRespondentLatestDetails(d) {
        if (!d || !d.matched) {
            $('#contactAutofillNote').addClass('d-none').text('');
            $('#projectAutofillNote').hide().text('');
            return;
        }
        var gender = (d.gender || '').trim();
        $('input[name=gender]').prop('checked', false);
        if (gender !== '') {
            $('input[name=gender][value=\"' + gender.replace(/\"/g, '\\\"') + '\"]').prop('checked', true);
        }
        $('input[name=gender]').trigger('change');
        $('input[name=gender_specify]').val(d.gender_specify || '');

        $('input[name=valid_id_philippines]').val(d.valid_id_philippines || '');
        $('input[name=id_number]').val(d.id_number || '');

        var vulnIds = Array.isArray(d.vulnerability_ids) ? d.vulnerability_ids.map(function(v){ return parseInt(v, 10); }) : [];
        $('input[name=\"vulnerability_ids[]\"]').prop('checked', false);
        vulnIds.forEach(function(id){
            $('input[name=\"vulnerability_ids[]\"][value=\"' + id + '\"]').prop('checked', true);
        });

        var respTypeIds = Array.isArray(d.respondent_type_ids) ? d.respondent_type_ids.map(function(v){ return parseInt(v, 10); }) : [];
        var selectedCategory = '';
        if (respTypeIds.length) {
            var firstMatch = $('input[name=\"respondent_type_ids[]\"][value=\"' + respTypeIds[0] + '\"]');
            if (firstMatch.length) {
                selectedCategory = firstMatch.closest('.respondent-type-group').data('type') || '';
            }
        }
        $('#respondentTypeCategory').val(selectedCategory).trigger('change');
        $('input[name=\"respondent_type_ids[]\"]').prop('checked', false);
        respTypeIds.forEach(function(id){
            $('input[name=\"respondent_type_ids[]\"][value=\"' + id + '\"]').prop('checked', true);
        });
        $('input[name=respondent_type_other_specify]').val(d.respondent_type_other_specify || '');

        // Auto-fill Contact Details card based on latest matched respondent grievance.
        $('input[name=home_business_address]').val(d.home_business_address || '');
        $('input[name=mobile_number]').val(d.mobile_number || '');
        $('input[name=email]').val(d.email || '');
        $('input[name=contact_others_specify]').val(d.contact_others_specify || '');

        var labelName = buildRespondentDisplayName();
        var note = 'Contact details auto-filled from latest historical grievance record.';
        if (labelName) {
            note += ' Matched respondent: ' + labelName + '.';
        }
        $('#contactAutofillNote').removeClass('d-none').text(note);

        var projectId = parseInt(d.project_id, 10) || 0;
        var projectName = (d.project_name || '').trim();
        if (projectId > 0 && projectName !== '') {
            var currentProjectVal = parseInt($('#projectSelect').val(), 10) || 0;
            if (currentProjectVal !== projectId) {
                $('#projectSelect')
                    .empty()
                    .append(new Option('-- Search Project --', '', false, false))
                    .append(new Option(projectName, projectId, true, true))
                    .trigger('change');
            }
            var projectNote = 'Project auto-filled from latest historical grievance record.';
            if (labelName) {
                projectNote += ' Matched respondent: ' + labelName + '.';
            }
            $('#projectAutofillNote').text(projectNote).show();
        } else {
            $('#projectAutofillNote').hide().text('');
        }
    }
    var latestDetailsDebounce = null;
    function loadRespondentLatestDetailsDebounced() {
        if (latestDetailsDebounce) clearTimeout(latestDetailsDebounce);
        latestDetailsDebounce = setTimeout(function(){
            loadRespondentLatestDetails();
        }, 300);
    }
    function loadRespondentLatestDetails() {
        if ($('#isPaps').is(':checked')) return;
        var first = ($('#respondentFirstName').val() || '').trim();
        var last = ($('#respondentLastName').val() || '').trim();
        if (!first || !last) {
            $('#contactAutofillNote').addClass('d-none').text('');
            $('#projectAutofillNote').hide().text('');
            return;
        }
        var middle = ($('#respondentMiddleName').val() || '').trim();
        var url = '/api/respondents/latest-details?first_name=' + encodeURIComponent(first)
            + '&middle_name=' + encodeURIComponent(middle)
            + '&last_name=' + encodeURIComponent(last);
        $.ajax({ url: url, method: 'GET', dataType: 'json' })
            .done(function(data){ applyRespondentLatestDetails(data || {}); });
    }
    function renderPapsInfo(d) {
        var html = '';
        html += '<div class=\"table-responsive\">';
        html += '<table class=\"table table-sm align-middle mb-0\">';
        html += '<tbody>';
        html += '<tr><th style=\"width:35%\">PAPSID</th><td>' + fmt(d.papsid) + '</td></tr>';
        html += '<tr><th>Control Number</th><td>' + fmt(d.control_number) + '</td></tr>';
        html += '<tr><th>Full Name</th><td>' + fmt(d.full_name) + '</td></tr>';
        html += '<tr><th>Age</th><td>' + fmt(d.age) + '</td></tr>';
        html += '<tr><th>Contact Number</th><td>' + fmt(d.contact_number) + '</td></tr>';
        html += '<tr><th>Project</th><td>' + fmt(d.project_name) + '</td></tr>';
        html += '<tr><th>Residing in Project Affected</th><td>' + yesNo(d.residing_in_project_affected) + '</td></tr>';
        html += '<tr><th>Residing Note</th><td>' + fmt(d.residing_in_project_affected_note) + '</td></tr>';
        html += '<tr><th>Residing Attachments</th><td>' + renderAttachmentLinks(d.residing_in_project_affected_attachments) + '</td></tr>';
        html += '<tr><th>Structure Owners</th><td>' + yesNo(d.structure_owners) + '</td></tr>';
        html += '<tr><th>Structure Owner Note</th><td>' + fmt(d.structure_owners_note) + '</td></tr>';
        html += '<tr><th>Structure Owner Attachments</th><td>' + renderAttachmentLinks(d.structure_owners_attachments) + '</td></tr>';
        html += '<tr><th>If Not Structure Owner</th><td>' + fmt(d.if_not_structure_owner_what) + '</td></tr>';
        html += '<tr><th>If Not Owner Attachments</th><td>' + renderAttachmentLinks(d.if_not_structure_owner_attachments) + '</td></tr>';
        html += '<tr><th>Own Property Elsewhere</th><td>' + yesNo(d.own_property_elsewhere) + '</td></tr>';
        html += '<tr><th>Own Property Note</th><td>' + fmt(d.own_property_elsewhere_note) + '</td></tr>';
        html += '<tr><th>Own Property Attachments</th><td>' + renderAttachmentLinks(d.own_property_elsewhere_attachments) + '</td></tr>';
        html += '<tr><th>Availed Gov Housing</th><td>' + yesNo(d.availed_government_housing) + '</td></tr>';
        html += '<tr><th>Availed Housing Note</th><td>' + fmt(d.availed_government_housing_note) + '</td></tr>';
        html += '<tr><th>Availed Housing Attachments</th><td>' + renderAttachmentLinks(d.availed_government_housing_attachments) + '</td></tr>';
        html += '<tr><th>HH Income</th><td>' + fmt(d.hh_income) + '</td></tr>';
        html += '</tbody>';
        html += '</table>';
        html += '</div>';
        return html;
    }
    // GRM Mode: require at least one selection per checkbox group
    $('#grievanceForm').on('submit', function(e){
        var err = [];
        if (!$('#grmChannelGroup input[name=grm_channel_id]:checked').length && $('#grmChannelGroup input[name=grm_channel_id]').length) err.push('Please select a GRM Channel.');
        if (!$('#preferredLanguageGroup input[name=\"preferred_language_ids[]\"]:checked').length && $('#preferredLanguageGroup input[name=\"preferred_language_ids[]\"]').length) err.push('Please select at least one Preferred Language.');
        if (!$('#grievanceTypeGroup input[name=\"grievance_type_ids[]\"]:checked').length && $('#grievanceTypeGroup input[name=\"grievance_type_ids[]\"]').length) err.push('Please select at least one Type of Grievance.');
        if (!$('#grievanceCategoryGroup input[name=\"grievance_category_ids[]\"]:checked').length && $('#grievanceCategoryGroup input[name=\"grievance_category_ids[]\"]').length) err.push('Please select at least one Category of Grievance.');
        if (err.length) { e.preventDefault(); alert(err.join('\\n')); return false; }
    });
    // Is PAPS toggle
    function updateProjectFieldState() {
        var isPaps = $('#isPaps').is(':checked');
        if (isPaps) {
            $('#projectSelect').prop('disabled', true).trigger('change.select2');
            $('#projectFromProfileNote').show();
        } else {
            $('#projectSelect').prop('disabled', false).trigger('change.select2');
            $('#projectFromProfileNote').hide();
        }
    }
    $('#isPaps').on('change', function(){
        var checked = $(this).is(':checked');
        $('#papsProfileBlock').toggle(checked);
        $('#fullNameBlock').toggle(!checked);
        $('#contactAutofillNote').addClass('d-none').text('');
        $('#projectAutofillNote').hide().text('');
        updateProjectFieldState();
        if (!checked) {
            $('#profileSelect').val(null).trigger('change');
            $('#projectSelect').empty().append(new Option('-- Search Project --', '', true, true)).trigger('change');
            loadRespondentHistory();
        } else {
            clearRespondentHistory();
        }
        updatePapsInfoButtonState();
    });
    $('#respondentFirstName').on('input focus', function(){
        if ($('#isPaps').is(':checked')) return;
        var q = ($(this).val() || '').trim();
        loadRespondentSuggestions('/api/respondents/first-names?q=' + encodeURIComponent(q), 'respondentFirstNameList');
    }).on('change blur', function(){
        $('#respondentMiddleName').val('');
        $('#respondentLastName').val('');
        setDatalistOptions('respondentMiddleNameList', []);
        setDatalistOptions('respondentLastNameList', []);
        clearRespondentHistory();
        $('#contactAutofillNote').addClass('d-none').text('');
        $('#projectAutofillNote').hide().text('');
    });
    $('#respondentMiddleName').on('input focus', function(){
        if ($('#isPaps').is(':checked')) return;
        var first = ($('#respondentFirstName').val() || '').trim();
        if (!first) {
            setDatalistOptions('respondentMiddleNameList', []);
            return;
        }
        var q = ($(this).val() || '').trim();
        loadRespondentSuggestions('/api/respondents/middle-names?first_name=' + encodeURIComponent(first) + '&q=' + encodeURIComponent(q), 'respondentMiddleNameList');
    }).on('change blur', function(){
        $('#respondentLastName').val('');
        setDatalistOptions('respondentLastNameList', []);
        clearRespondentHistory();
        $('#contactAutofillNote').addClass('d-none').text('');
        $('#projectAutofillNote').hide().text('');
    });
    $('#respondentLastName').on('input focus', function(){
        if ($('#isPaps').is(':checked')) return;
        var first = ($('#respondentFirstName').val() || '').trim();
        if (!first) {
            setDatalistOptions('respondentLastNameList', []);
            return;
        }
        var middle = ($('#respondentMiddleName').val() || '').trim();
        var q = ($(this).val() || '').trim();
        loadRespondentSuggestions('/api/respondents/last-names?first_name=' + encodeURIComponent(first) + '&middle_name=' + encodeURIComponent(middle) + '&q=' + encodeURIComponent(q), 'respondentLastNameList');
        loadRespondentLatestDetailsDebounced();
    }).on('change blur', function(){
        loadRespondentHistory();
        loadRespondentLatestDetails();
    });
    // Gender Others specify
    $('input[name=gender]').on('change', function(){
        $('#genderSpecifyBlock').toggle($(this).val() === 'Others');
    });
    // Respondent Type: dropdown filters checkboxes; show Specify when Other specify
    $('#respondentTypeCategory').on('change', function(){
        var v = $(this).val();
        if (v) {
            $('#respondentTypeCheckboxes').show();
            $('.respondent-type-group').hide().filter('[data-type=\"'+v+'\"]').show();
            $('#respondentTypeOtherSpecifyBlock').toggle(v === 'Others');
        } else {
            $('#respondentTypeCheckboxes').hide();
            $('#respondentTypeOtherSpecifyBlock').hide();
        }
    }).trigger('change');
    // Location same as address
    $('#locationSame').on('change', function(){
        $('#locationSpecifyBlock').toggle(!$(this).is(':checked'));
    });
    // Incident type blocks
    $('.incident-type').on('change', function(){
        $('#incidentOneDateBlock').toggle($('#incidentOne').is(':checked'));
        $('#incidentMultipleBlock').toggle($('#incidentMultiple').is(':checked'));
    });
    // Project search (Select2)
    $('#projectSelect').select2({
        theme: 'bootstrap-5',
        ajax: { url: '/api/projects', dataType: 'json', delay: 250, data: function(p){ return { q: p.term }; },
            processResults: function(d){ return { results: d.map(function(r){ return { id: r.id, text: r.name }; }) }; } },
        minimumInputLength: 0,
        placeholder: 'Search project...',
        allowClear: true
    });
    // Profile search (Select2) - when PAPS profile selected, auto-fill project from profile
    $('#profileSelect').select2({
        theme: 'bootstrap-5',
        ajax: { url: '/api/profiles', dataType: 'json', delay: 250, data: function(p){ return { q: p.term }; },
            processResults: function(d){ return { results: d.map(function(r){ return { id: r.id, text: r.name || r.papsid || 'ID '+r.id, project_id: r.project_id, project_name: r.project_name }; }) }; } },
        minimumInputLength: 0,
        placeholder: 'Search profile (PAPSID or name)...',
        allowClear: true
    });
    $('#profileSelect').on('select2:select', function(e){
        var d = e.params.data;
        if (d.project_id && d.project_name) {
            $('#projectSelect').empty().append(new Option('-- Search Project --', '', false, false))
                .append(new Option(d.project_name, d.project_id, true, true)).trigger('change');
        }
        updatePapsInfoButtonState();
    });
    $('#profileSelect').on('select2:clear', function(){
        $('#projectSelect').empty().append(new Option('-- Search Project --', '', true, true)).trigger('change');
        updatePapsInfoButtonState();
    });
    $('#viewPapsInfoBtn').on('click', function(){
        var profileId = parseInt($('#profileSelect').val(), 10) || 0;
        if (!profileId) return;
        $('#papsInfoBody').html('<div class=\"text-muted small\">Loading profile information...</div>');
        if (papsModal) papsModal.show();
        $.ajax({
            url: '/api/profile/' + profileId,
            method: 'GET',
            dataType: 'json'
        }).done(function(data){
            $('#papsInfoBody').html(renderPapsInfo(data || {}));
        }).fail(function(xhr){
            var msg = 'Unable to load profile information.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                msg = msg + ' ' + xhr.responseJSON.error;
            }
            $('#papsInfoBody').html('<div class=\"alert alert-danger mb-0\">' + escapeHtml(msg) + '</div>');
        });
    });
    updateProjectFieldState();
    updatePapsInfoButtonState();
    // Attachment cards: add
    $('#addAttachmentCard').on('click', function(){
        var t = document.getElementById('attachmentCardTemplate');
        var clone = t.content.cloneNode(true);
        $('#attachmentCards').append(clone);
    });
    // Attachment cards: remove
    $(document).on('click', '.remove-attachment-card', function(){
        $(this).closest('.attachment-card').remove();
    });
});
</script>";
$content = ob_get_clean();
$pageTitle = $g ? 'Edit Grievance' : 'Grievance Registration';
$currentPage = 'grievance-list';
require __DIR__ . '/../layout/main.php';
?>
