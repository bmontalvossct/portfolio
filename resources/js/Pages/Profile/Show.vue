<script setup>
import { Link } from '@inertiajs/vue3';
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
    Moon,
    Network,
    Newspaper,
    Palette,
    Search,
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
const currentViewerCount = ref(25);
const hoveredBadge = ref(null);
const isDark = ref(false);
const publicationSearch = ref('');
const selectedBadge = ref(null);
const selectedCertificate = ref(null);
const selectedBackground = ref(null);
const selectedDesign = ref(null);
const selectedProject = ref(null);
const theme = ref('system');

let gradientFrame = null;
let gradientX = 50;
let gradientY = 18;
let gradientTargetX = 50;
let gradientTargetY = 18;
let viewerHeartbeatTimer = null;
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
    ...[...new Set(props.designMedia.map((item) => item.media_type))]
        .map((type) => ({ key: type, label: designTypeLabel(type) })),
]);

const featuredDesignMedia = computed(() => {
    const selections = [
        (item) => item.title === 'Campaign Poster',
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

    if (name.includes('ai') || name.includes('gpt') || name.includes('notebook')) return Bot;
    if (name.includes('power bi') || name.includes('data')) return BarChart3;
    if (name.includes('git')) return GitBranch;
    if (name.includes('adobe') || name.includes('canva') || name.includes('figma')) return Palette;
    if (name.includes('laravel') || name.includes('php') || name.includes('python')) return Database;

    return Wrench;
}

function designTypeLabel(type) {
    if (type === 'image') return 'Images';
    if (type === 'video') return 'Motion';
    if (type === 'pdf') return 'Brochure PDF';

    return type;
}

function labelFor(type) {
    return String(type ?? 'work').replace('_', ' ').replace(/\b\w/g, (character) => character.toUpperCase());
}

function projectIcon(category) {
    const clean = String(category ?? '').toLowerCase();

    if (clean.includes('dashboard') || clean.includes('power bi')) return BarChart3;
    if (clean.includes('dss') || clean.includes('forecasting')) return Bot;

    return Layout;
}

function isPowerBiProject(project) {
    try {
        const url = new URL(project?.url ?? '');
        return url.hostname.includes('powerbi.com');
    } catch {
        return false;
    }
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

function avatarUrlForSize(url, size) {
    if (!url) return null;

    try {
        const parsed = new URL(url, window.location.origin);
        if (parsed.hostname.includes('githubusercontent.com')) {
            parsed.searchParams.set('s', String(size));
            return parsed.toString();
        }
    } catch {
        return url;
    }

    return url;
}

function applyTheme() {
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = theme.value === 'dark' || (theme.value === 'system' && systemDark);
    document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
}

function setTheme(value) {
    theme.value = value;
}

function openBadge(badge) {
    selectedBadge.value = badge;
}

function closeBadge() {
    selectedBadge.value = null;
}

function openProject(project) {
    selectedProject.value = project;
}

function closeProject() {
    selectedProject.value = null;
}

function openCertificate(certificate) {
    selectedCertificate.value = certificate;
}

function closeCertificate() {
    selectedCertificate.value = null;
}

function openBackground(type, item) {
    selectedBackground.value = { type, item };
}

function closeBackground() {
    selectedBackground.value = null;
}

function openDesign(item) {
    selectedDesign.value = item;
}

function closeDesign() {
    selectedDesign.value = null;
}

function onBadgeImageError(event) {
    const target = event.currentTarget;
    if (target) {
        target.style.display = 'none';
        const fallback = target.nextElementSibling;
        if (fallback) {
            fallback.classList.add('is-active');
            fallback.style.display = 'grid';
        }
    }
}

function videoElementForDirection(direction) {
    return direction === 1 ? portraitForwardVideo.value : portraitReverseVideo.value;
}

function stopPortraitPlayback() {
    portraitPlaybackToken += 1;
    pendingPortraitDirection = null;

    if (portraitForwardVideo.value) {
        portraitForwardVideo.value.pause();
        portraitForwardVideo.value.currentTime = 0;
    }

    if (portraitReverseVideo.value) {
        portraitReverseVideo.value.pause();
        portraitReverseVideo.value.currentTime = 0;
    }
}

function playDirection(direction) {
    const video = videoElementForDirection(direction);
    if (!video) return;

    if (video.readyState < 2) {
        pendingPortraitDirection = direction;
        return;
    }

    const currentToken = ++portraitPlaybackToken;
    activePortraitDirection.value = direction;
    video.currentTime = 0;

    const promise = video.play();
    if (promise && typeof promise.then === 'function') {
        promise
            .then(() => {
                if (currentToken !== portraitPlaybackToken) return;

                video.onended = () => {
                    if (currentToken !== portraitPlaybackToken) return;
                    video.onended = null;
                    portraitAnimationDirection = direction === 1 ? -1 : 1;
                    playDirection(portraitAnimationDirection);
                };
            })
            .catch(() => {
                // Autoplay policy fallback
            });
    }
}

function initializePortraitVideo(direction) {
    if (direction === 1 && pendingPortraitDirection === null) {
        playDirection(1);
    }
}

function resumePendingPortrait(direction) {
    if (pendingPortraitDirection === direction) {
        pendingPortraitDirection = null;
        playDirection(direction);
    }
}

async function refreshViewerCount() {
    if (document.hidden) return;

    try {
        const response = await fetch('/viewer-presence', {
            headers: { Accept: 'application/json' },
            cache: 'no-store',
        });

        if (!response.ok) return;

        const payload = await response.json();
        if (typeof payload?.viewers === 'number') {
            currentViewerCount.value = payload.viewers;
        }
    } catch {
        // Soft fail
    }
}

function handleVisibilityChange() {
    if (!document.hidden) void refreshViewerCount();
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        closeBadge();
        closeCertificate();
        closeDesign();
        closeProject();
        closeBackground();
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
    void refreshViewerCount();
    viewerHeartbeatTimer = window.setInterval(refreshViewerCount, 5_000);
    window.addEventListener('keydown', onKeydown);
    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
});

onBeforeUnmount(() => {
    if (gradientFrame !== null) window.cancelAnimationFrame(gradientFrame);
    if (viewerHeartbeatTimer !== null) window.clearInterval(viewerHeartbeatTimer);
    stopPortraitPlayback();
    window.removeEventListener('keydown', onKeydown);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
    window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', applyTheme);
    document.body.style.overflow = '';
});

watch(theme, (value) => {
    window.localStorage.setItem('profile-theme', value);
    applyTheme();
});

watch([selectedBadge, selectedCertificate, selectedDesign, selectedProject, selectedBackground], ([badge, certificate, design, project, bg]) => {
    document.body.style.overflow = badge || certificate || design || project || bg ? 'hidden' : '';
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
                    <img v-if="profile.avatar_url" :src="avatarUrlForSize(profile.avatar_url, 64)" alt="" @error="$event.currentTarget.remove()">
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
            </nav>

            <div class="theme-switch" aria-label="Color theme">
                <button type="button" title="Light theme" aria-label="Light theme" :class="{ active: !isDark }" @click="setTheme('light')"><Sun :size="15" /></button>
                <button type="button" title="Dark theme" aria-label="Dark theme" :class="{ active: isDark }" @click="setTheme('dark')"><Moon :size="15" /></button>
            </div>
        </header>

        <aside class="live-viewers" role="status" aria-live="polite" aria-label="Current portfolio viewers">
            <span class="live-viewers-dot" aria-hidden="true"></span>
            <span class="live-viewers-copy">
                <small>Live now</small>
                <strong>{{ currentViewerCount }} {{ currentViewerCount === 1 ? 'viewer' : 'viewers' }}</strong>
            </span>
        </aside>

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
                            <video ref="portraitReverseVideo" :class="{ 'is-active': activePortraitDirection === -1 }" :src="profilePortraitReverseUrl" muted playsinline preload="none" disablepictureinpicture disableremoteplayback controlslist="nodownload noplaybackrate noremoteplayback" aria-hidden="true" @loadedmetadata="initializePortraitVideo(-1)" @canplay="resumePendingPortrait(-1)"></video>
                        </div>
                        <img v-else-if="profile.avatar_url" :src="profile.avatar_url" :alt="profile.display_name">
                        <div v-else class="portrait-fallback">{{ initials(profile.display_name) }}</div>
                        <figcaption>
                            <span>Information Analyst</span>
                            <span>Research / Programming / Design</span>
                        </figcaption>
                    </figure>
                </div>

                <div class="stat-line" aria-label="Portfolio highlights">
                    <div v-for="stat in profile.stats" :key="stat.label">
                        <strong>{{ stat.value }}</strong>
                        <span>{{ stat.label }}</span>
                    </div>
                </div>
            </section>

            <section id="work" class="content-band section-wrap">
                <div class="section-head">
                    <div><span class="folio-label">01 / featured</span><h2>Selected work</h2></div>
                    <p>Production systems, research applications, responsive portals, and data analytics.</p>
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
                            <button class="project-more" type="button" aria-haspopup="dialog" @click="openProject(project)">View details<ArrowUpRight :size="14" /></button>
                        </div>
                    </article>
                </div>
            </section>

            <section id="designs" class="content-band section-wrap design-band">
                <div class="section-head">
                    <div><span class="folio-label">02 / design</span><h2>Selected media</h2></div>
                    <div class="section-actions">
                        <Link class="section-link" href="/designs">Design archive<ArrowUpRight :size="14" /></Link>
                        <a v-if="profile.canva_url" class="section-link" :href="profile.canva_url" target="_blank" rel="noreferrer">Canva public profile<ArrowUpRight :size="14" /></a>
                    </div>
                </div>

                <div class="filter-row" aria-label="Design formats">
                    <button v-for="type in designTypes" :key="type.key" type="button" :class="{ active: activeDesignType === type.key }" @click="activeDesignType = type.key">{{ type.label }}</button>
                </div>

                <div v-if="featuredDesignMedia.length" class="design-grid">
                    <article v-for="item in featuredDesignMedia" :key="item.id" class="design-card">
                        <div class="design-media">
                            <video v-if="item.media_type === 'video'" :src="item.media_url" :poster="item.thumbnail_url || undefined" muted playsinline preload="metadata"></video>
                            <div v-else-if="item.media_type === 'pdf'" class="pdf-card-preview">
                                <FileText :size="30" />
                                <span>{{ item.type_label }}</span>
                            </div>
                            <img v-else :src="item.media_url" :alt="item.title" loading="lazy">
                            <span class="design-expand" aria-hidden="true"><Maximize2 :size="15" /></span>
                        </div>
                        <div class="design-copy">
                            <div class="meta-line"><span>{{ item.type_label }}</span><span>{{ item.year || 'Portfolio' }}</span></div>
                            <h3>{{ item.title }}</h3>
                        </div>
                        <button class="design-card-trigger" type="button" :aria-label="`Open ${item.title}`" @click="openDesign(item)"></button>
                    </article>
                </div>
                <div v-else class="design-empty">
                    <Images :size="24" />
                    <div><strong>No featured media configured</strong><p>Explore all available design assets in the media archive.</p></div>
                </div>
            </section>

            <section id="published" class="content-band section-wrap">
                <div class="section-head">
                    <div><span class="folio-label">03 / research</span><h2>Published works</h2></div>
                    <div class="search-box">
                        <Search :size="15" />
                        <input v-model="publicationSearch" type="search" placeholder="Filter research and publications" aria-label="Filter publications">
                    </div>
                </div>

                <div class="filter-row" aria-label="Publication formats">
                    <button v-for="type in workTypes" :key="type.key" type="button" :class="{ active: activeWorkType === type.key }" @click="activeWorkType = type.key">{{ type.label }}</button>
                </div>

                <div class="publication-list">
                    <article v-for="work in filteredWorks" :key="work.id" class="publication-row">
                        <div class="publication-cover">
                            <img v-if="work.cover_preview_url || work.cover_url" :src="work.cover_preview_url || work.cover_url" :alt="`${work.title} cover`" loading="lazy">
                            <BookOpen v-else :size="24" />
                        </div>
                        <div class="publication-main">
                            <div class="meta-line"><span>{{ work.type_label }}</span><span>{{ work.published_on }}</span></div>
                            <h3>{{ work.title }}</h3>
                            <p class="publication-name">{{ work.publication }}</p>
                            <p>{{ work.summary }}</p>
                            <div class="tag-row"><span v-for="tag in work.tags" :key="tag">{{ tag }}</span></div>
                        </div>
                        <div class="publication-action">
                            <a v-if="work.external_url" :href="work.external_url" target="_blank" rel="noreferrer" :title="`Open ${work.title}`" :aria-label="`Open ${work.title}`"><ArrowUpRight :size="18" /></a>
                        </div>
                    </article>
                </div>
            </section>

            <section v-if="githubActivity" id="github" class="content-band section-wrap github-band">
                <div class="section-head">
                    <div><span class="folio-label">04 / code</span><h2>GitHub contributions</h2></div>
                    <a class="section-link" :href="`https://github.com/${profile.github_username}`" target="_blank" rel="noreferrer">@{{ profile.github_username }}<ArrowUpRight :size="14" /></a>
                </div>

                <div class="contribution-panel">
                    <div class="contribution-summary">
                        <strong>{{ githubActivity.total }}</strong>
                        <span>contributions in the last year</span>
                    </div>
                    <div class="contribution-scroll">
                        <div class="contribution-grid" aria-hidden="true">
                            <div v-for="(week, weekIndex) in githubActivity.weeks" :key="weekIndex" class="contribution-week">
                                <span v-for="(day, dayIndex) in week" :key="dayIndex" class="contribution-day" :class="`level-${day.level}`" :title="`${day.count} contributions on ${day.date}`"></span>
                            </div>
                        </div>
                    </div>
                    <div class="contribution-legend"><small>Less</small><div><i class="level-0"></i><i class="level-1"></i><i class="level-2"></i><i class="level-3"></i><i class="level-4"></i></div><small>More</small></div>
                </div>
            </section>

            <section v-if="achievements.length" id="achievements" class="content-band section-wrap">
                <div class="section-head">
                    <div><span class="folio-label">05 / milestones</span><h2>Achievements</h2></div>
                </div>

                <div class="achievement-grid">
                    <article v-for="achievement in achievements" :key="achievement.id" class="achievement-item">
                        <div class="achievement-mark">
                            <img v-if="achievementLogoUrl(achievement)" :src="achievementLogoUrl(achievement)" :alt="achievement.issuer || achievement.title" loading="lazy">
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
                    <div class="section-actions">
                        <Link class="section-link" href="/badges">Badge archive<ArrowUpRight :size="14" /></Link>
                        <a class="section-link" :href="`https://www.credly.com/users/${profile.credly_username}`" target="_blank" rel="noreferrer">Credly profile<ArrowUpRight :size="14" /></a>
                    </div>
                </div>

                <div class="badge-layout">
                    <div class="badge-grid">
                        <button
                            v-for="badge in visibleBadges"
                            :key="badge.id"
                            class="badge-card"
                            type="button"
                            @mouseenter="hoveredBadge = badge"
                            @mouseleave="hoveredBadge = null"
                            @focus="hoveredBadge = badge"
                            @click="openBadge(badge)"
                        >
                            <span class="badge-image">
                                <img
                                    v-if="badge.image_url"
                                    :src="badge.image_url"
                                    :alt="badge.name"
                                    loading="lazy"
                                    decoding="async"
                                    @error="onBadgeImageError($event)"
                                >
                                <span class="badge-fallback" :class="{ 'is-active': !badge.image_url }">{{ initials(badge.name) }}</span>
                            </span>
                            <span class="badge-copy">
                                <small>{{ badge.issuer || 'Credential' }}</small>
                                <strong>{{ badge.name }}</strong>
                                <span>{{ badge.issued_at }}</span>
                            </span>
                        </button>
                    </div>

                    <aside v-if="previewBadge" class="badge-preview">
                        <div class="preview-badge-header">
                            <span class="folio-label">Credential preview</span>
                            <span class="badge-verified-pill"><CheckCircle2 :size="12" />Verified</span>
                        </div>
                        <div class="badge-preview-art">
                            <img
                                v-if="previewBadge.image_url"
                                :src="previewBadge.image_url"
                                :alt="previewBadge.name"
                                decoding="async"
                                @error="onBadgeImageError($event)"
                            >
                            <div class="preview-fallback" :class="{ 'is-active': !previewBadge.image_url }">{{ initials(previewBadge.name) }}</div>
                        </div>
                        <p class="preview-issuer">{{ previewBadge.issuer }}</p>
                        <h3>{{ previewBadge.name }}</h3>
                        <button class="preview-action" type="button" @click="openBadge(previewBadge)">View details<ArrowUpRight :size="14" /></button>
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
                            <span class="timeline-date">{{ item.start_date ? item.start_date + ' – ' : '' }}{{ item.end_date }}</span>
                            <span class="timeline-entry">
                                <span class="organization-mark" aria-hidden="true"><span>{{ initials(item.institution) }}</span><img v-if="item.logo_url" :src="item.logo_url" alt="" loading="lazy" @error="$event.currentTarget.remove()"></span>
                                <span class="timeline-copy"><span class="meta-line"><span>{{ item.level }}</span></span><strong class="timeline-heading">{{ item.program }}</strong><span class="school-name">{{ item.institution }}</span><span v-if="item.description" class="timeline-summary">{{ item.description }}</span></span>
                            </span>
                            <Maximize2 class="timeline-expand" :size="15" aria-hidden="true" />
                        </button>
                    </div>
                </div>

                <div v-if="profile.skills?.length" class="skills-index">
                    <span class="folio-label">Core competencies</span>
                    <div><span v-for="skill in profile.skills" :key="skill">{{ skill }}</span></div>
                </div>
            </section>

            <section v-if="portfolioTools.length" id="tools" class="content-band section-wrap tools-band">
                <div class="section-head">
                    <div><span class="folio-label">09 / toolkit</span><h2>Tools & technologies</h2></div>
                    <p>Frameworks, cloud environments, data platforms, and productivity suites.</p>
                </div>

                <div class="tool-groups">
                    <section v-for="category in groupedPortfolioTools" :key="category.key" class="tool-category">
                        <header class="tool-category-head">
                            <component :is="category.icon" :size="17" />
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
                                    <img v-if="tool.icon_url" :src="tool.icon_url" :alt="tool.name" loading="lazy">
                                    <component :is="toolFallbackIcon(tool)" v-else :size="17" />
                                </span>
                                <div class="tool-copy">
                                    <strong>{{ tool.name }}</strong>
                                    <span v-if="tool.description">{{ tool.description }}</span>
                                </div>
                                <ArrowUpRight v-if="tool.external_url" :size="13" />
                            </component>
                        </div>
                    </section>
                </div>
            </section>

            <section class="collaboration-bridge section-wrap" aria-labelledby="collaboration-title">
                <div>
                    <span class="folio-label">10 / collaboration</span>
                    <h2 id="collaboration-title">Open to innovative projects & research.</h2>
                    <p>Available for technical systems development, data solutions, IT consulting, and academic collaborations.</p>
                </div>
                <div class="collaboration-actions">
                    <Link href="/services">View services catalog<ArrowUpRight :size="15" /></Link>
                    <a
                        :href="gmailComposeUrl(profile.email, { subject: 'Project inquiry' })"
                        target="_blank"
                        rel="noopener noreferrer"
                    >Request a quote<ArrowUpRight :size="15" /></a>
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
                    <div class="modal-badge">
                        <img
                            v-if="selectedBadge.image_url"
                            :src="selectedBadge.image_url"
                            :alt="selectedBadge.name"
                            @error="onBadgeImageError($event)"
                        >
                        <span class="badge-fallback" :class="{ 'is-active': !selectedBadge.image_url }">{{ initials(selectedBadge.name) }}</span>
                    </div>
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
.portfolio-shell { position: relative; min-height: 100vh; overflow-x: hidden; background: var(--profile-bg); color: var(--profile-ink); --gradient-x: 50%; --gradient-y: 18%; --gradient-counter-x: 50%; --gradient-counter-y: 54%; }
.gemini-wash { position: fixed; inset: 0; z-index: 0; pointer-events: none; opacity: 0.85; background: radial-gradient(circle at var(--gradient-x) var(--gradient-y), color-mix(in srgb, var(--profile-blue-light) 12%, transparent), transparent 38%), radial-gradient(circle at var(--gradient-counter-x) var(--gradient-counter-y), color-mix(in srgb, var(--profile-blue) 9%, transparent), transparent 42%); transition: opacity 500ms ease; }
:global(:root[data-theme='dark']) .gemini-wash { opacity: 0.95; background: radial-gradient(circle at var(--gradient-x) var(--gradient-y), color-mix(in srgb, #3b82f6 18%, transparent), transparent 40%), radial-gradient(circle at var(--gradient-counter-x) var(--gradient-counter-y), color-mix(in srgb, #8b5cf6 14%, transparent), transparent 46%); }
.section-wrap { width: min(100% - clamp(2rem, 6vw, 6rem), 82rem); margin-inline: auto; position: relative; z-index: 1; }
.topbar { position: sticky; top: 0; z-index: 30; display: flex; align-items: center; justify-content: space-between; gap: 1.2rem; border-bottom: 1px solid color-mix(in srgb, var(--profile-ink) 12%, transparent); background: color-mix(in srgb, var(--profile-bg) 84%, transparent); padding: 0.9rem clamp(1rem, 3.5vw, 2.5rem); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); transition: border-color 300ms ease, background-color 300ms ease; }
.brand { display: inline-flex; align-items: center; gap: 0.75rem; color: var(--profile-ink); font-weight: 700; text-decoration: none; }
.brand-mark { display: grid; width: 2.25rem; height: 2.25rem; place-items: center; border: 1px solid color-mix(in srgb, var(--profile-ink) 20%, transparent); border-radius: 6px; overflow: hidden; background: var(--profile-50); font-family: var(--font-mono); font-size: 0.75rem; box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.08); }
.brand-mark img { width: 100%; height: 100%; object-fit: cover; }
.brand-name { font-family: var(--font-mono); font-size: 0.92rem; letter-spacing: 0.02em; text-transform: uppercase; }
.nav-links { display: flex; flex-wrap: wrap; gap: clamp(0.6rem, 1.8vw, 1.5rem); }
.nav-links a { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.05em; text-decoration: none; text-transform: uppercase; transition: color 180ms ease; }
.nav-links a:hover, .nav-links a:focus-visible { color: var(--profile-ink); }
.theme-switch { display: inline-flex; border: 1px solid color-mix(in srgb, var(--profile-ink) 18%, transparent); border-radius: 999px; background: var(--profile-50); padding: 2px; }
.theme-switch button { display: grid; width: 1.85rem; height: 1.85rem; place-items: center; border: 0; border-radius: 999px; background: transparent; color: var(--profile-400); cursor: pointer; transition: color 180ms ease, background-color 180ms ease; }
.theme-switch button.active { background: var(--profile-ink); color: var(--profile-bg); box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12); }
.live-viewers { position: fixed; right: clamp(1rem, 2.5vw, 1.8rem); bottom: clamp(1rem, 2.5vw, 1.8rem); z-index: 25; display: inline-flex; align-items: center; gap: 0.65rem; border: 1px solid color-mix(in srgb, var(--profile-ink) 16%, transparent); border-radius: 999px; background: color-mix(in srgb, var(--profile-bg) 88%, transparent); padding: 0.45rem 0.95rem; box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.18); backdrop-filter: blur(16px); }
.live-viewers-dot { width: 0.55rem; height: 0.55rem; border-radius: 50%; background: #16a34a; box-shadow: 0 0 0 0 rgb(22 163 74 / 65%); animation: viewer-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
.live-viewers-copy { display: grid; line-height: 1.15; font-family: var(--font-mono); }
.live-viewers-copy small { color: var(--profile-500); font-size: 0.55rem; text-transform: uppercase; }
.live-viewers-copy strong { font-size: 0.72rem; }
@keyframes viewer-pulse { 70%, 100% { box-shadow: 0 0 0 0.42rem rgb(22 163 74 / 0%); } }
@media (prefers-reduced-motion: reduce) { .live-viewers-dot { animation: none; } }
.hero-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(17rem, 0.65fr); column-gap: clamp(2rem, 8vw, 7rem); row-gap: 1.3rem; align-items: start; margin-top: 1.5rem; }
.hero-copy { grid-column: 1; grid-row: 2; padding-bottom: clamp(0.75rem, 2vw, 1.75rem); }
.hero-role { grid-column: 1; grid-row: 1; max-width: 43rem; margin: 0; color: var(--profile-700); font-size: clamp(1rem, 2vw, 1.35rem); font-weight: 500; }
h1 { max-width: 15ch; margin: 0; font-family: var(--font-mono); font-size: clamp(3.1rem, 8vw, 7.2rem); font-weight: 700; line-height: 0.91; overflow-wrap: anywhere; letter-spacing: -0.02em; }
.hero-bio { max-width: 43rem; margin: 1.8rem 0 0; color: var(--profile-700); font-family: var(--font-serif); font-size: clamp(1.05rem, 2vw, 1.28rem); line-height: 1.7; }
.location-line { display: flex; flex-wrap: wrap; gap: 0.7rem 1.2rem; margin-top: 1.25rem; color: var(--profile-500); font-size: 0.7rem; text-transform: uppercase; font-family: var(--font-mono); }
.location-line span { display: inline-flex; align-items: center; gap: 0.35rem; }
.hero-links { display: flex; flex-wrap: wrap; gap: 0.65rem; margin-top: 1.7rem; }
.hero-links a, .link-row a, .section-link, .inline-link, .modal-actions a, .badge-preview button, .preview-action { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; text-decoration: none; transition: transform 180ms ease, color 180ms ease; }
.hero-links a { border: 1px solid color-mix(in srgb, var(--profile-ink) 20%, transparent); border-radius: 4px; padding: 0.4rem 0.75rem; background: var(--profile-50); }
.hero-links a:hover { border-color: var(--profile-ink); transform: translateY(-1px); }
.portrait-block { grid-column: 2; grid-row: 2; margin: 0; }
.portrait-block > img, .portrait-video-stage, .portrait-fallback { width: 100%; aspect-ratio: 4 / 5; object-fit: cover; border-radius: 6px 6px 0 0; }
.portrait-block > img, .portrait-fallback { border: 1px solid var(--profile-ink); }
.portrait-video-stage { position: relative; overflow: hidden; background: var(--profile-ink); pointer-events: none; border: 1px solid color-mix(in srgb, var(--profile-ink) 30%, transparent); }
.portrait-video-stage video { position: absolute; inset: 1px; display: block; width: calc(100% - 2px); height: calc(100% - 2px); object-fit: cover; object-position: center 12%; opacity: 0; visibility: hidden; pointer-events: none; }
.portrait-video-stage video.is-active { opacity: 1; visibility: visible; }
.portrait-fallback { display: grid; place-items: center; background: var(--profile-ink); color: var(--profile-bg); font-family: var(--font-mono); font-size: 4rem; }
.portrait-block figcaption { display: flex; justify-content: space-between; gap: 0.75rem; border: 1px solid var(--profile-ink); border-top: 0; border-radius: 0 0 6px 6px; padding: 0.6rem 0.75rem; background: var(--profile-50); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; color: var(--profile-500); }
.stat-line { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); border-top: 1px solid color-mix(in srgb, var(--profile-ink) 20%, transparent); border-bottom: 1px solid color-mix(in srgb, var(--profile-ink) 20%, transparent); margin-top: clamp(2rem, 4vw, 3.5rem); background: var(--profile-50); border-radius: 6px; overflow: hidden; }
.stat-line div { display: flex; align-items: baseline; gap: 0.75rem; border-right: 1px solid color-mix(in srgb, var(--profile-ink) 12%, transparent); padding: 1.25rem; }
.stat-line div:last-child { border-right: 0; }
.stat-line strong { font-family: var(--font-mono); font-size: 2.1rem; font-weight: 700; }
.stat-line span { color: var(--profile-500); font-size: 0.68rem; text-transform: uppercase; font-family: var(--font-mono); }
.content-band { content-visibility: auto; contain-intrinsic-size: auto 50rem; scroll-margin-top: 2rem; border-bottom: 1px solid var(--profile-200); padding: clamp(3rem, 6vw, 5rem) 0; }
.section-head { display: flex; align-items: end; justify-content: space-between; gap: 1.5rem; margin-bottom: 1.75rem; }
.folio-label { display: inline-block; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.06em; text-transform: uppercase; }
.section-head h2, .footer h2 { margin: 0.4rem 0 0; font-size: clamp(2.1rem, 5vw, 4rem); line-height: 1; font-weight: 700; letter-spacing: -0.02em; }
.section-head > p { max-width: 32rem; margin: 0; color: var(--profile-700); font-family: var(--font-serif); font-size: 1.05rem; line-height: 1.6; }
.section-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 0.8rem; }
.section-link { padding: 0.35rem 0.65rem; border: 1px solid color-mix(in srgb, var(--profile-ink) 18%, transparent); border-radius: 4px; background: var(--profile-50); }
.section-link:hover { border-color: var(--profile-ink); transform: translateY(-1px); }
.filter-row { display: flex; flex-wrap: wrap; gap: 0.45rem; margin-bottom: 1.35rem; }
.filter-row button { border: 1px solid var(--profile-300); border-radius: 999px; background: var(--profile-50); padding: 0.4rem 0.75rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.66rem; text-transform: uppercase; cursor: pointer; transition: all 180ms ease; }
.filter-row button.active, .filter-row button:hover { border-color: var(--profile-ink); background: var(--profile-ink); color: var(--profile-bg); box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); }
.project-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.9rem; }
.project-card { position: relative; display: grid; min-width: 0; min-height: 19rem; grid-template-columns: 3.5rem minmax(0, 1fr); border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); transition: border-color 200ms ease, transform 200ms ease, box-shadow 200ms ease; overflow: hidden; }
.project-card:hover { border-color: var(--profile-ink); transform: translateY(-3px); box-shadow: 0 12px 28px -10px rgba(0, 0, 0, 0.15); }
.project-card:last-child:nth-child(odd) { width: 100%; grid-column: 1 / -1; }
.project-index { border-right: 1px solid var(--profile-200); padding: 1.25rem 0; color: var(--profile-400); font-family: var(--font-mono); font-size: 0.75rem; font-weight: 700; text-align: center; background: color-mix(in srgb, var(--profile-bg) 50%, var(--profile-50)); }
.project-copy { display: flex; min-width: 0; flex-direction: column; align-items: flex-start; padding: 1.35rem; }
.project-copy h3 { line-height: 1.25; margin: 0.5rem 0 0.6rem; font-size: 1.25rem; font-weight: 700; }
.project-copy > p { margin: 0; color: var(--profile-700); font-family: var(--font-serif); line-height: 1.6; }
.project-copy .tag-row { margin: 0.9rem 0 1.2rem; }
.project-more { display: inline-flex; align-items: center; gap: 0.35rem; border: 0; border-bottom: 1.5px solid var(--profile-ink); margin-top: auto; background: transparent; padding: 0 0 0.2rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; cursor: pointer; transition: color 180ms ease, border-color 180ms ease; }
.project-more:hover, .project-more:focus-visible { color: var(--profile-blue); border-color: var(--profile-blue); }
.design-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.9rem; align-items: stretch; }
.design-card { position: relative; display: grid; height: 100%; min-width: 0; grid-template-rows: auto 1fr; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); transition: border-color 200ms ease, transform 200ms ease, box-shadow 200ms ease; }
.design-card:hover, .design-card:focus-within { border-color: var(--profile-ink); transform: translateY(-3px); box-shadow: 0 12px 28px -10px rgba(0, 0, 0, 0.15); }
.design-media { position: relative; aspect-ratio: 4 / 3; overflow: hidden; border-bottom: 1px solid var(--profile-200); background: #111513; }
.design-media img, .design-media video, .design-media iframe { display: block; width: 100%; height: 100%; }
.design-media img, .design-media video { object-fit: contain; transition: transform 450ms cubic-bezier(0.16, 1, 0.3, 1); }
.design-media iframe { border: 0; pointer-events: none; }
.pdf-card-preview { display: grid; width: 100%; height: 100%; place-content: center; justify-items: center; gap: 0.65rem; color: #f7f1e7; font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; }
.design-card:hover .design-media img, .design-card:hover .design-media video, .design-card:focus-within .design-media img, .design-card:focus-within .design-media video { transform: scale(1.03); }
.design-expand { position: absolute; right: 0.65rem; bottom: 0.65rem; display: grid; width: 2.1rem; height: 2.1rem; place-items: center; border: 1px solid #fff; border-radius: 50%; background: color-mix(in srgb, #000 65%, transparent); color: #fff; backdrop-filter: blur(6px); }
.design-copy { display: grid; min-height: 5.5rem; align-content: start; padding: 1rem; }
.design-copy h3 { display: -webkit-box; overflow: hidden; margin: 0.6rem 0 0; font-size: 1.05rem; line-height: 1.35; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.design-card-trigger { position: absolute; inset: 0; z-index: 2; border: 0; border-radius: inherit; outline-offset: -3px; background: transparent; cursor: zoom-in; }
.design-card-trigger:focus-visible { outline: 2px solid var(--profile-ink); }
.design-empty { display: flex; min-height: 15rem; align-items: center; justify-content: center; gap: 0.9rem; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); color: var(--profile-500); }
.design-empty div { display: grid; gap: 0.25rem; }
.design-empty strong { color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.75rem; text-transform: uppercase; }
.design-empty p { margin: 0; font-family: var(--font-serif); }
.meta-line { display: flex; justify-content: space-between; gap: 1rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.65rem; text-transform: uppercase; }
.tag-row { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.tag-row span { border: 1px solid var(--profile-300); border-radius: 999px; padding: 0.22rem 0.55rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; background: color-mix(in srgb, var(--profile-bg) 60%, transparent); }
.search-box { display: flex; min-width: min(100%, 18rem); align-items: center; gap: 0.55rem; border: 1px solid var(--profile-300); border-radius: 6px; padding: 0 0.75rem; background: var(--profile-50); }
.search-box input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; padding: 0.65rem 0; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; }
.publication-list { border-top: 1px solid var(--profile-200); }
.publication-row { display: grid; grid-template-columns: 6rem minmax(0, 1fr) auto; gap: 1.35rem; align-items: start; border-bottom: 1px solid var(--profile-200); padding: 1.35rem 0; transition: background-color 180ms ease; }
.publication-cover { display: grid; width: 6rem; aspect-ratio: 4 / 5; place-items: center; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-100); overflow: hidden; }
.publication-cover img { width: 100%; height: 100%; object-fit: cover; object-position: top; }
.publication-main h3 { margin: 0.4rem 0 0.3rem; font-size: 1.15rem; font-weight: 700; line-height: 1.35; }
.publication-main > p { max-width: 54rem; margin: 0 0 0.6rem; color: var(--profile-700); font-family: var(--font-serif); line-height: 1.6; }
.publication-name { margin: 0.2rem 0 0.5rem !important; color: var(--profile-ink) !important; font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; font-weight: 600; }
.publication-action a { display: inline-grid; width: 2.6rem; height: 2.6rem; place-items: center; border: 1px solid var(--profile-300); border-radius: 50%; background: var(--profile-50); color: var(--profile-ink); transition: all 180ms ease; }
.publication-action a:hover { border-color: var(--profile-ink); background: var(--profile-ink); color: var(--profile-bg); transform: scale(1.08); }
.contribution-panel { border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); padding: clamp(1rem, 3vw, 1.6rem); }
.contribution-summary { display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 1rem; }
.contribution-summary strong { font-family: var(--font-mono); font-size: 2.6rem; font-weight: 700; }
.contribution-summary span { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; }
.contribution-scroll { overflow-x: auto; padding-bottom: 0.5rem; }
.contribution-grid { display: flex; width: max-content; gap: 3px; margin-inline: auto; }
.contribution-week { display: grid; grid-template-rows: repeat(7, 10px); gap: 3px; }
.contribution-day, .contribution-legend i { display: block; width: 10px; height: 10px; border: 1px solid color-mix(in srgb, var(--profile-ink) 8%, transparent); border-radius: 2px; background: var(--profile-100); }
.level-1 { background: #9be9a8 !important; }
.level-2 { background: #40c463 !important; }
.level-3 { background: #30a14e !important; }
.level-4 { background: #216e39 !important; }
.contribution-legend { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 0.75rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; }
.contribution-legend div { display: flex; gap: 0.3rem; }
.achievement-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border: 1px solid var(--profile-200); border-radius: 6px; overflow: hidden; background: var(--profile-50); }
.achievement-item { position: relative; min-height: 16rem; border-right: 1px solid var(--profile-200); border-bottom: 1px solid var(--profile-200); padding: 1.4rem; transition: background-color 180ms ease; }
.achievement-item:nth-child(2n) { border-right: 0; }
.achievement-item:hover { background: color-mix(in srgb, var(--profile-ink) 2%, var(--profile-50)); }
.achievement-mark { display: grid; width: 7.5rem; height: 3.2rem; place-items: center; border: 1px solid var(--profile-200); border-radius: 4px; margin-bottom: 1.5rem; overflow: hidden; background: #ffffff; padding: 0.35rem; }
.achievement-mark img { display: block; width: auto; height: auto; max-width: 6.5rem; max-height: 2.4rem; object-fit: contain; }
.achievement-item h3 { margin: 0.5rem 0 0.4rem; font-size: 1.2rem; font-weight: 700; line-height: 1.3; }
.achievement-item p { margin: 0 0 1rem; color: var(--profile-700); font-family: var(--font-serif); line-height: 1.6; }
.badge-layout { display: grid; grid-template-columns: minmax(0, 1fr) 19rem; gap: 1.25rem; align-items: start; }
.badge-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
.badge-card { display: grid; min-height: 14.5rem; grid-template-rows: 7rem 1fr; gap: 0.8rem; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); padding: 0.9rem; color: var(--profile-ink); text-align: left; cursor: pointer; transition: all 200ms ease; }
.badge-card:hover, .badge-card:focus-visible { border-color: var(--profile-ink); transform: translateY(-3px); box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.14); }
.badge-image { position: relative; display: grid; width: 7rem; height: 7rem; place-items: center; justify-self: center; font-family: var(--font-mono); }
.badge-image img { width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.1)); }
.badge-fallback { display: none; width: 100%; height: 100%; place-items: center; border: 1px solid var(--profile-300); border-radius: 50%; background: var(--profile-100); font-size: 1.5rem; font-weight: 700; color: var(--profile-700); }
.badge-fallback.is-active { display: grid; }
.badge-copy { display: grid; align-content: end; gap: 0.25rem; }
.badge-copy small, .badge-copy > span { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; }
.badge-copy strong { font-size: 0.85rem; line-height: 1.35; font-weight: 600; }
.badge-preview { position: sticky; top: 5.5rem; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); padding: 1.25rem; box-shadow: 0 8px 24px -6px rgba(0, 0, 0, 0.08); }
.preview-badge-header { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem; }
.badge-verified-pill { display: inline-flex; align-items: center; gap: 0.3rem; border: 1px solid #16a34a; border-radius: 999px; padding: 0.2rem 0.5rem; font-family: var(--font-mono); font-size: 0.6rem; color: #16a34a; text-transform: uppercase; }
.badge-preview-art { position: relative; display: grid; width: 9.5rem; height: 9.5rem; place-items: center; margin: 1.2rem auto; }
.badge-preview-art img { width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.15)); }
.preview-fallback { display: none; width: 100%; height: 100%; place-items: center; border: 1px solid var(--profile-300); border-radius: 50%; background: var(--profile-100); font-family: var(--font-mono); font-size: 2.2rem; font-weight: 700; color: var(--profile-700); }
.preview-fallback.is-active { display: grid; }
.preview-issuer { margin: 0; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; }
.badge-preview h3 { margin: 0.4rem 0 0.9rem; font-size: 1.05rem; line-height: 1.35; font-weight: 700; }
.preview-action { border: 0; border-bottom: 1.5px solid var(--profile-ink); background: transparent; padding: 0 0 0.2rem; cursor: pointer; font-weight: 600; }
.preview-action:hover { color: var(--profile-blue); border-color: var(--profile-blue); }
.archive-route { display: flex; width: fit-content; align-items: center; gap: 0.35rem; border-bottom: 1.5px solid var(--profile-ink); margin: 1.8rem auto 0; padding-bottom: 0.25rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.72rem; font-weight: 600; text-decoration: none; text-transform: uppercase; }
.archive-route:hover { color: var(--profile-blue); border-color: var(--profile-blue); }
.certificate-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
.certificate-card { position: relative; display: grid; width: 100%; grid-template-columns: 5rem minmax(0, 1fr) auto; gap: 0.85rem; align-items: center; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); padding: 0.75rem; color: var(--profile-ink); font: inherit; text-align: left; cursor: pointer; transition: all 180ms ease; }
.certificate-card:hover { border-color: var(--profile-ink); transform: translateY(-2px); box-shadow: 0 8px 20px -6px rgba(0, 0, 0, 0.12); }
.certificate-art { display: grid; width: 5rem; aspect-ratio: 4 / 5; place-items: center; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-bg); overflow: hidden; }
.certificate-art img { width: 100%; height: 100%; object-fit: contain; object-position: center; padding: 0.65rem; }
.provider-initials { font-family: var(--font-mono); font-size: 0.76rem; letter-spacing: 0.08em; }
.certificate-copy { min-width: 0; display: grid; gap: 0.25rem; }
.certificate-copy small, .certificate-copy > span { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.6rem; text-transform: uppercase; }
.certificate-copy strong { display: -webkit-box; overflow: hidden; font-size: 0.8rem; line-height: 1.35; -webkit-box-orient: vertical; -webkit-line-clamp: 3; font-weight: 600; }
.tool-groups { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border: 1px solid var(--profile-200); border-radius: 6px; overflow: hidden; background: var(--profile-50); }
.tool-category { min-width: 0; border-right: 1px solid var(--profile-200); border-bottom: 1px solid var(--profile-200); padding: clamp(1rem, 3vw, 1.4rem); }
.tool-category:nth-child(2n) { border-right: 0; }
.tool-category-head { display: flex; gap: 0.65rem; align-items: center; padding-bottom: 0.9rem; border-bottom: 1px solid var(--profile-200); }
.tool-category-head h3 { margin: 0; font-family: var(--font-mono); font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.04em; }
.tool-list { display: grid; }
.tool-entry { display: grid; min-width: 0; grid-template-columns: 2.75rem minmax(0, 1fr) auto; gap: 0.85rem; align-items: center; border-top: 1px solid var(--profile-100); padding: 0.8rem 0; color: var(--profile-ink); text-decoration: none; transition: background-color 160ms ease; }
.tool-entry:first-child { border-top: 0; }
a.tool-entry:hover .tool-copy strong { color: var(--profile-blue); }
.tool-mark { position: relative; display: grid; width: 2.75rem; aspect-ratio: 1; place-items: center; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 6px; background: #ffffff; font-family: var(--font-mono); font-size: 0.65rem; font-weight: 700; color: #1e293b; }
.tool-mark img { position: absolute; inset: 0; width: 100%; height: 100%; background: #ffffff; object-fit: contain; padding: 0.35rem; }
.tool-copy { min-width: 0; display: grid; gap: 0.2rem; }
.tool-copy strong { font-size: 0.85rem; line-height: 1.35; font-weight: 600; }
.tool-copy > span { color: var(--profile-500); font-family: var(--font-serif); font-size: 0.82rem; line-height: 1.45; }
.background-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2rem; }
.timeline-title { display: flex; align-items: center; gap: 0.55rem; border-bottom: 1px solid var(--profile-ink); padding-bottom: 0.75rem; font-family: var(--font-mono); font-size: 0.75rem; text-transform: uppercase; font-weight: 700; }
.timeline-row { position: relative; display: grid; width: 100%; grid-template-columns: 8rem minmax(0, 1fr); gap: 1rem; border: 0; border-bottom: 1px solid var(--profile-200); background: transparent; padding: 1.4rem 2rem 1.4rem 0; color: var(--profile-ink); font: inherit; text-align: left; }
.timeline-date { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; line-height: 1.6; text-transform: uppercase; }
.timeline-entry { display: grid; grid-template-columns: 3.25rem minmax(0, 1fr); gap: 0.9rem; align-items: start; min-width: 0; }
.organization-mark { position: relative; display: grid; width: 3.25rem; aspect-ratio: 1; place-items: center; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 6px; background: #fff; color: #17251e; font-family: var(--font-mono); font-size: 0.7rem; font-weight: 700; }
.organization-mark img { position: absolute; inset: 0; width: 100%; height: 100%; background: #fff; object-fit: contain; padding: 0.25rem; }
.timeline-copy { display: grid; min-width: 0; gap: 0.55rem; align-content: start; }
.timeline-heading { display: block; margin: 0.15rem 0 0; font-size: 1.05rem; line-height: 1.4; font-weight: 700; }
.timeline-summary { display: block; margin: 0; color: var(--profile-700); font-family: var(--font-serif); line-height: 1.65; }
.school-name { color: var(--profile-ink); font-size: 0.85rem; font-weight: 700; line-height: 1.45; }
.timeline-trigger { cursor: pointer; transition: background-color 160ms ease, padding-left 160ms ease; }
.timeline-trigger:hover, .timeline-trigger:focus-visible { background: var(--profile-50); padding-left: 0.65rem; border-radius: 4px; }
.timeline-expand { position: absolute; top: 1.4rem; right: 0.35rem; color: var(--profile-500); transition: color 160ms ease, transform 160ms ease; }
.timeline-trigger:hover .timeline-expand, .timeline-trigger:focus-visible .timeline-expand { color: var(--profile-ink); transform: scale(1.12); }
.skills-index { display: grid; grid-template-columns: 10rem minmax(0, 1fr); gap: 1rem; border-top: 1px solid var(--profile-200); margin-top: 2.5rem; padding-top: 1.25rem; }
.skills-index > div { display: flex; flex-wrap: wrap; gap: 0.45rem; }
.skills-index > div span { border: 1px solid var(--profile-300); border-radius: 999px; padding: 0.35rem 0.7rem; font-family: var(--font-mono); font-size: 0.66rem; text-transform: uppercase; background: var(--profile-50); }
.collaboration-bridge { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: clamp(2rem, 6vw, 6rem); align-items: end; margin-top: clamp(3rem, 7vw, 6rem); margin-bottom: clamp(3rem, 7vw, 6rem); border: 1px solid var(--profile-ink); border-radius: 8px; background: linear-gradient(135deg, rgb(37 99 235 / 8%), transparent 52%), var(--profile-50); padding: clamp(1.6rem, 4vw, 2.8rem); color: var(--profile-ink); box-shadow: 10px 10px 0 var(--profile-blue); transition: all 300ms ease; }
:global(:root[data-theme='dark']) .collaboration-bridge { border-color: var(--profile-300); background: radial-gradient(circle at 92% 8%, rgb(59 130 246 / 20%), transparent 42%), radial-gradient(circle at 68% 110%, rgb(139 92 246 / 14%), transparent 45%), var(--profile-50); box-shadow: 10px 10px 0 color-mix(in srgb, var(--profile-blue) 75%, #7c3aed); }
.collaboration-bridge h2 { margin: 0.55rem 0 0; font-size: clamp(2.5rem, 6vw, 5rem); line-height: 0.95; font-weight: 700; letter-spacing: -0.02em; }
.collaboration-bridge p { max-width: 47rem; margin: 1rem 0 0; color: var(--profile-700); font-size: 1.08rem; line-height: 1.65; }
.collaboration-actions { display: grid; gap: 0.75rem; min-width: 11.5rem; }
.collaboration-actions a { display: inline-flex; min-height: 2.9rem; align-items: center; justify-content: space-between; gap: 0.65rem; border: 1px solid var(--profile-ink); border-radius: 4px; background: transparent; padding: 0.75rem 0.95rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.7rem; font-weight: 600; text-decoration: none; text-transform: uppercase; transition: all 180ms ease; }
.collaboration-actions a:first-child { background: var(--profile-ink); color: var(--profile-bg); }
.collaboration-actions a:hover { box-shadow: 4px 4px 0 var(--profile-blue); transform: translate(-2px, -2px); }
.footer { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2rem; align-items: end; padding: clamp(3rem, 6vw, 5rem) 0 2.5rem; border-top: 1px solid var(--profile-200); }
.footer-links { display: grid; gap: 0.6rem; justify-items: end; font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; }
.footer-links a { gap: 0.35rem; }
.modal-backdrop { position: fixed; inset: 0; z-index: 60; display: grid; place-items: center; background: color-mix(in srgb, #000 60%, transparent); padding: 1rem; backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }
.badge-modal { position: relative; width: min(100%, 34rem); max-height: calc(100vh - 2rem); overflow-y: auto; border: 1px solid var(--profile-ink); border-radius: 8px; background: var(--profile-bg); padding: clamp(1.2rem, 4vw, 2.2rem); box-shadow: 0 20px 48px -12px rgba(0, 0, 0, 0.4); }
.background-modal { width: min(100%, 40rem); }
.background-organization-mark { width: 6.5rem; height: 6.5rem; border: 1px solid var(--profile-200); border-radius: 6px; background: #fff; }
.background-organization-mark img { width: 100%; height: 100%; object-fit: contain; padding: 0.55rem; }
.modal-badge { position: relative; display: grid; width: 6.5rem; height: 6.5rem; place-items: center; border: 1px solid var(--profile-200); border-radius: 6px; background: var(--profile-50); margin-bottom: 1.25rem; font-family: var(--font-mono); font-size: 1.8rem; font-weight: 700; }
.modal-badge img { width: 100%; height: 100%; object-fit: contain; }
.modal-badge .badge-fallback { font-size: 1.8rem; }
.certificate-provider-mark { width: 5.5rem; height: 5.5rem; padding: 0.45rem; }
.modal-close { position: absolute; top: 1rem; right: 1rem; display: grid; width: 2.2rem; height: 2.2rem; place-items: center; border: 1px solid var(--profile-200); border-radius: 50%; background: var(--profile-50); color: var(--profile-ink); cursor: pointer; transition: all 180ms ease; }
.modal-close:hover { border-color: var(--profile-ink); transform: scale(1.08); }
.badge-modal h2 { margin: 0.4rem 0 0.6rem; font-size: 1.5rem; line-height: 1.25; font-weight: 700; }
.badge-modal p { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.65; }
.badge-modal dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; border-top: 1px solid var(--profile-200); border-bottom: 1px solid var(--profile-200); margin: 1.2rem 0; padding: 0.9rem 0; font-family: var(--font-mono); font-size: 0.72rem; }
.badge-modal dt { color: var(--profile-500); text-transform: uppercase; font-size: 0.64rem; }
.badge-modal dd { margin: 0.2rem 0 0; font-weight: 600; }
.modal-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.4rem; }
.modal-actions a { border: 1px solid var(--profile-ink); border-radius: 4px; padding: 0.55rem 0.85rem; background: var(--profile-50); font-weight: 600; }
.modal-actions a:hover { background: var(--profile-ink); color: var(--profile-bg); }
.design-modal, .project-modal { position: relative; width: min(100%, 54rem); max-height: calc(100vh - 2rem); overflow-y: auto; border: 1px solid var(--profile-ink); border-radius: 8px; background: var(--profile-bg); box-shadow: 0 20px 48px -12px rgba(0, 0, 0, 0.4); }
.design-modal-media, .project-modal-media { position: relative; aspect-ratio: 16 / 10; border-bottom: 1px solid var(--profile-200); background: #0c0f0d; overflow: hidden; }
.design-modal-media img, .design-modal-media video, .design-modal-media iframe, .project-modal-media img, .project-modal-media iframe { width: 100%; height: 100%; object-fit: contain; }
.project-modal-placeholder { display: grid; width: 100%; height: 100%; place-items: center; color: var(--profile-400); }
.design-modal-copy, .project-modal-copy { padding: clamp(1.2rem, 3.5vw, 2rem); }
.design-modal-copy h2, .project-modal-copy h2 { margin: 0.4rem 0 0.6rem; font-size: 1.6rem; font-weight: 700; }
.design-modal-copy p, .project-modal-copy p { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.65; }
.project-modal dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; border-top: 1px solid var(--profile-200); border-bottom: 1px solid var(--profile-200); margin: 1.2rem 0; padding: 0.85rem 0; font-family: var(--font-mono); font-size: 0.72rem; }
.project-modal dt { color: var(--profile-500); font-size: 0.64rem; text-transform: uppercase; }
.project-modal dd { margin: 0.2rem 0 0; font-weight: 600; }

@media (max-width: 1024px) {
    .hero-grid { grid-template-columns: 1fr; }
    .portrait-block { grid-column: 1; grid-row: 3; max-width: 22rem; }
    .badge-layout { grid-template-columns: 1fr; }
    .badge-preview { display: none; }
    .design-grid, .certificate-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .background-grid { grid-template-columns: 1fr; }
    .tool-groups { grid-template-columns: 1fr; }
    .tool-category { border-right: 0; }
}

@media (max-width: 768px) {
    .nav-links { display: none; }
    .topbar { padding: 0.75rem 1rem; }
    .project-grid { grid-template-columns: 1fr; }
    .project-card:last-child:nth-child(odd) { width: 100%; }
    .design-grid, .certificate-grid, .badge-grid { grid-template-columns: 1fr; }
    .achievement-grid { grid-template-columns: 1fr; }
    .achievement-item { border-right: 0; }
    .publication-row { grid-template-columns: 1fr; }
    .publication-cover { width: 100%; max-width: 10rem; }
    .stat-line { grid-template-columns: 1fr; }
    .stat-line div { border-right: 0; border-bottom: 1px solid var(--profile-200); }
    .stat-line div:last-child { border-bottom: 0; }
    .collaboration-bridge { grid-template-columns: 1fr; }
    .footer { grid-template-columns: 1fr; justify-items: start; }
    .footer-links { justify-items: start; }
}
</style>
