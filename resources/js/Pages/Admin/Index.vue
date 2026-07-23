<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    AlertCircle,
    Award,
    BadgeCheck,
    BookOpen,
    BriefcaseBusiness,
    Check,
    Clock3,
    ExternalLink,
    FileBadge,
    Film,
    FolderSync,
    FolderKanban,
    GitCommitHorizontal,
    GraduationCap,
    LogOut,
    MessageSquareQuote,
    Images,
    Pencil,
    Plus,
    RefreshCw,
    Search,
    Star,
    Trash2,
    UserRound,
    Wrench,
    X,
} from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import { gmailComposeUrl } from '../../Support/gmail.js';

const props = defineProps({
    profile: { type: Object, required: true },
    achievements: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    designMedia: { type: Array, default: () => [] },
    portfolioTools: { type: Array, default: () => [] },
    publishedWorks: { type: Array, default: () => [] },
    certificates: { type: Array, default: () => [] },
    credentialBadges: { type: Array, default: () => [] },
    credentialCategories: { type: Array, default: () => [] },
    education: { type: Array, default: () => [] },
    workExperiences: { type: Array, default: () => [] },
    guestbookEntries: { type: Array, default: () => [] },
    pendingReviewCount: { type: Number, default: 0 },
    credentialCount: { type: Number, default: 0 },
    flash: { type: Object, default: () => ({}) },
});

const tabs = [
    { key: 'profile', label: 'Profile', icon: UserRound },
    { key: 'sync', label: 'Sync', icon: RefreshCw },
    { key: 'projects', label: 'Showcase', icon: FolderKanban },
    { key: 'designs', label: 'Designs', icon: Images },
    { key: 'tools', label: 'Tools', icon: Wrench },
    { key: 'publications', label: 'Published', icon: BookOpen },
    { key: 'work', label: 'Work', icon: BriefcaseBusiness },
    { key: 'education', label: 'Education', icon: GraduationCap },
    { key: 'achievements', label: 'Achievements', icon: Award },
    { key: 'badges', label: 'Badges', icon: BadgeCheck },
    { key: 'certificates', label: 'Certificates', icon: FileBadge },
    { key: 'guestbook', label: 'Reviews', icon: MessageSquareQuote },
];

const activeTab = ref('profile');
const syncingSource = ref(null);
const certificateSearch = ref('');
const badgeSearch = ref('');
const editingAchievement = ref(null);
const editingProject = ref(null);
const editingDesign = ref(null);
const editingPortfolioTool = ref(null);
const editingPublication = ref(null);
const editingWorkExperience = ref(null);
const editingEducation = ref(null);
const editingCredentialBadge = ref(null);
const editingCertificate = ref(null);
function guestbookDraft(entry) {
    return {
        name: entry.name,
        email: entry.email,
        role_or_organization: entry.role_or_organization ?? '',
        body: entry.body,
        rating: entry.rating,
        admin_reply: entry.admin_reply ?? '',
    };
}

const guestbookDrafts = ref(Object.fromEntries(
    props.guestbookEntries.map((entry) => [entry.id, guestbookDraft(entry)]),
));

watch(() => props.guestbookEntries, (entries) => {
    for (const entry of entries) {
        if (!guestbookDrafts.value[entry.id]) {
            guestbookDrafts.value[entry.id] = guestbookDraft(entry);
        }
    }
});

const profileForm = useForm({
    _method: 'put',
    display_name: props.profile.display_name ?? '',
    headline: props.profile.headline ?? '',
    availability: props.profile.availability ?? '',
    location: props.profile.location ?? '',
    email: props.profile.email ?? '',
    bio: props.profile.bio ?? '',
    credly_username: props.profile.credly_username ?? '',
    github_username: props.profile.github_username ?? '',
    canva_url: props.profile.canva_url ?? '',
    external_links_text: (props.profile.external_links ?? []).map((link) => `${link.label}|${link.url}`).join('\n'),
    highlights_text: (props.profile.highlights ?? []).join('\n'),
    skills_text: (props.profile.skills ?? []).join('\n'),
    avatar: null,
});

const achievementForm = useForm(emptyAchievement());
const projectForm = useForm(emptyProject());
const designForm = useForm(emptyDesign());
const portfolioToolForm = useForm(emptyPortfolioTool());
const publicationForm = useForm(emptyPublication());
const workExperienceForm = useForm(emptyWorkExperience());
const educationForm = useForm(emptyEducation());
const credentialBadgeForm = useForm(emptyCredentialBadge());
const certificateForm = useForm(emptyCertificate());

const filteredCertificates = computed(() => {
    const term = certificateSearch.value.trim().toLowerCase();
    if (!term) return props.certificates;

    return props.certificates.filter((item) => `${item.title} ${item.issuer ?? ''}`.toLowerCase().includes(term));
});

const filteredCredentialBadges = computed(() => {
    const term = badgeSearch.value.trim().toLowerCase();
    if (!term) return props.credentialBadges;

    return props.credentialBadges.filter((item) => `${item.name} ${item.issuer ?? ''}`.toLowerCase().includes(term));
});

const toolCategoryOptions = [
    { value: 'backend', label: 'Backend' },
    { value: 'frontend', label: 'Frontend' },
    { value: 'automation', label: 'Automation & AI' },
    { value: 'data', label: 'Data & BI' },
    { value: 'design', label: 'Design tools' },
    { value: 'infrastructure', label: 'Infrastructure' },
    { value: 'platforms', label: 'Platforms & marketing' },
    { value: 'development', label: 'Development' },
    { value: 'other', label: 'Other' },
];

function emptyAchievement() {
    return { _method: '', title: '', issuer: '', summary: '', achieved_on: '', external_url: '', tags_text: '', sort_order: 0, is_featured: false, image: null };
}

function emptyProject() {
    return { _method: '', title: '', category: 'Website', summary: '', year: '', url: '', repo_url: '', tags_text: '', sort_order: 0, is_featured: true, thumbnail: null };
}

function emptyDesign() {
    return { _method: '', title: '', media_type: 'image', description: '', year: '', media_url: '', external_url: '', sort_order: 0, is_featured: false, media: null, thumbnail: null };
}

function emptyPortfolioTool() {
    return { _method: '', name: '', category: 'backend', description: '', icon_url: '', external_url: '', sort_order: 0, is_featured: true, icon: null };
}

function emptyPublication() {
    return { _method: '', type: 'article', title: '', publication: '', role: '', summary: '', published_on: '', doi: '', external_url: '', tags_text: '', sort_order: 0, is_featured: true, cover: null };
}

function emptyWorkExperience() {
    return { _method: '', organization: '', position: '', start_date: '', end_date: '', location: '', summary: '', responsibilities_text: '', logo_url: '', logo: null, sort_order: 0, is_current: false };
}

function emptyEducation() {
    return { _method: '', institution: '', program: '', level: '', start_date: '', end_date: '', location: '', description: '', activities_text: '', logo_url: '', logo: null, sort_order: 0 };
}

function emptyCredentialBadge() {
    return { _method: '', name: '', issuer: '', category: 'professional', description: '', issued_at: '', expires_at: '', image_url: '', certificate_url: '', criteria_url: '', evidence_url: '', skills_text: '', sort_order: 0, is_featured: false, image: null };
}

function emptyCertificate() {
    return { _method: '', title: '', issuer: '', category: 'professional', description: '', issued_on: '', file_url: '', tags_text: '', sort_order: 0, is_featured: false, thumbnail: null, document: null };
}

