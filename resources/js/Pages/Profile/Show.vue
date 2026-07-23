<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Award,
    BarChart3,
    Bot,
    BookOpen,
    BriefcaseBusiness,
    CheckCircle2,
    Code2,
    Database,
    ExternalLink,

    FileHeart,
    FileText,
    GraduationCap,
    GitBranch,
    Images,
    Layout,
    Mail,
    MapPin,
    Maximize2,
    MessageSquareQuote,
    Moon,
    Network,
    Newspaper,
    Palette,
    Search,
    Send,
    Star,
    Store,
    Sun,
    Wrench,
    X,
} from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import SeoHead from '../../Components/SeoHead.vue';
import { gmailComposeUrl } from '../../Support/gmail.js';

const props = defineProps({
    seo: { type: Object, required: true },
    profile: { type: Object, required: true },
    achievements: { type: Array, default: () => [] },
    badges: { type: Array, default: () => [] },
    certificates: { type: Array, default: () => [] },
    designMedia: { type: Array, default: () => [] },
    education: { type: Array, default: () => [] },
    githubActivity: { type: Object, default: null },
    guestbookEntries: { type: Array, default: () => [] },
    portfolioTools: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    publishedWorks: { type: Array, default: () => [] },
    workExperiences: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
});

const activeProjectCategory = ref('all');
const activeDesignType = ref('all');
const activeWorkType = ref('all');
const portfolioShell = ref(null);
const portraitForwardVideo = ref(null);
const portraitReverseVideo = ref(null);
const activePortraitDirection = ref(1);
const hoveredBadge = ref(null);
const isDark = ref(false);
const publicationSearch = ref('');
const selectedBadge = ref(null);
const selectedCertificate = ref(null);
const selectedBackground = ref(null);
const selectedDesign = ref(null);
const selectedProject = ref(null);
const theme = ref('system');
const guestbookForm = useForm({
    name: '',
    email: '',
    role_or_organization: '',
    body: '',
    rating: 5,
    website: '',
});

let gradientFrame = null;
let gradientX = 50;
let gradientY = 18;
let gradientTargetX = 50;
let gradientTargetY = 18;
let portraitAnimationDirection = 1;
let pendingPortraitDirection = null;
let portraitPlaybackToken = 0;

const projectCategories = computed(() => [
    { key: 'all', label: 'All work' },
    ...[...new Set(props.projects.map((item) => item.category).filter(Boolean))]
        .map((category) => ({ key: category, label: category })),
]);

const filteredProjects = computed(() => props.projects.filter((item) => (
    activeProjectCategory.value === 'all' || item.category === activeProjectCategory.value
)));

const designTypes = computed(() => [
    { key: 'all', label: 'All media' },
    ...[...new Set(props.designMedia.map((item) => item.media_type).filter(Boolean))]
        .map((type) => ({ key: type, label: labelFor(type) })),
]);

const filteredDesignMedia = computed(() => props.designMedia.filter((item) => (
    activeDesignType.value === 'all' || item.media_type === activeDesignType.value
)));

const curatedDesignMedia = computed(() => {
    const selections = [
        (item) => item.media_type === 'video' && item.title === 'University Intramurials Team Indtroductions',
        (item) => item.title === 'Instagram Post for Spa',
        (item) => item.title === 'Business Card',
        (item) => item.title === 'Business Logo',
        (item) => item.title === 'Coffeee Sip Poster',
        (item) => item.title === 'Brochure Design',
    ];

    return selections.map((matches) => props.designMedia.find(matches)).filter(Boolean);
});
const profilePortraitForwardUrl = '/storage/portfolio/Videos/Profile%20Video.mp4';
const profilePortraitReverseUrl = '/storage/portfolio/profile/portrait-reversed.mp4';

const workTypes = computed(() => [
    { key: 'all', label: 'All formats' },
    ...[...new Set(props.publishedWorks.map((item) => item.type))]
        .map((type) => ({ key: type, label: labelFor(type) })),
]);

const filteredWorks = computed(() => {
    const term = publicationSearch.value.trim().toLowerCase();

    return props.publishedWorks.filter((work) => {
        const matchesType = activeWorkType.value === 'all' || work.type === activeWorkType.value;
        const content = [work.title, work.publication, work.role, work.summary, ...(work.tags ?? [])]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return matchesType && (!term || content.includes(term));
    });
});

const visibleBadges = computed(() => props.badges.slice(0, 6));
const visibleCertificates = computed(() => props.certificates.slice(0, 6));
const previewBadge = computed(() => hoveredBadge.value ?? selectedBadge.value ?? props.badges[0] ?? null);
function achievementLogoUrl(achievement) {
    if (achievement.image_url) return achievement.image_url;

    return String(achievement.issuer ?? '').trim().toLowerCase() === 'sap'
        ? '/images/brands/sap.svg'
        : null;
}

const toolCategoryDefinitions = [
    { key: 'backend', label: 'Backend', icon: Database },
    { key: 'frontend', label: 'Frontend', icon: Layout },
    { key: 'automation', label: 'Automation & AI', icon: Bot },
    { key: 'data', label: 'Data & BI', icon: BarChart3 },
    { key: 'design', label: 'Design tools', icon: Palette },
    { key: 'infrastructure', label: 'Infrastructure', icon: Network },
    { key: 'platforms', label: 'Platforms & marketing', icon: Store },
    { key: 'development', label: 'Development', icon: GitBranch },
    { key: 'other', label: 'Other', icon: Wrench },
];

const groupedPortfolioTools = computed(() => toolCategoryDefinitions
    .map((category) => ({
        ...category,
        tools: props.portfolioTools.filter((tool) => tool.category === category.key),
    }))
    .filter((category) => category.tools.length));

function toolFallbackIcon(tool) {
    const name = String(tool.name ?? '').toLowerCase();

    if (name.includes('sql')) return Database;
    if (name.includes('medical record')) return FileHeart;

    return null;
}

function labelFor(value) {
    return String(value ?? '').replaceAll('_', ' ').replace(/\b\w/g, (match) => match.toUpperCase());
}

function initials(value) {
    return String(value ?? 'BM')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
}

function iconForWork(type) {
    if (type === 'digital_magazine') return BookOpen;
    if (type === 'newspaper' || type === 'article') return Newspaper;
    return FileText;
}

function projectIcon(category) {
    const value = String(category ?? '').toLowerCase();
    if (value.includes('design') || value.includes('graphic')) return Palette;
    if (value.includes('web') || value.includes('system')) return Code2;
    return BriefcaseBusiness;
}

function isPowerBiProject(project) {
    try {
        const url = new URL(project?.url ?? '');

        return url.protocol === 'https:'
            && url.hostname.toLowerCase() === 'app.powerbi.com'
            && url.pathname.startsWith('/view');
    } catch {
        return false;
    }
}

function contributionTitle(day) {
    const levels = ['No', 'Low', 'Moderate', 'High', 'Very high'];
    return `${levels[day.level]} contribution activity on ${day.date}`;
}

function openBadge(badge) {
    selectedBadge.value = badge;
}

function closeBadge() {
    selectedBadge.value = null;
}

function openBackground(type, item) {
    selectedBackground.value = { type, item };
}

function closeBackground() {
    selectedBackground.value = null;
}

function openCertificate(certificate) {
    selectedCertificate.value = certificate;
}

function closeCertificate() {
    selectedCertificate.value = null;
}

function openDesign(item) {
    selectedDesign.value = item;
}

function closeDesign() {
    selectedDesign.value = null;
}

function openProject(project) {
    selectedProject.value = project;
}

function closeProject() {
    selectedProject.value = null;
}

function applyTheme() {
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = theme.value === 'dark' || (theme.value === 'system' && systemDark);
    document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
}

function portraitVideoFor(direction) {
    return direction === 1 ? portraitForwardVideo.value : portraitReverseVideo.value;
}

function stopPortraitPlayback() {
    portraitPlaybackToken += 1;
    portraitForwardVideo.value?.pause();
    portraitReverseVideo.value?.pause();
}

function playPortrait(direction) {
    const target = portraitVideoFor(direction);
    const sourceVideo = portraitVideoFor(direction * -1);

    if (!target) return;

    if (target.readyState < 3) {
        pendingPortraitDirection = direction;
        return;
    }

    stopPortraitPlayback();
    pendingPortraitDirection = null;
    const playbackToken = portraitPlaybackToken;
    const switchingDirection = activePortraitDirection.value !== direction;
    const mirroredTime = switchingDirection && sourceVideo?.readyState >= 1
        ? Math.max(0, Math.min(target.duration, target.duration - sourceVideo.currentTime))
        : (target.ended || target.currentTime >= target.duration - 0.05 ? 0 : target.currentTime);

    const beginPlayback = () => {
        if (playbackToken !== portraitPlaybackToken) return;

        activePortraitDirection.value = direction;
        void target.play().catch(() => target.pause());
    };

    if (Math.abs(target.currentTime - mirroredTime) < 0.04) {
        beginPlayback();
        return;
    }

    target.addEventListener('seeked', beginPlayback, { once: true });
    target.currentTime = mirroredTime;
}

function initializePortraitVideo(direction) {
    const video = portraitVideoFor(direction);

    if (!video) return;

    video.pause();
    video.currentTime = 0;
}

function resumePendingPortrait(direction) {
    if (pendingPortraitDirection === direction) playPortrait(direction);
}
function setTheme(value) {
    theme.value = value;

    if (portraitAnimationDirection === 1 && portraitReverseVideo.value?.preload !== 'auto') {
        portraitReverseVideo.value.preload = 'auto';
        portraitReverseVideo.value.load();
    }

    playPortrait(portraitAnimationDirection);
    portraitAnimationDirection *= -1;
}

function submitGuestbook() {
    guestbookForm.post('/guestbook', {
        preserveScroll: true,
        onSuccess: () => guestbookForm.reset('body', 'website'),
    });
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        closeBadge();
        closeCertificate();
        closeDesign();
        closeProject();
    }
}

