<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Certificate;
use App\Models\CredentialBadge;
use App\Models\DesignMedia;
use App\Models\Education;
use App\Models\GuestbookEntry;
use App\Models\PortfolioTool;
use App\Models\Profile;
use App\Models\Project;
use App\Models\PublishedWork;
use App\Models\WorkExperience;
use App\Support\Profile\CredentialCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminPortfolioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Index', [
            'profile' => Profile::query()->firstOrFail(),
            'achievements' => Achievement::query()->orderBy('sort_order')->orderByDesc('achieved_on')->get(),
            'projects' => Project::query()->orderBy('sort_order')->get(),
            'designMedia' => DesignMedia::query()->orderByDesc('is_featured')->orderBy('sort_order')->get(),
            'publishedWorks' => PublishedWork::query()->orderBy('sort_order')->get(),
            'portfolioTools' => PortfolioTool::query()
                ->orderByRaw("case category when 'backend' then 1 when 'frontend' then 2 when 'automation' then 3 when 'data' then 4 when 'design' then 5 when 'infrastructure' then 6 when 'platforms' then 7 when 'development' then 8 else 9 end")
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'certificates' => Certificate::query()->orderBy('sort_order')->limit(250)->get()->each(function (Certificate $certificate): void {
                $certificate->category = CredentialCategory::classify($certificate->category, [$certificate->title, $certificate->issuer, $certificate->description, $certificate->tags ?? []]);
            }),
            'credentialBadges' => CredentialBadge::query()->orderByDesc('is_featured')->orderBy('sort_order')->limit(250)->get()->each(function (CredentialBadge $badge): void {
                $badge->category = CredentialCategory::classify($badge->category, [$badge->name, $badge->issuer, $badge->description, $badge->skills ?? []]);
            }),
            'credentialCategories' => CredentialCategory::optionList(),
            'education' => Education::query()->orderBy('sort_order')->get(),
            'workExperiences' => WorkExperience::query()->orderBy('sort_order')->get(),
            'guestbookEntries' => GuestbookEntry::query()
                ->orderByRaw("case status when 'pending' then 0 when 'approved' then 1 else 2 end")
                ->orderByDesc('created_at')
                ->get()
                ->map(fn (GuestbookEntry $entry): array => [
                    'id' => $entry->id,
                    'name' => $entry->name,
                    'email' => $entry->email,
                    'role_or_organization' => $entry->role_or_organization,
                    'body' => $entry->body,
                    'rating' => $entry->rating,
                    'status' => $entry->status,
                    'admin_reply' => $entry->admin_reply,
                    'approved_at' => $entry->approved_at?->toDateTimeString(),
                    'created_at' => $entry->created_at?->format('M j, Y g:i A'),
                ]),
            'credentialCount' => CredentialBadge::query()->count(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:255'],
            'availability' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:255'],
            'bio' => ['required', 'string', 'max:3000'],
            'credly_username' => ['nullable', 'string', 'max:80'],
            'github_username' => ['nullable', 'regex:/\A[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?\z/'],
            'canva_url' => ['nullable', 'url', 'max:2048'],
            'external_links_text' => ['nullable', 'string', 'max:4000'],
            'highlights_text' => ['nullable', 'string', 'max:4000'],
            'skills_text' => ['nullable', 'string', 'max:4000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $profile = Profile::query()->firstOrFail();
        $profile->fill(Arr::except($validated, [
            'external_links_text',
            'highlights_text',
            'skills_text',
            'avatar',
        ]));
        $profile->external_links = $this->links($validated['external_links_text'] ?? '');
        $profile->highlights = $this->lines($validated['highlights_text'] ?? '');
        $profile->skills = $this->lines($validated['skills_text'] ?? '');

        if ($request->file('avatar')) {
            $profile->avatar_url = $this->storeMedia($request->file('avatar'), 'profile');
        }

        $profile->save();

        return back()->with('success', 'Profile updated.');
    }

    public function storeAchievement(Request $request): RedirectResponse
    {
        $data = $this->achievementData($request);
        $data['image_url'] = $request->file('image')
            ? $this->storeMedia($request->file('image'), 'achievements')
            : null;

        Achievement::query()->create($data);

        return back()->with('success', 'Achievement added.');
    }

    public function updateAchievement(Request $request, Achievement $achievement): RedirectResponse
    {
        $data = $this->achievementData($request);

        if ($request->file('image')) {
            $data['image_url'] = $this->storeMedia($request->file('image'), 'achievements');
        }

        $achievement->update($data);

        return back()->with('success', 'Achievement updated.');
    }

    public function destroyAchievement(Achievement $achievement): RedirectResponse
    {
        $achievement->delete();

        return back()->with('success', 'Achievement removed.');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $data = $this->projectData($request);
        $data['thumbnail_url'] = $request->file('thumbnail')
            ? $this->storeMedia($request->file('thumbnail'), 'projects')
            : null;

        Project::query()->create($data);

        return back()->with('success', 'Showcase project added.');
    }

    public function updateProject(Request $request, Project $project): RedirectResponse
    {
        $data = $this->projectData($request);

        if ($request->file('thumbnail')) {
            $data['thumbnail_url'] = $this->storeMedia($request->file('thumbnail'), 'projects');
        }

        $project->update($data);

        return back()->with('success', 'Showcase project updated.');
    }

    public function destroyProject(Project $project): RedirectResponse
    {
        $project->delete();

        return back()->with('success', 'Showcase project removed.');
    }

    public function storeDesignMedia(Request $request): RedirectResponse
    {
        $data = $this->designMediaData($request);

        if ($request->file('media')) {
            $data['media_url'] = $this->storeMedia($request->file('media'), 'designs');
        }

        if (empty($data['media_url'])) {
            throw ValidationException::withMessages(['media' => 'Upload a media file or add an external media URL.']);
        }

        if ($request->file('thumbnail')) {
            $data['thumbnail_url'] = $this->storeMedia($request->file('thumbnail'), 'design-thumbnails');
        }

        DesignMedia::query()->create($data);

        return back()->with('success', 'Design media added.');
    }

    public function updateDesignMedia(Request $request, DesignMedia $designMedia): RedirectResponse
    {
        $data = $this->designMediaData($request);

        if ($designMedia->media_type !== $data['media_type'] && ! $request->file('media') && empty($data['media_url'])) {
            throw ValidationException::withMessages(['media' => 'Upload replacement media when changing the format.']);
        }

        if ($request->file('media')) {
            $mediaUrl = $this->storeMedia($request->file('media'), 'designs');
            $this->deleteStoredMedia($designMedia->media_url);
            $data['media_url'] = $mediaUrl;
        } elseif (empty($data['media_url'])) {
            unset($data['media_url']);
        }

        if ($request->file('thumbnail')) {
            $thumbnailUrl = $this->storeMedia($request->file('thumbnail'), 'design-thumbnails');
            $this->deleteStoredMedia($designMedia->thumbnail_url);
            $data['thumbnail_url'] = $thumbnailUrl;
        }

        $designMedia->update($data);

        return back()->with('success', 'Design media updated.');
    }

    public function destroyDesignMedia(DesignMedia $designMedia): RedirectResponse
    {
        $this->deleteStoredMedia($designMedia->media_url);
        $this->deleteStoredMedia($designMedia->thumbnail_url);
        $designMedia->delete();

        return back()->with('success', 'Design media removed.');
    }

    public function storePortfolioTool(Request $request): RedirectResponse
    {
        $data = $this->portfolioToolData($request);

        if ($request->file('icon')) {
            $data['icon_url'] = $this->storeMedia($request->file('icon'), 'tools');
        }

        PortfolioTool::query()->create($data);

        return back()->with('success', 'Tool added.');
    }

    public function updatePortfolioTool(Request $request, PortfolioTool $portfolioTool): RedirectResponse
    {
        $data = $this->portfolioToolData($request);

        if ($request->file('icon')) {
            $iconUrl = $this->storeMedia($request->file('icon'), 'tools');
            $this->deleteStoredMedia($portfolioTool->icon_url);
            $data['icon_url'] = $iconUrl;
        } elseif (empty($data['icon_url'])) {
            unset($data['icon_url']);
        } elseif ($data['icon_url'] !== $portfolioTool->icon_url) {
            $this->deleteStoredMedia($portfolioTool->icon_url);
        }

        $portfolioTool->update($data);

        return back()->with('success', 'Tool updated.');
    }

    public function destroyPortfolioTool(PortfolioTool $portfolioTool): RedirectResponse
    {
        $this->deleteStoredMedia($portfolioTool->icon_url);
        $portfolioTool->delete();

        return back()->with('success', 'Tool removed.');
    }

    public function storePublishedWork(Request $request): RedirectResponse
    {
        $data = $this->publishedWorkData($request);
        $data['cover_url'] = $request->file('cover')
            ? $this->storeMedia($request->file('cover'), 'publications')
            : null;

        PublishedWork::query()->create($data);

        return back()->with('success', 'Published work added.');
    }

    public function updatePublishedWork(Request $request, PublishedWork $publishedWork): RedirectResponse
    {
        $data = $this->publishedWorkData($request);

        if ($request->file('cover')) {
            $data['cover_url'] = $this->storeMedia($request->file('cover'), 'publications');
        }

        $publishedWork->update($data);

        return back()->with('success', 'Published work updated.');
    }

    public function destroyPublishedWork(PublishedWork $publishedWork): RedirectResponse
    {
        $publishedWork->delete();

        return back()->with('success', 'Published work removed.');
    }

    public function storeWorkExperience(Request $request): RedirectResponse
    {
        $data = $this->workExperienceData($request);
        $data['logo_url'] = $request->file('logo')
            ? $this->storeMedia($request->file('logo'), 'organizations')
            : ($data['logo_url'] ?? null);

        WorkExperience::query()->create($data);

        return back()->with('success', 'Work experience added.');
    }

    public function updateWorkExperience(Request $request, WorkExperience $workExperience): RedirectResponse
    {
        $data = $this->workExperienceData($request);

        if ($request->file('logo')) {
            $logoUrl = $this->storeMedia($request->file('logo'), 'organizations');
            $this->deleteStoredMedia($workExperience->logo_url);
            $data['logo_url'] = $logoUrl;
        } elseif (empty($data['logo_url'])) {
            unset($data['logo_url']);
        } elseif ($data['logo_url'] !== $workExperience->logo_url) {
            $this->deleteStoredMedia($workExperience->logo_url);
        }

        $workExperience->update($data);

        return back()->with('success', 'Work experience updated.');
    }

    public function destroyWorkExperience(WorkExperience $workExperience): RedirectResponse
    {
        $this->deleteStoredMedia($workExperience->logo_url);
        $workExperience->delete();

        return back()->with('success', 'Work experience removed.');
    }

    public function storeEducation(Request $request): RedirectResponse
    {
        $data = $this->educationData($request);
        $data['logo_url'] = $request->file('logo')
            ? $this->storeMedia($request->file('logo'), 'organizations')
            : ($data['logo_url'] ?? null);

        Education::query()->create($data);

        return back()->with('success', 'Education entry added.');
    }

    public function updateEducation(Request $request, Education $education): RedirectResponse
    {
        $data = $this->educationData($request);

        if ($request->file('logo')) {
            $logoUrl = $this->storeMedia($request->file('logo'), 'organizations');
            $this->deleteStoredMedia($education->logo_url);
            $data['logo_url'] = $logoUrl;
        } elseif (empty($data['logo_url'])) {
            unset($data['logo_url']);
        } elseif ($data['logo_url'] !== $education->logo_url) {
            $this->deleteStoredMedia($education->logo_url);
        }

        $education->update($data);

        return back()->with('success', 'Education entry updated.');
    }

    public function destroyEducation(Education $education): RedirectResponse
    {
        $this->deleteStoredMedia($education->logo_url);
        $education->delete();

        return back()->with('success', 'Education entry removed.');
    }

    public function storeCredentialBadge(Request $request): RedirectResponse
    {
        $data = $this->credentialBadgeData($request);

        if ($request->file('image')) {
            $data['image_url'] = $this->storeMedia($request->file('image'), 'badges');
        }

        if (empty($data['image_url'])) {
            throw ValidationException::withMessages(['image' => 'Upload a badge image or add a direct image URL.']);
        }

        $data['provider'] = 'admin';
        $data['external_id'] = (string) str()->uuid();
        CredentialBadge::query()->create($data);

        return back()->with('success', 'Credential badge added.');
    }

    public function updateCredentialBadge(Request $request, CredentialBadge $credentialBadge): RedirectResponse
    {
        $data = $this->credentialBadgeData($request);

        if ($request->file('image')) {
            $imageUrl = $this->storeMedia($request->file('image'), 'badges');
            $this->deleteStoredMedia($credentialBadge->image_url);
            $data['image_url'] = $imageUrl;
        } elseif (empty($data['image_url'])) {
            unset($data['image_url']);
        } elseif ($data['image_url'] !== $credentialBadge->image_url) {
            $this->deleteStoredMedia($credentialBadge->image_url);
        }

        $credentialBadge->update($data);

        return back()->with('success', 'Credential badge updated.');
    }

    public function destroyCredentialBadge(CredentialBadge $credentialBadge): RedirectResponse
    {
        $this->deleteStoredMedia($credentialBadge->image_url);
        $credentialBadge->delete();

        return back()->with('success', 'Credential badge removed.');
    }

    public function storeCertificate(Request $request): RedirectResponse
    {
        $data = $this->certificateData($request);

        if ($request->file('document')) {
            $data['file_url'] = $this->storeMedia($request->file('document'), 'certificates');
        }

        if (empty($data['file_url'])) {
            throw ValidationException::withMessages(['file_url' => 'Add a certificate URL or upload a PDF.']);
        }

        if ($request->file('thumbnail')) {
            $data['thumbnail_url'] = $this->storeMedia($request->file('thumbnail'), 'certificates');
        }

        $data['source'] = 'admin';
        $data['external_id'] = (string) str()->uuid();
        Certificate::query()->create($data);

        return back()->with('success', 'Certificate added.');
    }

    public function updateCertificate(Request $request, Certificate $certificate): RedirectResponse
    {
        $data = $this->certificateData($request);

        if ($request->file('document')) {
            $data['file_url'] = $this->storeMedia($request->file('document'), 'certificates');
        } elseif (empty($data['file_url'])) {
            unset($data['file_url']);
        }

        if ($request->file('thumbnail')) {
            $data['thumbnail_url'] = $this->storeMedia($request->file('thumbnail'), 'certificates');
        }

        $certificate->update($data);

        return back()->with('success', 'Certificate updated.');
    }

    public function destroyCertificate(Certificate $certificate): RedirectResponse
    {
        $certificate->delete();

        return back()->with('success', 'Certificate removed.');
    }

    public function updateGuestbookEntry(Request $request, GuestbookEntry $guestbookEntry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,hidden'],
            'admin_reply' => ['nullable', 'string', 'max:2000'],
        ]);

        $guestbookEntry->status = $validated['status'];
        $guestbookEntry->admin_reply = $validated['admin_reply'] ?? null;
        $guestbookEntry->approved_at = $validated['status'] === 'approved'
            ? ($guestbookEntry->approved_at ?? now())
            : null;
        $guestbookEntry->replied_at = filled($validated['admin_reply'] ?? null)
            ? now()
            : null;
        $guestbookEntry->save();

        return back()->with('success', 'Guestbook entry updated.');
    }

    public function destroyGuestbookEntry(GuestbookEntry $guestbookEntry): RedirectResponse
    {
        $guestbookEntry->delete();

        return back()->with('success', 'Guestbook entry removed.');
    }

    private function achievementData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'achieved_on' => ['nullable', 'date'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'tags_text' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $data['tags'] = $this->commaList($data['tags_text'] ?? '');
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return Arr::except($data, ['tags_text', 'image']);
    }

    private function projectData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:3000'],
            'year' => ['nullable', 'string', 'max:20'],
            'url' => ['nullable', 'url', 'max:2048'],
            'repo_url' => ['nullable', 'url', 'max:2048'],
            'tags_text' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $data['tags'] = $this->commaList($data['tags_text'] ?? '');
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return Arr::except($data, ['tags_text', 'thumbnail']);
    }

    private function portfolioToolData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'in:backend,frontend,automation,data,design,infrastructure,platforms,development,other'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon_url' => ['nullable', 'url', 'max:2048'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return Arr::except($data, ['icon']);
    }

    private function publishedWorkData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:scopus_paper,research_paper,digital_magazine,article,newspaper,book_chapter,other'],
            'title' => ['required', 'string', 'max:255'],
            'publication' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'published_on' => ['nullable', 'date'],
            'doi' => ['nullable', 'string', 'max:255'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'tags_text' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $data['tags'] = $this->commaList($data['tags_text'] ?? '');
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return Arr::except($data, ['tags_text', 'cover']);
    }

    private function workExperienceData(Request $request): array
    {
        $data = $request->validate([
            'organization' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'string', 'max:80'],
            'end_date' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:4000'],
            'responsibilities_text' => ['nullable', 'string', 'max:6000'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        $data['responsibilities'] = $this->lines($data['responsibilities_text'] ?? '');
        $data['is_current'] = (bool) ($data['is_current'] ?? false);

        if ($data['is_current']) {
            $data['end_date'] = null;
        }

        return Arr::except($data, ['responsibilities_text', 'logo']);
    }

    private function educationData(Request $request): array
    {
        $data = $request->validate([
            'institution' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'max:255'],
            'level' => ['nullable', 'string', 'max:160'],
            'start_date' => ['nullable', 'string', 'max:80'],
            'end_date' => ['nullable', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:4000'],
            'activities_text' => ['nullable', 'string', 'max:6000'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $data['activities'] = $this->lines($data['activities_text'] ?? '');

        return Arr::except($data, ['activities_text', 'logo']);
    }

    private function credentialBadgeData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'in:ai_data,software,networking,cybersecurity,cloud_enterprise,project_management,design_marketing,professional'],
            'description' => ['nullable', 'string', 'max:4000'],
            'issued_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'certificate_url' => ['nullable', 'url', 'max:2048'],
            'criteria_url' => ['nullable', 'url', 'max:2048'],
            'evidence_url' => ['nullable', 'url', 'max:2048'],
            'skills_text' => ['nullable', 'string', 'max:3000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $data['skills'] = $this->commaList($data['skills_text'] ?? '');
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return Arr::except($data, ['skills_text', 'image']);
    }

    private function certificateData(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'in:ai_data,software,networking,cybersecurity,cloud_enterprise,project_management,design_marketing,professional'],
            'description' => ['nullable', 'string', 'max:3000'],
            'issued_on' => ['nullable', 'date'],
            'file_url' => ['nullable', 'url', 'max:2048'],
            'tags_text' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'document' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ]);

        $data['tags'] = $this->commaList($data['tags_text'] ?? '');
        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return Arr::except($data, ['tags_text', 'thumbnail', 'document']);
    }

    private function designMediaData(Request $request): array
    {
        $mediaType = (string) $request->input('media_type');
        $mediaRules = $mediaType === 'video'
            ? ['nullable', 'file', 'mimes:mp4,webm,mov', 'max:102400']
            : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'];

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'media_type' => ['required', 'in:image,infographic,video'],
            'description' => ['nullable', 'string', 'max:3000'],
            'year' => ['nullable', 'string', 'max:20'],
            'media_url' => ['nullable', 'url', 'max:2048'],
            'external_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_featured' => ['nullable', 'boolean'],
            'media' => $mediaRules,
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $data['is_featured'] = (bool) ($data['is_featured'] ?? false);

        return Arr::except($data, ['media', 'thumbnail']);
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\R/', $value) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function commaList(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function links(string $value): array
    {
        return collect(preg_split('/\R/', $value) ?: [])
            ->map(function (string $line): ?array {
                [$label, $url] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

                if ($label === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
                    return null;
                }

                return ['label' => $label, 'url' => $url];
            })
            ->filter()
            ->values()
            ->all();
    }

    private function storeMedia(UploadedFile $file, string $directory): string
    {
        $path = $file->store("portfolio/{$directory}", 'public');

        return '/storage/'.$path;
    }

    private function deleteStoredMedia(?string $url): void
    {
        if (! $url || ! str_starts_with($url, '/storage/portfolio/')) {
            return;
        }

        Storage::disk('public')->delete(str($url)->after('/storage/')->toString());
    }
}