function activate(key, event) {
    activeTab.value = key;
    event?.currentTarget?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function revealEditor() {
    nextTick(() => document.querySelector('.sticky-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
}

function tagsText(item) {
    return (item.tags ?? []).join(', ');
}

function lineText(items) {
    return (items ?? []).join('\n');
}

function editableUrl(url) {
    return /^https?:\/\//.test(url ?? '') ? url : '';
}

function toolCategoryLabel(category) {
    return toolCategoryOptions.find((option) => option.value === category)?.label ?? 'Other';
}

function credentialCategoryLabel(category) {
    return props.credentialCategories.find((option) => option.value === category)?.label ?? 'Professional Development';
}

function submitProfile() {
    profileForm.post('/admin/profile', { forceFormData: true, preserveScroll: true });
}

function syncSource(source) {
    if (syncingSource.value !== null) return;

    syncingSource.value = source;
    router.post(`/admin/sync/${source}`, {}, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => {
            syncingSource.value = null;
        },
    });
}

function editAchievement(item) {
    editingAchievement.value = item.id;
    achievementForm.defaults({ ...emptyAchievement(), ...item, _method: 'put', tags_text: tagsText(item), achieved_on: item.achieved_on?.slice(0, 10) ?? '', image: null });
    achievementForm.reset();
    revealEditor();
}

function resetAchievement() {
    editingAchievement.value = null;
    achievementForm.defaults(emptyAchievement());
    achievementForm.reset();
}

function submitAchievement() {
    achievementForm._method = editingAchievement.value ? 'put' : '';
    const url = editingAchievement.value ? `/admin/achievements/${editingAchievement.value}` : '/admin/achievements';
    achievementForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetAchievement });
}

function editProject(item) {
    editingProject.value = item.id;
    projectForm.defaults({ ...emptyProject(), ...item, _method: 'put', tags_text: tagsText(item), thumbnail: null });
    projectForm.reset();
    revealEditor();
}

function resetProject() {
    editingProject.value = null;
    projectForm.defaults(emptyProject());
    projectForm.reset();
}

function submitProject() {
    projectForm._method = editingProject.value ? 'put' : '';
    const url = editingProject.value ? `/admin/projects/${editingProject.value}` : '/admin/projects';
    projectForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetProject });
}

function editDesign(item) {
    editingDesign.value = item.id;
    designForm.defaults({
        ...emptyDesign(),
        ...item,
        _method: 'put',
        media_url: /^https?:\/\//.test(item.media_url ?? '') ? item.media_url : '',
        media: null,
        thumbnail: null,
    });
    designForm.reset();
    revealEditor();
}

function resetDesign() {
    editingDesign.value = null;
    designForm.defaults(emptyDesign());
    designForm.reset();
}

function submitDesign() {
    designForm._method = editingDesign.value ? 'put' : '';
    const url = editingDesign.value ? `/admin/design-media/${editingDesign.value}` : '/admin/design-media';
    designForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetDesign });
}

function editPortfolioTool(item) {
    editingPortfolioTool.value = item.id;
    portfolioToolForm.defaults({
        ...emptyPortfolioTool(),
        ...item,
        _method: 'put',
        icon_url: editableUrl(item.icon_url),
        icon: null,
    });
    portfolioToolForm.reset();
    revealEditor();
}

function resetPortfolioTool() {
    editingPortfolioTool.value = null;
    portfolioToolForm.defaults(emptyPortfolioTool());
    portfolioToolForm.reset();
}

function submitPortfolioTool() {
    portfolioToolForm._method = editingPortfolioTool.value ? 'put' : '';
    const url = editingPortfolioTool.value ? `/admin/portfolio-tools/${editingPortfolioTool.value}` : '/admin/portfolio-tools';
    portfolioToolForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetPortfolioTool });
}

function editPublication(item) {
    editingPublication.value = item.id;
    publicationForm.defaults({ ...emptyPublication(), ...item, _method: 'put', tags_text: tagsText(item), published_on: item.published_on?.slice(0, 10) ?? '', cover: null });
    publicationForm.reset();
    revealEditor();
}

function resetPublication() {
    editingPublication.value = null;
    publicationForm.defaults(emptyPublication());
    publicationForm.reset();
}

function submitPublication() {
    publicationForm._method = editingPublication.value ? 'put' : '';
    const url = editingPublication.value ? `/admin/published-works/${editingPublication.value}` : '/admin/published-works';
    publicationForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetPublication });
}

function editWorkExperience(item) {
    editingWorkExperience.value = item.id;
    workExperienceForm.defaults({
        ...emptyWorkExperience(),
        ...item,
        _method: 'put',
        responsibilities_text: lineText(item.responsibilities),
        logo_url: editableUrl(item.logo_url),
        logo: null,
    });
    workExperienceForm.reset();
    revealEditor();
}

function resetWorkExperience() {
    editingWorkExperience.value = null;
    workExperienceForm.defaults(emptyWorkExperience());
    workExperienceForm.reset();
}

function submitWorkExperience() {
    workExperienceForm._method = editingWorkExperience.value ? 'put' : '';
    const url = editingWorkExperience.value ? `/admin/work-experiences/${editingWorkExperience.value}` : '/admin/work-experiences';
    workExperienceForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetWorkExperience });
}

function editEducation(item) {
    editingEducation.value = item.id;
    educationForm.defaults({
        ...emptyEducation(),
        ...item,
        _method: 'put',
        activities_text: lineText(item.activities),
        logo_url: editableUrl(item.logo_url),
        logo: null,
    });
    educationForm.reset();
    revealEditor();
}

function resetEducation() {
    editingEducation.value = null;
    educationForm.defaults(emptyEducation());
    educationForm.reset();
}

function submitEducation() {
    educationForm._method = editingEducation.value ? 'put' : '';
    const url = editingEducation.value ? `/admin/education/${editingEducation.value}` : '/admin/education';
    educationForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetEducation });
}

function editCredentialBadge(item) {
    editingCredentialBadge.value = item.id;
    credentialBadgeForm.defaults({
        ...emptyCredentialBadge(),
        ...item,
        _method: 'put',
        issued_at: item.issued_at?.slice(0, 10) ?? '',
        expires_at: item.expires_at?.slice(0, 10) ?? '',
        image_url: editableUrl(item.image_url),
        skills_text: (item.skills ?? []).join(', '),
        image: null,
    });
    credentialBadgeForm.reset();
    revealEditor();
}

function resetCredentialBadge() {
    editingCredentialBadge.value = null;
    credentialBadgeForm.defaults(emptyCredentialBadge());
    credentialBadgeForm.reset();
}

function submitCredentialBadge() {
    credentialBadgeForm._method = editingCredentialBadge.value ? 'put' : '';
    const url = editingCredentialBadge.value ? `/admin/credential-badges/${editingCredentialBadge.value}` : '/admin/credential-badges';
    credentialBadgeForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetCredentialBadge });
}

function editCertificate(item) {
    editingCertificate.value = item.id;
    certificateForm.defaults({ ...emptyCertificate(), ...item, _method: 'put', tags_text: tagsText(item), issued_on: item.issued_on?.slice(0, 10) ?? '', thumbnail: null, document: null });
    certificateForm.reset();
    revealEditor();
}

function resetCertificate() {
    editingCertificate.value = null;
    certificateForm.defaults(emptyCertificate());
    certificateForm.reset();
}

function submitCertificate() {
    certificateForm._method = editingCertificate.value ? 'put' : '';
    const url = editingCertificate.value ? `/admin/certificates/${editingCertificate.value}` : '/admin/certificates';
    certificateForm.post(url, { forceFormData: true, preserveScroll: true, onSuccess: resetCertificate });
}

function remove(url, label) {
    if (window.confirm(`Remove ${label}?`)) {
        router.delete(url, { preserveScroll: true });
    }
}

function updateGuestbook(entry, status = entry.status) {
    const draft = guestbookDrafts.value[entry.id] ?? entry;

    router.put(`/admin/guestbook/${entry.id}`, {
        ...draft,
        status,
    }, { preserveScroll: true });
}

function retryGuestbookNotification(entry) {
    router.post(`/admin/guestbook/${entry.id}/notification`, {}, { preserveScroll: true });
}
</script>

