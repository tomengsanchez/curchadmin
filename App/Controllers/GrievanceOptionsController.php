<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\GrievanceVulnerability;
use App\Models\GrievanceRespondentType;
use App\Models\GrievanceGrmChannel;
use App\Models\GrievancePreferredLanguage;
use App\Models\GrievanceType;
use App\Models\GrievanceCategory;
use App\Models\GrievanceProgressLevel;
use App\Models\Project;

class GrievanceOptionsController extends Controller
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireCapability('manage_grievance_options');
    }

    // Vulnerabilities
    public function vulnerabilities(): void
    {
        $items = GrievanceVulnerability::all();
        $this->view('grievance/options/vulnerabilities', ['items' => $items, 'backUrl' => '/grievance/options/vulnerabilities']);
    }

    public function vulnerabilityCreate(): void
    {
        $this->view('grievance/options/vulnerability_form', ['item' => null, 'backUrl' => '/grievance/options/vulnerabilities']);
    }

    public function vulnerabilityStore(): void
    {
        $this->validateCsrf();
        $id = GrievanceVulnerability::create(['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/vulnerabilities');
    }

    public function vulnerabilityEdit(int $id): void
    {
        $item = GrievanceVulnerability::find($id);
        if (!$item) { $this->redirect('/grievance/options/vulnerabilities'); return; }
        $this->view('grievance/options/vulnerability_form', ['item' => $item, 'backUrl' => '/grievance/options/vulnerabilities']);
    }

    public function vulnerabilityUpdate(int $id): void
    {
        $this->validateCsrf();
        GrievanceVulnerability::update($id, ['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/vulnerabilities');
    }

    public function vulnerabilityDelete(int $id): void
    {
        $this->validateCsrf();
        GrievanceVulnerability::delete($id);
        $this->redirect('/grievance/options/vulnerabilities');
    }

    // Respondent Types
    public function respondentTypes(): void
    {
        $items = GrievanceRespondentType::all();
        $this->view('grievance/options/respondent_types', ['items' => $items]);
    }

    public function respondentTypeCreate(): void
    {
        $this->view('grievance/options/respondent_type_form', ['item' => null]);
    }

    public function respondentTypeStore(): void
    {
        $this->validateCsrf();
        $id = GrievanceRespondentType::create([
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? 'Directly Affected',
            'type_specify' => $_POST['type_specify'] ?? '',
            'guide' => $_POST['guide'] ?? '',
            'description' => $_POST['description'] ?? '',
        ]);
        $this->redirect('/grievance/options/respondent-types');
    }

    public function respondentTypeEdit(int $id): void
    {
        $item = GrievanceRespondentType::find($id);
        if (!$item) { $this->redirect('/grievance/options/respondent-types'); return; }
        $this->view('grievance/options/respondent_type_form', ['item' => $item]);
    }

    public function respondentTypeUpdate(int $id): void
    {
        $this->validateCsrf();
        GrievanceRespondentType::update($id, [
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? 'Directly Affected',
            'type_specify' => $_POST['type_specify'] ?? '',
            'guide' => $_POST['guide'] ?? '',
            'description' => $_POST['description'] ?? '',
        ]);
        $this->redirect('/grievance/options/respondent-types');
    }

    public function respondentTypeDelete(int $id): void
    {
        $this->validateCsrf();
        GrievanceRespondentType::delete($id);
        $this->redirect('/grievance/options/respondent-types');
    }

    // GRM Channels
    public function grmChannels(): void
    {
        $items = GrievanceGrmChannel::all();
        $this->view('grievance/options/grm_channels', ['items' => $items]);
    }

    public function grmChannelCreate(): void
    {
        $this->view('grievance/options/grm_channel_form', ['item' => null]);
    }

    public function grmChannelStore(): void
    {
        $this->validateCsrf();
        GrievanceGrmChannel::create(['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/grm-channels');
    }

    public function grmChannelEdit(int $id): void
    {
        $item = GrievanceGrmChannel::find($id);
        if (!$item) { $this->redirect('/grievance/options/grm-channels'); return; }
        $this->view('grievance/options/grm_channel_form', ['item' => $item]);
    }

    public function grmChannelUpdate(int $id): void
    {
        $this->validateCsrf();
        GrievanceGrmChannel::update($id, ['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/grm-channels');
    }

    public function grmChannelDelete(int $id): void
    {
        $this->validateCsrf();
        GrievanceGrmChannel::delete($id);
        $this->redirect('/grievance/options/grm-channels');
    }

    // Preferred Languages
    public function preferredLanguages(): void
    {
        $items = GrievancePreferredLanguage::all();
        $this->view('grievance/options/preferred_languages', ['items' => $items]);
    }

    public function preferredLanguageCreate(): void
    {
        $this->view('grievance/options/preferred_language_form', ['item' => null]);
    }

    public function preferredLanguageStore(): void
    {
        $this->validateCsrf();
        GrievancePreferredLanguage::create(['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/preferred-languages');
    }

    public function preferredLanguageEdit(int $id): void
    {
        $item = GrievancePreferredLanguage::find($id);
        if (!$item) { $this->redirect('/grievance/options/preferred-languages'); return; }
        $this->view('grievance/options/preferred_language_form', ['item' => $item]);
    }

    public function preferredLanguageUpdate(int $id): void
    {
        $this->validateCsrf();
        GrievancePreferredLanguage::update($id, ['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/preferred-languages');
    }

    public function preferredLanguageDelete(int $id): void
    {
        $this->validateCsrf();
        GrievancePreferredLanguage::delete($id);
        $this->redirect('/grievance/options/preferred-languages');
    }

    // Grievance Types
    public function grievanceTypes(): void
    {
        $items = GrievanceType::all();
        $this->view('grievance/options/grievance_types', ['items' => $items]);
    }

    public function grievanceTypeCreate(): void
    {
        $this->view('grievance/options/grievance_type_form', ['item' => null]);
    }

    public function grievanceTypeStore(): void
    {
        $this->validateCsrf();
        GrievanceType::create(['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/types');
    }

    public function grievanceTypeEdit(int $id): void
    {
        $item = GrievanceType::find($id);
        if (!$item) { $this->redirect('/grievance/options/types'); return; }
        $this->view('grievance/options/grievance_type_form', ['item' => $item]);
    }

    public function grievanceTypeUpdate(int $id): void
    {
        $this->validateCsrf();
        GrievanceType::update($id, ['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/types');
    }

    public function grievanceTypeDelete(int $id): void
    {
        $this->validateCsrf();
        GrievanceType::delete($id);
        $this->redirect('/grievance/options/types');
    }

    // Grievance Categories
    public function grievanceCategories(): void
    {
        $items = GrievanceCategory::all();
        $this->view('grievance/options/grievance_categories', ['items' => $items]);
    }

    public function grievanceCategoryCreate(): void
    {
        $this->view('grievance/options/grievance_category_form', ['item' => null]);
    }

    public function grievanceCategoryStore(): void
    {
        $this->validateCsrf();
        GrievanceCategory::create(['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/categories');
    }

    public function grievanceCategoryEdit(int $id): void
    {
        $item = GrievanceCategory::find($id);
        if (!$item) { $this->redirect('/grievance/options/categories'); return; }
        $this->view('grievance/options/grievance_category_form', ['item' => $item]);
    }

    public function grievanceCategoryUpdate(int $id): void
    {
        $this->validateCsrf();
        GrievanceCategory::update($id, ['name' => $_POST['name'] ?? '', 'description' => $_POST['description'] ?? '']);
        $this->redirect('/grievance/options/categories');
    }

    public function grievanceCategoryDelete(int $id): void
    {
        $this->validateCsrf();
        GrievanceCategory::delete($id);
        $this->redirect('/grievance/options/categories');
    }

    // Progress Levels (In Progress stages)
    public function progressLevels(): void
    {
        $projectId = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;
        $projects = Project::all();
        $selectedProject = $projectId > 0 ? Project::find($projectId) : null;
        $projectHasOwnLevels = $projectId > 0 ? GrievanceProgressLevel::hasForProject($projectId) : false;
        $items = $projectId > 0
            ? GrievanceProgressLevel::forProjectOrDefault($projectId)
            : GrievanceProgressLevel::all();
        $this->view('grievance/options/progress_levels', [
            'items' => $items,
            'projects' => $projects,
            'selectedProjectId' => $projectId,
            'selectedProject' => $selectedProject,
            'projectHasOwnLevels' => $projectHasOwnLevels,
        ]);
    }

    public function progressLevelCreate(): void
    {
        $projectId = isset($_GET['project_id']) ? (int) $_GET['project_id'] : 0;
        $this->view('grievance/options/progress_level_form', [
            'item' => null,
            'projects' => Project::all(),
            'selectedProjectId' => $projectId,
        ]);
    }

    public function progressLevelStore(): void
    {
        $this->validateCsrf();
        $projectId = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;
        GrievanceProgressLevel::create([
            'project_id' => $projectId,
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'sort_order' => $_POST['sort_order'] ?? 0,
            'days_to_address' => $_POST['days_to_address'] ?? null,
        ]);
        if ($projectId > 0) {
            GrievanceProgressLevel::remapProjectLevelReferences($projectId);
        }
        $suffix = $projectId > 0 ? ('?project_id=' . $projectId) : '';
        $this->redirect('/grievance/options/progress-levels' . $suffix);
    }

    public function progressLevelEdit(int $id): void
    {
        $item = GrievanceProgressLevel::find($id);
        if (!$item) { $this->redirect('/grievance/options/progress-levels'); return; }
        $this->view('grievance/options/progress_level_form', [
            'item' => $item,
            'projects' => Project::all(),
            'selectedProjectId' => (int) ($item->project_id ?? 0),
        ]);
    }

    public function progressLevelUpdate(int $id): void
    {
        $this->validateCsrf();
        $projectId = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;
        GrievanceProgressLevel::update($id, [
            'project_id' => $projectId,
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'sort_order' => $_POST['sort_order'] ?? 0,
            'days_to_address' => $_POST['days_to_address'] ?? null,
        ]);
        if ($projectId > 0) {
            GrievanceProgressLevel::remapProjectLevelReferences($projectId);
        }
        $suffix = $projectId > 0 ? ('?project_id=' . $projectId) : '';
        $this->redirect('/grievance/options/progress-levels' . $suffix);
    }

    public function progressLevelDelete(int $id): void
    {
        $this->validateCsrf();
        GrievanceProgressLevel::delete($id);
        $this->redirect('/grievance/options/progress-levels');
    }

    public function progressLevelsInitializeProject(): void
    {
        $this->validateCsrf();
        $projectId = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;
        if ($projectId <= 0 || !Project::find($projectId)) {
            $this->redirect('/grievance/options/progress-levels');
            return;
        }
        GrievanceProgressLevel::initializeProjectFromDefaults($projectId);
        $this->redirect('/grievance/options/progress-levels?project_id=' . $projectId);
    }

    public function progressLevelsRemapProject(): void
    {
        $this->validateCsrf();
        $projectId = isset($_POST['project_id']) ? (int) $_POST['project_id'] : 0;
        if ($projectId <= 0 || !Project::find($projectId)) {
            $this->redirect('/grievance/options/progress-levels');
            return;
        }
        GrievanceProgressLevel::remapProjectLevelReferences($projectId);
        $this->redirect('/grievance/options/progress-levels?project_id=' . $projectId);
    }
}