function renderGradientPosition() {
    gradientX += (gradientTargetX - gradientX) * 0.12;
    gradientY += (gradientTargetY - gradientY) * 0.12;

    portfolioShell.value?.style.setProperty('--gradient-x', `${gradientX.toFixed(2)}%`);
    portfolioShell.value?.style.setProperty('--gradient-y', `${gradientY.toFixed(2)}%`);
    portfolioShell.value?.style.setProperty('--gradient-counter-x', `${(100 - gradientX).toFixed(2)}%`);
    portfolioShell.value?.style.setProperty('--gradient-counter-y', `${Math.min(88, 36 + gradientY * 0.48).toFixed(2)}%`);

    if (Math.abs(gradientTargetX - gradientX) > 0.04 || Math.abs(gradientTargetY - gradientY) > 0.04) {
        gradientFrame = window.requestAnimationFrame(renderGradientPosition);
    } else {
        gradientFrame = null;
    }
}

function queueGradientPosition() {
    if (gradientFrame === null) {
        gradientFrame = window.requestAnimationFrame(renderGradientPosition);
    }
}

function moveGradient(event) {
    if (event.pointerType === 'touch' || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    gradientTargetX = Math.max(4, Math.min(96, (event.clientX / window.innerWidth) * 100));
    gradientTargetY = Math.max(5, Math.min(95, (event.clientY / window.innerHeight) * 100));
    queueGradientPosition();
}

function resetGradient() {
    gradientTargetX = 50;
    gradientTargetY = 18;
    queueGradientPosition();
}

onMounted(() => {
    theme.value = window.localStorage.getItem('profile-theme') ?? 'system';
    applyTheme();
    window.addEventListener('keydown', onKeydown);
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
});

onBeforeUnmount(() => {
    if (gradientFrame !== null) window.cancelAnimationFrame(gradientFrame);
    stopPortraitPlayback();
    window.removeEventListener('keydown', onKeydown);
    window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', applyTheme);
});

watch(theme, (value) => {
    window.localStorage.setItem('profile-theme', value);
    applyTheme();
});

watch([selectedBadge, selectedCertificate, selectedDesign, selectedProject], ([badge, certificate, design, project]) => {
    document.body.style.overflow = badge || certificate || design || project ? 'hidden' : '';
});
</script>

<template>
    <SeoHead :seo="seo" />

    <div ref="portfolioShell" class="portfolio-shell" @pointermove.passive="moveGradient" @pointerleave="resetGradient">
        <div class="gemini-wash" aria-hidden="true"></div>
        <header class="topbar">
            <a class="brand" href="#profile" :aria-label="`${profile.display_name} home`">
                <span class="brand-mark">
                    <span>{{ initials(profile.display_name) }}</span>
                    <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="" @error="$event.currentTarget.remove()">
                </span>
                <span class="brand-name">{{ profile.display_name }}</span>
            </a>

            <nav class="nav-links" aria-label="Portfolio sections">
                <a href="#work">Work</a>
                <Link href="/services">Services</Link>
                <a href="#designs">Designs</a>
                <a href="#published">Published</a>
                <a href="#credentials">Credentials</a>
                <a href="#tools">Tools</a>
                <a href="#background">Background</a>
                <a href="#guestbook">Reviews</a>
            </nav>

            <div class="theme-switch" aria-label="Color theme">
                <button type="button" title="Light theme" aria-label="Light theme" :class="{ active: !isDark }" @click="setTheme('light')"><Sun :size="15" /></button>
                <button type="button" title="Dark theme" aria-label="Dark theme" :class="{ active: isDark }" @click="setTheme('dark')"><Moon :size="15" /></button>
            </div>
        </header>

        <main>
            <section id="profile" class="hero section-wrap">
                <div class="folio-label">Portfolio / 2026</div>
                <div class="hero-grid">
                    <p class="hero-role">{{ profile.headline }}</p>

                    <div class="hero-copy">
                        <h1>{{ profile.display_name }}</h1>
                        <p class="hero-bio">{{ profile.bio }}</p>

                        <div class="location-line">
                            <span v-if="profile.location"><MapPin :size="14" />{{ profile.location }}</span>
                            <span v-if="profile.availability"><CheckCircle2 :size="14" />{{ profile.availability }}</span>
                        </div>

                        <div class="hero-links" aria-label="Profile links">
                            <a v-for="link in profile.external_links" :key="link.url" :href="link.url" target="_blank" rel="noreferrer">
                                {{ link.label }}<ArrowUpRight :size="14" />
                            </a>
                            <a v-if="profile.email" :href="gmailComposeUrl(profile.email)" target="_blank" rel="noreferrer"><Mail :size="14" />Email</a>
                        </div>
                    </div>

                    <figure class="portrait-block">
                        <div v-if="profilePortraitForwardUrl" class="portrait-video-stage">
                            <video ref="portraitForwardVideo" :class="{ 'is-active': activePortraitDirection === 1 }" :src="profilePortraitForwardUrl" muted playsinline preload="auto" fetchpriority="high" disablepictureinpicture disableremoteplayback controlslist="nodownload noplaybackrate noremoteplayback" :aria-label="`${profile.display_name} animated portrait`" @loadedmetadata="initializePortraitVideo(1)" @canplay="resumePendingPortrait(1)"></video>
                            <video ref="portraitReverseVideo" :class="{ 'is-active': activePortraitDirection === -1 }" :src="profilePortraitReverseUrl" muted playsinline preload="metadata" disablepictureinpicture disableremoteplayback controlslist="nodownload noplaybackrate noremoteplayback" aria-hidden="true" @loadedmetadata="initializePortraitVideo(-1)" @canplay="resumePendingPortrait(-1)"></video>
                        </div>
                        <img v-else-if="profile.avatar_url" :src="profile.avatar_url" :alt="profile.display_name">
                        <div v-else class="portrait-fallback">{{ initials(profile.display_name) }}</div>
                        <figcaption>
                            <span>Information Analyst</span>
                            <span>Research / Programming / Design</span>
                        </figcaption>
                    </figure>
                </div>

                <div class="stat-line" aria-label="Portfolio summary">
                    <div v-for="stat in profile.stats" :key="stat.label"><strong>{{ stat.value }}</strong><span>{{ stat.label }}</span></div>
                </div>
            </section>

            <section id="work" class="content-band section-wrap">
                <div class="section-head">
                    <div><span class="folio-label">01 / practice</span><h2>Systems Development</h2></div>
                    <p>Websites, information systems, implementation work, and visual design.</p>
                </div>

                <div class="filter-row" aria-label="Project categories">
                    <button v-for="category in projectCategories" :key="category.key" type="button" :class="{ active: activeProjectCategory === category.key }" @click="activeProjectCategory = category.key">{{ category.label }}</button>
                </div>

                <div class="project-grid">
                    <article v-for="(project, index) in filteredProjects" :key="project.id" class="project-card">
                        <div class="project-index" aria-hidden="true">{{ String(index + 1).padStart(2, '0') }}</div>
                        <div class="project-copy">
                            <div class="meta-line"><span>{{ project.category || 'Selected project' }}</span><span>{{ project.year }}</span></div>
                            <h3>{{ project.title }}</h3>
                            <p>{{ project.summary }}</p>
                            <div class="tag-row"><span v-for="tag in project.tags" :key="tag">{{ tag }}</span></div>
                            <button class="project-more" type="button" aria-haspopup="dialog" @click="openProject(project)">View more<ArrowUpRight :size="14" /></button>
                        </div>
                    </article>
                </div>
            </section>

            <section id="designs" class="content-band section-wrap design-band">
                <div class="section-head">
                    <div><span class="folio-label">02 / visual archive</span><h2>Design & media</h2></div>
                    <div class="section-actions"><Link class="section-link" href="/designs">Design archive<ArrowUpRight :size="14" /></Link><a v-if="profile.canva_url" class="section-link" :href="profile.canva_url" target="_blank" rel="noreferrer"><Palette :size="15" />Canva showcase<ArrowUpRight :size="14" /></a></div>
                </div>

                <div v-if="curatedDesignMedia.length" class="design-grid">
                    <article v-for="(item, index) in curatedDesignMedia" :key="item.id" class="design-card">
                        <div class="design-media">
                            <video v-if="item.media_type === 'video'" muted playsinline preload="metadata" :poster="item.thumbnail_url || undefined"><source :src="item.media_url"></video>
                            <iframe v-else-if="item.media_type === 'pdf'" :src="`${item.media_url}#page=1&toolbar=0&navpanes=0&scrollbar=0`" :title="`${item.title} preview`" tabindex="-1"></iframe>
                            <img v-else :src="item.media_url" :alt="item.title" loading="lazy">
                            <span class="design-expand"><ArrowUpRight :size="16" /></span>
                        </div>
                        <div class="design-copy">
                            <div class="meta-line"><span>{{ String(index + 1).padStart(2, '0') }} / {{ item.type_label }}</span><span>{{ item.year }}</span></div>
                            <h3>{{ item.title }}</h3>
                        </div>
                        <button class="design-card-trigger" type="button" :aria-label="`Open full view of ${item.title}`" @click="openDesign(item)"></button>
                    </article>
                </div>

                <Link v-if="designMedia.length" class="archive-route" href="/designs">Explore more designs — {{ designMedia.length }} works<ArrowUpRight :size="14" /></Link>

                <div v-if="!curatedDesignMedia.length" class="design-empty">
                    <Images :size="28" />
                    <div><strong>Visual archive in progress</strong><p>Selected image, infographic, and motion work will appear here.</p></div>
                </div>
            </section>

            <section id="published" class="content-band section-wrap">
                <div class="section-head controls-head">
                    <div><span class="folio-label">03 / authored</span><h2>Published works</h2></div>
                    <label class="search-box">
                        <Search :size="15" />
                        <input v-model="publicationSearch" type="search" placeholder="Search archive" aria-label="Search published works">
                    </label>
                </div>

                <div class="filter-row" aria-label="Publication types">
                    <button v-for="type in workTypes" :key="type.key" type="button" :class="{ active: activeWorkType === type.key }" @click="activeWorkType = type.key">{{ type.label }}</button>
                </div>

                <div class="publication-list">
                    <article v-for="work in filteredWorks" :key="work.id" class="publication-row">
                        <div class="publication-cover">
                            <img v-if="work.cover_url" :src="work.cover_url" :alt="`${work.title} cover`" loading="lazy">
                            <component v-else :is="iconForWork(work.type)" :size="27" />
                        </div>
                        <div class="publication-main">
                            <div class="meta-line"><span>{{ work.type_label }}</span><span>{{ work.published_on }}</span></div>
                            <h3>{{ work.title }}</h3>
                            <p class="publication-name">{{ work.publication }}</p>
                            <p>{{ work.summary }}</p>
                            <div class="tag-row"><span v-for="tag in work.tags" :key="tag">{{ tag }}</span></div>
                        </div>
                        <div class="publication-action">
                            <a v-if="work.external_url" :href="work.external_url" target="_blank" rel="noreferrer" title="Open publication" aria-label="Open publication"><ArrowUpRight :size="18" /></a>
                            <a v-else-if="work.doi" :href="`https://doi.org/${work.doi}`" target="_blank" rel="noreferrer" title="Open DOI" aria-label="Open DOI"><ArrowUpRight :size="18" /></a>
                        </div>
                    </article>
                </div>
            </section>

            <section v-if="githubActivity" id="github" class="content-band section-wrap github-band">
                <div class="section-head">
                    <div><span class="folio-label">04 / open source</span><h2>GitHub activity</h2></div>
                    <a class="section-link" :href="githubActivity.profile_url" target="_blank" rel="noreferrer"><Code2 :size="15" />@{{ githubActivity.username }}<ArrowUpRight :size="14" /></a>
                </div>
                <div class="contribution-panel">
                    <div class="contribution-summary"><strong>{{ githubActivity.total ?? 0 }}</strong><span>public contributions in the last year</span></div>
                    <div class="contribution-scroll">
                        <div class="contribution-grid" role="img" :aria-label="`${githubActivity.total ?? 0} GitHub contributions from ${githubActivity.from} to ${githubActivity.to}`">
                            <div v-for="week in githubActivity.weeks" :key="week.start_date" class="contribution-week">
                                <span v-for="day in week.days" :key="day.date" class="contribution-day" :class="`level-${day.level}`" :title="contributionTitle(day)" />
                            </div>
                        </div>
                    </div>
                    <div class="contribution-legend"><span>{{ githubActivity.from }}</span><div><span>Less</span><i v-for="level in 5" :key="level" :class="`level-${level - 1}`" /><span>More</span></div><span>{{ githubActivity.to }}</span></div>
                </div>
            </section>

            <section v-if="achievements.length" id="achievements" class="content-band section-wrap">
                <div class="section-head"><div><span class="folio-label">05 / milestones</span><h2>Achievements</h2></div></div>
                <div class="achievement-grid">
                    <article v-for="achievement in achievements" :key="achievement.id" class="achievement-item">
                        <div class="achievement-mark">
                            <img v-if="achievementLogoUrl(achievement)" :src="achievementLogoUrl(achievement)" :alt="achievement.title" loading="lazy">
                            <Award v-else :size="24" />
                        </div>
                        <div class="meta-line"><span>{{ achievement.issuer || 'Milestone' }}</span><span>{{ achievement.achieved_on }}</span></div>
                        <h3>{{ achievement.title }}</h3>
                        <p>{{ achievement.summary }}</p>
                        <a v-if="achievement.external_url" :href="achievement.external_url" target="_blank" rel="noreferrer" class="inline-link">Evidence<ArrowUpRight :size="14" /></a>
                    </article>
                </div>
            </section>

            <section id="credentials" class="content-band section-wrap">
                <div class="section-head">
                    <div><span class="folio-label">06 / verified</span><h2>Credential badges</h2></div>
                    <div class="section-actions"><Link class="section-link" href="/badges">Badge archive<ArrowUpRight :size="14" /></Link><a class="section-link" :href="`https://www.credly.com/users/${profile.credly_username}`" target="_blank" rel="noreferrer">Credly profile<ArrowUpRight :size="14" /></a></div>
                </div>

                <div class="badge-layout">
                    <div class="badge-grid">
                        <button v-for="badge in visibleBadges" :key="badge.id" class="badge-card" type="button" @mouseenter="hoveredBadge = badge" @mouseleave="hoveredBadge = null" @focus="hoveredBadge = badge" @click="openBadge(badge)">
                            <span class="badge-image">
                                <img v-if="badge.image_url" :src="badge.image_url" :alt="badge.name" loading="lazy">
                                <span v-else>{{ initials(badge.name) }}</span>
                            </span>
                            <span class="badge-copy"><small>{{ badge.issuer || 'Credential' }}</small><strong>{{ badge.name }}</strong><span>{{ badge.issued_at }}</span></span>
                        </button>
                    </div>

                    <aside v-if="previewBadge" class="badge-preview">
                        <span class="folio-label">Certificate preview</span>
                        <img v-if="previewBadge.image_url" :src="previewBadge.image_url" :alt="previewBadge.name">
                        <div v-else class="preview-fallback">{{ initials(previewBadge.name) }}</div>
                        <p>{{ previewBadge.issuer }}</p>
                        <h3>{{ previewBadge.name }}</h3>
                        <button type="button" @click="openBadge(previewBadge)">View details<ArrowUpRight :size="14" /></button>
                    </aside>
                </div>
                <Link v-if="badges.length" class="archive-route" href="/badges">Explore all {{ badges.length }} verified badges<ArrowUpRight :size="14" /></Link>
            </section>

            <section v-if="certificates.length" id="certificates" class="content-band section-wrap">
                <div class="section-head">
                    <div><span class="folio-label">07 / archive</span><h2>Certificates</h2></div>
                    <Link class="section-link" href="/certifications">Certificate archive<ArrowUpRight :size="14" /></Link>
                </div>

                <div class="certificate-grid">
                    <button
                        v-for="certificate in visibleCertificates"
                        :key="certificate.id"
                        class="certificate-card"
                        type="button"
                        :aria-label="'View ' + certificate.title + ' details'"
                        @click="openCertificate(certificate)"
                    >
                        <span class="certificate-art">
                            <img v-if="certificate.thumbnail_url" :src="certificate.thumbnail_url" :alt="certificate.provider_name + ' logo'" loading="lazy">
                            <strong v-else class="provider-initials">{{ certificate.provider_initials }}</strong>
                        </span>
                        <span class="certificate-copy"><small>{{ certificate.provider_name || certificate.issuer || 'Certificate' }}</small><strong>{{ certificate.title }}</strong><span>{{ certificate.issued_on }}</span></span>
                        <Maximize2 :size="16" />
                    </button>
                </div>
                <Link class="archive-route" href="/certifications">Browse all {{ certificates.length }} certificates<ArrowUpRight :size="14" /></Link>
            </section>

            <section id="background" class="content-band section-wrap background-band">
                <div class="section-head"><div><span class="folio-label">08 / background</span><h2>Experience & education</h2></div></div>
                <div class="background-grid">
                    <div class="timeline-group">
                        <div class="timeline-title"><BriefcaseBusiness :size="18" /><span>Experience</span></div>
                        <button v-for="role in workExperiences" :key="role.id" class="timeline-row timeline-trigger" type="button" :aria-label="`View details for ${role.position} at ${role.organization}`" @click="openBackground('experience', role)">
                            <span class="timeline-date">{{ role.start_date }}<br>{{ role.is_current ? 'Present' : role.end_date }}</span>
                            <span class="timeline-entry">
                                <span class="organization-mark" aria-hidden="true"><span>{{ initials(role.organization) }}</span><img v-if="role.logo_url" :src="role.logo_url" alt="" loading="lazy" @error="$event.currentTarget.remove()"></span>
                                <span class="timeline-copy"><span class="meta-line"><span>{{ role.organization }}</span></span><strong class="timeline-heading">{{ role.position }}</strong><span class="timeline-summary">{{ role.summary }}</span><span class="tag-row"><span v-for="item in role.responsibilities" :key="item">{{ item }}</span></span></span>
                            </span>
                            <Maximize2 class="timeline-expand" :size="15" aria-hidden="true" />
                        </button>
                    </div>
                    <div class="timeline-group">
                        <div class="timeline-title"><GraduationCap :size="18" /><span>Education</span></div>
                        <button v-for="item in education" :key="item.id" class="timeline-row timeline-trigger education-row" type="button" :aria-label="`View details for ${item.program} at ${item.institution}`" @click="openBackground('education', item)">
                            <span class="timeline-date">{{ item.start_date }}<br>{{ item.end_date }}</span>
                            <span class="timeline-entry">
                                <span class="organization-mark" aria-hidden="true"><span>{{ initials(item.institution) }}</span><img v-if="item.logo_url" :src="item.logo_url" alt="" loading="lazy" @error="$event.currentTarget.remove()"></span>
                                <span class="timeline-copy"><span class="meta-line school-name"><span>{{ item.institution }}</span></span><strong class="timeline-heading">{{ item.program }}</strong><span class="timeline-summary">{{ item.level }}</span><span class="tag-row"><span v-for="activity in item.activities" :key="activity">{{ activity }}</span></span></span>
                            </span>
                            <Maximize2 class="timeline-expand" :size="15" aria-hidden="true" />
                        </button>
                    </div>
                </div>
                <div class="skills-index"><span class="folio-label">Core capabilities</span><div><span v-for="skill in profile.skills" :key="skill">{{ skill }}</span></div></div>
            </section>

            <section v-if="portfolioTools.length" id="tools" class="content-band section-wrap tools-band">
                <div class="section-head">
                    <div><span class="folio-label">09 / toolkit</span><h2>Tools & technologies</h2></div>
                    <p>The platforms used to build systems, shape interfaces, analyze information, and produce visual work.</p>
                </div>

                <div class="tool-groups">
                    <section v-for="(category, categoryIndex) in groupedPortfolioTools" :key="category.key" class="tool-category">
                        <header class="tool-category-head">
                            <span>{{ String(categoryIndex + 1).padStart(2, '0') }}</span>
                            <component :is="category.icon" :size="18" />
                            <h3>{{ category.label }}</h3>
                        </header>
                        <div class="tool-list">
                            <component
                                :is="tool.external_url ? 'a' : 'div'"
                                v-for="tool in category.tools"
                                :key="tool.id"
                                class="tool-entry"
                                :href="tool.external_url || undefined"
                                :target="tool.external_url ? '_blank' : undefined"
                                :rel="tool.external_url ? 'noreferrer' : undefined"
                            >
                                <span class="tool-mark">
                                    <component v-if="toolFallbackIcon(tool)" :is="toolFallbackIcon(tool)" :size="23" />
                                    <span v-else>{{ initials(tool.name) }}</span>
                                    <img v-if="tool.icon_url" :src="tool.icon_url" :alt="tool.name" loading="lazy" @error="$event.currentTarget.remove()">
                                </span>
                                <span class="tool-copy"><strong>{{ tool.name }}</strong><span>{{ tool.description }}</span></span>
                                <ArrowUpRight v-if="tool.external_url" :size="15" />
                            </component>
                        </div>
                    </section>
                </div>
            </section>

            <section class="collaboration-bridge section-wrap" aria-labelledby="collaboration-title">
                <div>
                    <span class="folio-label">10 / collaborate</span>
                    <h2 id="collaboration-title">Let's work together.</h2>
                    <p>Need a clearer system, better use of data, stronger digital operations, or a practical session for your team? Explore the services I can shape around your goals.</p>
                </div>
                <div class="collaboration-actions">
                    <Link href="/services">See services<ArrowUpRight :size="15" /></Link>
                    <a
                        v-if="profile.email"
                        :href="gmailComposeUrl(profile.email, { subject: 'Project inquiry' })"
                        target="_blank"
                        rel="noopener noreferrer"
                    >Request a quote<ArrowUpRight :size="15" /></a>
                </div>
            </section>

            <section id="guestbook" class="content-band section-wrap guestbook-band">
                <div class="section-head">
                    <div><span class="folio-label">11 / reviews</span><h2>Reviews</h2></div>
                    <p>Share a thought about the work, a possible collaboration, or your experience.</p>
                </div>

                <div class="guestbook-layout">
                    <form class="guestbook-form" @submit.prevent="submitGuestbook">
                        <div class="form-heading"><MessageSquareQuote :size="20" /><h3>Leave a review</h3></div>
                        <div class="guestbook-fields two">
                            <label><span>Name</span><input v-model="guestbookForm.name" required maxlength="120"></label>
                            <label><span>Email <small>private</small></span><input v-model="guestbookForm.email" type="email" required maxlength="255"></label>
                        </div>
                        <label><span>Role or organization</span><input v-model="guestbookForm.role_or_organization" maxlength="180"></label>
                        <label><span>Review</span><textarea v-model="guestbookForm.body" rows="6" minlength="12" maxlength="1500" required /></label>
                        <div class="rating-field">
                            <span>Rating</span>
                            <div>
                                <button v-for="rating in 5" :key="rating" type="button" :aria-label="`${rating} star rating`" :aria-pressed="guestbookForm.rating === rating" @click="guestbookForm.rating = rating">
                                    <Star :size="18" :fill="rating <= guestbookForm.rating ? 'currentColor' : 'none'" />
                                </button>
                            </div>
                        </div>
                        <label class="honeypot" aria-hidden="true">Website<input v-model="guestbookForm.website" tabindex="-1" autocomplete="off"></label>
                        <p v-if="guestbookForm.hasErrors" class="form-error">Please review the highlighted information and try again.</p>
                        <p v-if="flash?.success" class="form-success">{{ flash.success }}</p>
                        <button class="submit-note" type="submit" :disabled="guestbookForm.processing"><Send :size="15" />{{ guestbookForm.processing ? 'Submitting' : 'Submit review' }}</button>
                    </form>

                    <div class="review-list">
                        <article v-for="entry in guestbookEntries" :key="entry.public_id" class="review-card">
                            <div class="review-head"><div><strong>{{ entry.name }}</strong><span>{{ entry.role_or_organization || entry.approved_at }}</span></div><div v-if="entry.rating" class="review-stars" :aria-label="`${entry.rating} out of 5 stars`"><Star v-for="star in 5" :key="star" :size="13" :fill="star <= entry.rating ? 'currentColor' : 'none'" /></div></div>
                            <blockquote>{{ entry.body }}</blockquote>
                            <div v-if="entry.admin_reply" class="owner-reply"><span>Reply / Britt</span><p>{{ entry.admin_reply }}</p></div>
                        </article>
                        <div v-if="!guestbookEntries.length" class="guestbook-empty"><MessageSquareQuote :size="24" /><p>No reviews yet. Be the first to share one.</p></div>
                    </div>
                </div>
            </section>

            <footer class="section-wrap footer">
                <div><span class="folio-label">End / index</span><h2>Let the work speak.<br>Then let&#39;s talk.</h2></div>
                <div class="footer-links">
                    <a v-if="profile.email" :href="gmailComposeUrl(profile.email)" target="_blank" rel="noreferrer">{{ profile.email }}<ArrowUpRight :size="14" /></a>
                    <a v-for="link in profile.external_links" :key="link.url" :href="link.url" target="_blank" rel="noreferrer">{{ link.label }}<ArrowUpRight :size="14" /></a>
                </div>
            </footer>
        </main>

        <Transition name="modal">
            <div v-if="selectedBadge" class="modal-backdrop" role="presentation" @click.self="closeBadge">
                <article class="badge-modal" role="dialog" aria-modal="true" :aria-labelledby="`badge-title-${selectedBadge.id}`">
                    <button class="icon-button modal-close" type="button" title="Close" aria-label="Close" @click="closeBadge"><X :size="17" /></button>
                    <div class="modal-badge"><img v-if="selectedBadge.image_url" :src="selectedBadge.image_url" :alt="selectedBadge.name"><span v-else>{{ initials(selectedBadge.name) }}</span></div>
                    <span class="folio-label">{{ selectedBadge.issuer || 'Verified credential' }}</span>
                    <h2 :id="`badge-title-${selectedBadge.id}`">{{ selectedBadge.name }}</h2>
                    <p>{{ selectedBadge.description }}</p>
                    <dl><div><dt>Issued</dt><dd>{{ selectedBadge.issued_at || 'Public profile' }}</dd></div><div v-if="selectedBadge.expires_at"><dt>Expires</dt><dd>{{ selectedBadge.expires_at }}</dd></div></dl>
                    <div class="tag-row"><span v-for="skill in selectedBadge.skills" :key="skill">{{ skill }}</span></div>
                    <div class="modal-actions"><a v-if="selectedBadge.certificate_url" :href="selectedBadge.certificate_url" target="_blank" rel="noreferrer">Open certificate<ExternalLink :size="14" /></a><a v-if="selectedBadge.criteria_url" :href="selectedBadge.criteria_url" target="_blank" rel="noreferrer">Criteria<ArrowUpRight :size="14" /></a></div>
                </article>
            </div>
        </Transition>

        <Transition name="modal">
            <div v-if="selectedCertificate" class="modal-backdrop" role="presentation" @click.self="closeCertificate">
                <article class="badge-modal certificate-modal" role="dialog" aria-modal="true" :aria-labelledby="'certificate-title-' + selectedCertificate.id">
                    <button class="icon-button modal-close" type="button" title="Close" aria-label="Close" @click="closeCertificate"><X :size="17" /></button>
                    <div class="modal-badge certificate-provider-mark">
                        <img v-if="selectedCertificate.thumbnail_url" :src="selectedCertificate.thumbnail_url" :alt="selectedCertificate.provider_name + ' logo'">
                        <span v-else>{{ selectedCertificate.provider_initials }}</span>
                    </div>
                    <span class="folio-label">{{ selectedCertificate.provider_name || selectedCertificate.issuer || 'Certificate' }}</span>
                    <h2 :id="'certificate-title-' + selectedCertificate.id">{{ selectedCertificate.title }}</h2>
                    <p v-if="selectedCertificate.description">{{ selectedCertificate.description }}</p>
                    <dl v-if="selectedCertificate.issued_on || selectedCertificate.verification_code"><div v-if="selectedCertificate.issued_on"><dt>Issued</dt><dd>{{ selectedCertificate.issued_on }}</dd></div><div v-if="selectedCertificate.verification_code"><dt>Verification code</dt><dd>{{ selectedCertificate.verification_code }}</dd></div></dl>
                    <div v-if="selectedCertificate.tags?.length" class="tag-row"><span v-for="tag in selectedCertificate.tags" :key="tag">{{ tag }}</span></div>
                    <div class="modal-actions">
                        <a v-if="selectedCertificate.file_url" :href="selectedCertificate.file_url" target="_blank" rel="noreferrer">Verify credential<ArrowUpRight :size="14" /></a>
                        <span v-else class="certificate-privacy-note">Original document withheld for privacy</span>
                    </div>
                </article>
            </div>
        </Transition>

        <Transition name="modal">
            <div v-if="selectedBackground" class="modal-backdrop" role="presentation" @click.self="closeBackground">
                <article class="badge-modal background-modal" role="dialog" aria-modal="true" :aria-labelledby="`background-title-${selectedBackground.type}-${selectedBackground.item.id}`">
                    <button class="icon-button modal-close" type="button" title="Close" aria-label="Close" @click="closeBackground"><X :size="17" /></button>
                    <div class="modal-badge background-organization-mark">
                        <img v-if="selectedBackground.item.logo_url" :src="selectedBackground.item.logo_url" :alt="`${selectedBackground.type === 'education' ? selectedBackground.item.institution : selectedBackground.item.organization} logo`">
                        <span v-else>{{ initials(selectedBackground.type === 'education' ? selectedBackground.item.institution : selectedBackground.item.organization) }}</span>
                    </div>
                    <span class="folio-label">{{ selectedBackground.type === 'education' ? 'Education' : 'Experience' }}</span>
                    <h2 :id="`background-title-${selectedBackground.type}-${selectedBackground.item.id}`">{{ selectedBackground.type === 'education' ? selectedBackground.item.program : selectedBackground.item.position }}</h2>
                    <p class="background-modal-organization">{{ selectedBackground.type === 'education' ? selectedBackground.item.institution : selectedBackground.item.organization }}</p>
                    <p v-if="selectedBackground.type === 'education' && selectedBackground.item.level" class="background-modal-level">{{ selectedBackground.item.level }}</p>
                    <p v-if="selectedBackground.type === 'experience' && selectedBackground.item.summary" class="background-modal-summary">{{ selectedBackground.item.summary }}</p>
                    <dl>
                        <div><dt>Started</dt><dd>{{ selectedBackground.item.start_date }}</dd></div>
                        <div><dt>{{ selectedBackground.type === 'experience' && selectedBackground.item.is_current ? 'Status' : 'Completed' }}</dt><dd>{{ selectedBackground.type === 'experience' && selectedBackground.item.is_current ? 'Present' : selectedBackground.item.end_date }}</dd></div>
                    </dl>
                    <div v-if="(selectedBackground.type === 'education' ? selectedBackground.item.activities : selectedBackground.item.responsibilities)?.length" class="background-detail-list">
                        <span class="folio-label">{{ selectedBackground.type === 'education' ? 'Activities and distinctions' : 'Responsibilities' }}</span>
                        <ul><li v-for="detail in (selectedBackground.type === 'education' ? selectedBackground.item.activities : selectedBackground.item.responsibilities)" :key="detail">{{ detail }}</li></ul>
                    </div>
                </article>
            </div>
        </Transition>

        <Transition name="modal">
            <div v-if="selectedDesign" class="modal-backdrop" role="presentation" @click.self="closeDesign">
                <article class="design-modal" role="dialog" aria-modal="true" :aria-labelledby="`design-title-${selectedDesign.id}`">
                    <button class="icon-button modal-close" type="button" title="Close" aria-label="Close" @click="closeDesign"><X :size="17" /></button>
                    <div class="design-modal-media">
                        <video v-if="selectedDesign.media_type === 'video'" controls autoplay playsinline :poster="selectedDesign.thumbnail_url || undefined"><source :src="selectedDesign.media_url"></video>
                        <iframe v-else-if="selectedDesign.media_type === 'pdf'" :src="`${selectedDesign.media_url}#page=1&toolbar=0&navpanes=0`" :title="selectedDesign.title"></iframe>
                        <img v-else :src="selectedDesign.media_url" :alt="selectedDesign.title">
                    </div>
                    <div class="design-modal-copy">
                        <span class="folio-label">{{ selectedDesign.type_label }} / {{ selectedDesign.year || 'Portfolio' }}</span>
                        <h2 :id="`design-title-${selectedDesign.id}`">{{ selectedDesign.title }}</h2>
                        <p v-if="selectedDesign.description">{{ selectedDesign.description }}</p>
                        <a v-if="selectedDesign.external_url" class="inline-link" :href="selectedDesign.external_url" target="_blank" rel="noreferrer">View source<ArrowUpRight :size="14" /></a>
                    </div>
                </article>
            </div>
        </Transition>

        <Transition name="modal">
            <div v-if="selectedProject" class="modal-backdrop" role="presentation" @click.self="closeProject">
                <article class="project-modal" role="dialog" aria-modal="true" :aria-labelledby="`project-title-${selectedProject.id}`">
                    <button class="icon-button modal-close" type="button" title="Close" aria-label="Close" @click="closeProject"><X :size="17" /></button>
                    <div class="project-modal-media">
                        <iframe
                            v-if="isPowerBiProject(selectedProject)"
                            :src="selectedProject.url"
                            :title="`${selectedProject.title} interactive Power BI report`"
                            allow="fullscreen"
                        />
                        <img v-else-if="selectedProject.thumbnail_url" :src="selectedProject.thumbnail_url" :alt="`${selectedProject.title} screenshot`">
                        <div v-else class="project-modal-placeholder" aria-hidden="true">
                            <component :is="projectIcon(selectedProject.category)" :size="48" />
                        </div>
                    </div>
                    <div class="project-modal-copy">
                        <span class="folio-label">{{ selectedProject.category || 'Selected project' }} / {{ selectedProject.year || 'Portfolio' }}</span>
                        <h2 :id="`project-title-${selectedProject.id}`">{{ selectedProject.title }}</h2>
                        <p>{{ selectedProject.summary }}</p>
                        <dl>
                            <div><dt>Format</dt><dd>{{ isPowerBiProject(selectedProject) ? 'Interactive dashboard' : selectedProject.category || 'Digital project' }}</dd></div>
                            <div v-if="selectedProject.year"><dt>Year</dt><dd>{{ selectedProject.year }}</dd></div>
                        </dl>
                        <div class="tag-row"><span v-for="tag in selectedProject.tags" :key="tag">{{ tag }}</span></div>
                        <div class="modal-actions">
                            <a v-if="selectedProject.url" :href="selectedProject.url" target="_blank" rel="noreferrer">{{ isPowerBiProject(selectedProject) ? 'Open dashboard' : 'Open project' }}<ArrowUpRight :size="14" /></a>
                            <a v-if="selectedProject.repo_url" :href="selectedProject.repo_url" target="_blank" rel="noreferrer"><Code2 :size="14" />Repository</a>
                        </div>
                    </div>
                </article>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.portfolio-shell { --gradient-x: 50%; --gradient-y: 18%; --gradient-counter-x: 50%; --gradient-counter-y: 45%; position: relative; min-height: 100vh; isolation: isolate; background: transparent; color: var(--profile-ink); }
.gemini-wash { position: fixed; inset: 0; z-index: 0; pointer-events: none; background: radial-gradient(ellipse 92% 78% at var(--gradient-x) var(--gradient-y), rgb(251 188 4 / 10%) 0%, rgb(255 227 128 / 5%) 40%, transparent 72%), radial-gradient(ellipse 86% 76% at var(--gradient-counter-x) var(--gradient-counter-y), rgb(255 214 102 / 4%) 0%, transparent 68%), var(--profile-bg); filter: none; }
.portfolio-shell > main { position: relative; z-index: 1; }
:global(:root[data-theme='dark']) .gemini-wash { background: radial-gradient(ellipse 88% 74% at var(--gradient-x) var(--gradient-y), rgb(66 133 244 / 27%) 0%, rgb(161 66 244 / 17%) 34%, transparent 70%), radial-gradient(ellipse 82% 72% at var(--gradient-counter-x) var(--gradient-counter-y), rgb(242 139 130 / 16%) 0%, rgb(77 208 225 / 10%) 38%, transparent 72%), var(--profile-bg); filter: saturate(118%); }
.topbar { position: sticky; top: 0; z-index: 30; display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: center; gap: 1.2rem; border-bottom: 1px solid var(--profile-200); background: color-mix(in srgb, var(--profile-bg) 94%, transparent); padding: 0.75rem clamp(1rem, 3vw, 2rem); backdrop-filter: blur(16px); }
.brand, .nav-links, .hero-links, .location-line, .link-row, .section-link, .inline-link, .filter-row, .tag-row, .meta-line, .contribution-legend, .contribution-legend div, .modal-actions, .timeline-title, .footer-links a { display: flex; align-items: center; }
.brand { min-width: 0; gap: 0.65rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.72rem; text-decoration: none; text-transform: uppercase; }
.brand-mark { position: relative; display: inline-grid; width: 2rem; height: 2rem; flex: 0 0 auto; place-items: center; overflow: hidden; border-radius: 50%; background: var(--profile-ink); color: var(--profile-bg); font-size: 0.62rem; }
.brand-mark img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center top; }
.brand-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.nav-links { gap: 1rem; font-family: var(--font-mono); font-size: 0.67rem; text-transform: uppercase; }
.nav-links a { color: var(--profile-500); text-decoration: none; }
.nav-links a:hover { color: var(--profile-ink); }
.icon-button { display: inline-grid; width: 2.25rem; height: 2.25rem; place-items: center; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-bg); color: var(--profile-ink); cursor: pointer; }
.icon-button:hover { border-color: var(--profile-ink); }
.theme-switch { display: grid; grid-template-columns: repeat(2, 2rem); border: 1px solid var(--profile-200); border-radius: 4px; overflow: hidden; }
.theme-switch button { display: inline-grid; width: 2rem; height: 2rem; place-items: center; border: 0; border-right: 1px solid var(--profile-200); background: var(--profile-bg); color: var(--profile-500); cursor: pointer; }
.theme-switch button:last-child { border-right: 0; }
.theme-switch button.active { background: var(--profile-ink); color: var(--profile-bg); }
.section-wrap { width: min(100% - 2rem, 78rem); margin: 0 auto; }
.hero { padding: clamp(2.25rem, 5vw, 4.5rem) 0 0; }
.folio-label, .meta-line, .filter-row, .tag-row, .badge-copy small, .badge-copy > span, .certificate-copy small, .certificate-copy > span, .timeline-date, .publication-name, .location-line, .hero-links, .section-link, .inline-link, .stat-line span, .contribution-legend, .timeline-title, .footer-links { font-family: var(--font-mono); }
.folio-label { color: var(--profile-500); font-size: 0.66rem; text-transform: uppercase; }
.hero-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(17rem, 0.65fr); column-gap: clamp(2rem, 8vw, 7rem); row-gap: 1.3rem; align-items: start; margin-top: 1.5rem; }
.hero-copy { grid-column: 1; grid-row: 2; padding-bottom: clamp(0.75rem, 2vw, 1.75rem); }
.hero-role { grid-column: 1; grid-row: 1; max-width: 43rem; margin: 0; color: var(--profile-700); font-size: clamp(1rem, 2vw, 1.35rem); }
h1 { max-width: 15ch; margin: 0; font-family: var(--font-mono); font-size: clamp(3.1rem, 8vw, 7.2rem); font-weight: 650; line-height: 0.91; overflow-wrap: anywhere; }
.hero-bio { max-width: 43rem; margin: 1.8rem 0 0; color: var(--profile-700); font-family: var(--font-serif); font-size: clamp(1.05rem, 2vw, 1.28rem); line-height: 1.7; }
.location-line { flex-wrap: wrap; gap: 0.7rem 1.1rem; margin-top: 1.25rem; color: var(--profile-500); font-size: 0.7rem; text-transform: uppercase; }
.location-line span { display: inline-flex; align-items: center; gap: 0.35rem; }
.hero-links { flex-wrap: wrap; gap: 0.65rem; margin-top: 1.7rem; }
.hero-links a, .link-row a, .section-link, .inline-link, .modal-actions a, .badge-preview button { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; }
.portrait-block { grid-column: 2; grid-row: 2; margin: 0; }
.portrait-block > img, .portrait-video-stage, .portrait-fallback { width: 100%; aspect-ratio: 4 / 5; object-fit: cover; }
.portrait-block > img, .portrait-fallback { border: 1px solid var(--profile-ink); }
.portrait-video-stage { position: relative; overflow: hidden; background: var(--profile-ink); pointer-events: none; }
.portrait-video-stage video { position: absolute; inset: 1px; display: block; width: calc(100% - 2px); height: calc(100% - 2px); object-fit: cover; object-position: center 12%; opacity: 0; visibility: hidden; pointer-events: none; }
.portrait-video-stage video.is-active { opacity: 1; visibility: visible; }
.portrait-fallback { display: grid; place-items: center; background: var(--profile-ink); color: var(--profile-bg); font-family: var(--font-mono); font-size: 4rem; }
.portrait-block figcaption { display: flex; justify-content: space-between; gap: 0.75rem; border: 1px solid var(--profile-ink); border-top: 0; padding: 0.55rem; font-family: var(--font-mono); font-size: 0.6rem; text-transform: uppercase; }
.stat-line { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border-top: 1px solid var(--profile-ink); border-bottom: 1px solid var(--profile-ink); margin-top: clamp(2rem, 4vw, 3.5rem); }
.stat-line div { display: flex; align-items: baseline; gap: 0.7rem; border-right: 1px solid var(--profile-ink); padding: 1.15rem; }
.stat-line div:last-child { border-right: 0; }
.stat-line strong { font-family: var(--font-mono); font-size: 2rem; }
.stat-line span { color: var(--profile-500); font-size: 0.65rem; text-transform: uppercase; }
.content-band { scroll-margin-top: 2rem; border-bottom: 1px solid var(--profile-200); padding: clamp(3rem, 6vw, 5rem) 0; }
.section-head { display: flex; align-items: end; justify-content: space-between; gap: 1.5rem; margin-bottom: 1.5rem; }
.section-head h2, .footer h2 { margin: 0.4rem 0 0; font-size: clamp(2.1rem, 5vw, 4rem); line-height: 1; }
.section-head > p { max-width: 30rem; margin: 0; color: var(--profile-700); font-family: var(--font-serif); font-size: 1.05rem; line-height: 1.6; }
.section-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 0.8rem; }
.filter-row { flex-wrap: wrap; gap: 0.45rem; margin-bottom: 1.25rem; }
.filter-row button { border: 1px solid var(--profile-300); border-radius: 999px; background: transparent; padding: 0.38rem 0.68rem; color: var(--profile-500); font-size: 0.65rem; text-transform: uppercase; cursor: pointer; }
.filter-row button.active, .filter-row button:hover { border-color: var(--profile-ink); background: var(--profile-ink); color: var(--profile-bg); }
.project-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; }
.project-card { display: grid; min-width: 0; min-height: 19rem; grid-template-columns: 3.5rem minmax(0, 1fr); border: 1px solid var(--profile-200); background: var(--profile-50); }
.project-card:last-child:nth-child(odd) { width: calc((100% - 0.8rem) / 2); grid-column: 1 / -1; justify-self: center; }
.project-index { border-right: 1px solid var(--profile-200); padding: 1.25rem 0; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.66rem; text-align: center; }
.project-copy { display: flex; min-width: 0; flex-direction: column; align-items: flex-start; padding: 1.25rem; }
.project-copy h3 { line-height: 1.25; }
.project-copy > p { margin-bottom: 0; }
.project-copy .tag-row { margin-bottom: 1.4rem; }
.project-more { display: inline-flex; align-items: center; gap: 0.35rem; border: 0; border-bottom: 1px solid var(--profile-ink); margin-top: auto; background: transparent; padding: 0 0 0.2rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; cursor: pointer; }
.project-more:hover, .project-more:focus-visible { border-color: var(--profile-500); color: var(--profile-500); }
.design-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; align-items: stretch; }
.design-card { position: relative; display: grid; height: 100%; min-width: 0; grid-template-rows: auto 1fr; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-50); transition: border-color 180ms ease, transform 180ms ease; }
.design-card:hover, .design-card:focus-within { border-color: var(--profile-ink); transform: translateY(-3px); }
.design-media { position: relative; aspect-ratio: 4 / 3; overflow: hidden; border-bottom: 1px solid var(--profile-200); background: #111513; }
.design-media img, .design-media video, .design-media iframe { display: block; width: 100%; height: 100%; }
.design-media img, .design-media video { object-fit: contain; transition: transform 450ms cubic-bezier(0.16, 1, 0.3, 1); }
.design-media iframe { border: 0; pointer-events: none; }
.design-card:hover .design-media img, .design-card:hover .design-media video, .design-card:focus-within .design-media img, .design-card:focus-within .design-media video { transform: scale(1.018); }
.design-expand { position: absolute; right: 0.65rem; bottom: 0.65rem; display: grid; width: 2.1rem; height: 2.1rem; place-items: center; border: 1px solid #fff; border-radius: 50%; background: color-mix(in srgb, #000 62%, transparent); color: #fff; }
.design-copy { display: grid; min-height: 6rem; align-content: start; padding: 1rem; }
.design-copy h3 { display: -webkit-box; overflow: hidden; margin: 0.8rem 0 0; font-size: 1rem; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.design-card-trigger { position: absolute; inset: 0; z-index: 2; border: 0; border-radius: inherit; outline-offset: -3px; background: transparent; cursor: zoom-in; }
.design-card-trigger:focus-visible { outline: 2px solid var(--profile-ink); }
.design-empty { display: flex; min-height: 15rem; align-items: center; justify-content: center; gap: 0.9rem; border-top: 1px solid var(--profile-ink); border-bottom: 1px solid var(--profile-200); color: var(--profile-500); }
.design-empty div { display: grid; gap: 0.25rem; }
.design-empty strong { color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.75rem; text-transform: uppercase; }
.design-empty p { margin: 0; font-family: var(--font-serif); }
.meta-line { justify-content: space-between; gap: 1rem; color: var(--profile-500); font-size: 0.63rem; text-transform: uppercase; }
.project-copy h3, .publication-main h3, .achievement-item h3, .badge-preview h3, .timeline-row h3 { margin: 0.8rem 0 0; font-size: 1.2rem; }
.project-copy p, .publication-main > p, .achievement-item p, .timeline-row p { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.65; }
.tag-row { flex-wrap: wrap; gap: 0.35rem; margin-top: 0.9rem; }
.tag-row span { border: 1px solid var(--profile-300); border-radius: 999px; padding: 0.25rem 0.55rem; color: var(--profile-500); font-size: 0.61rem; }
.link-row { flex-wrap: wrap; gap: 0.8rem; margin-top: 1.15rem; }
.search-box { display: flex; min-width: min(100%, 17rem); align-items: center; gap: 0.5rem; border: 1px solid var(--profile-300); border-radius: 4px; padding: 0 0.65rem; }
.search-box input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; padding: 0.65rem 0; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; }
.publication-list { border-top: 1px solid var(--profile-ink); }
.publication-row { display: grid; grid-template-columns: 6rem minmax(0, 1fr) auto; gap: 1.2rem; align-items: start; border-bottom: 1px solid var(--profile-200); padding: 1.2rem 0; }
.publication-cover { display: grid; width: 6rem; aspect-ratio: 4 / 5; place-items: center; border: 1px solid var(--profile-200); background: var(--profile-100); overflow: hidden; }
.publication-cover img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
.publication-main > p { max-width: 54rem; margin-bottom: 0; }
.publication-name { margin: 0.4rem 0 0 !important; color: var(--profile-ink) !important; font-size: 0.7rem; text-transform: uppercase; }
.publication-action a { display: inline-grid; width: 2.5rem; height: 2.5rem; place-items: center; border: 1px solid var(--profile-300); border-radius: 50%; }
.contribution-panel { border: 1px solid var(--profile-200); background: var(--profile-50); padding: clamp(1rem, 3vw, 1.5rem); }
.contribution-summary { display: flex; align-items: baseline; gap: 0.7rem; margin-bottom: 1rem; }
.contribution-summary strong { font-family: var(--font-mono); font-size: 2.5rem; }
.contribution-summary span { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; }
.contribution-scroll { overflow-x: auto; padding-bottom: 0.5rem; }
.contribution-grid { display: flex; width: max-content; gap: 3px; margin-inline: auto; }
.contribution-week { display: grid; grid-template-rows: repeat(7, 10px); gap: 3px; }
.contribution-day, .contribution-legend i { display: block; width: 10px; height: 10px; border: 1px solid color-mix(in srgb, var(--profile-ink) 7%, transparent); border-radius: 2px; background: var(--profile-100); }
.level-1 { background: #9be9a8 !important; }
.level-2 { background: #40c463 !important; }
.level-3 { background: #30a14e !important; }
.level-4 { background: #216e39 !important; }
.contribution-legend { justify-content: space-between; gap: 1rem; margin-top: 0.7rem; color: var(--profile-500); font-size: 0.6rem; text-transform: uppercase; }
.contribution-legend div { gap: 0.3rem; }
.achievement-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border-top: 1px solid var(--profile-ink); }
.achievement-item { position: relative; min-height: 17rem; border-right: 1px solid var(--profile-200); border-bottom: 1px solid var(--profile-200); padding: 1.2rem; }
.achievement-item:nth-child(2n) { border-right: 0; }
.achievement-mark { display: grid; width: 7rem; height: 3rem; place-items: center; border: 1px solid var(--profile-ink); margin-bottom: 2rem; overflow: hidden; background: #fff; }
.achievement-mark img { display: block; width: auto; height: auto; max-width: 6.25rem; max-height: 2.25rem; object-fit: contain; }
.badge-layout { display: grid; grid-template-columns: minmax(0, 1fr) 18rem; gap: 1rem; align-items: start; }
.badge-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.6rem; }
.badge-card { display: grid; min-height: 14rem; grid-template-rows: 7rem 1fr; gap: 0.8rem; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-50); padding: 0.8rem; color: var(--profile-ink); text-align: left; cursor: pointer; }
.badge-card:hover, .badge-card:focus-visible { border-color: var(--profile-ink); transform: translateY(-2px); }
.badge-image { display: grid; width: 7rem; height: 7rem; place-items: center; justify-self: center; font-family: var(--font-mono); }
.badge-image img { width: 100%; height: 100%; object-fit: contain; }
.badge-copy { display: grid; align-content: end; gap: 0.25rem; }
.badge-copy small, .badge-copy > span { color: var(--profile-500); font-size: 0.59rem; text-transform: uppercase; }
.badge-copy strong { font-size: 0.82rem; line-height: 1.35; }
.badge-preview { position: sticky; top: 5rem; border: 1px solid var(--profile-ink); padding: 1rem; }
.badge-preview > img, .preview-fallback { display: grid; width: 9rem; height: 9rem; place-items: center; margin: 1.8rem auto; object-fit: contain; font-family: var(--font-mono); font-size: 2rem; }
.badge-preview > p { margin: 0; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.65rem; text-transform: uppercase; }
.badge-preview h3 { line-height: 1.35; }
.badge-preview button { border: 0; border-bottom: 1px solid var(--profile-ink); margin-top: 1rem; background: transparent; padding: 0 0 0.2rem; cursor: pointer; }
.archive-route { display: flex; width: fit-content; align-items: center; gap: 0.35rem; border-bottom: 1px solid var(--profile-ink); margin: 1.5rem auto 0; padding-bottom: 0.2rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.66rem; text-decoration: none; text-transform: uppercase; }
.certificate-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.65rem; }
.certificate-card { position: relative; display: grid; width: 100%; grid-template-columns: 5rem minmax(0, 1fr) auto; gap: 0.8rem; align-items: center; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-50); padding: 0.65rem; color: var(--profile-ink); font: inherit; text-align: left; cursor: pointer; }
.certificate-card:hover { border-color: var(--profile-ink); }

.certificate-art { display: grid; width: 5rem; aspect-ratio: 4 / 5; place-items: center; border: 1px solid var(--profile-200); background: var(--profile-bg); overflow: hidden; }
.certificate-art img { width: 100%; height: 100%; object-fit: contain; object-position: center; padding: 0.65rem; }
.provider-initials { font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.08em; }
.certificate-copy { min-width: 0; display: grid; gap: 0.25rem; }
.certificate-copy small, .certificate-copy > span { color: var(--profile-500); font-size: 0.58rem; text-transform: uppercase; }
.certificate-copy strong { display: -webkit-box; overflow: hidden; font-size: 0.75rem; line-height: 1.35; -webkit-box-orient: vertical; -webkit-line-clamp: 3; }
.tool-groups { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border-top: 1px solid var(--profile-ink); border-left: 1px solid var(--profile-200); }
.tool-category { min-width: 0; border-right: 1px solid var(--profile-200); border-bottom: 1px solid var(--profile-200); padding: clamp(1rem, 3vw, 1.35rem); }
.tool-category-head { display: grid; grid-template-columns: auto auto minmax(0, 1fr); gap: 0.55rem; align-items: center; padding-bottom: 1rem; }
.tool-category-head > span { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.6rem; }
.tool-category-head h3 { margin: 0; font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; }
.tool-list { display: grid; }
.tool-entry { display: grid; min-width: 0; grid-template-columns: 2.75rem minmax(0, 1fr) auto; gap: 0.75rem; align-items: center; border-top: 1px solid var(--profile-200); padding: 0.8rem 0; color: var(--profile-ink); text-decoration: none; }
a.tool-entry:hover .tool-copy strong { text-decoration: underline; text-underline-offset: 0.2rem; }
.tool-mark { position: relative; display: grid; width: 2.75rem; aspect-ratio: 1; place-items: center; overflow: hidden; border: 1px solid var(--profile-200); background: var(--profile-50); font-family: var(--font-mono); font-size: 0.62rem; font-weight: 700; }
.tool-mark img { position: absolute; inset: 0; width: 100%; height: 100%; background: #fff; object-fit: contain; padding: 0.3rem; }
.tool-copy { min-width: 0; display: grid; gap: 0.2rem; }
.tool-copy strong { font-size: 0.82rem; line-height: 1.35; }
.tool-copy > span { color: var(--profile-500); font-family: var(--font-serif); font-size: 0.82rem; line-height: 1.45; }
.tool-entry > svg { color: var(--profile-500); }
.background-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2rem; }
.timeline-title { gap: 0.5rem; border-bottom: 1px solid var(--profile-ink); padding-bottom: 0.7rem; font-size: 0.7rem; text-transform: uppercase; }
.timeline-row { position: relative; display: grid; width: 100%; grid-template-columns: 8rem minmax(0, 1fr); gap: 1rem; border: 0; border-bottom: 1px solid var(--profile-200); background: transparent; padding: 1.4rem 2rem 1.4rem 0; color: var(--profile-ink); font: inherit; text-align: left; }
.timeline-date { color: var(--profile-500); font-size: 0.6rem; line-height: 1.6; text-transform: uppercase; }
.timeline-entry { display: grid; grid-template-columns: 3.25rem minmax(0, 1fr); gap: 0.9rem; align-items: start; min-width: 0; }
.organization-mark { position: relative; display: grid; width: 3.25rem; aspect-ratio: 1; place-items: center; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 4px; background: #fff; color: #17251e; font-family: var(--font-mono); font-size: 0.68rem; font-weight: 700; }
.organization-mark img { position: absolute; inset: 0; width: 100%; height: 100%; background: #fff; object-fit: contain; padding: 0.25rem; }
.timeline-copy { display: grid; min-width: 0; gap: 0.55rem; align-content: start; }
.timeline-copy .meta-line { display: flex; width: 100%; margin: 0; }
.timeline-heading { display: block; margin: 0.15rem 0 0; font-size: 1rem; line-height: 1.4; }
.timeline-summary { display: block; margin: 0; color: var(--profile-700); font-family: var(--font-serif); line-height: 1.65; }
.school-name { color: var(--profile-ink); font-size: 0.82rem; font-weight: 700; line-height: 1.45; }
.timeline-copy .tag-row { display: flex; margin-top: 0.25rem; }
.timeline-trigger { cursor: pointer; transition: background-color 160ms ease, padding-left 160ms ease; }
.timeline-trigger:hover, .timeline-trigger:focus-visible { background: var(--profile-50); padding-left: 0.65rem; outline: 1px solid var(--profile-300); outline-offset: -1px; }
.timeline-expand { position: absolute; top: 1.4rem; right: 0.35rem; color: var(--profile-500); transition: color 160ms ease, transform 160ms ease; }
.timeline-trigger:hover .timeline-expand, .timeline-trigger:focus-visible .timeline-expand { color: var(--profile-ink); transform: scale(1.08); }
.skills-index { display: grid; grid-template-columns: 10rem minmax(0, 1fr); gap: 1rem; border-top: 1px solid var(--profile-ink); margin-top: 2.5rem; padding-top: 1rem; }
.skills-index > div { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.skills-index > div span { border: 1px solid var(--profile-300); border-radius: 999px; padding: 0.35rem 0.6rem; font-family: var(--font-mono); font-size: 0.64rem; text-transform: uppercase; }
.collaboration-bridge { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: clamp(2rem, 6vw, 6rem); align-items: end; margin-top: clamp(3rem, 7vw, 6rem); margin-bottom: clamp(3rem, 7vw, 6rem); border: 1px solid var(--profile-ink); background: linear-gradient(135deg, rgb(251 188 4 / 10%), transparent 52%), var(--profile-50); padding: clamp(1.4rem, 4vw, 2.6rem); color: var(--profile-ink); box-shadow: 10px 10px 0 var(--profile-red); transition: background-color 500ms ease, border-color 500ms ease, color 500ms ease, box-shadow 500ms ease; }
:global(:root[data-theme='dark']) .collaboration-bridge { border-color: var(--profile-300); background: radial-gradient(circle at 92% 8%, rgb(66 133 244 / 18%), transparent 42%), radial-gradient(circle at 68% 110%, rgb(161 66 244 / 12%), transparent 45%), var(--profile-50); box-shadow: 10px 10px 0 color-mix(in srgb, var(--profile-red) 72%, #7c3aed); }
.collaboration-bridge .folio-label { color: var(--profile-500); }
.collaboration-bridge h2 { margin: 0.55rem 0 0; font-size: clamp(2.5rem, 6vw, 5rem); line-height: 0.95; }
.collaboration-bridge p { max-width: 47rem; margin: 1rem 0 0; color: var(--profile-700); font-size: 1.05rem; line-height: 1.65; }
.collaboration-actions { display: grid; gap: 0.65rem; min-width: 10.5rem; }
.collaboration-actions a { display: inline-flex; min-height: 2.8rem; align-items: center; justify-content: space-between; gap: 0.6rem; border: 1px solid var(--profile-ink); background: transparent; padding: 0.7rem 0.8rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.68rem; text-decoration: none; text-transform: uppercase; }
.collaboration-actions a:first-child { background: var(--profile-ink); color: var(--profile-bg); }
.collaboration-actions a:hover { box-shadow: 4px 4px 0 var(--profile-red); transform: translate(-2px, -2px); }
.guestbook-layout { display: grid; grid-template-columns: minmax(20rem, 0.8fr) minmax(0, 1.2fr); gap: 1.2rem; align-items: start; }
.guestbook-form { display: grid; gap: 0.9rem; border: 1px solid var(--profile-ink); background: var(--profile-bg); padding: clamp(1rem, 3vw, 1.4rem); }
.form-heading { display: flex; align-items: center; gap: 0.55rem; border-bottom: 1px solid var(--profile-200); padding-bottom: 0.8rem; }
.form-heading h3 { margin: 0; font-size: 1rem; }
.guestbook-fields { display: grid; gap: 0.8rem; }
.guestbook-fields.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.guestbook-form label { display: grid; gap: 0.4rem; color: var(--profile-700); font-family: var(--font-mono); font-size: 0.64rem; text-transform: uppercase; }
.guestbook-form label small { color: var(--profile-400); font-size: inherit; }
.guestbook-form input, .guestbook-form textarea { width: 100%; min-width: 0; border: 1px solid var(--profile-300); border-radius: 4px; outline: 0; background: var(--profile-bg); padding: 0.7rem; color: var(--profile-ink); }
.guestbook-form textarea { resize: vertical; line-height: 1.55; }
.guestbook-form input:focus, .guestbook-form textarea:focus { border-color: var(--profile-ink); }
.rating-field { display: flex; align-items: center; justify-content: space-between; gap: 1rem; color: var(--profile-700); font-family: var(--font-mono); font-size: 0.64rem; text-transform: uppercase; }
.rating-field > div { display: flex; gap: 0.2rem; }
.rating-field button { display: inline-grid; width: 2rem; height: 2rem; place-items: center; border: 0; background: transparent; color: var(--profile-ink); cursor: pointer; }
.honeypot { position: absolute !important; left: -10000px !important; width: 1px !important; height: 1px !important; overflow: hidden !important; }
.form-error, .form-success { margin: 0; font-size: 0.76rem; }
.form-error { color: #b42318; }
.form-success { color: #157347; }
.submit-note { display: inline-flex; width: fit-content; align-items: center; gap: 0.45rem; border: 1px solid var(--profile-ink); border-radius: 4px; background: var(--profile-ink); padding: 0.7rem 0.85rem; color: var(--profile-bg); font-family: var(--font-mono); font-size: 0.67rem; text-transform: uppercase; cursor: pointer; }
.submit-note:disabled { cursor: wait; opacity: 0.55; }
.review-list { display: grid; gap: 0.65rem; }
.review-card, .guestbook-empty { border: 1px solid var(--profile-200); background: var(--profile-50); padding: 1rem; }
.review-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
.review-head > div:first-child { display: grid; gap: 0.2rem; }
.review-head strong { font-size: 0.85rem; }
.review-head span, .owner-reply > span { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.59rem; text-transform: uppercase; }
.review-stars { display: flex; gap: 0.1rem; color: var(--profile-ink); }
.review-card blockquote { margin: 1rem 0 0; color: var(--profile-700); font-family: var(--font-serif); font-size: 1.02rem; line-height: 1.65; }
.owner-reply { border-left: 2px solid var(--profile-ink); margin-top: 1rem; padding-left: 0.8rem; }
.owner-reply p { margin: 0.35rem 0 0; color: var(--profile-700); line-height: 1.55; }
.guestbook-empty { display: flex; min-height: 12rem; align-items: center; justify-content: center; gap: 0.7rem; color: var(--profile-500); }
.footer { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2rem; align-items: end; padding: clamp(3rem, 6vw, 5rem) 0 2rem; }
.footer-links { display: grid; gap: 0.55rem; justify-items: end; font-size: 0.68rem; text-transform: uppercase; }
.footer-links a { gap: 0.35rem; }
.modal-backdrop { position: fixed; inset: 0; z-index: 60; display: grid; place-items: center; background: color-mix(in srgb, #000 55%, transparent); padding: 1rem; backdrop-filter: blur(10px); }
.badge-modal { position: relative; width: min(100%, 34rem); max-height: calc(100vh - 2rem); overflow-y: auto; border: 1px solid var(--profile-ink); background: var(--profile-bg); padding: clamp(1rem, 4vw, 2rem); box-shadow: 12px 12px 0 var(--profile-ink); }
.background-modal { width: min(100%, 40rem); }
.background-organization-mark { width: 6.5rem; height: 6.5rem; border: 1px solid var(--profile-200); background: #fff; }
.background-organization-mark img { width: 100%; height: 100%; object-fit: contain; padding: 0.55rem; }
.background-modal-organization { margin: 0.8rem 0 0; color: var(--profile-ink) !important; font-family: var(--font-serif); font-size: clamp(1.15rem, 3vw, 1.45rem); font-weight: 700; line-height: 1.35; }
.background-modal-level, .background-modal-summary { color: var(--profile-700); font-family: var(--font-serif); font-size: 1rem; line-height: 1.7; }
.background-detail-list { border-top: 1px solid var(--profile-200); margin-top: 1.4rem; padding-top: 1rem; }
.background-detail-list ul { display: grid; gap: 0.55rem; margin: 0.8rem 0 0; padding-left: 1.25rem; color: var(--profile-700); font-family: var(--font-serif); line-height: 1.5; }
.design-modal { position: relative; display: grid; width: min(100%, 70rem); max-height: calc(100vh - 2rem); grid-template-columns: minmax(0, 1.45fr) minmax(18rem, 0.55fr); overflow: hidden; border: 1px solid var(--profile-ink); background: var(--profile-bg); box-shadow: 12px 12px 0 var(--profile-ink); }
.project-modal { position: relative; display: grid; width: min(100%, 72rem); max-height: calc(100vh - 2rem); grid-template-columns: minmax(0, 1.25fr) minmax(20rem, 0.75fr); overflow: hidden; border: 1px solid var(--profile-ink); background: var(--profile-bg); box-shadow: 12px 12px 0 var(--profile-ink); }
.project-modal-media { display: grid; min-height: 34rem; place-items: center; overflow: hidden; background: #fff; }
.project-modal-media img, .project-modal-media iframe { display: block; width: 100%; height: 100%; min-height: 34rem; border: 0; object-fit: contain; }
.project-modal-media img { object-position: center top; }
.project-modal-placeholder { display: grid; width: 100%; height: 100%; min-height: 34rem; place-items: center; background: var(--profile-100); color: var(--profile-500); }
.project-modal-copy { overflow-y: auto; border-left: 1px solid var(--profile-200); padding: clamp(1.5rem, 4vw, 2.5rem); }
.project-modal-copy h2 { margin: 0.7rem 0 0; font-size: clamp(1.7rem, 3vw, 2.35rem); line-height: 1.08; }
.project-modal-copy > p { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.7; }
.project-modal-copy dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; margin: 1.5rem 0; }
.project-modal-copy dl div { border-top: 1px solid var(--profile-200); padding-top: 0.65rem; }
.project-modal-copy dt { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; }
.project-modal-copy dd { margin: 0.3rem 0 0; }
.design-modal-media { display: grid; min-height: 34rem; place-items: center; overflow: hidden; background: #111513; }
.design-modal-media img, .design-modal-media video, .design-modal-media iframe { display: block; width: 100%; max-height: calc(100vh - 2.1rem); object-fit: contain; }
.design-modal-media iframe { height: min(80vh, 54rem); border: 0; }
.design-modal-copy { overflow-y: auto; border-left: 1px solid var(--profile-200); padding: clamp(1.25rem, 4vw, 2rem); }
.design-modal-copy h2 { margin: 0.7rem 0 0; font-size: clamp(1.6rem, 4vw, 2.7rem); line-height: 1.05; }
.design-modal-copy p { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.7; }
.modal-close { position: absolute; top: 1rem; right: 1rem; }
.modal-badge { display: grid; width: 9rem; height: 9rem; place-items: center; margin: 0 0 1.4rem; font-family: var(--font-mono); font-size: 2rem; }
.modal-badge img { width: 100%; height: 100%; object-fit: contain; }
.badge-modal h2 { max-width: 25rem; margin: 0.55rem 0 0; font-size: clamp(1.6rem, 5vw, 2.6rem); line-height: 1.05; }
.badge-modal > p { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.7; }
.badge-modal dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; margin: 1.2rem 0; }
.badge-modal dl div { border-top: 1px solid var(--profile-200); padding-top: 0.6rem; }
.badge-modal dt { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; }
.badge-modal dd { margin: 0.25rem 0 0; }
.modal-actions { flex-wrap: wrap; gap: 0.8rem; margin-top: 1.3rem; }
.certificate-privacy-note { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; }
.modal-enter-active, .modal-leave-active { transition: opacity 180ms ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

@media (max-width: 980px) {
    .badge-grid, .certificate-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .design-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .background-grid { grid-template-columns: 1fr; }
}

@media (max-width: 760px) {
    .topbar { grid-template-columns: minmax(0, 1fr) auto; }
    .nav-links { grid-column: 1 / -1; grid-row: 2; overflow-x: auto; padding-top: 0.25rem; }
    .hero-grid, .project-card, .badge-layout, .tool-groups, .footer { grid-template-columns: 1fr; }
    .section-actions { justify-content: flex-start; }
    .design-modal, .project-modal { grid-template-columns: 1fr; overflow-y: auto; }
    .design-modal-media img, .design-modal-media video, .design-modal-media iframe, .project-modal-media img, .project-modal-media iframe { height: auto; min-height: 0; max-height: 62vh; }
    .design-modal-media, .project-modal-media, .project-modal-placeholder { min-height: 18rem; }
    .design-modal-copy, .project-modal-copy { overflow: visible; border-top: 1px solid var(--profile-200); border-left: 0; }
    .hero-role, .hero-copy, .portrait-block { grid-column: 1; grid-row: auto; }
    .portrait-block { width: min(100%, 22rem); }
    .section-head { align-items: flex-start; flex-direction: column; }
    .controls-head { gap: 1rem; }
    .search-box { width: 100%; }
    .badge-preview { position: static; order: -1; }
    .skills-index { grid-template-columns: 1fr; }
    .footer-links { justify-items: start; }
    .collaboration-bridge { grid-template-columns: 1fr; align-items: start; }
    .collaboration-actions { width: min(100%, 20rem); }
    .guestbook-layout { grid-template-columns: 1fr; }
}

@media (max-width: 560px) {
    .section-wrap { width: min(100% - 1.25rem, 78rem); }
    .stat-line { grid-template-columns: 1fr; }
    .stat-line div { border-right: 0; border-bottom: 1px solid var(--profile-ink); }
    .stat-line div:last-child { border-bottom: 0; }
    .project-grid, .achievement-grid, .badge-grid, .certificate-grid { grid-template-columns: 1fr; }
    .project-card:last-child:nth-child(odd) { width: 100%; grid-column: auto; }
    .design-grid { grid-template-columns: 1fr; }
    .achievement-item { border-right: 0; }
    .publication-row { grid-template-columns: 4.5rem minmax(0, 1fr); }
    .publication-cover { width: 4.5rem; }
    .publication-action { display: none; }
    .timeline-row { grid-template-columns: 1fr; gap: 0.5rem; }
    .certificate-card { grid-template-columns: 4rem minmax(0, 1fr) auto; }
    .certificate-art { width: 4rem; }
    .badge-modal { box-shadow: 6px 6px 0 var(--profile-ink); }
    .guestbook-fields.two { grid-template-columns: 1fr; }
}
</style>