<template>
    <Head title="Portfolio admin">
        <link v-if="profile.avatar_url" rel="icon" :href="profile.avatar_url">
    </Head>

    <div class="admin-shell">
        <header class="admin-header">
            <div class="admin-brand">
                <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="" @error="$event.currentTarget.remove()">
                <UserRound v-else :size="20" />
                <div class="admin-brand-copy">
                    <span class="kicker">Private workspace</span>
                    <strong>Portfolio admin</strong>
                </div>
            </div>
            <div class="header-actions">
                <a class="icon-action" href="/" target="_blank" title="Open public portfolio" aria-label="Open public portfolio">
                    <ExternalLink :size="17" />
                </a>
                <form action="/admin/logout" method="post" @submit.prevent="router.post('/admin/logout')">
                    <button class="icon-action" type="submit" title="Log out" aria-label="Log out">
                        <LogOut :size="17" />
                    </button>
                </form>
            </div>
        </header>

        <nav class="admin-tabs" aria-label="Portfolio editors">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                :class="{ active: activeTab === tab.key }"
                @click="activate(tab.key, $event)"
            >
                <component :is="tab.icon" :size="16" />
                <span>{{ tab.label }}</span>
                <span
                    v-if="tab.key === 'guestbook' && pendingReviewCount > 0"
                    class="tab-badge"
                    :aria-label="`${pendingReviewCount} reviews pending approval`"
                >{{ pendingReviewCount }}</span>
            </button>
        </nav>

        <div v-if="flash?.success" class="notice" role="status">
            <Check :size="16" />
            <span>{{ flash.success }}</span>
        </div>

        <div v-if="flash?.error" class="notice error" role="alert">
            <AlertCircle :size="16" />
            <span>{{ flash.error }}</span>
        </div>

        <main class="admin-main">
            <section v-if="activeTab === 'profile'" class="editor-section">
                <div class="section-intro">
                    <span class="kicker">01 / identity</span>
                    <h1>Profile details</h1>
                    <div class="summary-strip">
                        <span>{{ workExperiences.length }} roles</span>
                        <span>{{ education.length }} schools</span>
                        <span>{{ credentialCount }} credential badges</span>
                    </div>
                </div>

                <form class="data-form" @submit.prevent="submitProfile">
                    <div class="avatar-row">
                        <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="Current profile" class="avatar-preview">
                        <div class="field grow">
                            <label for="avatar">Profile picture</label>
                            <input id="avatar" type="file" accept="image/png,image/jpeg,image/webp" @input="profileForm.avatar = $event.target.files[0]">
                            <span v-if="profileForm.errors.avatar" class="field-error">{{ profileForm.errors.avatar }}</span>
                        </div>
                    </div>
                    <div class="form-grid two">
                        <div class="field"><label for="display-name">Display name</label><input id="display-name" v-model="profileForm.display_name" required><span v-if="profileForm.errors.display_name" class="field-error">{{ profileForm.errors.display_name }}</span></div>
                        <div class="field"><label for="location">Location</label><input id="location" v-model="profileForm.location"></div>
                    </div>
                    <div class="field"><label for="headline">Headline</label><input id="headline" v-model="profileForm.headline" required></div>
                    <div class="field"><label for="availability">Availability line</label><input id="availability" v-model="profileForm.availability"></div>
                    <div class="field"><label for="bio">Biography</label><textarea id="bio" v-model="profileForm.bio" rows="5" required /></div>
                    <div class="form-grid two">
                        <div class="field"><label for="email">Public email</label><input id="email" v-model="profileForm.email" type="email"></div>
                        <div class="field"><label for="github">GitHub username</label><input id="github" v-model="profileForm.github_username"></div>
                        <div class="field"><label for="credly">Credly username</label><input id="credly" v-model="profileForm.credly_username"></div>
                        <div class="field"><label for="canva">Canva showcase URL</label><input id="canva" v-model="profileForm.canva_url" type="url" placeholder="https://www.canva.com/..."></div>
                    </div>
                    <div class="field"><label for="links">External links <small>Label|URL, one per line</small></label><textarea id="links" v-model="profileForm.external_links_text" rows="4" /></div>
                    <div class="form-grid two">
                        <div class="field"><label for="highlights">Highlights <small>one per line</small></label><textarea id="highlights" v-model="profileForm.highlights_text" rows="6" /></div>
                        <div class="field"><label for="skills">Skills <small>one per line</small></label><textarea id="skills" v-model="profileForm.skills_text" rows="6" /></div>
                    </div>
                    <button class="primary-action" type="submit" :disabled="profileForm.processing">Save profile</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'sync'" class="editor-section">
                <div class="section-intro">
                    <span class="kicker">External data</span>
                    <h1>Data sync</h1>
                    <div class="summary-strip">
                        <span>{{ certificates.length }} certificates</span>
                        <span>{{ credentialBadges.length }} badges</span>
                        <span>{{ profile.github_username || 'GitHub not set' }}</span>
                    </div>
                </div>

                <div class="sync-list">
                    <article class="sync-row">
                        <div class="sync-source">
                            <span class="sync-source-icon"><FolderSync :size="19" /></span>
                            <div class="sync-source-copy"><span>Google Drive</span><strong>Certificates</strong></div>
                        </div>
                        <span class="sync-meta">{{ certificates.length }} records</span>
                        <button class="sync-action" type="button" :disabled="syncingSource !== null" @click="syncSource('certificates')">
                            <RefreshCw :size="15" :class="{ 'is-spinning': syncingSource === 'certificates' }" />
                            {{ syncingSource === 'certificates' ? 'Syncing' : 'Sync now' }}
                        </button>
                    </article>

                    <article class="sync-row">
                        <div class="sync-source">
                            <span class="sync-source-icon"><BadgeCheck :size="19" /></span>
                            <div class="sync-source-copy"><span>Credly</span><strong>Credential badges</strong></div>
                        </div>
                        <span class="sync-meta">{{ credentialBadges.length }} records</span>
                        <button class="sync-action" type="button" :disabled="syncingSource !== null" @click="syncSource('badges')">
                            <RefreshCw :size="15" :class="{ 'is-spinning': syncingSource === 'badges' }" />
                            {{ syncingSource === 'badges' ? 'Syncing' : 'Sync now' }}
                        </button>
                    </article>

                    <article class="sync-row">
                        <div class="sync-source">
                            <span class="sync-source-icon"><GitCommitHorizontal :size="19" /></span>
                            <div class="sync-source-copy"><span>GitHub</span><strong>Contribution activity</strong></div>
                        </div>
                        <span class="sync-meta">{{ profile.github_username || 'Not configured' }}</span>
                        <button class="sync-action" type="button" :disabled="syncingSource !== null" @click="syncSource('github')">
                            <RefreshCw :size="15" :class="{ 'is-spinning': syncingSource === 'github' }" />
                            {{ syncingSource === 'github' ? 'Refreshing' : 'Refresh now' }}
                        </button>
                    </article>
                </div>
            </section>

            <section v-else-if="activeTab === 'projects'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">02 / selected work</span><h1>Showcase</h1></div>
                    <article v-for="item in projects" :key="item.id" class="catalog-row">
                        <img v-if="item.thumbnail_url" :src="item.thumbnail_url" alt="" class="row-thumb">
                        <div class="row-copy"><span>{{ item.category || 'Project' }} / {{ item.year || 'Undated' }}</span><strong>{{ item.title }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit project" @click="editProject(item)"><Pencil :size="15" /></button><button type="button" title="Delete project" @click="remove(`/admin/projects/${item.id}`, item.title)"><Trash2 :size="15" /></button></div>
                    </article>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitProject">
                    <div class="form-title"><h2>{{ editingProject ? 'Edit project' : 'Add project' }}</h2><button v-if="editingProject" type="button" class="icon-action" title="Cancel edit" @click="resetProject"><X :size="16" /></button></div>
                    <div class="field"><label>Title</label><input v-model="projectForm.title" required><span v-if="projectForm.errors.title" class="field-error">{{ projectForm.errors.title }}</span></div>
                    <div class="form-grid two"><div class="field"><label>Category</label><input v-model="projectForm.category" placeholder="Website, system, graphic design"></div><div class="field"><label>Year</label><input v-model="projectForm.year"></div></div>
                    <div class="field"><label>Summary</label><textarea v-model="projectForm.summary" rows="5" /></div>
                    <div class="field"><label>Live URL</label><input v-model="projectForm.url" type="url"></div>
                    <div class="field"><label>Repository URL</label><input v-model="projectForm.repo_url" type="url"></div>
                    <div class="field"><label>Tags <small>comma separated</small></label><input v-model="projectForm.tags_text"></div>
                    <div class="field"><label>Project image</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="projectForm.thumbnail = $event.target.files[0]"></div>
                    <div class="form-grid two"><div class="field"><label>Order</label><input v-model.number="projectForm.sort_order" type="number" min="0"></div><label class="check-field"><input v-model="projectForm.is_featured" type="checkbox"><span>Featured</span></label></div>
                    <button class="primary-action" type="submit" :disabled="projectForm.processing"><Plus v-if="!editingProject" :size="16" />{{ editingProject ? 'Update project' : 'Add project' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'designs'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">03 / visual archive</span><h1>Designs & media</h1></div>
                    <article v-for="item in designMedia" :key="item.id" class="catalog-row">
                        <img v-if="item.thumbnail_url || item.media_type !== 'video'" :src="item.thumbnail_url || item.media_url" alt="" class="row-thumb">
                        <span v-else class="row-thumb row-thumb-fallback"><Film :size="18" /></span>
                        <div class="row-copy"><span>{{ item.media_type }} / {{ item.year || 'Undated' }}</span><strong>{{ item.title }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit design media" @click="editDesign(item)"><Pencil :size="15" /></button><button type="button" title="Delete design media" @click="remove(`/admin/design-media/${item.id}`, item.title)"><Trash2 :size="15" /></button></div>
                    </article>
                    <div v-if="!designMedia.length" class="catalog-empty"><Images :size="22" /><span>No design media uploaded yet.</span></div>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitDesign">
                    <div class="form-title"><h2>{{ editingDesign ? 'Edit design media' : 'Add design media' }}</h2><button v-if="editingDesign" type="button" class="icon-action" title="Cancel edit" @click="resetDesign"><X :size="16" /></button></div>
                    <div class="field"><label>Title</label><input v-model="designForm.title" required><span v-if="designForm.errors.title" class="field-error">{{ designForm.errors.title }}</span></div>
                    <div class="form-grid two"><div class="field"><label>Format</label><select v-model="designForm.media_type"><option value="image">Image</option><option value="infographic">Infographic</option><option value="video">Video</option></select></div><div class="field"><label>Year</label><input v-model="designForm.year" placeholder="2026"></div></div>
                    <div class="field"><label>Description</label><textarea v-model="designForm.description" rows="5" /></div>
                    <div class="field"><label>Media file <small>{{ designForm.media_type === 'video' ? 'MP4, WebM, or MOV up to 100 MB' : 'JPG, PNG, or WebP up to 12 MB' }}</small></label><input type="file" :accept="designForm.media_type === 'video' ? 'video/mp4,video/webm,video/quicktime' : 'image/png,image/jpeg,image/webp'" @input="designForm.media = $event.target.files[0]"><span v-if="designForm.errors.media" class="field-error">{{ designForm.errors.media }}</span></div>
                    <div class="field"><label>Direct media URL <small>{{ editingDesign ? 'leave blank to keep the current upload' : 'alternative to uploading' }}</small></label><input v-model="designForm.media_url" type="url"><span v-if="designForm.errors.media_url" class="field-error">{{ designForm.errors.media_url }}</span></div>
                    <div v-if="designForm.media_type === 'video'" class="field"><label>Video poster image</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="designForm.thumbnail = $event.target.files[0]"><span v-if="designForm.errors.thumbnail" class="field-error">{{ designForm.errors.thumbnail }}</span></div>
                    <div class="field"><label>Source / Canva URL</label><input v-model="designForm.external_url" type="url"></div>
                    <div class="form-grid two"><div class="field"><label>Order</label><input v-model.number="designForm.sort_order" type="number" min="0"></div><label class="check-field"><input v-model="designForm.is_featured" type="checkbox"><span>Featured</span></label></div>
                    <button class="primary-action" type="submit" :disabled="designForm.processing"><Plus v-if="!editingDesign" :size="16" />{{ editingDesign ? 'Update media' : 'Add media' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'tools'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">04 / working toolkit</span><h1>Tools & technologies</h1></div>
                    <article v-for="item in portfolioTools" :key="item.id" class="catalog-row">
                        <img v-if="item.icon_url" :src="item.icon_url" alt="" class="row-thumb logo-thumb">
                        <span v-else class="row-thumb row-thumb-fallback"><Wrench :size="18" /></span>
                        <div class="row-copy"><span>{{ toolCategoryLabel(item.category) }}</span><strong>{{ item.name }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit tool" @click="editPortfolioTool(item)"><Pencil :size="15" /></button><button type="button" title="Delete tool" @click="remove(`/admin/portfolio-tools/${item.id}`, item.name)"><Trash2 :size="15" /></button></div>
                    </article>
                    <div v-if="!portfolioTools.length" class="catalog-empty"><Wrench :size="22" /><span>No tools added yet.</span></div>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitPortfolioTool">
                    <div class="form-title"><h2>{{ editingPortfolioTool ? 'Edit tool' : 'Add tool' }}</h2><button v-if="editingPortfolioTool" type="button" class="icon-action" title="Cancel edit" @click="resetPortfolioTool"><X :size="16" /></button></div>
                    <div class="field"><label>Tool name</label><input v-model="portfolioToolForm.name" required><span v-if="portfolioToolForm.errors.name" class="field-error">{{ portfolioToolForm.errors.name }}</span></div>
                    <div class="field"><label>Category</label><select v-model="portfolioToolForm.category"><option v-for="option in toolCategoryOptions" :key="option.value" :value="option.value">{{ option.label }}</option></select></div>
                    <div class="field"><label>How you use it</label><textarea v-model="portfolioToolForm.description" rows="4" maxlength="1000" /></div>
                    <div class="form-grid two"><div class="field"><label>Tool icon</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="portfolioToolForm.icon = $event.target.files[0]"><span v-if="portfolioToolForm.errors.icon" class="field-error">{{ portfolioToolForm.errors.icon }}</span></div><div class="field"><label>Icon URL</label><input v-model="portfolioToolForm.icon_url" type="url" placeholder="https://..."><span v-if="portfolioToolForm.errors.icon_url" class="field-error">{{ portfolioToolForm.errors.icon_url }}</span></div></div>
                    <div class="field"><label>Reference URL</label><input v-model="portfolioToolForm.external_url" type="url" placeholder="https://..."></div>
                    <div class="form-grid two"><div class="field"><label>Order</label><input v-model.number="portfolioToolForm.sort_order" type="number" min="0"></div><label class="check-field"><input v-model="portfolioToolForm.is_featured" type="checkbox"><span>Featured</span></label></div>
                    <button class="primary-action" type="submit" :disabled="portfolioToolForm.processing"><Plus v-if="!editingPortfolioTool" :size="16" />{{ editingPortfolioTool ? 'Update tool' : 'Add tool' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'publications'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">05 / authored work</span><h1>Published</h1></div>
                    <article v-for="item in publishedWorks" :key="item.id" class="catalog-row">
                        <img v-if="item.cover_url" :src="item.cover_url" alt="" class="row-thumb cover">
                        <div class="row-copy"><span>{{ item.type.replaceAll('_', ' ') }}</span><strong>{{ item.title }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit publication" @click="editPublication(item)"><Pencil :size="15" /></button><button type="button" title="Delete publication" @click="remove(`/admin/published-works/${item.id}`, item.title)"><Trash2 :size="15" /></button></div>
                    </article>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitPublication">
                    <div class="form-title"><h2>{{ editingPublication ? 'Edit publication' : 'Add publication' }}</h2><button v-if="editingPublication" type="button" class="icon-action" title="Cancel edit" @click="resetPublication"><X :size="16" /></button></div>
                    <div class="field"><label>Type</label><select v-model="publicationForm.type"><option value="scopus_paper">Scopus paper</option><option value="research_paper">Research paper</option><option value="digital_magazine">Digital magazine</option><option value="article">Article</option><option value="newspaper">Newspaper</option><option value="book_chapter">Book chapter</option><option value="other">Other</option></select></div>
                    <div class="field"><label>Title</label><input v-model="publicationForm.title" required></div>
                    <div class="form-grid two"><div class="field"><label>Publication / journal</label><input v-model="publicationForm.publication"></div><div class="field"><label>Published date</label><input v-model="publicationForm.published_on" type="date"></div></div>
                    <div class="field"><label>Your role</label><input v-model="publicationForm.role" placeholder="Author, researcher, art director"></div>
                    <div class="field"><label>Abstract / summary</label><textarea v-model="publicationForm.summary" rows="5" /></div>
                    <div class="form-grid two"><div class="field"><label>Public URL</label><input v-model="publicationForm.external_url" type="url"></div><div class="field"><label>DOI</label><input v-model="publicationForm.doi"></div></div>
                    <div class="field"><label>Tags <small>comma separated</small></label><input v-model="publicationForm.tags_text"></div>
                    <div class="field"><label>Cover image</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="publicationForm.cover = $event.target.files[0]"></div>
                    <div class="form-grid two"><div class="field"><label>Order</label><input v-model.number="publicationForm.sort_order" type="number" min="0"></div><label class="check-field"><input v-model="publicationForm.is_featured" type="checkbox"><span>Featured</span></label></div>
                    <button class="primary-action" type="submit" :disabled="publicationForm.processing"><Plus v-if="!editingPublication" :size="16" />{{ editingPublication ? 'Update publication' : 'Add publication' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'work'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">06 / professional record</span><h1>Work experience</h1></div>
                    <article v-for="item in workExperiences" :key="item.id" class="catalog-row">
                        <img v-if="item.logo_url" :src="item.logo_url" alt="" class="row-thumb logo-thumb">
                        <span v-else class="row-thumb row-thumb-fallback"><BriefcaseBusiness :size="18" /></span>
                        <div class="row-copy"><span>{{ item.organization }} / {{ item.start_date }} - {{ item.is_current ? 'Present' : (item.end_date || 'Undated') }}</span><strong>{{ item.position }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit work experience" @click="editWorkExperience(item)"><Pencil :size="15" /></button><button type="button" title="Delete work experience" @click="remove(`/admin/work-experiences/${item.id}`, item.position)"><Trash2 :size="15" /></button></div>
                    </article>
                    <div v-if="!workExperiences.length" class="catalog-empty"><BriefcaseBusiness :size="22" /><span>No work experience added yet.</span></div>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitWorkExperience">
                    <div class="form-title"><h2>{{ editingWorkExperience ? 'Edit work experience' : 'Add work experience' }}</h2><button v-if="editingWorkExperience" type="button" class="icon-action" title="Cancel edit" @click="resetWorkExperience"><X :size="16" /></button></div>
                    <div class="field"><label>Organization</label><input v-model="workExperienceForm.organization" required><span v-if="workExperienceForm.errors.organization" class="field-error">{{ workExperienceForm.errors.organization }}</span></div>
                    <div class="field"><label>Position</label><input v-model="workExperienceForm.position" required><span v-if="workExperienceForm.errors.position" class="field-error">{{ workExperienceForm.errors.position }}</span></div>
                    <div class="form-grid two"><div class="field"><label>Start</label><input v-model="workExperienceForm.start_date" required placeholder="January 2024"></div><div class="field"><label>End</label><input v-model="workExperienceForm.end_date" :disabled="workExperienceForm.is_current" placeholder="June 2026"></div></div>
                    <div class="form-grid two"><div class="field"><label>Location</label><input v-model="workExperienceForm.location"></div><label class="check-field"><input v-model="workExperienceForm.is_current" type="checkbox"><span>Current role</span></label></div>
                    <div class="field"><label>Summary</label><textarea v-model="workExperienceForm.summary" rows="4" /></div>
                    <div class="field"><label>Responsibilities <small>one per line</small></label><textarea v-model="workExperienceForm.responsibilities_text" rows="6" /></div>
                    <div class="form-grid two"><div class="field"><label>Organization logo</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="workExperienceForm.logo = $event.target.files[0]"><span v-if="workExperienceForm.errors.logo" class="field-error">{{ workExperienceForm.errors.logo }}</span></div><div class="field"><label>Logo URL</label><input v-model="workExperienceForm.logo_url" type="url" placeholder="https://..."><span v-if="workExperienceForm.errors.logo_url" class="field-error">{{ workExperienceForm.errors.logo_url }}</span></div></div>
                    <div class="field"><label>Order</label><input v-model.number="workExperienceForm.sort_order" type="number" min="0"></div>
                    <button class="primary-action" type="submit" :disabled="workExperienceForm.processing"><Plus v-if="!editingWorkExperience" :size="16" />{{ editingWorkExperience ? 'Update experience' : 'Add experience' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'education'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">07 / academic record</span><h1>Education</h1></div>
                    <article v-for="item in education" :key="item.id" class="catalog-row">
                        <img v-if="item.logo_url" :src="item.logo_url" alt="" class="row-thumb logo-thumb">
                        <span v-else class="row-thumb row-thumb-fallback"><GraduationCap :size="18" /></span>
                        <div class="row-copy"><span>{{ item.institution }} / {{ item.end_date || item.start_date || 'Undated' }}</span><strong>{{ item.program }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit education" @click="editEducation(item)"><Pencil :size="15" /></button><button type="button" title="Delete education" @click="remove(`/admin/education/${item.id}`, item.program)"><Trash2 :size="15" /></button></div>
                    </article>
                    <div v-if="!education.length" class="catalog-empty"><GraduationCap :size="22" /><span>No education added yet.</span></div>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitEducation">
                    <div class="form-title"><h2>{{ editingEducation ? 'Edit education' : 'Add education' }}</h2><button v-if="editingEducation" type="button" class="icon-action" title="Cancel edit" @click="resetEducation"><X :size="16" /></button></div>
                    <div class="field"><label>Institution</label><input v-model="educationForm.institution" required><span v-if="educationForm.errors.institution" class="field-error">{{ educationForm.errors.institution }}</span></div>
                    <div class="field"><label>Program / degree</label><input v-model="educationForm.program" required><span v-if="educationForm.errors.program" class="field-error">{{ educationForm.errors.program }}</span></div>
                    <div class="form-grid two"><div class="field"><label>Level</label><input v-model="educationForm.level" placeholder="Graduate degree"></div><div class="field"><label>Location</label><input v-model="educationForm.location"></div></div>
                    <div class="form-grid two"><div class="field"><label>Start</label><input v-model="educationForm.start_date" placeholder="2024"></div><div class="field"><label>End / graduation</label><input v-model="educationForm.end_date" placeholder="June 2026"></div></div>
                    <div class="field"><label>Description</label><textarea v-model="educationForm.description" rows="4" /></div>
                    <div class="field"><label>Activities & distinctions <small>one per line</small></label><textarea v-model="educationForm.activities_text" rows="5" /></div>
                    <div class="form-grid two"><div class="field"><label>Institution logo</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="educationForm.logo = $event.target.files[0]"><span v-if="educationForm.errors.logo" class="field-error">{{ educationForm.errors.logo }}</span></div><div class="field"><label>Logo URL</label><input v-model="educationForm.logo_url" type="url" placeholder="https://..."><span v-if="educationForm.errors.logo_url" class="field-error">{{ educationForm.errors.logo_url }}</span></div></div>
                    <div class="field"><label>Order</label><input v-model.number="educationForm.sort_order" type="number" min="0"></div>
                    <button class="primary-action" type="submit" :disabled="educationForm.processing"><Plus v-if="!editingEducation" :size="16" />{{ editingEducation ? 'Update education' : 'Add education' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'achievements'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">08 / milestones</span><h1>Achievements</h1></div>
                    <article v-for="item in achievements" :key="item.id" class="catalog-row">
                        <img v-if="item.image_url" :src="item.image_url" alt="" class="row-thumb">
                        <div class="row-copy"><span>{{ item.issuer || 'Achievement' }}</span><strong>{{ item.title }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit achievement" @click="editAchievement(item)"><Pencil :size="15" /></button><button type="button" title="Delete achievement" @click="remove(`/admin/achievements/${item.id}`, item.title)"><Trash2 :size="15" /></button></div>
                    </article>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitAchievement">
                    <div class="form-title"><h2>{{ editingAchievement ? 'Edit achievement' : 'Add achievement' }}</h2><button v-if="editingAchievement" type="button" class="icon-action" title="Cancel edit" @click="resetAchievement"><X :size="16" /></button></div>
                    <div class="field"><label>Title</label><input v-model="achievementForm.title" required></div>
                    <div class="form-grid two"><div class="field"><label>Issuer / organization</label><input v-model="achievementForm.issuer"></div><div class="field"><label>Date</label><input v-model="achievementForm.achieved_on" type="date"></div></div>
                    <div class="field"><label>Summary</label><textarea v-model="achievementForm.summary" rows="5" /></div>
                    <div class="field"><label>Evidence URL</label><input v-model="achievementForm.external_url" type="url"></div>
                    <div class="field"><label>Tags <small>comma separated</small></label><input v-model="achievementForm.tags_text"></div>
                    <div class="field"><label>Image</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="achievementForm.image = $event.target.files[0]"></div>
                    <div class="form-grid two"><div class="field"><label>Order</label><input v-model.number="achievementForm.sort_order" type="number" min="0"></div><label class="check-field"><input v-model="achievementForm.is_featured" type="checkbox"><span>Featured</span></label></div>
                    <button class="primary-action" type="submit" :disabled="achievementForm.processing"><Plus v-if="!editingAchievement" :size="16" />{{ editingAchievement ? 'Update achievement' : 'Add achievement' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'badges'" class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">09 / verified credentials</span><h1>Credential badges</h1></div>
                    <div class="search-field"><Search :size="16" /><input v-model="badgeSearch" type="search" placeholder="Search badges" aria-label="Search badges"></div>
                    <article v-for="item in filteredCredentialBadges" :key="item.id" class="catalog-row">
                        <img v-if="item.image_url" :src="item.image_url" alt="" class="row-thumb logo-thumb">
                        <span v-else class="row-thumb row-thumb-fallback"><BadgeCheck :size="18" /></span>
                        <div class="row-copy"><span>{{ credentialCategoryLabel(item.category) }} / {{ item.issuer || item.provider }}</span><strong>{{ item.name }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit credential badge" @click="editCredentialBadge(item)"><Pencil :size="15" /></button><button type="button" title="Delete credential badge" @click="remove(`/admin/credential-badges/${item.id}`, item.name)"><Trash2 :size="15" /></button></div>
                    </article>
                    <div v-if="!filteredCredentialBadges.length" class="catalog-empty"><BadgeCheck :size="22" /><span>No matching badges.</span></div>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitCredentialBadge">
                    <div class="form-title"><h2>{{ editingCredentialBadge ? 'Edit credential badge' : 'Add credential badge' }}</h2><button v-if="editingCredentialBadge" type="button" class="icon-action" title="Cancel edit" @click="resetCredentialBadge"><X :size="16" /></button></div>
                    <div class="field"><label>Badge name</label><input v-model="credentialBadgeForm.name" required><span v-if="credentialBadgeForm.errors.name" class="field-error">{{ credentialBadgeForm.errors.name }}</span></div>
                    <div class="field"><label>Issuer</label><input v-model="credentialBadgeForm.issuer"></div>
                    <div class="field"><label>Archive category</label><select v-model="credentialBadgeForm.category"><option v-for="category in credentialCategories" :key="category.value" :value="category.value">{{ category.label }}</option></select><span v-if="credentialBadgeForm.errors.category" class="field-error">{{ credentialBadgeForm.errors.category }}</span></div>
                    <div class="form-grid two"><div class="field"><label>Issued date</label><input v-model="credentialBadgeForm.issued_at" type="date"></div><div class="field"><label>Expiration date</label><input v-model="credentialBadgeForm.expires_at" type="date"><span v-if="credentialBadgeForm.errors.expires_at" class="field-error">{{ credentialBadgeForm.errors.expires_at }}</span></div></div>
                    <div class="field"><label>Description</label><textarea v-model="credentialBadgeForm.description" rows="5" /></div>
                    <div class="form-grid two"><div class="field"><label>Badge image</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="credentialBadgeForm.image = $event.target.files[0]"><span v-if="credentialBadgeForm.errors.image" class="field-error">{{ credentialBadgeForm.errors.image }}</span></div><div class="field"><label>Image URL</label><input v-model="credentialBadgeForm.image_url" type="url" placeholder="https://..."><span v-if="credentialBadgeForm.errors.image_url" class="field-error">{{ credentialBadgeForm.errors.image_url }}</span></div></div>
                    <div class="field"><label>Public credential URL</label><input v-model="credentialBadgeForm.certificate_url" type="url"></div>
                    <div class="form-grid two"><div class="field"><label>Criteria URL</label><input v-model="credentialBadgeForm.criteria_url" type="url"></div><div class="field"><label>Evidence URL</label><input v-model="credentialBadgeForm.evidence_url" type="url"></div></div>
                    <div class="field"><label>Skills <small>comma separated</small></label><input v-model="credentialBadgeForm.skills_text"></div>
                    <div class="form-grid two"><div class="field"><label>Order</label><input v-model.number="credentialBadgeForm.sort_order" type="number" min="0"></div><label class="check-field"><input v-model="credentialBadgeForm.is_featured" type="checkbox"><span>Featured</span></label></div>
                    <button class="primary-action" type="submit" :disabled="credentialBadgeForm.processing"><Plus v-if="!editingCredentialBadge" :size="16" />{{ editingCredentialBadge ? 'Update badge' : 'Add badge' }}</button>
                </form>
            </section>

            <section v-else-if="activeTab === 'guestbook'" class="editor-section">
                <div class="section-intro compact">
                    <span class="kicker">11 / moderation</span>
                    <h1>Reviews & messages</h1>
                    <div class="summary-strip">
                        <span>{{ guestbookEntries.filter((entry) => entry.status === 'pending').length }} pending</span>
                        <span>{{ guestbookEntries.filter((entry) => entry.status === 'approved').length }} approved</span>
                    </div>
                </div>

                <div class="moderation-list">
                    <form v-for="entry in guestbookEntries" :key="entry.id" class="moderation-entry" @submit.prevent="updateGuestbook(entry)">
                        <div class="moderation-head">
                            <div>
                                <span class="status-chip" :class="entry.status">{{ entry.status }}</span>
                                <h2>{{ entry.name }}</h2>
                                <a
                                    :href="gmailComposeUrl(entry.email, { subject: 'Regarding your portfolio review' })"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >{{ entry.email }}</a>
                                <span>{{ entry.role_or_organization || entry.created_at }}</span>
                            </div>
                            <div v-if="entry.rating" class="moderation-stars" :aria-label="`${entry.rating} out of 5 stars`">
                                <Star v-for="star in 5" :key="star" :size="14" :fill="star <= entry.rating ? 'currentColor' : 'none'" />
                            </div>
                        </div>
                        <div class="review-edit-grid">
                            <label><span>Name</span><input v-model="guestbookDrafts[entry.id].name" required maxlength="120"></label>
                            <label><span>Email</span><input v-model="guestbookDrafts[entry.id].email" type="email" required maxlength="255"></label>
                            <label><span>Role or organization</span><input v-model="guestbookDrafts[entry.id].role_or_organization" maxlength="180"></label>
                            <label><span>Rating</span><select v-model.number="guestbookDrafts[entry.id].rating"><option :value="null">No rating</option><option v-for="rating in 5" :key="rating" :value="rating">{{ rating }} star{{ rating === 1 ? '' : 's' }}</option></select></label>
                        </div>
                        <label class="review-body-field">
                            <span>Public review</span>
                            <textarea v-model="guestbookDrafts[entry.id].body" rows="5" minlength="12" maxlength="1500" required />
                        </label>
                        <div class="notification-state" :class="entry.notification_status">
                            <div class="notification-state-head">
                                <Check v-if="entry.notification_status === 'accepted'" :size="15" />
                                <AlertCircle v-else-if="entry.notification_status === 'failed'" :size="15" />
                                <Clock3 v-else :size="15" />
                                <span>
                                    <strong v-if="entry.notification_status === 'accepted'">Email transport accepted</strong>
                                    <strong v-else-if="entry.notification_status === 'failed'">Email notification failed</strong>
                                    <strong v-else>Email notification not attempted</strong>
                                    <small>{{ entry.notification_accepted_at || entry.notification_attempted_at || 'No attempt recorded' }}</small>
                                </span>
                                <button type="button" @click="retryGuestbookNotification(entry)"><RefreshCw :size="14" />Retry email</button>
                            </div>
                            <p v-if="entry.notification_error">{{ entry.notification_error }}</p>
                        </div>
                        <label class="reply-field">
                            <span>Your public reply</span>
                            <textarea v-model="guestbookDrafts[entry.id].admin_reply" rows="3" maxlength="2000" />
                        </label>
                        <div class="moderation-actions">
                            <button type="submit" class="approve"><Check :size="15" />Save changes</button>
                            <button v-if="entry.status !== 'approved'" type="button" @click="updateGuestbook(entry, 'approved')"><Check :size="15" />Publish</button>
                            <button v-if="entry.status !== 'hidden'" type="button" @click="updateGuestbook(entry, 'hidden')"><X :size="15" />Hide</button>
                            <button type="button" class="danger" @click="remove(`/admin/guestbook/${entry.id}`, entry.name)"><Trash2 :size="15" />Delete</button>
                        </div>
                    </form>
                    <div v-if="!guestbookEntries.length" class="moderation-empty"><MessageSquareQuote :size="24" /><p>No visitor notes yet.</p></div>
                </div>
            </section>

            <section v-else class="editor-section catalog-layout">
                <div class="catalog-list">
                    <div class="section-intro compact"><span class="kicker">10 / evidence archive</span><h1>Certificates</h1></div>
                    <div class="search-field"><Search :size="16" /><input v-model="certificateSearch" type="search" placeholder="Search certificates" aria-label="Search certificates"></div>
                    <article v-for="item in filteredCertificates" :key="item.id" class="catalog-row">
                        <img v-if="item.thumbnail_url" :src="item.thumbnail_url" alt="" class="row-thumb cover">
                        <div class="row-copy"><span>{{ credentialCategoryLabel(item.category) }} / {{ item.issuer || item.source }}</span><strong>{{ item.title }}</strong></div>
                        <div class="row-actions"><button type="button" title="Edit certificate" @click="editCertificate(item)"><Pencil :size="15" /></button><button type="button" title="Delete certificate" @click="remove(`/admin/certificates/${item.id}`, item.title)"><Trash2 :size="15" /></button></div>
                    </article>
                </div>
                <form class="data-form sticky-form" @submit.prevent="submitCertificate">
                    <div class="form-title"><h2>{{ editingCertificate ? 'Edit certificate' : 'Add certificate' }}</h2><button v-if="editingCertificate" type="button" class="icon-action" title="Cancel edit" @click="resetCertificate"><X :size="16" /></button></div>
                    <div class="field"><label>Title</label><input v-model="certificateForm.title" required><span v-if="certificateForm.errors.title" class="field-error">{{ certificateForm.errors.title }}</span></div>
                    <div class="form-grid two"><div class="field"><label>Issuer</label><input v-model="certificateForm.issuer"></div><div class="field"><label>Issued date</label><input v-model="certificateForm.issued_on" type="date"></div></div>
                    <div class="field"><label>Archive category</label><select v-model="certificateForm.category"><option v-for="category in credentialCategories" :key="category.value" :value="category.value">{{ category.label }}</option></select><span v-if="certificateForm.errors.category" class="field-error">{{ certificateForm.errors.category }}</span></div>
                    <div class="field"><label>Description</label><textarea v-model="certificateForm.description" rows="4" /></div>
                    <div class="field"><label>Certificate URL</label><input v-model="certificateForm.file_url" type="url"><span v-if="certificateForm.errors.file_url" class="field-error">{{ certificateForm.errors.file_url }}</span></div>
                    <div class="form-grid two"><div class="field"><label>PDF document</label><input type="file" accept="application/pdf" @input="certificateForm.document = $event.target.files[0]"></div><div class="field"><label>Preview image</label><input type="file" accept="image/png,image/jpeg,image/webp" @input="certificateForm.thumbnail = $event.target.files[0]"></div></div>
                    <div class="field"><label>Tags <small>comma separated</small></label><input v-model="certificateForm.tags_text"></div>
                    <div class="form-grid two"><div class="field"><label>Order</label><input v-model.number="certificateForm.sort_order" type="number" min="0"></div><label class="check-field"><input v-model="certificateForm.is_featured" type="checkbox"><span>Featured</span></label></div>
                    <button class="primary-action" type="submit" :disabled="certificateForm.processing"><Plus v-if="!editingCertificate" :size="16" />{{ editingCertificate ? 'Update certificate' : 'Add certificate' }}</button>
                </form>
            </section>
        </main>
    </div>
</template>

<style scoped>
.admin-shell { min-height: 100vh; background: var(--profile-50); color: var(--profile-ink); }
.admin-header { position: sticky; top: 0; z-index: 30; display: flex; align-items: center; justify-content: space-between; gap: 1rem; border-bottom: 1px solid var(--profile-200); background: color-mix(in srgb, var(--profile-bg) 94%, transparent); padding: 0.8rem clamp(1rem, 4vw, 2.5rem); backdrop-filter: blur(16px); }
.admin-brand { display: flex; min-width: 0; align-items: center; gap: 0.7rem; }
.admin-brand > img, .admin-brand > svg { width: 2.35rem; height: 2.35rem; flex: 0 0 auto; border-radius: 50%; object-fit: cover; object-position: center top; }
.admin-brand > svg { border: 1px solid var(--profile-300); padding: 0.45rem; }
.admin-brand-copy { display: grid; min-width: 0; gap: 0.15rem; }
.admin-header strong, .kicker, label, .admin-tabs, .row-copy span, .summary-strip { font-family: var(--font-mono); }
.admin-header strong { font-size: 0.82rem; text-transform: uppercase; }
.kicker { color: var(--profile-500); font-size: 0.66rem; text-transform: uppercase; }
.header-actions, .row-actions, .form-title, .notice, .summary-strip, .search-field { display: flex; align-items: center; }
.header-actions { gap: 0.45rem; }
.icon-action, .row-actions button { display: inline-grid; width: 2.25rem; height: 2.25rem; place-items: center; border: 1px solid var(--profile-300); border-radius: 4px; background: var(--profile-bg); color: var(--profile-ink); cursor: pointer; }
.admin-tabs { position: sticky; top: 3.85rem; z-index: 20; display: flex; gap: 0.35rem; overflow-x: auto; border-bottom: 1px solid var(--profile-200); background: var(--profile-bg); padding: 0.6rem clamp(1rem, 4vw, 2.5rem); }
.admin-tabs button { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 0.45rem; border: 1px solid transparent; border-radius: 4px; background: transparent; padding: 0.55rem 0.7rem; color: var(--profile-500); font-size: 0.7rem; text-transform: uppercase; cursor: pointer; }
.admin-tabs button.active { border-color: var(--profile-ink); background: var(--profile-ink); color: var(--profile-bg); }
.tab-badge { display: inline-grid; min-width: 1.25rem; height: 1.25rem; place-items: center; border-radius: 999px; background: #b42318; padding: 0 0.3rem; color: #fff; font-size: 0.65rem; font-weight: 700; line-height: 1; }
.admin-tabs button.active .tab-badge { background: var(--profile-bg); color: var(--profile-ink); }
.notice { position: fixed; right: 1rem; bottom: 1rem; z-index: 50; gap: 0.55rem; border: 1px solid #157347; border-radius: 4px; background: #ecfdf3; padding: 0.7rem 0.9rem; color: #11633d; box-shadow: var(--profile-shadow); }
.notice.error { border-color: #b42318; background: #fff1f0; color: #9f1c13; }
.admin-main { width: min(100%, 86rem); margin: 0 auto; padding: clamp(1rem, 4vw, 2.5rem); }
.editor-section { display: grid; gap: 1.25rem; }
.section-intro { padding: 1rem 0 0.5rem; }
.section-intro.compact { padding-top: 0; }
h1 { margin: 0.35rem 0 0; font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1; }
h2 { margin: 0; font-size: 1.05rem; }
.summary-strip { flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; color: var(--profile-500); font-size: 0.68rem; text-transform: uppercase; }
.summary-strip span { border: 1px solid var(--profile-200); background: var(--profile-bg); padding: 0.35rem 0.5rem; }
.data-form { display: grid; gap: 1rem; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-bg); padding: clamp(1rem, 3vw, 1.5rem); }
.avatar-row { display: flex; align-items: center; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--profile-200); }
.avatar-preview { width: 5rem; height: 5rem; border: 1px solid var(--profile-200); border-radius: 50%; object-fit: cover; }
.grow { flex: 1; }
.form-grid { display: grid; gap: 1rem; }
.form-grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.field { min-width: 0; display: grid; gap: 0.4rem; align-content: start; }
label { color: var(--profile-700); font-size: 0.68rem; text-transform: uppercase; }
label small { color: var(--profile-400); font-size: inherit; text-transform: none; }
input, textarea, select { width: 100%; min-width: 0; border: 1px solid var(--profile-300); border-radius: 4px; outline: 0; background: var(--profile-bg); padding: 0.7rem 0.75rem; color: var(--profile-ink); }
textarea { resize: vertical; line-height: 1.55; }
input:focus, textarea:focus, select:focus { border-color: var(--profile-ink); box-shadow: 0 0 0 2px color-mix(in srgb, var(--profile-ink) 12%, transparent); }
input[type='file'] { padding: 0.55rem; }
input:disabled { cursor: not-allowed; opacity: 0.55; }
.field-error { color: #b42318; font-size: 0.75rem; }
.check-field { display: flex; align-items: center; gap: 0.55rem; align-self: end; min-height: 2.7rem; }
.check-field input { width: 1rem; height: 1rem; }
.primary-action { display: inline-flex; width: fit-content; align-items: center; justify-content: center; gap: 0.5rem; border: 1px solid var(--profile-ink); border-radius: 4px; background: var(--profile-ink); padding: 0.72rem 1rem; color: var(--profile-bg); font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; cursor: pointer; }
.primary-action:disabled { cursor: wait; opacity: 0.55; }
.sync-list { display: grid; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-bg); }
.sync-row { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: center; gap: 1rem; padding: 1rem; }
.sync-row + .sync-row { border-top: 1px solid var(--profile-200); }
.sync-source { display: flex; min-width: 0; align-items: center; gap: 0.8rem; }
.sync-source-icon { display: grid; width: 2.65rem; height: 2.65rem; flex: 0 0 auto; place-items: center; border: 1px solid var(--profile-300); border-radius: 4px; background: var(--profile-50); }
.sync-source-copy { display: grid; min-width: 0; gap: 0.18rem; }
.sync-source-copy span, .sync-meta, .sync-action { font-family: var(--font-mono); }
.sync-source-copy span { color: var(--profile-500); font-size: 0.62rem; text-transform: uppercase; }
.sync-source-copy strong { overflow-wrap: anywhere; font-size: 0.9rem; }
.sync-meta { color: var(--profile-500); font-size: 0.66rem; text-transform: uppercase; }
.sync-action { display: inline-flex; min-width: 7.4rem; align-items: center; justify-content: center; gap: 0.45rem; border: 1px solid var(--profile-ink); border-radius: 4px; background: var(--profile-ink); padding: 0.62rem 0.75rem; color: var(--profile-bg); font-size: 0.66rem; text-transform: uppercase; cursor: pointer; }
.sync-action:disabled { cursor: wait; opacity: 0.55; }
.is-spinning { animation: admin-spin 0.8s linear infinite; }
@keyframes admin-spin { to { transform: rotate(360deg); } }
.catalog-layout { grid-template-columns: minmax(0, 1.25fr) minmax(20rem, 0.75fr); align-items: start; }
.catalog-list { display: grid; gap: 0.55rem; }
.catalog-row { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 0.8rem; align-items: center; border: 1px solid var(--profile-200); border-radius: 5px; background: var(--profile-bg); padding: 0.7rem; }
.row-thumb { width: 3rem; height: 3rem; border: 1px solid var(--profile-200); border-radius: 3px; object-fit: cover; }
.row-thumb-fallback { display: grid; place-items: center; color: var(--profile-500); }
.row-thumb.cover { object-position: top; }
.row-thumb.logo-thumb { background: #fff; object-fit: contain; padding: 0.3rem; }
.row-copy { min-width: 0; display: grid; gap: 0.2rem; }
.row-copy span { color: var(--profile-500); font-size: 0.62rem; text-transform: uppercase; }
.row-copy strong { overflow: hidden; font-size: 0.84rem; text-overflow: ellipsis; white-space: nowrap; }
.row-actions { gap: 0.35rem; }
.row-actions button { width: 2rem; height: 2rem; }
.row-actions button:last-child:hover { border-color: #b42318; color: #b42318; }
.sticky-form { position: sticky; top: 8rem; max-height: calc(100vh - 9rem); overflow-y: auto; }
.form-title { justify-content: space-between; gap: 1rem; padding-bottom: 0.7rem; border-bottom: 1px solid var(--profile-200); }
.search-field { gap: 0.5rem; border: 1px solid var(--profile-300); border-radius: 4px; background: var(--profile-bg); padding: 0 0.65rem; }
.search-field input { border: 0; box-shadow: none; }
.moderation-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
.moderation-entry { display: grid; gap: 1rem; border: 1px solid var(--profile-200); border-radius: 5px; background: var(--profile-bg); padding: 1rem; }
.moderation-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
.moderation-head > div:first-child { display: grid; gap: 0.25rem; }
.moderation-head h2 { margin-top: 0.35rem; }
.moderation-head a, .moderation-head > div:first-child > span:last-child, .reply-field > span { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.64rem; }
.status-chip { width: fit-content; border: 1px solid var(--profile-300); border-radius: 999px; padding: 0.2rem 0.45rem; font-family: var(--font-mono); font-size: 0.57rem; text-transform: uppercase; }
.status-chip.approved { border-color: #157347; color: #157347; }
.status-chip.hidden { border-color: #b42318; color: #b42318; }
.moderation-stars { display: flex; gap: 0.1rem; }
.moderation-entry blockquote { margin: 0; color: var(--profile-700); font-family: var(--font-serif); font-size: 1rem; line-height: 1.6; }
.review-edit-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
.review-edit-grid label, .review-body-field { display: grid; gap: 0.35rem; }
.review-edit-grid label > span, .review-body-field > span { color: var(--profile-500); font-size: 0.62rem; }
.review-body-field textarea { min-height: 7rem; }
.notification-state { display: grid; gap: 0.55rem; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-50); padding: 0.65rem; }
.notification-state.accepted { border-color: #8bc7a5; }
.notification-state.failed { border-color: #e6a09a; background: #fff8f7; }
.notification-state-head { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 0.55rem; align-items: center; }
.notification-state-head > svg { color: var(--profile-500); }
.notification-state.accepted .notification-state-head > svg { color: #157347; }
.notification-state.failed .notification-state-head > svg { color: #b42318; }
.notification-state-head > span { display: grid; gap: 0.12rem; }
.notification-state-head strong, .notification-state-head small, .notification-state-head button { font-family: var(--font-mono); }
.notification-state-head strong { font-size: 0.66rem; text-transform: uppercase; }
.notification-state-head small { color: var(--profile-500); font-size: 0.58rem; }
.notification-state-head button { display: inline-flex; align-items: center; gap: 0.3rem; border: 1px solid var(--profile-300); border-radius: 4px; background: var(--profile-bg); padding: 0.42rem 0.5rem; color: var(--profile-ink); font-size: 0.6rem; text-transform: uppercase; cursor: pointer; }
.notification-state > p { margin: 0; color: #9f1c13; font-family: var(--font-mono); font-size: 0.62rem; line-height: 1.5; overflow-wrap: anywhere; }
.reply-field { display: grid; gap: 0.4rem; }
.reply-field textarea { min-height: 5rem; }
.moderation-actions { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.moderation-actions button { display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid var(--profile-300); border-radius: 4px; background: var(--profile-bg); padding: 0.5rem 0.6rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; cursor: pointer; }
.moderation-actions button.approve { border-color: var(--profile-ink); background: var(--profile-ink); color: var(--profile-bg); }
.moderation-actions button.danger { color: #b42318; }
.moderation-empty { display: flex; min-height: 12rem; grid-column: 1 / -1; align-items: center; justify-content: center; gap: 0.7rem; border: 1px solid var(--profile-200); color: var(--profile-500); }
.catalog-empty { display: flex; min-height: 8rem; align-items: center; justify-content: center; gap: 0.6rem; border: 1px solid var(--profile-200); color: var(--profile-500); font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; }

@media (max-width: 880px) {
    .catalog-layout { grid-template-columns: 1fr; }
    .sticky-form { position: static; order: -1; max-height: none; }
    .moderation-list { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
    .form-grid.two { grid-template-columns: 1fr; }
    .review-edit-grid { grid-template-columns: 1fr; }
    .admin-tabs { top: 3.75rem; }
    .avatar-row { align-items: flex-start; flex-direction: column; }
    .catalog-row { grid-template-columns: minmax(0, 1fr) auto; }
    .row-thumb { display: none; }
    .sync-row { grid-template-columns: minmax(0, 1fr) auto; }
    .sync-meta { grid-column: 1 / -1; grid-row: 2; }
}
</style>
