<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowUpRight, Award, BarChart3, Bot, BriefcaseBusiness, Code2, Copy, Database, HeartPulse, Layout, Menu, Moon, Network, Palette, ShieldCheck, Store, Sun, GitBranch, Wrench, X } from '@lucide/vue';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import gsap from 'gsap';
import SeoHead from '../../Components/SeoHead.vue';
import PublicationSpread from '../../Components/PublicationSpread.vue';
import CreativeFlipbook from '../../Components/CreativeFlipbook.vue';
import { gmailComposeUrl } from '../../Support/gmail.js';

const props = defineProps({
    seo: { type: Object, required: true },
    profile: { type: Object, required: true },
    achievements: { type: Array, default: () => [] },
    badges: { type: Array, default: () => [] },
    certificates: { type: Array, default: () => [] },
    education: { type: Array, default: () => [] },
    githubActivity: { type: Object, default: null },
    portfolioTools: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    visitStats: { type: Object, default: null },
    publishedWorks: { type: Array, default: () => [] },
    workExperiences: { type: Array, default: () => [] },
    flash: { type: Object, default: () => ({}) },
});

const githubProfileUrl = computed(() => props.githubActivity?.profile_url || props.profile.external_links?.find((link) => link.label?.toLowerCase() === 'github')?.url);
const linkedinProfileUrl = computed(() => props.profile.external_links?.find((link) => link.label?.toLowerCase() === 'linkedin')?.url);
const collaborationMessage = 'Hello Britt,\n\nI would like to discuss a potential collaboration.\n\nBest regards,';
const copyEmailStatus = ref('');
async function copyContactEmail() {
    try {
        await navigator.clipboard.writeText(props.profile.email);
        copyEmailStatus.value = 'Email address copied.';
    } catch {
        copyEmailStatus.value = 'Copy unavailable. Select the email address above to copy it.';
    }
}
const inquiry = useForm({ name: '', email: '', category: 'Software development', message: '', website: '' });
const inquiryCategories = ['Software development', 'Network infrastructure', 'Cybersecurity', 'Systems integration', 'Research collaboration', 'IT consulting'];
const inquirySuccess = ref('');
function submitInquiry() {
    inquirySuccess.value = '';
    inquiry.post('/inquiry', {
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.flash?.inquiry_success) {
                inquiry.reset();
                inquirySuccess.value = page.props.flash.inquiry_success;
            }
        },
    });
}

const githubWeeks = computed(() => (props.githubActivity?.weeks || []).map((week, index, weeks) => {
    const month = week.start_date?.slice(0, 7);
    const showMonth = month && (index === 0 || month !== weeks[index - 1].start_date?.slice(0, 7));
    return { ...week, monthLabel: showMonth ? new Date(`${week.start_date}T00:00:00Z`).toLocaleString('en', { month: 'short', timeZone: 'UTC' }) : '' };
}));
function githubDayLabel(day) {
    const date = new Date(`${day.date}T00:00:00Z`).toLocaleDateString('en', { dateStyle: 'long', timeZone: 'UTC' });
    return `${date}: ${day.level === 0 ? 'no contributions' : `contribution activity level ${day.level} of 4`}`;
}

const experienceGroups = computed(() => props.workExperiences.reduce((groups, experience) => {
    const organizationKey = (experience.organization ?? '').trim().replace(/\s+/g, ' ').toLowerCase();
    const previous = groups[groups.length - 1];

    if (organizationKey && previous?.organizationKey === organizationKey) {
        previous.roles.push(experience);
        previous.logoUrl ||= experience.logo_url;
    } else {
        groups.push({
            id: experience.id,
            organizationKey,
            organization: experience.organization,
            logoUrl: experience.logo_url,
            roles: [experience],
        });
    }

    return groups;
}, []));

function experienceDates(experience) {
    const start = experience.start_date?.trim();
    const end = experience.is_current === true ? 'Present' : experience.end_date?.trim();
    return [start, end].filter(Boolean).join(' to ');
}

function experienceContributions(experience) {
    if (!Array.isArray(experience.responsibilities)) return [];
    return experience.responsibilities.filter((item) => typeof item === 'string').map((item) => item.trim()).filter(Boolean);
}

function isFreelanceExperience(experience) {
    return /freelanc|self[\s-]*employed/i.test(`${experience.organization ?? ''} ${experience.position ?? ''}`);
}

function freelanceResponsibilityGroups(experience) {
    return experienceContributions(experience).reduce((groups, text) => {
        if (/^\d+\.\s+\S/.test(text)) {
            groups.push({ heading: text, items: [] });
        } else if (groups.length && groups[groups.length - 1].heading) {
            groups[groups.length - 1].items.push(text);
        } else {
            groups.push({ heading: null, items: [text] });
        }
        return groups;
    }, []);
}

const activeProjectCategory = ref('all');
const activeWorkType = ref('all');
const isDark = ref(false);
const publicationSearch = ref('');
const workPage = ref(0);
const workTurnDirection = ref(0);
const workTurning = ref(false);
const workBookRevision = ref(0);
const workMeasurements = ref(null);
const workBookHeight = ref(0);
let workMeasureObserver = null;
let workMeasureFrame = null;
let workBookDisposed = false;
const selectedBadge = ref(null);
const selectedDesign = ref(null);
const designIndex = ref(0);
const graphicDirection = ref(1);
const graphicDesigns = [
    { id: 'io', title: 'IO graphic design', media_url: '/creative-assets/graphic-io.jpg' },
    { id: 'dimsum', title: 'El Corso dimsum graphic design', media_url: '/creative-assets/graphic-el-corso-2.jpg' },
    { id: 'whisky', title: 'El Corso whisky graphic design', media_url: '/creative-assets/graphic-el-corso.jpg' },
];
const creativeVideo = { media_type: 'video', media_url: '/creative-assets/palakasan-2025-web.mp4' };
const graphicSamples = graphicDesigns;
const creativeVideoError = ref(false);
const creativeVideoElement = ref(null);
const selectedProject = ref(null);
const selectedRecognition = ref(null);
const selectedExperience = ref(null);
const theme = ref('system');
const pageRoot = ref(null);
const menuOpen = ref(false);
const menuButton = ref(null);
const achievementsSection = ref(null);
const carouselVisible = ref(false);
const portraitStage = ref(null);
const portraitForward = ref(null);
const portraitReverse = ref(null);
const profilePortraitForwardUrl = '/repository-media/portfolio/Videos/Profile%20Video.mp4';
const profilePortraitReverseUrl = '/repository-media/portfolio/profile/portrait-reversed.mp4';
const portraitDirection = ref(1);
const portraitHasFrame = ref(false);
const portraitAvailable = ref(true);
const portraitVisible = ref(false);
const reducedMotion = ref(true);
const dataOpen = ref(false);
const dataPinned = ref(false);
let dataDelay = null;
const extraOpen = ref({ health: false, ai: false });
const extraPinned = ref({ health: false, ai: false });
const extraDelays = { health: null, ai: null };
const extraTimers = { health: null, ai: null };
const extraVisible = ref({ health: 0, ai: 0 });
const healthDetails = ['EMR readiness assessment', 'EMR navigation and user training', 'System implementation and evaluation', 'Workflow and process improvement', 'Ongoing technical support'];
function extraLength(key) { return key === 'health' ? healthDetails.length : automationTools.length; }
function scheduleExtra(key) {
    if (extraOpen.value[key] || extraDelays[key]) return;
    extraDelays[key] = setTimeout(() => {
        extraDelays[key] = null;
        extraOpen.value[key] = true;
        extraVisible.value[key] = reducedMotion.value ? extraLength(key) : 0;
        if (reducedMotion.value) return;
        extraTimers[key] = setInterval(() => {
            extraVisible.value[key] += 1;
            if (extraVisible.value[key] >= extraLength(key)) { clearInterval(extraTimers[key]); extraTimers[key] = null; }
        }, 180);
    }, 1500);
}
function closeExtra(key) { clearTimeout(extraDelays[key]); clearInterval(extraTimers[key]); extraDelays[key] = null; extraTimers[key] = null; extraOpen.value[key] = false; }
function hideExtra(key, event) { if (!extraPinned.value[key] && !event.currentTarget.contains(event.relatedTarget)) closeExtra(key); }
function toggleExtra(key) { extraPinned.value[key] = !extraPinned.value[key]; if (extraPinned.value[key]) scheduleExtra(key); else closeExtra(key); }
const weeklyViews = computed(() => {
    if (!Array.isArray(props.visitStats?.weekly?.days)) return [];
    return props.visitStats.weekly.days.filter((entry) => /^\d{4}-\d{2}-\d{2}$/.test(entry.date)).map((entry) => ({
        date: entry.date,
        label: new Intl.DateTimeFormat('en', { weekday: 'short', timeZone: 'UTC' }).format(new Date(`${entry.date}T00:00:00Z`)),
        count: Number.isFinite(entry.count) && entry.count >= 0 ? entry.count + 20 : null,
    }));
});
const maxViews = computed(() => Math.max(1, ...weeklyViews.value.map((item) => item.count ?? 0)));
const adjustedWeeklyViews = computed(() => weeklyViews.value.reduce((total, item) => total + (item.count ?? 0), 0));
const chartPoints = computed(() => weeklyViews.value.map((item, index) => ({ ...item, x: 20 + index * (260 / 6), y: item.count === null ? null : 90 - (item.count / maxViews.value) * 70 })));
const chartPath = computed(() => {
    let drawing = false;
    return chartPoints.value.map((point) => {
        if (point.y === null) { drawing = false; return ''; }
        const command = drawing ? 'L' : 'M';
        drawing = true;
        return `${command}${point.x} ${point.y}`;
    }).filter(Boolean).join(' ');
});
function scheduleData() { if (!dataOpen.value && !dataDelay) dataDelay = setTimeout(() => { dataDelay = null; dataOpen.value = true; }, 1500); }
function closeData() { clearTimeout(dataDelay); dataDelay = null; dataOpen.value = false; }
function hideData(event) { if (!dataPinned.value && !event.currentTarget.contains(event.relatedTarget)) closeData(); }
function toggleData() { dataPinned.value = !dataPinned.value; if (dataPinned.value) scheduleData(); else closeData(); }
const connectionOpen = ref(false);
const connectionPinned = ref(false);
const connectionText = ref('');
const connectionResult = ref('');
const connectionLoading = ref(false);
let connectionTimer = null;
let connectionDelay = null;
let connectionRequest = null;
const networkOpen = ref(false);
const networkPinned = ref(false);
const networkText = ref('');
const networkDescription = 'Network design and configuration\nRouting and VLAN segmentation\nWAN failover\nFirewall and wireless security\nNetwork troubleshooting';
let networkDelay = null;
let networkTimer = null;
const webOpen = ref(false);
const webPinned = ref(false);
const webVisibleCount = ref(0);
const webTechnologies = ['PHP', 'JavaScript', 'Python', 'Laravel', 'Vue.js', 'Inertia.js'];
const automationTools = ['n8n', 'ChatGPT', 'Claude', 'GPTZero', 'Zapier', 'GoHighLevel', 'Ollama', 'Clawdbot', 'Meta'];
let webDelay = null;
let webTimer = null;

function scheduleWeb() {
    if (webOpen.value || webDelay) return;
    webDelay = setTimeout(() => {
        webDelay = null;
        webOpen.value = true;
        webVisibleCount.value = reducedMotion.value ? webTechnologies.length : 0;
        if (reducedMotion.value) return;
        webTimer = setInterval(() => {
            webVisibleCount.value += 1;
            if (webVisibleCount.value >= webTechnologies.length) clearInterval(webTimer);
        }, 180);
    }, 1500);
}

function hideWeb(event) {
    if (webPinned.value || event.currentTarget.contains(event.relatedTarget)) return;
    closeWeb();
}
function closeWeb() {
    clearTimeout(webDelay);
    clearInterval(webTimer);
    webDelay = null;
    webOpen.value = false;
}
function toggleWeb() { webPinned.value = !webPinned.value; if (webPinned.value) scheduleWeb(); else closeWeb(); }

function scheduleNetwork() {
    if (networkOpen.value || networkDelay) return;
    networkDelay = setTimeout(() => {
        networkDelay = null;
        networkOpen.value = true;
        networkText.value = reducedMotion.value ? networkDescription : '';
        if (reducedMotion.value) return;
        let position = 0;
        networkTimer = setInterval(() => {
            position = Math.min(position + 2, networkDescription.length);
            networkText.value = networkDescription.slice(0, position);
            if (position >= networkDescription.length) clearInterval(networkTimer);
        }, 18);
    }, 1500);
}

function hideNetwork(event) {
    if (networkPinned.value || event.currentTarget.contains(event.relatedTarget)) return;
    closeNetwork();
}
function closeNetwork() {
    clearTimeout(networkDelay);
    clearInterval(networkTimer);
    networkDelay = null;
    networkOpen.value = false;
}
function toggleNetwork() { networkPinned.value = !networkPinned.value; if (networkPinned.value) scheduleNetwork(); else closeNetwork(); }

function typeConnectionText(text) {
    clearInterval(connectionTimer);
    connectionResult.value = text;
    connectionText.value = reducedMotion.value ? text : '';
    if (reducedMotion.value) return;
    let position = 0;
    connectionTimer = setInterval(() => {
        position = Math.min(position + 2, text.length);
        connectionText.value = text.slice(0, position);
        if (position >= text.length) clearInterval(connectionTimer);
    }, 18);
}

async function showConnection() {
    if (connectionOpen.value) return;
    connectionOpen.value = true;
    if (connectionResult.value) { typeConnectionText(connectionResult.value); return; }
    if (connectionRequest) return;
    connectionText.value = '';
    connectionLoading.value = true;
    const request = new AbortController();
    connectionRequest = request;
    try {
        let ip = null;
        let location = 'Unavailable';
        const lookup = new AbortController();
        const abortLookup = () => lookup.abort();
        request.signal.addEventListener('abort', abortLookup, { once: true });
        const timeout = setTimeout(abortLookup, 3500);
        try {
            const response = await fetch('https://ipwho.is/', { signal: lookup.signal, cache: 'no-store', referrerPolicy: 'no-referrer' });
            if (response.ok) {
                const data = await response.json();
                if (data.success === true) {
                    if (typeof data.ip === 'string' && /^[0-9a-f:.]{2,45}$/i.test(data.ip)) ip = data.ip;
                    if (typeof data.latitude === 'number' && Number.isFinite(data.latitude) && Math.abs(data.latitude) <= 90 && typeof data.longitude === 'number' && Number.isFinite(data.longitude) && Math.abs(data.longitude) <= 180) location = `${data.latitude.toFixed(2)}, ${data.longitude.toFixed(2)} (IP-based estimate)`;
                }
            }
        } catch { /* Show unavailable when the IP location provider cannot be reached. */ }
        finally { clearTimeout(timeout); request.signal.removeEventListener('abort', abortLookup); }
        if (request.signal.aborted) return;
        if (!ip) {
            try {
                const response = await fetch('/visitor-connection', { signal: request.signal, cache: 'no-store', headers: { Accept: 'application/json' } });
                if (response.ok) {
                    const data = await response.json();
                    if (typeof data.ip === 'string' && /^[0-9a-f:.]{2,45}$/i.test(data.ip)) ip = data.ip;
                }
            } catch { /* Try the public IP fallback below. */ }
        }
        if (request.signal.aborted) return;
        if (!ip) {
            const lookup = new AbortController();
            const abortLookup = () => lookup.abort();
            request.signal.addEventListener('abort', abortLookup, { once: true });
            const timeout = setTimeout(abortLookup, 3500);
            try {
                const lookupResponse = await fetch('https://api64.ipify.org?format=json', { signal: lookup.signal, cache: 'no-store', referrerPolicy: 'no-referrer' });
                if (lookupResponse.ok) {
                    const details = await lookupResponse.json();
                    if (typeof details.ip === 'string' && /^[0-9a-f:.]{2,45}$/i.test(details.ip)) ip = details.ip;
                }
            } catch { /* The local response remains usable if the optional lookup fails. */ }
            finally { clearTimeout(timeout); request.signal.removeEventListener('abort', abortLookup); }
        }
        if (request.signal.aborted) return;
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Unavailable';
        const agent = navigator.userAgent;
        const browser = /Edg\//.test(agent) ? 'Microsoft Edge' : /OPR\//.test(agent) ? 'Opera' : /Firefox\//.test(agent) ? 'Firefox' : /Chrome\//.test(agent) ? 'Chrome' : /Safari\//.test(agent) ? 'Safari' : 'Unavailable';
        typeConnectionText(`Public IP: ${ip || 'Unavailable'}\nApproximate location: ${location}\nBrowser: ${browser}\nTime zone: ${timezone}`);
    } catch (error) {
        if (error.name !== 'AbortError') typeConnectionText('Connection details are unavailable right now.');
    } finally {
        if (connectionRequest === request) {
            connectionRequest = null;
            connectionLoading.value = false;
        }
    }
}

function closeConnection() {
    connectionOpen.value = false;
    clearTimeout(connectionDelay);
    connectionDelay = null;
    clearInterval(connectionTimer);
    connectionRequest?.abort();
    connectionRequest = null;
    connectionLoading.value = false;
}

function hideConnection(event) {
    if (!connectionPinned.value && !event.currentTarget.contains(event.relatedTarget)) closeConnection();
}

function scheduleConnection() {
    if (connectionOpen.value || connectionDelay) return;
    connectionDelay = setTimeout(() => { connectionDelay = null; showConnection(); }, 1500);
}

function toggleConnection() {
    connectionPinned.value = !connectionPinned.value;
    if (connectionPinned.value) scheduleConnection();
    else closeConnection();
}
let portraitObserver = null;
let sectionObserver = null;
let portraitPlayToken = 0;
let carouselObserver = null;

function pausePortrait() {
    portraitPlayToken += 1;
    portraitForward.value?.pause();
    portraitReverse.value?.pause();
}

async function playPortraitTransition(direction) {
    pausePortrait();
    if (reducedMotion.value || !portraitVisible.value || document.hidden || !portraitAvailable.value) return;
    const video = direction === 1 ? portraitForward.value : portraitReverse.value;
    if (!video) return;
    video.currentTime = 0;
    const token = ++portraitPlayToken;
    try {
        await video.play();
        if (token !== portraitPlayToken) { video.pause(); return; }
        portraitDirection.value = direction;
        portraitHasFrame.value = true;
    } catch { /* Keep the still portrait if playback is unavailable. */ }
}

function handlePortraitVisibility() {
    if (document.hidden) pausePortrait();
}

function recognitionUrl(achievement) {
    const title = String(achievement.title ?? '').trim().toLowerCase();
    const badge = title && props.badges.find((item) => String(item.name ?? '').trim().toLowerCase().startsWith(title) && item.certificate_url);
    return badge?.certificate_url || achievement.external_url || null;
}

function recognitionPreviewUrl(achievement) {
    return achievement?.title === 'Scam Ready ASEAN Master Trainer'
        && achievement.issuer === 'SmartCT / ASEAN Foundation'
        && achievement.certificate_preview_url === '/images/recognitions/scam-ready-asean-certificate.jpg'
        ? achievement.certificate_preview_url : null;
}

function portraitError() {
    portraitAvailable.value = false;
    portraitHasFrame.value = false;
    pausePortrait();
}

// Guestbook form state
const guestbookForm = useForm({
    name: '',
    email: '',
    website: '',
    role_or_organization: '',
    rating: 5,
    body: '',
});
const guestbookSuccess = ref(false);

function submitGuestbook() {
    guestbookSuccess.value = false;
    guestbookForm.post('/guestbook', {
        preserveScroll: true,
        onSuccess: () => {
            guestbookForm.reset();
            guestbookSuccess.value = true;
        },
        onError: async () => {
            await nextTick();
            pageRoot.value?.querySelector('.form-errors')?.focus();
        },
    });
}

const projectCategories = computed(() => [
    { key: 'all', label: 'All Projects' },
    ...[...new Set(props.projects.map((item) => item.category).filter(Boolean))]
        .map((category) => ({ key: category, label: category })),
]);

function latestProjectYear(year) {
    const years = String(year ?? '').match(/\b(?:19|20)\d{2}\b/g);
    return years ? Math.max(...years.map(Number)) : 0;
}

const filteredProjects = computed(() => props.projects.filter((item) => (
    activeProjectCategory.value === 'all' || item.category === activeProjectCategory.value
)).sort((a, b) => latestProjectYear(b.year) - latestProjectYear(a.year)));

const failedProjectMarks = ref(new Set());
const loadedProjectMarks = ref(new Set());
function isAmplifierProject(project) {
    return String(project.title ?? '').trim().toLowerCase() === 'amplifier publication & design system';
}

function projectMarkUrl(project) {
    const title = String(project.title ?? '').trim().toLowerCase();
    if (title === 'health information systems implementation') return '/images/projects/ihomis-plus-logo.png';
    if (title === 'governed mch forecasting and risk prediction dss') return '/repository-media/portfolio/organizations/department-of-health.png';
    if (isAmplifierProject(project)) return '/repository-media/portfolio/organizations/university-of-san-carlos.png';
    if (!project.url) return null;
    try {
        const site = new URL(project.url);
        if (!['http:', 'https:'].includes(site.protocol)) return null;
        if (site.hostname === 'classcheck.brittmontalvo.dev') return '/images/projects/classcheck-logo.png';
        if (site.hostname === 'gamma.snsu.edu.ph') return '/repository-media/portfolio/organizations/snsu.png';
        if (site.hostname === 'app.powerbi.com') return getIconifyLogoUrl('Power BI');
        return `${site.origin}/favicon.ico`;
    } catch {
        return null;
    }
}

function projectInitials(title) {
    return String(title || 'Project').split(/\s+/).filter((word) => word && !/^(and|or|the|of|for|a|an|&|-)$/i.test(word)).slice(0, 2).map((word) => word[0].toUpperCase()).join('');
}

const projectSlideIndex = ref(0);
const projectImageFailed = ref(false);
const projectFallbackFailed = ref(false);
const projectFallbackLogo = computed(() => {
    const title = String(selectedProject.value?.title ?? '').trim().toLowerCase();
    return title === 'health information systems implementation' || isAmplifierProject(selectedProject.value || {}) ? projectMarkUrl(selectedProject.value) : null;
});
const failedProjectLogos = ref(new Set());
const projectTechnologies = computed(() => {
    const unique = new Map();
    for (const tag of selectedProject.value?.tags ?? []) {
        if (typeof tag !== 'string' || !tag.trim()) continue;
        const name = tag.trim();
        const logo = getIconifyLogoUrl(name);
        const key = logo ? `logo:${logo.toLowerCase()}` : `name:${name.toLowerCase()}`;
        if (!unique.has(key)) unique.set(key, { key, logo, names: [] });
        const item = unique.get(key);
        if (!item.names.some((existing) => existing.toLowerCase() === name.toLowerCase())) item.names.push(name);
    }
    return [...unique.values()].map((item) => ({ ...item, label: item.names.join(' / ') }));
});
const projectSlides = computed(() => [...new Set([
    projectFallbackLogo.value,
    selectedProject.value?.thumbnail_url,
    ...(Array.isArray(selectedProject.value?.gallery_urls) ? selectedProject.value.gallery_urls : []),
].filter((url) => typeof url === 'string' && url.trim()))]);
function selectProjectSlide(index) { projectSlideIndex.value = index; projectImageFailed.value = false; projectFallbackFailed.value = false; }

const workTypes = computed(() => [
    { key: 'all', label: 'All Formats' },
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
function resetWorkBook() {
    workBookRevision.value += 1;
    workTurning.value = false;
    workTurnDirection.value = 0;
    workPage.value = 0;
}
watch([activeWorkType, publicationSearch, () => props.publishedWorks], resetWorkBook);
watch(reducedMotion, (reduce) => {
    if (!reduce || !workTurning.value) return;
    workBookRevision.value += 1;
    workTurning.value = false;
    workTurnDirection.value = 0;
});
const currentWork = computed(() => filteredWorks.value[workPage.value] ?? null);
function turnWorkPage(direction) {
    if (workTurning.value) return;
    const nextPage = Math.max(0, Math.min(filteredWorks.value.length - 1, workPage.value + direction));
    if (nextPage === workPage.value) return;
    workTurnDirection.value = reducedMotion.value ? 0 : direction;
    workTurning.value = !reducedMotion.value;
    workPage.value = nextPage;
}
function retireWorkPage(element) {
    element.setAttribute('inert', '');
    element.setAttribute('aria-hidden', 'true');
}
function scheduleWorkBookMeasure() {
    if (workBookDisposed || workMeasureFrame !== null) return;
    workMeasureFrame = requestAnimationFrame(() => {
        workMeasureFrame = null;
        const spreads = workMeasurements.value?.children;
        if (!spreads?.length) return;
        const height = Math.ceil(Math.max(...Array.from(spreads, (spread) => spread.getBoundingClientRect().height))) + 2;
        if (height > 2 && height !== workBookHeight.value) workBookHeight.value = height;
    });
}

function observeWorkBookMeasurements() {
    workMeasureObserver?.disconnect();
    const measurements = workMeasurements.value;
    if (!measurements) return;
    workMeasureObserver = new ResizeObserver(scheduleWorkBookMeasure);
    workMeasureObserver.observe(measurements);
    Array.from(measurements.children).forEach((spread) => workMeasureObserver.observe(spread));
    scheduleWorkBookMeasure();
}

watch(() => props.publishedWorks, async () => {
    await nextTick();
    if (!workBookDisposed) observeWorkBookMeasurements();
}, { deep: true });

onMounted(() => {
    observeWorkBookMeasurements();
    document.fonts?.ready.then(scheduleWorkBookMeasure);
    document.fonts?.addEventListener('loadingdone', scheduleWorkBookMeasure);
    window.addEventListener('resize', scheduleWorkBookMeasure);
});

onBeforeUnmount(() => {
    workBookDisposed = true;
    workMeasureObserver?.disconnect();
    if (workMeasureFrame !== null) cancelAnimationFrame(workMeasureFrame);
    document.fonts?.removeEventListener('loadingdone', scheduleWorkBookMeasure);
    window.removeEventListener('resize', scheduleWorkBookMeasure);
});

const visibleBadges = computed(() => props.badges.slice(0, 9));

const heroPortraitSrc = computed(() => {
    return isDark.value
        ? (props.profile.dark_avatar_url || props.profile.avatar_url || '/images/profile-avatar.jpg')
        : (props.profile.avatar_url || '/images/profile-avatar.jpg');
});

const toolCategoryDefinitions = [
    { key: 'backend', label: 'Backend', icon: Database },
    { key: 'frontend', label: 'Frontend', icon: Layout },
    { key: 'automation', label: 'Automation & AI', icon: Bot },
    { key: 'data', label: 'Data & BI', icon: BarChart3 },
    { key: 'design', label: 'Design Tools', icon: Palette },
    { key: 'infrastructure', label: 'Infrastructure', icon: Network },
    { key: 'platforms', label: 'Platforms & Cloud', icon: Store },
    { key: 'development', label: 'Development', icon: GitBranch },
    { key: 'other', label: 'Other Tools', icon: Wrench },
];

const groupedPortfolioTools = computed(() => toolCategoryDefinitions
    .map((category) => ({
        ...category,
        tools: props.portfolioTools.filter((tool) => tool.category === category.key),
    }))
    .filter((category) => category.tools.length));

function getIconifyLogoUrl(nameStr) {
    if (!nameStr) return null;
    const name = String(nameStr).toLowerCase().trim();

    if (
        name.includes('department of health') ||
        name === 'doh' ||
        name === 'fhsis'
    ) {
        return '/repository-media/portfolio/organizations/department-of-health.png';
    }
    if (name.includes('surigao del norte state university') || name.includes('snsu')) {
        return '/repository-media/portfolio/organizations/snsu.png';
    }

    const iconMap = {
        'linkedin': '/images/brands/iconify/logos--linkedin-icon.svg',
        'power bi': '/images/brands/iconify/logos--microsoft-power-bi.svg',
        'powerbi': '/images/brands/iconify/logos--microsoft-power-bi.svg',
        'microsoft power bi': '/images/brands/iconify/logos--microsoft-power-bi.svg',
        'microsoft': '/images/brands/iconify/logos--microsoft-icon.svg',
        'microsoft office': '/images/brands/iconify/logos--microsoft-icon.svg',
        'microsoft 365': '/images/brands/iconify/logos--microsoft-icon.svg',
        'microsoft excel': '/images/brands/iconify/mdi--microsoft-excel--color-217346.svg',
        'canva': '/images/brands/iconify/devicon--canva.svg',
        'adobe': '/images/brands/iconify/logos--adobe-icon.svg',
        'adobe creative cloud': '/images/brands/iconify/cib--adobe-creative-cloud--color-DA1F26.svg',
        'adobe creative suite': '/images/brands/iconify/cib--adobe-creative-cloud--color-DA1F26.svg',
        'adobe photoshop': '/images/brands/iconify/logos--adobe-photoshop.svg',
        'adobe illustrator': '/images/brands/iconify/logos--adobe-illustrator.svg',
        'adobe indesign': '/images/brands/iconify/logos--adobe-indesign.svg',
        'adobe premiere pro': '/images/brands/iconify/logos--adobe-premiere.svg',
        'laravel': '/images/brands/iconify/logos--laravel.svg',
        'php': '/images/brands/iconify/logos--php.svg',
        'vue.js': '/images/brands/iconify/logos--vue.svg',
        'vue': '/images/brands/iconify/logos--vue.svg',
        'inertia.js': '/images/brands/iconify/simple-icons--inertia.svg',
        'inertia': '/images/brands/iconify/simple-icons--inertia.svg',
        'codeigniter': '/images/brands/iconify/simple-icons--codeigniter.svg',
        'python': '/images/brands/iconify/logos--python.svg',
        'docker': '/images/brands/iconify/logos--docker-icon.svg',
        'tailwind css': '/images/brands/iconify/logos--tailwindcss-icon.svg',
        'tailwindcss': '/images/brands/iconify/logos--tailwindcss-icon.svg',
        'bootstrap': '/images/brands/iconify/logos--bootstrap.svg',
        'mysql': '/images/brands/iconify/logos--mysql-icon.svg',
        'postgresql': '/images/brands/iconify/logos--postgresql.svg',
        'sqlite': '/images/brands/iconify/logos--sqlite.svg',
        'sql': '/images/brands/iconify/lucide--database.svg',
        'sql databases': '/images/brands/iconify/lucide--database.svg',
        'emr': '/images/brands/iconify/lucide--heart-pulse.svg',
        'git': '/images/brands/iconify/logos--git-icon.svg',
        'github': '/images/brands/iconify/logos--github-icon.svg',
        'gitlab': '/images/brands/iconify/logos--gitlab.svg',
        'figma': '/images/brands/iconify/logos--figma.svg',
        'mikrotik': '/images/brands/iconify/simple-icons--mikrotik.svg',
        'cisco': '/images/brands/iconify/simple-icons--cisco.svg',
        'n8n': '/images/brands/iconify/simple-icons--n8n.svg',
        'javascript': '/images/brands/iconify/logos--javascript.svg',
        'typescript': '/images/brands/iconify/logos--typescript-icon.svg',
        'html5': '/images/brands/iconify/logos--html-5.svg',
        'html': '/images/brands/iconify/logos--html-5.svg',
        'css3': '/images/brands/iconify/logos--css-3.svg',
        'css': '/images/brands/iconify/logos--css-3.svg',
        'vite': '/images/brands/iconify/logos--vitejs.svg',
        'nginx': '/images/brands/iconify/logos--nginx.svg',
        'apache': '/images/brands/iconify/logos--apache.svg',
        'linux': '/images/brands/iconify/logos--tux.svg',
        'ubuntu': '/images/brands/iconify/logos--ubuntu.svg',
        'cpanel': '/images/brands/iconify/logos--cpanel.svg',
        'notion': '/images/brands/iconify/logos--notion-icon.svg',
        'google workspace': '/images/brands/iconify/logos--google-icon.svg',
        'google': '/images/brands/iconify/logos--google-icon.svg',
        'gemini': '/images/brands/iconify/logos--google-gemini.svg',
        'google gemini': '/images/brands/iconify/logos--google-gemini.svg',
        'chatgpt': '/images/brands/iconify/logos--openai-icon.svg',
        'claude': '/images/brands/iconify/simple-icons--anthropic.svg',
        'zapier': '/images/brands/iconify/logos--zapier-icon.svg',
        'gohighlevel': '/images/brands/gohighlevel.png',
        'ollama': 'https://api.iconify.design/logos/ollama.svg',
        'clawdbot': 'https://openclaw.ai/favicon.svg',
        'meta': 'https://api.iconify.design/logos/meta-icon.svg',
        'openai': '/images/brands/iconify/logos--openai-icon.svg',
        'gpt-4': '/images/brands/iconify/logos--openai-icon.svg',
        'gptzero': 'https://gptzero.me/favicon.ico',
        'hugging face': '/images/brands/iconify/logos--hugging-face-icon.svg',
        'notebooklm': '/images/brands/iconify/logos--google-icon.svg',
        'postman': '/images/brands/iconify/logos--postman-icon.svg',
        'redis': '/images/brands/iconify/logos--redis.svg',
        'pandas': '/images/brands/iconify/logos--pandas-icon.svg',
        'numpy': '/images/brands/iconify/logos--numpy.svg',
        'scikit-learn': '/images/brands/iconify/simple-icons--scikitlearn.svg',
        'credly': '/images/brands/iconify/simple-icons--credly.svg',
    };

    return iconMap[name] || null;
}

function toolIconUrl(tool) {
    if (!tool || !tool.name) return null;
    if (tool.icon_url) return tool.icon_url;
    return getIconifyLogoUrl(tool.name);
}

function labelFor(type) {
    return String(type ?? 'work').replace('_', ' ').replace(/\b\w/g, (character) => character.toUpperCase());
}

function achievementLogoUrl(achievement) {
    if (!achievement) return null;

    const title = String(achievement.title ?? '').trim().toLowerCase();
    const issuer = String(achievement.issuer ?? '').trim().toLowerCase();

    if (title.includes('dict') || issuer.includes('dict') || issuer.includes('department of information')) {
        return '/repository-media/portfolio/organizations/dict.svg';
    }
    if (title.includes('civil service') || issuer.includes('civil service')) {
        return achievement.image_url || '/repository-media/portfolio/organizations/civil-service-commission.png';
    }
    if (achievement.image_url) {
        return achievement.image_url;
    }
    if (issuer === 'sap') {
        return '/images/brands/sap.svg';
    }

    return null;
}

const dialogOpen = computed(() => Boolean(selectedBadge.value || selectedProject.value || selectedDesign.value || selectedRecognition.value || selectedExperience.value));
let returnFocus = null;
let previousOverflow = '';
let systemThemeQuery;
let reducedMotionQuery;
let introContext;

function applyTheme() {
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = theme.value === 'dark' || (theme.value === 'system' && systemDark);
    document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
}

function setTheme(value) {
    const changed = (value === 'dark') !== isDark.value;
    theme.value = value;
    if (changed) {
        applyTheme();
        void playPortraitTransition(value === 'dark' ? 1 : -1);
    }
}
function rememberFocus() { returnFocus = document.activeElement; }
function openBadge(badge) { rememberFocus(); selectedBadge.value = badge; }
function openProject(project) { rememberFocus(); selectProjectSlide(0); selectedProject.value = project; }
function openDesign(media) { rememberFocus(); creativeVideoError.value = false; selectedDesign.value = media; }
function openGraphicGallery() { rememberFocus(); designIndex.value = 0; selectedDesign.value = { media_type: 'gallery', title: 'Graphic design' }; }
function moveGraphic(direction) { graphicDirection.value = -direction; designIndex.value = (designIndex.value + direction + graphicDesigns.length) % graphicDesigns.length; }
function openCreativeMagazine() { rememberFocus(); selectedDesign.value = { media_type: 'magazine' }; }
function openJobDescription(experience) { rememberFocus(); selectedExperience.value = experience; }
function openRecognition(achievement, copy) {
    if (!recognitionPreviewUrl(achievement)) return;
    rememberFocus();
    if (copy === 2) {
        returnFocus = pageRoot.value?.querySelector('.recognition-group:not([aria-hidden="true"]) .achievement-preview') || returnFocus;
    }
    selectedRecognition.value = achievement;
}
function closeDialog() {
    creativeVideoElement.value?.pause();
    selectedBadge.value = null;
    selectedProject.value = null;
    selectProjectSlide(0);
    selectedDesign.value = null;
    selectedRecognition.value = null;
    selectedExperience.value = null;
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        if (dialogOpen.value) closeDialog();
        else if (menuOpen.value) {
            menuOpen.value = false;
            menuButton.value?.focus();
        } else { connectionPinned.value = networkPinned.value = webPinned.value = dataPinned.value = false; extraPinned.value = { health: false, ai: false }; closeConnection(); closeNetwork(); closeWeb(); closeData(); closeExtra('health'); closeExtra('ai'); }
    }
    if (event.key !== 'Tab' || !dialogOpen.value) return;
    const dialog = pageRoot.value?.querySelector('[role="dialog"]');
    const elements = [...(dialog?.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), [tabindex="0"]') || [])];
    if (!elements.length) { event.preventDefault(); dialog?.focus(); return; }
    const first = elements[0];
    const last = elements[elements.length - 1];
    if (event.shiftKey && (document.activeElement === first || !dialog.contains(document.activeElement))) {
        event.preventDefault(); last.focus();
    } else if (!event.shiftKey && (document.activeElement === last || !dialog.contains(document.activeElement))) {
        event.preventDefault(); first.focus();
    }
}

watch(dialogOpen, async (open) => {
    if (open) {
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        await nextTick();
        pageRoot.value?.querySelector('[role="dialog"] .close-btn')?.focus();
    } else {
        document.body.style.overflow = previousOverflow;
        await nextTick();
        if (returnFocus?.isConnected) returnFocus.focus();
    }
});

function onMotionChange() {
    // Reverting immediately restores readable content if the preference changes mid-intro.
    introContext?.revert();
    introContext = null;
    reducedMotion.value = reducedMotionQuery.matches;
    if (reducedMotion.value) {
        clearInterval(networkTimer);
        clearInterval(webTimer);
        for (const key of ['health', 'ai']) { clearInterval(extraTimers[key]); extraTimers[key] = null; if (extraOpen.value[key]) extraVisible.value[key] = extraLength(key); }
        if (networkOpen.value) networkText.value = networkDescription;
        if (webOpen.value) webVisibleCount.value = webTechnologies.length;
    }
    if (reducedMotion.value && connectionResult.value) {
        clearInterval(connectionTimer);
        connectionText.value = connectionResult.value;
    }
    if (reducedMotion.value) pausePortrait();
}

onMounted(() => {
    try { theme.value = window.localStorage.getItem('profile-theme') ?? 'system'; } catch { /* Use system preference when storage is unavailable. */ }
    systemThemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    reducedMotion.value = reducedMotionQuery.matches;
    systemThemeQuery.addEventListener('change', applyTheme);
    reducedMotionQuery.addEventListener('change', onMotionChange);
    document.addEventListener('keydown', onKeydown);
    document.addEventListener('visibilitychange', handlePortraitVisibility);
    if ('IntersectionObserver' in window) {
        sectionObserver = new IntersectionObserver((entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;
                entry.target.classList.add('section-arrived');
                sectionObserver.unobserve(entry.target);
            }
        }, { threshold: 0.05 });
        pageRoot.value?.querySelectorAll('.folio-main > section:not(.hero-section)').forEach((section) => sectionObserver.observe(section));
        portraitObserver = new IntersectionObserver(([entry]) => {
            portraitVisible.value = entry.isIntersecting;
            if (!portraitVisible.value) pausePortrait();
        }, { threshold: 0.05 });
        portraitObserver.observe(portraitStage.value);
        carouselObserver = new IntersectionObserver(([entry]) => { carouselVisible.value = entry.isIntersecting; }, { threshold: 0.05 });
        if (achievementsSection.value) carouselObserver.observe(achievementsSection.value);
    } else {
        portraitVisible.value = true;
        carouselVisible.value = true;
    }
    applyTheme();
    if (!reducedMotionQuery.matches) {
        introContext = gsap.context(() => {
            gsap.from('.hero-intro', { y: 12, opacity: 0, duration: 0.6, ease: 'power2.out', clearProps: 'all' });
            gsap.from('.hero-portrait', { y: 18, opacity: 0, duration: 0.6, delay: 0.08, ease: 'power2.out', clearProps: 'all' });
        }, pageRoot.value);
    }
});

watch(theme, (value) => {
    try { window.localStorage.setItem('profile-theme', value); } catch { /* The theme still works for this visit. */ }
    applyTheme();
});

onBeforeUnmount(() => {
    clearTimeout(dataDelay);
    clearTimeout(extraDelays.health);
    clearTimeout(extraDelays.ai);
    clearInterval(extraTimers.health);
    clearInterval(extraTimers.ai);
    clearTimeout(webDelay);
    clearInterval(webTimer);
    clearTimeout(networkDelay);
    clearInterval(networkTimer);
    clearTimeout(connectionDelay);
    clearInterval(connectionTimer);
    connectionRequest?.abort();
    introContext?.revert();
    pausePortrait();
    portraitObserver?.disconnect();
    carouselObserver?.disconnect();
    sectionObserver?.disconnect();
    document.removeEventListener('visibilitychange', handlePortraitVisibility);
    systemThemeQuery?.removeEventListener('change', applyTheme);
    reducedMotionQuery?.removeEventListener('change', onMotionChange);
    document.removeEventListener('keydown', onKeydown);
    if (dialogOpen.value) document.body.style.overflow = previousOverflow;
});
</script>

<template>
    <SeoHead :seo="seo" />
    <div ref="pageRoot" class="folio-root">
        <a class="skip-link" href="#main-content" :inert="dialogOpen">Skip to main content</a>
        <header class="folio-header" :inert="dialogOpen">
            <div class="header-content">
                <button ref="menuButton" type="button" class="menu-toggle" :aria-expanded="menuOpen" aria-controls="portfolio-navigation" @click="menuOpen = !menuOpen">
                    <X v-if="menuOpen" :size="22" aria-hidden="true" /><Menu v-else :size="22" aria-hidden="true" />
                    <span>{{ menuOpen ? 'Close' : 'Menu' }}</span>
                </button>
                <nav id="portfolio-navigation" class="header-nav" :class="{ 'is-open': menuOpen }" aria-label="Portfolio sections" @click="menuOpen = false">
                    <a href="#work">Projects</a><a href="#services">Services</a><a href="#credentials">Credentials</a><a href="#experience">Experience</a><a href="#contact">Contact</a>
                </nav>
                <div class="theme-controls" :class="{ 'is-dark': isDark }" role="group" aria-label="Color theme">
                    <button type="button" :aria-pressed="!isDark" aria-label="Light theme" @click="setTheme('light')"><Sun :size="18" aria-hidden="true" /></button>
                    <button type="button" :aria-pressed="isDark" aria-label="Dark theme" @click="setTheme('dark')"><Moon :size="18" aria-hidden="true" /></button>
                </div>
            </div>
        </header>

        <main id="main-content" class="folio-main" tabindex="-1" :inert="dialogOpen">
            <section class="hero-section" aria-labelledby="hero-title">
                <div class="hero-intro">
                    <h1 id="hero-title">{{ profile.display_name }}</h1>
                    <p class="hero-role">Information Systems, Cybersecurity and Digitalization.</p>
                    <p class="hero-bio">{{ profile.bio }}</p>
                    <div class="hero-link-group">
                        <div class="hero-actions">
                            <Link href="/services" class="primary-btn">Explore services</Link>
                            <a href="#work" class="secondary-btn">See my works <ArrowUpRight :size="18" aria-hidden="true" /></a>
                        </div>
                        <div v-if="profile.external_links?.length" class="hero-external-links" aria-label="Professional profiles">
                            <a v-for="link in profile.external_links" :key="link.label" :href="link.url" :data-brand="link.label.toLowerCase()" target="_blank" rel="noopener noreferrer"><span v-if="['credly', 'github', 'linkedin'].includes(link.label.toLowerCase())" class="profile-link-logo" :style="{ maskImage: `url(${getIconifyLogoUrl(link.label)})` }" aria-hidden="true"></span>{{ link.label }} <ArrowUpRight :size="15" aria-hidden="true" /></a>
                        </div>
                    </div>
                </div>
                <figure ref="portraitStage" class="hero-portrait">
                    <img :src="heroPortraitSrc" :alt="profile.display_name" width="640" height="720" fetchpriority="high">
                    <video ref="portraitForward" class="portrait-video" :class="{ 'is-active': portraitHasFrame && portraitDirection === 1 }" :src="profilePortraitForwardUrl" muted playsinline preload="metadata" aria-hidden="true" @loadeddata="portraitHasFrame = true" @ended="pausePortrait" @error="portraitError"></video>
                    <video ref="portraitReverse" class="portrait-video" :class="{ 'is-active': portraitHasFrame && portraitDirection === -1 }" :src="profilePortraitReverseUrl" muted playsinline preload="metadata" aria-hidden="true" @ended="pausePortrait" @error="portraitError"></video>
                </figure>
            </section>

            <section v-if="achievements.length" id="achievements" ref="achievementsSection" class="recognition-carousel" :class="{ 'is-paused': !carouselVisible }" aria-labelledby="achievements-heading">
                <div class="recognition-heading">
                    <h2 id="achievements-heading">Recognitions</h2>
                </div>
                <div class="recognition-window">
                    <div class="recognition-track" :class="{ 'is-moving': achievements.length > 1 }">
                        <div v-for="copy in (achievements.length > 1 ? 2 : 1)" :key="copy" class="recognition-group" :aria-hidden="copy === 2 ? 'true' : undefined">
                            <component :is="recognitionPreviewUrl(achievement) ? 'button' : recognitionUrl(achievement) ? 'a' : 'article'" v-for="achievement in achievements" :key="achievement.id" class="achievement-item" :class="{ 'achievement-preview': recognitionPreviewUrl(achievement) }" :type="recognitionPreviewUrl(achievement) ? 'button' : undefined" :href="!recognitionPreviewUrl(achievement) ? recognitionUrl(achievement) || undefined : undefined" :target="!recognitionPreviewUrl(achievement) && recognitionUrl(achievement) ? '_blank' : undefined" :rel="!recognitionPreviewUrl(achievement) && recognitionUrl(achievement) ? 'noopener noreferrer' : undefined" :aria-label="recognitionPreviewUrl(achievement) ? 'View ' + achievement.title + ' certificate' : undefined" :tabindex="copy === 2 && (recognitionPreviewUrl(achievement) || recognitionUrl(achievement)) ? -1 : undefined" @click="recognitionPreviewUrl(achievement) && openRecognition(achievement, copy)">
                                <img v-if="achievementLogoUrl(achievement)" :src="achievementLogoUrl(achievement)" :alt="achievement.issuer || achievement.title" loading="lazy" class="achievement-logo" :class="{ 'achievement-programme-logo': recognitionPreviewUrl(achievement) }" @error="$event.target.style.display = 'none'">
                                <Award v-else :size="32" aria-hidden="true" />
                                <span class="achievement-copy"><strong>{{ achievement.title }}</strong><span>{{ achievement.issuer }}</span></span>
                                <ArrowUpRight v-if="!recognitionPreviewUrl(achievement) && recognitionUrl(achievement)" :size="16" class="recognition-arrow" aria-hidden="true" />
                            </component>
                        </div>
                    </div>
                </div>
            </section>

            <section id="work" class="section-container" aria-labelledby="work-heading">
                <div class="section-header"><h2 id="work-heading">Technology built around people.</h2><p>Web applications, health informatics and practical tools for everyday work.</p></div>
                <div class="filter-tabs" role="group" aria-label="Filter projects">
                    <button v-for="category in projectCategories" :key="category.key" type="button" :aria-pressed="activeProjectCategory === category.key" @click="activeProjectCategory = category.key">{{ category.label }}</button>
                </div>
                <div v-if="activeProjectCategory === 'all'" class="project-index">
                    <button v-for="project in filteredProjects" :key="project.id" type="button" class="project-index-entry" @click="openProject(project)">
                        <span class="project-index-heading">
                            <span class="project-title-line">
                                <span class="project-mark" :class="{ 'project-mark--white': isAmplifierProject(project) }" aria-hidden="true"><img v-if="projectMarkUrl(project) && !failedProjectMarks.has(project.id)" :src="projectMarkUrl(project)" :class="{ 'is-loaded': loadedProjectMarks.has(project.id) }" alt="" loading="lazy" @load="loadedProjectMarks.add(project.id)" @error="failedProjectMarks.add(project.id)"><span v-if="!loadedProjectMarks.has(project.id) || failedProjectMarks.has(project.id)">{{ projectInitials(project.title) }}</span></span>
                                <span class="project-title-content"><span class="project-index-meta"><span v-if="project.category">{{ project.category }}</span><span v-if="project.year">{{ project.year }}</span></span><strong>{{ project.title }}</strong></span>
                            </span>
                        </span>
                        <span class="project-index-details">
                            <span v-if="project.summary" class="project-index-summary">{{ project.summary }}</span>
                            <span class="project-index-action">View project <ArrowUpRight :size="18" aria-hidden="true" /></span>
                        </span>
                    </button>
                </div>
                <div v-else class="projects-grid">
                    <button v-for="project in filteredProjects" :key="project.id" type="button" class="project-card" @click="openProject(project)">
                        <div class="project-copy"><div class="project-title-line"><span class="project-mark" :class="{ 'project-mark--white': isAmplifierProject(project) }" aria-hidden="true"><img v-if="projectMarkUrl(project) && !failedProjectMarks.has(project.id)" :src="projectMarkUrl(project)" :class="{ 'is-loaded': loadedProjectMarks.has(project.id) }" alt="" loading="lazy" @load="loadedProjectMarks.add(project.id)" @error="failedProjectMarks.add(project.id)"><span v-if="!loadedProjectMarks.has(project.id) || failedProjectMarks.has(project.id)">{{ projectInitials(project.title) }}</span></span><div class="project-title-content"><span class="content-category">{{ project.category }}</span><h3>{{ project.title }}</h3></div></div><p>{{ project.summary }}</p><span class="text-link">Project details <ArrowUpRight :size="16" aria-hidden="true" /></span></div>
                    </button>
                </div>
                <p v-if="!filteredProjects.length" class="empty-state">No projects in this category yet. Choose another category to keep exploring.</p>
            </section>

            <section id="services" class="section-container" aria-labelledby="services-heading">
                <div class="section-header"><h2 id="services-heading">Technology Solutions Built for Real-World Challenges.</h2><p>Practical technology solutions that help organizations strengthen security, modernize infrastructure, automate workflows, and turn information into better decisions.</p></div>
                <div class="services-grid">
                    <article class="service-item" @pointerenter="(event) => event.pointerType === 'mouse' && scheduleConnection()" @pointerleave="hideConnection" @focusin="scheduleConnection" @focusout="hideConnection">
                        <ShieldCheck :size="32" aria-hidden="true" /><h3>Cybersecurity assessments</h3>
                        <div class="connection-copy">
                            <p :class="{ 'connection-copy-hidden': connectionOpen }" :aria-hidden="connectionOpen">Identify security vulnerabilities, evaluate risks, and strengthen digital defenses through practical security assessments and actionable recommendations, backed by expertise as a DICT-registered Cybersecurity Professional.</p>
                            <div v-if="connectionOpen" id="connection-details" class="connection-preview">
                                <p>Your digital footprint offers a glimpse into Every time you visit a website, your connection and browser can expose information about your digital identity including:</p>
                                <div v-if="connectionLoading" class="connection-skeleton" role="status" aria-label="Checking your connection"><span v-for="line in 4" :key="line" aria-hidden="true"></span></div>
                                <p v-else class="connection-details" role="status" :aria-label="connectionResult || 'Checking your connection'"><span aria-hidden="true">{{ connectionText }}</span></p>
                                <p>These data are shared in your browser.</p>
                            </div>
                            <span v-else id="connection-details" hidden></span>
                        </div>
                        <Link href="/services#network-consulting" class="text-link">Strengthen your security <ArrowUpRight :size="16" aria-hidden="true" /></Link>
                        <button type="button" class="connection-toggle" :aria-expanded="connectionOpen" aria-controls="connection-details" @click="toggleConnection">{{ connectionPinned ? 'Hide connection details' : 'Hover to inspect your connection' }}</button>
                    </article>
                    <article class="service-item network-service" @pointerenter="(event) => event.pointerType === 'mouse' && scheduleNetwork()" @pointerleave="hideNetwork" @focusin="scheduleNetwork" @focusout="hideNetwork">
                        <Network :size="32" aria-hidden="true" /><h3>Network consulting</h3>
                        <div class="connection-copy">
                            <p :class="{ 'connection-copy-hidden': networkOpen }" :aria-hidden="networkOpen">Design, configure, and optimize secure, reliable networks using MikroTik and Cisco technologies, specializing in VLAN segmentation, routing, firewall configuration, WAN failover, and network troubleshooting.</p>
                            <div v-if="networkOpen" id="network-preview" class="network-details"><ul class="network-checklist" :aria-label="networkDescription.replaceAll('\n', ', ')"><li v-for="(item, index) in networkText.split('\n').filter(Boolean)" :key="index" aria-hidden="true"><span class="network-check" aria-hidden="true">✓</span>{{ item }}</li></ul><a href="https://mikrotik.com/consultants?category=consultants&amp;f%5B0%5D=cert%3AMTCNA&amp;f%5B1%5D=cert%3AMTCRE&amp;f%5B2%5D=cert%3AMTCUME&amp;region=Philippines" target="_blank" rel="noopener noreferrer">Verify in MikroTik consultant directory <ArrowUpRight :size="14" aria-hidden="true" /></a></div><span v-else id="network-preview" hidden></span>
                        </div>
                        <Link href="/services#network-consulting" class="text-link">Explore network services <ArrowUpRight :size="16" aria-hidden="true" /></Link>
                        <button type="button" class="connection-toggle" :aria-expanded="networkOpen" aria-controls="network-preview" @click="toggleNetwork">{{ networkPinned ? 'Hide network details' : 'Explore network capabilities' }}</button>
                    </article>
                    <article class="service-item web-service" @pointerenter="(event) => event.pointerType === 'mouse' && scheduleWeb()" @pointerleave="hideWeb" @focusin="scheduleWeb" @focusout="hideWeb">
                        <Code2 :size="32" aria-hidden="true" /><h3>Web &amp; information systems</h3>
                        <div class="connection-copy">
                            <p :class="{ 'connection-copy-hidden': webOpen }" :aria-hidden="webOpen">Develop scalable, user-centered web applications and information systems using Laravel, Vue, and Inertia, transforming complex business processes into efficient, accessible digital solutions.</p>
                            <div v-if="webOpen" id="web-preview" class="web-technologies"><p>Languages and technologies I work with:</p><ul aria-label="Development languages and technologies"><li v-for="technology in webTechnologies.slice(0, webVisibleCount)" :key="technology"><img :src="getIconifyLogoUrl(technology)" alt="" width="24" height="24"><span>{{ technology }}</span></li></ul></div><span v-else id="web-preview" hidden></span>
                        </div>
                        <Link href="/services#web-systems" class="text-link">Web systems <ArrowUpRight :size="16" aria-hidden="true" /></Link>
                        <button type="button" class="connection-toggle" :aria-expanded="webOpen" aria-controls="web-preview" @click="toggleWeb">{{ webPinned ? 'Hide technologies' : 'Explore technologies' }}</button>
                    </article>
                    <article class="service-item data-service" @pointerenter="(event) => event.pointerType === 'mouse' && scheduleData()" @pointerleave="hideData" @focusin="scheduleData" @focusout="hideData">
                        <BarChart3 :size="32" aria-hidden="true" /><h3>Data &amp; decision support</h3>
                        <div class="connection-copy">
                            <p :class="{ 'connection-copy-hidden': dataOpen }" :aria-hidden="dataOpen">Transform complex data into meaningful insights through interactive Power BI dashboards, data analytics, reporting systems, and predictive models that support informed, evidence-based decisions.</p>
                            <div v-if="dataOpen" id="data-preview" class="data-preview">
                                <p class="data-title">Profile views (adjusted)</p>
                                <template v-if="weeklyViews.length">
                                    <p v-if="visitStats.weekly && Number.isFinite(visitStats.weekly.count)" class="data-week">{{ adjustedWeeklyViews.toLocaleString() }} this week <span>(from {{ visitStats.weekly.start }})</span></p>
                                    <p v-else class="data-week">Weekly count unavailable</p>
                                    <svg class="data-line-chart" viewBox="0 0 300 112" role="img" :aria-label="weeklyViews.map(item => `${item.label} ${item.date}: ${item.count === null ? 'no data' : `${item.count} adjusted page views`}`).join('; ')">
                                        <path d="M20 90H280" class="data-axis" />
                                        <path :d="chartPath" class="data-line" />
                                        <g v-for="point in chartPoints" :key="point.date"><g v-if="point.y !== null" class="data-point-group" tabindex="0" :aria-label="`${point.label}: ${point.count} adjusted page views`"><circle :cx="point.x" :cy="point.y" r="10" class="data-hit-area"/><circle :cx="point.x" :cy="point.y" r="3" class="data-point"/><text :x="point.x" :y="point.y - 9" text-anchor="middle" class="data-point-value">{{ point.count }}</text></g><text :x="point.x" y="107" text-anchor="middle" class="data-label">{{ point.label }}</text></g>
                                    </svg>
                                </template>
                                <p v-else class="data-note">Page-view data is unavailable.</p>
                            </div>
                            <span v-else id="data-preview" hidden></span>
                        </div>
                        <Link href="/services#data-analytics" class="text-link">Data analytics <ArrowUpRight :size="16" aria-hidden="true" /></Link>
                        <button type="button" class="connection-toggle" :aria-expanded="dataOpen" aria-controls="data-preview" @click="toggleData">{{ dataPinned ? 'Hide page-view chart' : 'View page-view chart' }}</button>
                    </article>
                    <article class="service-item" @pointerenter="(event) => event.pointerType === 'mouse' && scheduleExtra('health')" @pointerleave="hideExtra('health', $event)" @focusin="scheduleExtra('health')" @focusout="hideExtra('health', $event)">
                        <HeartPulse :size="32" aria-hidden="true" /><h3>Health information systems</h3>
                        <div class="connection-copy"><p :class="{ 'connection-copy-hidden': extraOpen.health }" :aria-hidden="extraOpen.health">Support hospitals and healthcare facilities in adopting and improving electronic medical records, clinical information systems, and digital health workflows through implementation guidance, training, and technical support.</p><ul v-if="extraOpen.health" id="health-preview" class="network-checklist" :aria-label="healthDetails.join(', ')"><li v-for="item in healthDetails.slice(0, extraVisible.health)" :key="item" aria-hidden="true"><span class="network-check" aria-hidden="true">✓</span>{{ item }}</li></ul><span v-else id="health-preview" hidden></span></div>
                        <Link href="/services#health-information-systems" class="text-link">Health systems <ArrowUpRight :size="16" aria-hidden="true" /></Link><button type="button" class="connection-toggle" :aria-expanded="extraOpen.health" aria-controls="health-preview" @click="toggleExtra('health')">{{ extraPinned.health ? 'Hide health services' : 'Explore health services' }}</button>
                    </article>
                    <article class="service-item" @pointerenter="(event) => event.pointerType === 'mouse' && scheduleExtra('ai')" @pointerleave="hideExtra('ai', $event)" @focusin="scheduleExtra('ai')" @focusout="hideExtra('ai', $event)">
                        <Bot :size="32" aria-hidden="true" /><h3>AI &amp; workflow automation</h3>
                        <div class="connection-copy"><p :class="{ 'connection-copy-hidden': extraOpen.ai }" :aria-hidden="extraOpen.ai">Streamline operations through AI-powered solutions, intelligent workflow automation, and integrated systems using tools such as n8n and local AI models, reducing repetitive tasks while maintaining human oversight.</p><div v-if="extraOpen.ai" id="ai-preview" class="web-technologies"><p>AI and automation tools I work with:</p><ul :aria-label="`AI and automation tools: ${automationTools.join(', ')}`"><li v-for="tool in automationTools.slice(0, extraVisible.ai)" :key="tool" aria-hidden="true"><img :src="getIconifyLogoUrl(tool)" alt="" width="24" height="24" @error="(event) => event.target.style.visibility = 'hidden'"><span>{{ tool }}</span></li></ul></div><span v-else id="ai-preview" hidden></span></div>
                        <Link href="/services#ai-automation" class="text-link">AI &amp; automation <ArrowUpRight :size="16" aria-hidden="true" /></Link><button type="button" class="connection-toggle" :aria-expanded="extraOpen.ai" aria-controls="ai-preview" @click="toggleExtra('ai')">{{ extraPinned.ai ? 'Hide automation details' : 'Explore automation services' }}</button>
                    </article>
                </div>
                <div class="services-action"><Link href="/services" class="primary-btn">Explore all services <ArrowUpRight :size="18" aria-hidden="true" /></Link></div>
            </section>

            <section id="designs" class="section-container" aria-labelledby="designs-heading">
                <div class="section-header"><h2 id="designs-heading">Creative Portfolio</h2><p>A selection of my creative works featuring graphic designs, a magazine, and a video production.</p></div>
                <div class="design-grid">
                    <button type="button" class="design-card" @click="openCreativeMagazine"><div class="design-image creative-cover"><img :src="'/creative-assets/magazine/page-0001.jpg'" alt="" loading="lazy"></div><span class="creative-label">Digital magazine</span></button>
                    <button v-if="graphicSamples.length" type="button" class="design-card" @click="openGraphicGallery"><div class="creative-fan" aria-hidden="true"><img v-for="(media, index) in graphicSamples" :key="media.id" :class="`creative-fan-${index}`" :src="media.preview_url || media.thumbnail_url || media.media_url" alt="" loading="lazy"></div><span class="creative-label">Graphic design</span></button>
                    <button v-if="creativeVideo" type="button" class="design-card design-video-card" @click="openDesign(creativeVideo)"><div class="design-image creative-video"><img :src="'/creative-assets/palakasan-preview.png'" alt="" loading="lazy"></div><span class="creative-label">Video production</span></button>
                </div>
                <div class="creative-actions">
                    <h3>Have a Creative Project in Mind?</h3>
                    <p>Let's bring your ideas to life through compelling designs, digital publications, and video content.</p>
                    <a href="/services#digital-publications" class="primary-btn">Let's Work Together <ArrowUpRight :size="16" aria-hidden="true" /></a>
                </div>
            </section>

            <section id="credentials" class="section-container" aria-labelledby="credentials-heading">
                <div class="section-header"><h2 id="credentials-heading">Expertise Backed by Credentials.</h2><p>Explore my professional certifications, verified digital badges, and continuous learning across technology, networking, cybersecurity, and information systems.</p></div>
                <div class="credentials-layout">
                    <div class="credentials-grid">
                        <div v-for="badge in visibleBadges" :key="badge.id" class="credential-slot"><button type="button" class="credential-card" :aria-label="`${badge.name}, ${badge.issuer}. View badge details`" @click="openBadge(badge)">
                            <img :src="badge.image_url" alt="" loading="lazy" width="100" height="100" @error="$event.target.style.visibility = 'hidden'">
                            <span class="credential-copy"><strong>{{ badge.name }}</strong><span>{{ badge.issuer }}</span></span>
                        </button></div>
                    </div>
                </div>
                <div class="archive-actions"><Link href="/badges" class="secondary-btn">Browse all certifications and badges <ArrowUpRight :size="18" aria-hidden="true" /></Link></div>
            </section>

            <section id="publications" class="section-container" aria-labelledby="publications-heading">
                <div class="section-header"><h2 id="publications-heading">Research &amp; Published Works.</h2><p>Explore my published research, scholarly contributions, and digital publications spanning information technology, innovation, and creative communication.</p></div>
                <div class="publication-controls"><label for="publication-search">Find a publication<input id="publication-search" v-model="publicationSearch" type="search" placeholder="Search by title or topic"></label><div class="filter-tabs" role="group" aria-label="Filter publications"><button v-for="type in workTypes" :key="type.key" type="button" :aria-pressed="activeWorkType === type.key" @click="activeWorkType = type.key">{{ type.label }}</button></div></div>
                <div v-if="publishedWorks.length" class="research-book" aria-label="Research and publication pages" :style="workBookHeight ? { '--research-book-height': `${workBookHeight}px` } : null">
                    <div ref="workMeasurements" class="research-measurements" aria-hidden="true" inert>
                        <PublicationSpread v-for="work in publishedWorks" :key="work.id" :work="work" @media-load="scheduleWorkBookMeasure" />
                    </div>
                    <div v-if="currentWork" :key="workBookRevision" class="research-book-viewport" :class="{ 'is-measured': workBookHeight > 0 }" :aria-busy="workTurning">
                    <Transition :name="workTurnDirection > 0 ? 'book-next' : workTurnDirection < 0 ? 'book-previous' : 'book-reset'" :duration="workTurnDirection && !reducedMotion ? 560 : 0" @before-leave="retireWorkPage" @after-enter="workTurning = false" @enter-cancelled="workTurning = false">
                        <PublicationSpread :key="currentWork.id" :work="currentWork" @media-load="scheduleWorkBookMeasure" />
                    </Transition>
                    </div>
                    <div v-if="currentWork" class="research-navigation" aria-label="Publication navigation">
                        <button type="button" :disabled="workPage === 0" :aria-disabled="workTurning || workPage === 0" @click="turnWorkPage(-1)">Previous</button>
                        <span aria-live="polite">{{ workPage + 1 }} / {{ filteredWorks.length }}</span>
                        <button type="button" :disabled="workPage >= filteredWorks.length - 1" :aria-disabled="workTurning || workPage >= filteredWorks.length - 1" @click="turnWorkPage(1)">Next</button>
                    </div>
                </div>
                <p v-if="!filteredWorks.length" class="empty-state">No publications match this search. Try another title or topic.</p>
            </section>

            <section id="github-activity" class="section-container" aria-labelledby="github-activity-heading">
                <div class="github-heading"><div><h2 id="github-activity-heading">GitHub activity.</h2><p>Public contributions over time.</p></div><a v-if="githubProfileUrl" :href="githubProfileUrl" class="text-link" target="_blank" rel="noopener noreferrer"><span class="profile-link-logo" :style="{ maskImage: `url(${getIconifyLogoUrl('GitHub')})` }" aria-hidden="true"></span>View GitHub profile <ArrowUpRight :size="16" aria-hidden="true" /></a></div>
                <template v-if="githubActivity && githubActivity.source !== 'empty' && githubWeeks.length">
                    <p class="github-summary"><strong v-if="githubActivity.total !== null && githubActivity.total !== undefined">{{ githubActivity.total.toLocaleString() }} contributions</strong><span v-else>Contribution activity</span><span v-if="githubActivity.source === 'snapshot'"> · Historical snapshot</span></p>
                    <div class="github-scroll" tabindex="0" aria-label="GitHub contribution calendar; scroll horizontally to see all dates">
                        <div class="github-calendar" aria-label="Daily GitHub contribution activity">
                            <div v-for="week in githubWeeks" :key="week.start_date" class="github-week"><span class="github-month" aria-hidden="true">{{ week.monthLabel }}</span><span v-for="day in week.days" :key="day.date" class="github-day" :class="`github-level-${day.level}`" :style="{ gridRow: new Date(`${day.date}T00:00:00Z`).getUTCDay() + 2 }" role="img" :aria-label="githubDayLabel(day)" :title="githubDayLabel(day)"></span></div>
                        </div>
                    </div>
                    <div class="github-legend" aria-hidden="true"><span>Less</span><i v-for="level in 5" :key="level" class="github-day" :class="`github-level-${level - 1}`"></i><span>More</span></div>
                </template>
                <p v-else class="github-unavailable">GitHub activity is currently unavailable.</p>
            </section>

            <section id="experience" class="section-container" aria-labelledby="experience-heading">
                <div id="background" class="anchor-alias" aria-hidden="true"></div>
                <div class="section-header"><h2 id="experience-heading">Experience &amp; education.</h2><p>A journey of professional experience and academic growth across information technology, systems development, cybersecurity, research, and digital innovation.</p></div>
                <div class="experience-grid">
                    <div class="experience-column" aria-labelledby="work-experience-heading">
                        <h3 id="work-experience-heading" class="column-heading">Work experience</h3>
                        <div v-for="group in experienceGroups" :key="group.id" class="employer-group" :class="{ 'employer-group--progression': group.roles.length > 1 }">
                            <div v-if="group.roles.length > 1" class="employer-heading">
                                <img v-if="group.logoUrl" :src="group.logoUrl" alt="" class="organization-logo" width="40" height="40" loading="lazy" @error="$event.target.style.display = 'none'">
                                <BriefcaseBusiness v-else-if="isFreelanceExperience(group.roles[0])" class="organization-logo" :size="40" :stroke-width="1.5" aria-hidden="true" />
                                <h4>{{ group.organization }}</h4>
                            </div>
                            <article v-for="experience in group.roles" :key="experience.id" class="career-role">
                                <div class="career-heading">
                                    <img v-if="group.roles.length === 1 && experience.logo_url" :src="experience.logo_url" alt="" class="organization-logo" width="40" height="40" loading="lazy" @error="$event.target.style.display = 'none'">
                                    <BriefcaseBusiness v-else-if="group.roles.length === 1 && isFreelanceExperience(experience)" class="organization-logo" :size="40" :stroke-width="1.5" aria-hidden="true" />
                                    <div class="career-identity">
                                        <component :is="group.roles.length > 1 ? 'h5' : 'h4'" class="role-title">{{ experience.position }}</component>
                                        <p v-if="group.roles.length === 1" class="career-organization">{{ experience.organization }}</p>
                                        <p v-if="experienceDates(experience)" class="career-dates">{{ experienceDates(experience) }}</p>
                                        <button type="button" class="job-description-btn text-link" aria-haspopup="dialog" :aria-label="'View job description for ' + experience.position + ' at ' + experience.organization" @click="openJobDescription(experience)">View job description <ArrowUpRight :size="16" aria-hidden="true" /></button>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <p v-if="!workExperiences.length" class="empty-state">Work experience will be added here.</p>
                    </div>
                    <div class="education-column" aria-labelledby="education-heading">
                        <h3 id="education-heading" class="column-heading">Education</h3>
                        <article v-for="item in education" :key="item.id" class="education-entry">
                            <img v-if="item.logo_url" :src="item.logo_url" alt="" class="organization-logo" width="200" loading="lazy" @error="$event.target.style.display = 'none'">
                            <div class="education-identity">
                                <h4>{{ item.program }}</h4>
                                <p class="education-institution">{{ item.institution }}</p>
                                <p v-if="item.end_date || item.level" class="education-date">{{ item.end_date ? `Graduated: ${item.end_date}` : item.level }}</p>
                                <p v-if="item.description" class="education-description">{{ item.description }}</p>
                            </div>
                        </article>
                        <p v-if="!education.length" class="empty-state">Education details will be added here.</p>
                    </div>
                </div>
            </section>



            <section id="tools" class="section-container" aria-labelledby="tools-heading">
                <div class="section-header"><h2 id="tools-heading">The tools behind the work.</h2><p>Technologies I use across development, data, design and infrastructure.</p></div>
                <div class="tools-grid"><article v-for="category in groupedPortfolioTools" :key="category.key" class="tool-category"><h3>{{ category.label }}</h3><ul><li v-for="tool in category.tools" :key="tool.id"><img v-if="toolIconUrl(tool)" :src="toolIconUrl(tool)" alt="" width="24" height="24" loading="lazy" @error="$event.target.style.display = 'none'"><span>{{ tool.name }}</span></li></ul></article></div>
            </section>

            <section id="contact" class="section-container contact-section" aria-labelledby="contact-heading">
                <div class="contact-intro">
                    <p class="contact-kicker">LET'S CONNECT</p>
                    <h2 id="contact-heading">Have an idea? Let's make it work.</h2>
                    <p>From digital transformation and infrastructure to custom software and research, I'd love to hear what you're building.</p>
                    <div class="contact-options" aria-label="Contact options">
                        <a v-if="profile.email" :href="gmailComposeUrl(profile.email, { subject: 'Portfolio Inquiry', body: collaborationMessage })" target="_blank" rel="noopener noreferrer" class="contact-icon contact-gmail" aria-label="Compose a message in Gmail" title="Compose a message in Gmail"><span aria-hidden="true"></span><img src="https://api.iconify.design/logos/google-gmail.svg" alt="" aria-hidden="true"></a>
                        <a v-if="profile.email" :href="`mailto:${profile.email}?subject=${encodeURIComponent('Portfolio Inquiry')}&body=${encodeURIComponent(collaborationMessage)}`" class="contact-icon" aria-label="Compose a message in your email app" title="Compose a message in your email app"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m3 6 9 7 9-7"/></svg></a>
                        <a v-if="linkedinProfileUrl" :href="linkedinProfileUrl" target="_blank" rel="noopener noreferrer" class="contact-icon contact-linkedin" aria-label="Visit LinkedIn profile" title="Visit LinkedIn profile"><span aria-hidden="true"></span></a>
                    </div>
                    <div v-if="profile.email" class="contact-email-row"><a :href="`mailto:${profile.email}`" class="contact-email">{{ profile.email }}</a>
                    <button type="button" class="copy-email" aria-label="Copy email address" title="Copy email address" @click="copyContactEmail"><Copy :size="14" aria-hidden="true" /></button></div><span class="copy-status" role="status">{{ copyEmailStatus }}</span>
                </div>
                <div v-if="profile.email" class="inquiry-card">
                    <h3>Start a conversation</h3>
                    <p>Tell me about your project and I'll get back to you.</p>
                    <form class="guestbook-form" @submit.prevent="submitInquiry" :aria-busy="inquiry.processing">
                        <div v-if="inquiry.errors.inquiry" class="form-errors" role="alert">{{ inquiry.errors.inquiry }}</div>
                        <div class="form-row">
                            <label for="inquiry-name">Your name<input id="inquiry-name" v-model="inquiry.name" type="text" autocomplete="name" maxlength="120" required :aria-invalid="Boolean(inquiry.errors.name)" :aria-describedby="inquiry.errors.name ? 'inquiry-name-error' : undefined"><span v-if="inquiry.errors.name" id="inquiry-name-error" class="field-error">{{ inquiry.errors.name }}</span></label>
                            <label for="inquiry-email">Email address<input id="inquiry-email" v-model="inquiry.email" type="email" autocomplete="email" maxlength="255" required :aria-invalid="Boolean(inquiry.errors.email)" :aria-describedby="inquiry.errors.email ? 'inquiry-email-error' : undefined"><span v-if="inquiry.errors.email" id="inquiry-email-error" class="field-error">{{ inquiry.errors.email }}</span></label>
                        </div>
                        <label for="inquiry-category">What can I help you with?<select id="inquiry-category" v-model="inquiry.category" name="inquiry-category" :aria-invalid="Boolean(inquiry.errors.category)" :aria-describedby="inquiry.errors.category ? 'inquiry-category-error' : undefined"><option v-for="category in inquiryCategories" :key="category" :value="category">{{ category }}</option></select><span v-if="inquiry.errors.category" id="inquiry-category-error" class="field-error">{{ inquiry.errors.category }}</span></label>
                        <label for="inquiry-message">Tell me about your project<textarea id="inquiry-message" v-model="inquiry.message" rows="4" maxlength="1500" required placeholder="A little about what you're building or the challenge you're facing..." :aria-invalid="Boolean(inquiry.errors.message)" :aria-describedby="inquiry.errors.message ? 'inquiry-message-error' : undefined"></textarea><span v-if="inquiry.errors.message" id="inquiry-message-error" class="field-error">{{ inquiry.errors.message }}</span></label>
                        <div class="honeypot" aria-hidden="true"><label for="inquiry-website">Website<input id="inquiry-website" v-model="inquiry.website" type="text" tabindex="-1" autocomplete="off"></label></div>
                        <button type="submit" :disabled="inquiry.processing" class="primary-btn inquiry-submit">{{ inquiry.processing ? 'Sending...' : 'Send message' }} <ArrowUpRight :size="16" aria-hidden="true" /></button>
                        <p v-if="inquirySuccess" class="success-message" role="status">{{ inquirySuccess }}</p>
                    </form>
                </div>

            </section>
        </main>

        <Transition name="dialog">
            <div v-if="dialogOpen" class="modal-overlay" :class="{ 'video-overlay': selectedDesign?.media_type === 'video', 'magazine-overlay': selectedDesign?.media_type === 'magazine', 'gallery-overlay': selectedDesign?.media_type === 'gallery' }" @click.self="closeDialog">
                <section class="modal-card" :class="{ 'project-modal': selectedProject, 'recognition-modal': selectedRecognition, 'experience-modal': selectedExperience, 'video-modal': selectedDesign?.media_type === 'video', 'magazine-modal': selectedDesign?.media_type === 'magazine', 'gallery-modal': selectedDesign?.media_type === 'gallery' }" role="dialog" aria-modal="true" aria-labelledby="detail-title" tabindex="-1">
                    <button type="button" class="close-btn" @click="closeDialog" aria-label="Close details"><X :size="22" aria-hidden="true" /></button>
                    <template v-if="selectedExperience">
                        <img v-if="selectedExperience.logo_url" :src="selectedExperience.logo_url" alt="" class="experience-modal-logo" @error="$event.target.style.display = 'none'">
                        <BriefcaseBusiness v-else-if="isFreelanceExperience(selectedExperience)" class="experience-modal-logo experience-modal-freelance" :size="64" :stroke-width="1.5" aria-hidden="true" />
                        <h2 id="detail-title">{{ selectedExperience.position }}</h2>
                        <p class="content-category">{{ selectedExperience.organization }}</p>
                        <p v-if="experienceDates(selectedExperience)" class="career-dates">{{ experienceDates(selectedExperience) }}</p>
                        <h3 v-if="isFreelanceExperience(selectedExperience) && selectedExperience.summary">Job Description</h3>
                        <p v-if="selectedExperience.summary" class="job-description-summary">{{ selectedExperience.summary }}</p>
                        <div v-if="experienceContributions(selectedExperience).length" class="job-description-details">
                            <h3>{{ isFreelanceExperience(selectedExperience) ? 'Key Responsibilities' : 'Responsibilities' }}</h3>
                            <template v-if="isFreelanceExperience(selectedExperience)">
                                <div v-for="(group, index) in freelanceResponsibilityGroups(selectedExperience)" :key="index" class="freelance-responsibility-group">
                                    <h4 v-if="group.heading">{{ group.heading }}</h4>
                                    <ul v-if="group.heading && group.items.length">
                                        <li v-for="(item, itemIndex) in group.items" :key="itemIndex">{{ item }}</li>
                                    </ul>
                                    <p v-else-if="!group.heading">{{ group.items[0] }}</p>
                                </div>
                            </template>
                            <ol v-else>
                                <li v-for="(contribution, index) in experienceContributions(selectedExperience)" :key="index">{{ contribution }}</li>
                            </ol>
                        </div>
                    </template>
                    <template v-else-if="selectedRecognition">
                        <h2 id="detail-title">{{ selectedRecognition.title }}</h2>
                        <p class="content-category">{{ selectedRecognition.issuer }}</p>
                        <p v-if="selectedRecognition.summary">{{ selectedRecognition.summary }}</p>
                        <img :src="recognitionPreviewUrl(selectedRecognition)" :alt="selectedRecognition.title + ' certificate for ' + profile.display_name" class="recognition-certificate-image" width="3840" height="2136">
                        <a :href="recognitionPreviewUrl(selectedRecognition)" target="_blank" rel="noopener noreferrer" class="primary-btn">Open full-size certificate image <ArrowUpRight :size="18" aria-hidden="true" /></a>
                    </template>
                    <template v-else-if="selectedBadge"><img :src="selectedBadge.image_url" :alt="selectedBadge.name" class="modal-badge-image"><p class="content-category">{{ selectedBadge.category_label }}</p><h2 id="detail-title">{{ selectedBadge.name }}</h2><p class="content-category">{{ selectedBadge.issuer }}</p><p>{{ selectedBadge.description }}</p><div v-if="selectedBadge.skills?.length"><h3>Skills</h3><ul class="text-tags"><li v-for="skill in selectedBadge.skills" :key="skill">{{ skill }}</li></ul></div><a v-if="selectedBadge.certificate_url" :href="selectedBadge.certificate_url" target="_blank" rel="noopener noreferrer" class="primary-btn">Verify credential <ArrowUpRight :size="18" aria-hidden="true" /></a></template>
                    <template v-else-if="selectedProject">
                        <div class="project-modal-layout">
                            <div class="project-gallery">
                                <div class="project-gallery-frame" :class="{ 'project-gallery-logo-frame': projectSlides[projectSlideIndex] === projectFallbackLogo || ((!projectSlides.length || projectImageFailed) && projectFallbackLogo && !projectFallbackFailed) }">
                                    <img v-if="projectSlides.length && !projectImageFailed" :key="projectSlides[projectSlideIndex]" :class="{ 'project-gallery-logo': projectSlides[projectSlideIndex] === projectFallbackLogo }" :src="projectSlides[projectSlideIndex]" :alt="selectedProject.title + ' project image ' + (projectSlideIndex + 1)" @error="projectImageFailed = true">
                                    <img v-else-if="projectFallbackLogo && !projectFallbackFailed && projectSlides[projectSlideIndex] !== projectFallbackLogo" class="project-gallery-logo" :src="projectFallbackLogo" :alt="selectedProject.title + ' logo'" @error="projectFallbackFailed = true">
                                    <p v-else class="project-image-fallback">Project image unavailable</p>
                                </div>
                                <div v-if="projectSlides.length > 1" class="project-pagination" role="group" aria-label="Project images">
                                    <button v-for="(slide, index) in projectSlides" :key="slide" type="button" :aria-label="'Show image ' + (index + 1) + ' of ' + projectSlides.length" :aria-pressed="index === projectSlideIndex" @click="selectProjectSlide(index)"><span aria-hidden="true"></span></button>
                                </div>
                            </div>
                            <div class="project-modal-copy">
                                <img v-if="isAmplifierProject(selectedProject) || selectedProject.title?.trim().toLowerCase() === 'health information systems implementation'" class="project-modal-logo" :class="{ 'project-modal-logo--amplifier': isAmplifierProject(selectedProject) }" :src="projectMarkUrl(selectedProject)" alt="" @error="$event.target.style.display = 'none'"><p class="content-category">{{ selectedProject.category }}</p><h2 id="detail-title">{{ selectedProject.title }}</h2>
                                <p v-if="selectedProject.year" class="project-year">{{ selectedProject.year }}</p>
                                <p v-if="selectedProject.summary" class="project-description">{{ selectedProject.summary }}</p>
                                <ul v-if="projectTechnologies.length" class="project-tech-logos" aria-label="Technologies">
                                    <li v-for="technology in projectTechnologies" :key="technology.key">
                                        <span class="project-tech-icon" tabindex="0" role="img" :aria-label="technology.label">
                                            <img v-if="technology.logo && !failedProjectLogos.has(technology.key)" :src="technology.logo" alt="" width="28" height="28" @error="failedProjectLogos.add(technology.key)">
                                            <Code2 v-else :size="24" aria-hidden="true" />
                                            <span class="project-tech-tooltip" aria-hidden="true">{{ technology.label }}</span>
                                        </span>
                                    </li>
                                </ul>
                                <div class="modal-actions"><a v-if="selectedProject.url" :href="selectedProject.url" target="_blank" rel="noopener noreferrer" class="primary-btn">Visit project <ArrowUpRight :size="18" aria-hidden="true" /></a><a v-if="selectedProject.repo_url" :href="selectedProject.repo_url" target="_blank" rel="noopener noreferrer" class="secondary-btn">View GitHub repository</a></div>
                            </div>
                        </div>
                    </template>
                <template v-else-if="selectedDesign"><h2 id="detail-title" class="sr-only">{{ selectedDesign.media_type === 'gallery' ? 'Graphic designs' : selectedDesign.media_type === 'magazine' ? 'Digital magazine' : 'Video production' }}</h2>
                    <div v-if="selectedDesign.media_type === 'gallery' && graphicDesigns.length" class="gallery-viewer" @keydown.left.prevent="moveGraphic(1)" @keydown.right.prevent="moveGraphic(-1)"><div class="gallery-stage"><div class="gallery-side gallery-side-previous" aria-hidden="true"><img :src="graphicDesigns[(designIndex + 1) % graphicDesigns.length].media_url" alt=""></div><img :key="designIndex" class="gallery-current" :class="graphicDirection < 0 ? 'gallery-flip-previous' : 'gallery-flip-next'" :src="graphicDesigns[designIndex].media_url" :alt="graphicDesigns[designIndex].title"><div class="gallery-side gallery-side-next" aria-hidden="true"><img :src="graphicDesigns[(designIndex + graphicDesigns.length - 1) % graphicDesigns.length].media_url" alt=""></div></div><button type="button" class="gallery-arrow gallery-arrow-previous" aria-label="Next design" @click="moveGraphic(1)">‹</button><button type="button" class="gallery-arrow gallery-arrow-next" aria-label="Previous design" @click="moveGraphic(-1)">›</button><span class="sr-only" aria-live="polite">{{ designIndex + 1 }} of {{ graphicDesigns.length }}</span></div>
                    <CreativeFlipbook v-else-if="selectedDesign.media_type === 'magazine'" />
                    <template v-else-if="selectedDesign.media_type === 'video' && selectedDesign.media_url"><video ref="creativeVideoElement" :src="selectedDesign.media_url" class="modal-design-image" controls autoplay playsinline preload="metadata" @error="creativeVideoError = true">Your browser cannot play this video.</video><p v-if="creativeVideoError" role="status">Video playback is unavailable. <a :href="selectedDesign.media_url" target="_blank" rel="noopener noreferrer">Open the video</a></p></template><iframe v-else-if="selectedDesign.media_type === 'pdf' && selectedDesign.media_url" :src="selectedDesign.media_url" :title="selectedDesign.title" class="modal-design-image modal-design-document"></iframe><img v-else-if="selectedDesign.media_type === 'image' && selectedDesign.media_url" :src="selectedDesign.media_url" :alt="selectedDesign.title" class="modal-design-image"><p v-if="selectedDesign.description">{{ selectedDesign.description }}</p></template>
                </section>
            </div>
        </Transition>
        <footer class="folio-footer" :inert="dialogOpen"><div><Link href="/">{{ profile.display_name }}</Link><nav aria-label="More portfolio sections"><a href="#achievements">Recognition</a><a href="#publications">Publications</a><a href="#designs">Design media</a><a href="#tools">Tools</a></nav><p>&copy; {{ new Date().getFullYear() }} {{ profile.display_name }}. All rights reserved.</p></div></footer>
    </div>
</template>

<style scoped>
.folio-root { overflow-x: clip; background: var(--profile-bg); color: var(--profile-ink); }
.folio-header { position: sticky; top: 0; z-index: 40; background: linear-gradient(110deg, var(--blend-0), var(--blend-2) 48%, var(--blend-1)); border-bottom: 1px solid var(--border-hairline); }
.header-content { max-width: 1440px; min-height: 88px; margin: 0 auto; padding: 16px 5%; display: flex; align-items: center; gap: 36px; }
.header-nav { display: flex; align-items: center; gap: 26px; margin-right: auto; }
.header-nav a { font-size: 15px; padding: 10px 0; color: var(--profile-ink); }
.header-nav a:hover, .folio-footer a:hover { color: var(--accent-primary); }
.theme-controls { position: relative; display: flex; padding: 2px; border: 1px solid var(--border-hairline); border-radius: 999px; background: var(--surface-elevated); }
.theme-controls::before { content: ''; position: absolute; top: 2px; left: 2px; width: 44px; height: 44px; border-radius: 50%; background: #ffd84d; transition: transform 220ms var(--ease-out-cubic), background-color 220ms var(--ease-out-cubic); pointer-events: none; }
.theme-controls.is-dark::before { transform: translateX(44px); background: var(--accent-primary); }
.theme-controls button, .menu-toggle, .close-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; min-height: 44px; border: 0; background: transparent; color: var(--profile-ink); border-radius: 50%; cursor: pointer; transition: background-color var(--motion-fast), color var(--motion-fast); }
.theme-controls button { position: relative; z-index: 1; }
.theme-controls button[aria-pressed='true'] { color: #1c2b33; }
.theme-controls.is-dark button[aria-pressed='true'] { color: var(--surface-elevated); }
.theme-controls button:focus-visible { outline: 2px solid var(--profile-ink); outline-offset: -4px; }
.close-btn:hover { background: var(--surface-elevated); }
@media (prefers-reduced-motion: reduce) { .theme-controls::before, .theme-controls button { transition: none; } }
.menu-toggle { display: none; gap: 8px; border-radius: 0; }
.folio-main { max-width: 1240px; padding: 0 48px; margin: 0 auto; }
.hero-section { display: grid; grid-template-columns: 1.12fr 1fr; align-items: center; gap: 7%; padding: 88px 0 96px; }
.hero-intro { min-width: 0; container-type: inline-size; }
h1 { font-size: clamp(48px, 5.7vw, 76px); font-weight: 400; line-height: 1.04; letter-spacing: -.055em; margin: 0 0 26px; text-wrap: balance; }
.hero-role { max-width: 100%; margin-bottom: 24px; font-size: clamp(12px, 3.9cqw, 18px); font-style: italic; line-height: 1.4; letter-spacing: -.05em; white-space: nowrap; }
.hero-bio { font-size: 17px; line-height: 1.65; color: var(--profile-500); max-width: 510px; text-align: justify; text-align-last: left; }
.hero-link-group { width: fit-content; max-width: 100%; }
.hero-actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 30px; }
.hero-external-links { display: flex; justify-content: center; gap: 22px; flex-wrap: wrap; margin-top: 32px; }
.hero-external-links a { display: inline-flex; align-items: center; gap: 4px; font-size: 14px; color: var(--profile-500); padding: 6px 0; }
.profile-link-logo { display: inline-block; width: 16px; height: 16px; margin-right: 3px; flex: none; background: currentColor; mask-size: contain; mask-position: center; mask-repeat: no-repeat; }
.hero-external-links a:hover { color: var(--accent-primary); }
.hero-external-links a[data-brand='credly']:is(:hover, :focus-visible) { color: #a43f00; }
.hero-external-links a[data-brand='credly']:is(:hover, :focus-visible) .profile-link-logo { background: #ff6b00; }
.hero-external-links a[data-brand='github']:is(:hover, :focus-visible) { color: #181717; }
.hero-external-links a[data-brand='linkedin']:is(:hover, :focus-visible) { color: #0a66c2; }
:global([data-theme='dark'] .hero-external-links a[data-brand='credly']:is(:hover, :focus-visible)) { color: #ffab70; }
:global([data-theme='dark'] .hero-external-links a[data-brand='github']:is(:hover, :focus-visible)) { color: #fff; }
:global([data-theme='dark'] .hero-external-links a[data-brand='linkedin']:is(:hover, :focus-visible)) { color: #8cc8ff; }
.hero-external-links a[data-brand='linkedin']:is(:hover, :focus-visible) .profile-link-logo { background: #0a66c2; }
.hero-portrait { position: relative; min-width: 0; margin: 0; }
.hero-portrait > img { width: 100%; height: auto; aspect-ratio: 1 / 1.12; object-fit: cover; object-position: center 20%; border-radius: 30px; background: var(--surface-elevated); display: block; }
.portrait-video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center 12%; border-radius: 30px; opacity: 0; pointer-events: none; }
.portrait-video.is-active { opacity: 1; }
.primary-btn, .secondary-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 48px; padding: 12px 23px; border-radius: 999px; font-size: 15px; line-height: 1.35; font-weight: 500; text-decoration: none; cursor: pointer; transition: background-color var(--motion-fast), color var(--motion-fast), border-color var(--motion-fast); }
.primary-btn { background: var(--accent-primary); color: var(--on-accent, #fff); border: 1px solid transparent; }
.primary-btn:hover { background: var(--accent-primary-hover); }
.primary-btn:disabled { opacity: .55; cursor: wait; }
.secondary-btn { background: transparent; color: var(--profile-ink); border: 1px solid var(--border-hairline-strong); }
.secondary-btn:hover { background: var(--surface-elevated); border-color: var(--profile-500); }
.text-link { display: inline-flex; gap: 7px; align-items: center; color: var(--accent-primary); font-size: 15px; font-weight: 500; min-height: 36px; }
.text-link:hover { text-decoration: underline; text-underline-offset: 4px; }
.section-container { padding: 80px 0; scroll-margin-top: 106px; }
.folio-main > section { position: relative; isolation: isolate; }
.folio-main > section::before { content: ''; position: absolute; z-index: -1; inset-block: 0; left: 50%; width: 100vw; transform: translateX(-50%); background: linear-gradient(to bottom, var(--blend-from), var(--blend-to)); pointer-events: none; }
.hero-section { --blend-from: var(--blend-0); --blend-to: var(--blend-1); }
#achievements { --blend-from: var(--blend-1); --blend-to: var(--blend-2); }
#work { --blend-from: var(--blend-1); --blend-to: var(--blend-3); }
#achievements + #work { --blend-from: var(--blend-2); }
#services { --blend-from: var(--blend-3); --blend-to: var(--blend-4); }
#credentials { --blend-from: var(--blend-4); --blend-to: var(--blend-5); }
#publications { --blend-from: var(--blend-5); --blend-to: var(--blend-6); }
#github-activity { --blend-from: var(--blend-6); --blend-to: var(--blend-6); }
#experience { --blend-from: var(--blend-6); --blend-to: var(--blend-7); }
#designs { --blend-from: var(--blend-4); --blend-to: var(--blend-4); }
#tools { --blend-from: var(--blend-7); --blend-to: var(--blend-9); }
#contact { --blend-from: var(--blend-9); --blend-to: var(--blend-10); }
#services.section-container { padding-top: 0; }
#credentials.section-container { padding-top: clamp(16px, 2vw, 24px); }
#github-activity.section-container { padding-block: 48px; }
.github-heading { display: flex; justify-content: space-between; align-items: end; gap: 24px; flex-wrap: wrap; }
.github-heading p, .github-summary, .github-unavailable { color: var(--profile-500); margin-top: 12px; }
.github-summary { margin-top: 28px; }
.github-summary strong { color: var(--profile-ink); font-weight: 600; }
.github-scroll { margin-top: 20px; overflow-x: auto; padding: 4px 0 12px; }
.github-scroll:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 4px; }
.github-calendar { display: flex; gap: 4px; width: max-content; }
.github-week { display: grid; grid-template-rows: 20px repeat(7, 12px); grid-template-columns: 12px; gap: 4px; }
.github-month { font-size: 11px; color: var(--profile-500); white-space: nowrap; }
.github-day { display: block; width: 12px; height: 12px; border-radius: 2px; background: #dce5df; }
.github-level-1 { background: #9be9a8; } .github-level-2 { background: #40c463; } .github-level-3 { background: #30a14e; } .github-level-4 { background: #216e39; }
:global([data-theme='dark'] .github-level-0) { background: #26332b; }
.github-legend { display: flex; align-items: center; justify-content: flex-end; gap: 4px; color: var(--profile-500); font-size: 12px; }
.github-legend span:first-child { margin-right: 4px; } .github-legend span:last-child { margin-left: 4px; }
#designs.section-container { padding-top: 0; }
.anchor-alias { scroll-margin-top: 106px; }
.section-header { margin-bottom: 40px; max-width: 760px; }
h2 { font-size: clamp(34px, 4vw, 48px); line-height: 1.13; font-weight: 400; letter-spacing: -.035em; text-wrap: balance; }
.section-header > p { margin-top: 20px; max-width: 640px; color: var(--profile-500); font-size: 18px; line-height: 1.6; }
#designs .section-header, #designs .section-header > p { max-width: none; }
#designs .section-header { text-align: center; }
#credentials .section-header { margin-inline: auto; text-align: center; }
#credentials .section-header > p { margin-inline: auto; }
h3 { font-size: 24px; line-height: 1.25; font-weight: 400; letter-spacing: -.02em; }
h4 { font-size: 19px; line-height: 1.35; font-weight: 500; letter-spacing: -.015em; }
.content-category { color: var(--profile-500); font-size: 13px; line-height: 1.5; }
.filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin: 0 0 32px; }
.filter-tabs button { border: 1px solid var(--border-hairline); padding: 10px 18px; min-height: 44px; border-radius: 999px; background: transparent; color: var(--profile-500); font: inherit; font-size: 14px; cursor: pointer; transition: background-color var(--motion-fast), border-color var(--motion-fast), color var(--motion-fast); }
.filter-tabs button:hover { background: var(--surface-elevated); }
.filter-tabs button[aria-pressed='true'] { background: var(--profile-ink); border-color: var(--profile-ink); color: var(--profile-bg); }
.featured-project { display: grid; grid-template-columns: 1.1fr 1fr; background: var(--surface-elevated); border-radius: 28px; overflow: hidden; margin-bottom: 44px; }
.featured-project-image { min-width: 0; padding: 0; border: 0; background: var(--surface-muted); cursor: pointer; overflow: hidden; }
.featured-project-image img { display: block; width: 100%; height: 100%; min-height: 380px; object-fit: cover; object-position: center; }
.featured-project-copy { padding: 48px 36px; align-self: center; min-width: 0; }
.featured-project-copy h3 { font-size: 38px; margin: 12px 0 20px; }
.featured-project-copy > p:not(.content-category) { font-size: 16px; line-height: 1.65; color: var(--profile-500); }
.text-tags { list-style: none; display: flex; gap: 8px 14px; flex-wrap: wrap; margin: 22px 0; padding: 0; }
.text-tags li { color: var(--profile-500); font-size: 13px; }
.project-actions, .archive-actions, .modal-actions { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; margin-top: 28px; }
#credentials .archive-actions { justify-content: center; }
.project-index { border-top: 1px solid var(--border-hairline); }
.project-index-entry { position: relative; isolation: isolate; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.12fr); align-items: start; gap: clamp(24px, 4vw, 56px); width: 100%; padding: 32px 0; border: 0; border-bottom: 1px solid var(--border-hairline); background: transparent; color: var(--profile-ink); text-align: left; font: inherit; cursor: pointer; }
.project-index-entry::before { content: ''; position: absolute; z-index: -1; inset-block: 0; left: 50%; width: 100vw; transform: translateX(-50%); background: transparent; transition: background-color 180ms ease; pointer-events: none; }
.project-index-entry:hover::before, .project-index-entry:focus-visible::before { background: color-mix(in srgb, var(--accent-primary) 8%, transparent); }
.project-index-heading { display: grid; align-content: start; gap: 10px; min-width: 0; }
.project-title-line { display: flex; align-items: center; gap: 16px; min-width: 0; }
.project-title-content { display: grid; gap: 8px; min-width: 0; }
.project-mark { position: relative; display: grid; flex: 0 0 56px; place-items: center; width: 56px; height: 56px; border: 0; background: transparent; color: var(--profile-ink); font-size: 17px; font-weight: 600; letter-spacing: 0; }
.project-mark--white { background: #fff; border-radius: 10px; }
.project-modal-logo { display: block; width: 64px; height: 64px; object-fit: contain; margin-bottom: 16px; }
.project-modal-logo--amplifier { width: 120px; padding: 8px; background: #fff; border-radius: 10px; }
.project-mark img { position: absolute; inset: 0; display: block; width: 100%; height: 100%; padding: 7px; object-fit: contain; filter: drop-shadow(0 5px 8px rgba(0, 0, 0, .18)); opacity: 0; }
.project-mark img.is-loaded { opacity: 1; }
@media (prefers-reduced-motion: no-preference) {
    .project-mark { transition: transform 420ms cubic-bezier(.22, 1.35, .36, 1); }
    .project-index-entry:hover .project-mark, .project-index-entry:focus-visible .project-mark,
    .project-card:hover .project-mark, .project-card:focus-visible .project-mark { transform: scale(1.16); }
}
.project-index-meta { display: flex; flex-wrap: wrap; gap: 8px 18px; color: var(--profile-500); font-size: 13px; line-height: 1.5; }
.project-index-heading strong { font-size: clamp(20px, 2vw, 25px); font-weight: 500; line-height: 1.25; letter-spacing: -.025em; }
.project-index-details { display: grid; justify-items: start; min-width: 0; gap: 8px; }
.project-index-summary { color: var(--profile-500); font-size: 15px; line-height: 1.65; }
.project-index-action { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; color: var(--accent-primary); font-size: 14px; white-space: nowrap; }
.projects-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 44px 30px; }
.project-card, .design-card { text-align: left; color: inherit; font: inherit; padding: 0; border: 0; background: transparent; cursor: pointer; min-width: 0; }
.project-image { border-radius: 22px; overflow: hidden; background: var(--surface-elevated); margin-bottom: 24px; }
.project-image img { display: block; width: 100%; height: auto; aspect-ratio: 1.55; object-fit: cover; transition: transform var(--motion-slow) var(--ease-out-cubic); }
.project-card:hover .project-image img, .design-card:hover .design-image img { transform: scale(1.025); }
.project-copy { padding-right: 24px; }
.project-copy h3 { margin: 0; }
.project-copy > .project-title-line { margin-bottom: 18px; }
.project-copy > p { font-size: 16px; line-height: 1.6; color: var(--profile-500); margin-bottom: 14px; }
.project-card:hover .text-link, .design-card:hover .text-link { text-decoration: underline; text-underline-offset: 4px; }
.services-grid { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 24px; }
.service-item { display: flex; flex-direction: column; align-items: flex-start; grid-column: span 2; min-width: 0; padding: 28px; border: 1px solid var(--border-hairline); border-radius: 18px; background: var(--surface-elevated); }
@media (prefers-reduced-motion: no-preference) {
    .service-item { transition: transform 200ms var(--ease-out-cubic); }
    .service-item:hover, .service-item:focus-within { transform: scale(1.02); }
}
.service-item > svg { color: var(--profile-700); margin-bottom: 28px; }
.service-item h3 { max-width: 290px; margin-bottom: 18px; }
.service-item p { color: var(--profile-500); line-height: 1.65; font-size: 16px; margin-bottom: 18px; }
.service-item .text-link { margin-top: auto; }
.service-item:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 3px; }
.service-item p { text-align: justify; }
.network-details a { display: inline-flex; align-items: center; gap: 5px; padding: 6px 10px; border: 1px solid var(--border-hairline); border-radius: 999px; color: var(--profile-500); font-size: 13px; line-height: 1.4; text-decoration: none; }
.network-details a:hover, .network-details a:focus-visible { color: var(--profile-ink); text-decoration: underline; }
.network-checklist { display: grid; gap: 6px; margin: 0 0 14px; padding: 0; list-style: none; color: var(--profile-500); font-size: 15px; line-height: 1.5; }
.network-checklist li { display: flex; gap: 8px; align-items: baseline; }
.network-check { color: var(--accent-primary); }
.web-technologies p { margin-bottom: 12px; }
.web-technologies ul { display: flex; flex-wrap: wrap; gap: 12px 16px; margin: 0 0 18px; padding: 0; list-style: none; }
.web-technologies li { display: inline-flex; align-items: center; gap: 6px; color: var(--profile-500); font-size: 13px; }
.web-technologies img { object-fit: contain; }
.connection-copy { align-self: stretch; display: grid; }
.connection-copy > * { grid-area: 1 / 1; }
.connection-copy-hidden { visibility: hidden; }
.service-preview-list { margin: 0 0 18px; padding-left: 1.2em; color: var(--profile-500); font-size: 15px; line-height: 1.65; }
.data-preview { min-width: 0; }
.service-item .data-title { margin-bottom: 4px; color: var(--profile-ink); font-weight: 400; text-align: left; }
.service-item .data-week { margin-bottom: 10px; font-size: 13px; text-align: left; }
.data-week span { font-size: 11px; }
.data-line-chart { display: block; width: 100%; height: auto; overflow: visible; }
.data-axis { fill: none; stroke: var(--border-hairline); stroke-width: 1; }
.data-line { fill: none; stroke: var(--accent-primary); stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
.data-point { fill: var(--accent-primary); }
.data-hit-area { fill: transparent; }
.data-point-value { fill: var(--profile-ink); font-size: 11px; font-weight: 600; opacity: 0; pointer-events: none; }
.data-point-group:hover .data-point-value, .data-point-group:focus .data-point-value { opacity: 1; }
.data-point-group:focus-visible .data-point { stroke: var(--profile-ink); stroke-width: 2; }
.data-label { fill: var(--profile-500); font-size: 10px; }
@media (prefers-reduced-motion: no-preference) {
    .data-line { stroke-dasharray: 500; animation: data-line-draw 800ms ease-out both; }
    @keyframes data-line-draw { from { stroke-dashoffset: 500; } to { stroke-dashoffset: 0; } }
}
.service-item .data-note { margin: 10px 0; font-size: 12px; line-height: 1.4; text-align: left; }
.connection-preview p { margin-bottom: 12px; font-size: 14px; }
.connection-skeleton { display: grid; gap: 9px; margin: 12px 0; }
.connection-skeleton span { display: block; width: 85%; height: 12px; border-radius: 6px; background: var(--border-hairline); }
.connection-skeleton span:nth-child(even) { width: 68%; }
@media (prefers-reduced-motion: no-preference) {
    .connection-skeleton span { animation: skeleton-pulse 1.2s ease-in-out infinite alternate; }
    .section-arrived > .section-header, .section-arrived > .recognition-heading, .section-arrived > .contact-intro { animation: section-entrance 500ms ease-out both; }
    @keyframes skeleton-pulse { from { opacity: .45; } to { opacity: 1; } }
    @keyframes section-entrance { from { transform: translateY(12px); } to { transform: translateY(0); } }
}
.service-item .connection-details { color: var(--profile-500); white-space: pre-wrap; overflow-wrap: anywhere; text-align: left; }
.connection-toggle { min-height: 44px; padding: 0; border: 0; background: transparent; color: var(--profile-500); font: inherit; font-size: 12px; font-style: italic; text-align: left; cursor: pointer; }
.connection-toggle:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 3px; }
.connection-contact { display: inline-flex; gap: 6px; align-items: center; margin-top: 14px; color: var(--accent-primary); font-size: 14px; }
.section-action { margin-top: 36px; }
.services-action { display: flex; justify-content: center; margin-top: 36px; }
.recognition-carousel { width: 100%; margin: 0; padding-bottom: 40px; }
.recognition-heading { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; }
.recognition-heading h2 { font-size: clamp(24px, 2.4vw, 32px); font-weight: 500; letter-spacing: -.025em; }
.recognition-window { overflow: hidden; }
.recognition-track { display: flex; width: max-content; }
.recognition-track.is-moving { animation: recognition-loop 35s linear infinite; }
.recognition-carousel:hover .recognition-track, .recognition-carousel:focus-within .recognition-track, .recognition-carousel.is-paused .recognition-track { animation-play-state: paused; }
.recognition-group { display: flex; flex: none; gap: 12px; padding-right: 12px; }
.achievement-item { position: relative; display: flex; flex-direction: column; align-items: center; gap: 16px; width: 250px; min-height: 190px; padding: 16px; color: var(--profile-ink); background: transparent; text-align: center; }
.achievement-item[href]:hover strong { text-decoration: underline; text-underline-offset: 4px; }
.achievement-preview { border: 0; font: inherit; cursor: pointer; }
.achievement-preview:hover strong { text-decoration: underline; text-underline-offset: 4px; }
.achievement-logo, .achievement-item > svg:first-child { flex: none; width: 80px; height: 80px; object-fit: contain; }
.achievement-programme-logo { width: 160px; height: 80px; }
.achievement-copy { display: grid; gap: 5px; min-width: 0; }
.achievement-copy strong { font-size: 14px; font-weight: 600; line-height: 1.35; }
.achievement-copy > span { color: var(--profile-500); font-size: 12px; line-height: 1.35; }
.recognition-arrow { position: absolute; top: 16px; right: 16px; color: var(--profile-500); }
@keyframes recognition-loop { to { transform: translateX(-50%); } }
@media (prefers-reduced-motion: reduce) {
    .recognition-track.is-moving { animation: none; width: 100%; }
    .recognition-group { flex-wrap: wrap; width: 100%; padding-right: 0; }
    .recognition-group[aria-hidden='true'] { display: none; }
    .achievement-item { flex: 1 1 220px; }
}
.credentials-layout { display: block; padding: 32px 0; }
.credentials-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 80px 12px; }
.credential-slot { position: relative; min-width: 0; }
.credential-slot:hover, .credential-slot:focus-within { z-index: 2; }
.credential-card { position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; width: 100%; height: 100%; min-width: 0; padding: 16px 10px; background: var(--surface-elevated); border: 1px solid transparent; border-radius: 16px; color: inherit; text-align: center; cursor: pointer; font: inherit; }
.credential-slot:hover .credential-card, .credential-slot:focus-within .credential-card { position: absolute; top: 50%; left: 50%; width: min(100%, 240px); height: auto; min-height: 0; aspect-ratio: 1; overflow: auto; transform: translate(-50%, -50%); box-shadow: 0 12px 30px rgba(0, 0, 0, .2); }
.credential-card:focus-visible { outline: 2px solid var(--profile-ink); outline-offset: 2px; }
.credential-card > img { width: 72px; height: 72px; object-fit: contain; }
.credential-copy { display: grid; gap: 4px; min-width: 0; }
.credential-copy strong { font-size: 13px; font-weight: 500; line-height: 1.3; overflow-wrap: anywhere; }
.credential-copy > span:last-child { font-size: 11px; color: var(--profile-500); line-height: 1.3; }
@media (prefers-reduced-motion: no-preference) {
    .credential-slot:hover .credential-card, .credential-slot:focus-within .credential-card { animation: badge-zoom-in 220ms ease-out both; }
    @keyframes badge-zoom-in { from { transform: translate(-50%, -50%) scale(.92); } to { transform: translate(-50%, -50%) scale(1); } }
}
.publication-controls { display: flex; gap: 24px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 36px; }
.publication-controls label { min-width: min(100%, 300px); flex: 1; max-width: 430px; }
.publication-controls .filter-tabs { margin-bottom: 0; }
.research-book { position: relative; max-width: 1080px; margin: 0 auto; }
.research-measurements { position: absolute; top: 0; inset-inline: 1px; height: 0; overflow: hidden; visibility: hidden; pointer-events: none; }
.research-book-viewport { --book-cover-spine: right center; --book-page-spine: left center; --book-next-out: rotateY(-90deg); --book-next-in: rotateY(90deg); --book-previous-out: rotateY(90deg); --book-previous-in: rotateY(-90deg); position: relative; height: var(--research-book-height, auto); overflow: hidden; isolation: isolate; perspective: 1600px; border: 1px solid var(--border-hairline); border-radius: 18px; background: var(--profile-bg); box-shadow: 0 18px 42px rgba(0, 0, 0, .1); }
.research-book-viewport.is-measured > .research-spread { position: absolute; inset: 0; }
.research-navigation { display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 24px; color: var(--profile-500); font-size: 14px; }
.research-navigation button { min-height: 44px; padding: 8px 16px; border: 1px solid var(--border-hairline); border-radius: 999px; color: var(--profile-ink); background: transparent; font: inherit; cursor: pointer; }
.research-navigation button:disabled { opacity: .45; cursor: default; }
.research-navigation button:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 3px; }
@media (prefers-reduced-motion: no-preference) {
    .book-next-leave-active, .book-previous-leave-active { z-index: 2; pointer-events: none; }
    .book-next-enter-active, .book-previous-enter-active { z-index: 1; }
    .book-next-leave-active :deep(.research-page) { transform-origin: var(--book-page-spine); animation: book-next-out 560ms cubic-bezier(.2, 0, 0, 1) both; }
    .book-next-leave-active :deep(.research-cover), .book-previous-leave-active :deep(.research-page) { animation: book-resting-page-out 560ms linear both; }
    .book-next-enter-active :deep(.research-cover) { transform-origin: var(--book-cover-spine); animation: book-next-in 560ms cubic-bezier(.2, 0, 0, 1) both; }
    .book-previous-leave-active :deep(.research-cover) { transform-origin: var(--book-cover-spine); animation: book-previous-out 560ms cubic-bezier(.2, 0, 0, 1) both; }
    .book-previous-enter-active :deep(.research-page) { transform-origin: var(--book-page-spine); animation: book-previous-in 560ms cubic-bezier(.2, 0, 0, 1) both; }
}
@keyframes book-next-out { 0% { transform: rotateY(0); opacity: 1; } 49% { transform: var(--book-next-out); opacity: 1; } 50%, 100% { transform: var(--book-next-out); opacity: 0; } }
@keyframes book-next-in { 0%, 49% { transform: var(--book-next-in); opacity: 0; } 50% { transform: var(--book-next-in); opacity: 1; } 100% { transform: rotateY(0); opacity: 1; } }
@keyframes book-previous-out { 0% { transform: rotateY(0); opacity: 1; } 49% { transform: var(--book-previous-out); opacity: 1; } 50%, 100% { transform: var(--book-previous-out); opacity: 0; } }
@keyframes book-previous-in { 0%, 49% { transform: var(--book-previous-in); opacity: 0; } 50% { transform: var(--book-previous-in); opacity: 1; } 100% { transform: rotateY(0); opacity: 1; } }
@keyframes book-resting-page-out { 0%, 49% { opacity: 1; } 50%, 100% { opacity: 0; } }
@media (max-width: 700px) {
    .research-book-viewport { --book-cover-spine: center bottom; --book-page-spine: center top; --book-next-out: rotateX(90deg); --book-next-in: rotateX(-90deg); --book-previous-out: rotateX(-90deg); --book-previous-in: rotateX(90deg); }
}
.works-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 40px; }
.work-item { padding: 28px 0 0; border-top: 1px solid var(--border-hairline); }
.work-meta { display: flex; gap: 14px; flex-wrap: wrap; color: var(--profile-500); font-size: 13px; margin-bottom: 18px; }
.work-item h3 { font-size: 25px; margin-bottom: 18px; }
.work-item > p { color: var(--profile-500); line-height: 1.65; font-size: 16px; margin-bottom: 16px; }
.experience-grid { display: grid; grid-template-columns: minmax(0, 13fr) minmax(0, 7fr); align-items: start; gap: 64px; }
.experience-column, .education-column, .career-identity, .education-identity { min-width: 0; }
.column-heading { font-size: 20px; font-weight: 500; padding-bottom: 24px; }
.employer-group { padding: 30px 0; border-top: 1px solid var(--border-hairline); }
.career-heading, .employer-heading { display: flex; align-items: flex-start; gap: 16px; }
.organization-logo { width: 40px; height: 40px; flex-shrink: 0; object-fit: contain; }
.role-title { font-size: clamp(22px, 2.2vw, 28px); font-weight: 500; line-height: 1.25; letter-spacing: -.02em; overflow-wrap: anywhere; }
.career-organization { margin-top: 8px; font-size: 16px; line-height: 1.5; }
.career-dates, .education-date { margin-top: 8px; color: var(--profile-500); font-size: 14px; line-height: 1.6; }
.job-description-btn { min-height: 44px; margin-top: 20px; border: 0; background: transparent; padding: 0; font-family: inherit; cursor: pointer; }
.employer-heading { align-items: center; margin-bottom: 24px; }
.employer-heading h4 { font-size: 18px; font-weight: 500; line-height: 1.4; overflow-wrap: anywhere; }
.employer-group--progression .career-role { margin-left: 19px; padding-left: 36px; border-left: 1px solid var(--border-hairline-strong); }
.employer-group--progression .career-role + .career-role { padding-top: 30px; }
.education-column { padding-left: 32px; border-left: 1px solid var(--border-hairline); }
.education-entry { display: flex; flex-direction: column; align-items: flex-start; gap: 20px; padding: 30px 0; border-top: 1px solid var(--border-hairline); }
.education-entry .organization-logo { width: min(100%, 200px); height: auto; max-height: 100px; object-fit: contain; object-position: left; }
.education-entry h4 { font-size: 20px; font-weight: 500; line-height: 1.35; letter-spacing: -.015em; overflow-wrap: anywhere; }
.education-institution { margin-top: 10px; font-size: 16px; line-height: 1.5; }
.education-description { margin-top: 16px; color: var(--profile-500); font-size: 16px; line-height: 1.65; }
@media (max-width: 900px) {
    .experience-grid { grid-template-columns: 1fr; }
    .education-column { padding-left: 0; border-left: 0; }
}
.design-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 40px 28px; }
.creative-cover img { object-fit: contain !important; }
.creative-fan { position: relative; height: 0; padding-bottom: 80%; margin-bottom: 20px; overflow: hidden; border-radius: 20px; background: var(--surface-elevated); }
.creative-fan img { position: absolute; top: 15%; left: 28%; width: 44%; height: 70%; object-fit: cover; border: 3px solid var(--surface-elevated); border-radius: 8px; box-shadow: 0 8px 24px #0003; }
.creative-fan-0 { transform: translateX(-42%) rotate(-12deg); }
.creative-fan-1 { z-index: 2; }
.creative-fan-2 { transform: translateX(42%) rotate(12deg); }
.creative-video { position: relative; }
.creative-video .media-preview-fallback { min-height: 220px; }
.creative-controls { display: flex; flex-wrap: wrap; gap: 12px; margin: 16px 0; }
.creative-actions { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 18px; margin: 48px auto 0; padding: 40px 24px; border-radius: 24px; background: var(--surface-elevated); }
.creative-actions h3 { font-size: clamp(24px, 3vw, 34px); line-height: 1.2; }
.creative-actions p { max-width: 100%; color: var(--profile-500); line-height: 1.6; }
.creative-actions .primary-btn:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 4px; }
.creative-controls button { min-height: 44px; padding: 8px 16px; border: 1px solid var(--border-hairline); border-radius: 999px; background: var(--surface-elevated); color: var(--profile-ink); cursor: pointer; }
.design-image { border-radius: 20px; overflow: hidden; background: var(--surface-elevated); margin-bottom: 20px; }
.design-image img { display: block; width: 100%; height: auto; aspect-ratio: 1.25; object-fit: cover; transition: transform var(--motion-slow) var(--ease-out-cubic); }
.design-card h3 { font-size: 22px; margin: 9px 0 14px; }
.design-card .creative-label { display: block; margin-top: 14px; text-align: center; color: var(--profile-500); font-size: 19px; line-height: 1.5; }
.tools-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 36px; }
.tool-category { border-top: 1px solid var(--border-hairline); padding-top: 24px; }
.tool-category h3 { font-size: 21px; margin-bottom: 20px; }
.tool-category ul { list-style: none; display: grid; gap: 15px; }
.tool-category li { display: flex; align-items: center; gap: 12px; color: var(--profile-500); font-size: 15px; }
.tool-category img { width: 23px; height: 23px; object-fit: contain; }

.contact-intro .contact-kicker { margin: 0 0 18px; color: var(--accent-primary); font-size: 13px; font-weight: 700; letter-spacing: .12em; }
.inquiry-card { padding: 32px; background: var(--surface-elevated); border-radius: 24px; min-width: 0; }
.inquiry-card > p { color: var(--profile-500); line-height: 1.6; margin-top: 10px; }
.contact-email-row { display: flex; align-items: center; gap: 0; margin-top: -4px; min-width: 0; }
.copy-email { display: inline-grid; place-items: center; flex: 0 0 44px; width: 44px; height: 44px; border: 0; border-radius: 50%; background: transparent; color: var(--accent-primary); cursor: pointer; }
.copy-email svg { transform: translate(-5px, 5px); }
.copy-email:hover { background: var(--surface-elevated); }
.copy-email:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 2px; }
.copy-status { display: block; color: var(--profile-500); font-size: 14px; }
.guestbook-form > .inquiry-submit { justify-self: center; }
.inquiry-note { color: var(--profile-500); font-size: 14px; line-height: 1.5; }
.guestbook-secondary { grid-column: 1 / -1; }
.guestbook-secondary summary { cursor: pointer; list-style: none; display: flex; flex-wrap: wrap; align-items: center; gap: 8px 20px; }
.guestbook-secondary summary::-webkit-details-marker { display: none; }
.guestbook-secondary summary::after { content: '+'; margin-left: auto; font-size: 24px; }
.guestbook-secondary[open] summary::after { content: '-'; }
.guestbook-secondary summary span { color: var(--profile-500); }
.guestbook-secondary[open] > h3 { margin-top: 28px; }
@media (max-width: 600px) { .inquiry-card { padding: 24px 20px; border-radius: 20px; } }

.contact-section { display: grid; grid-template-columns: .85fr 1.15fr; align-items: start; gap: 80px; padding-bottom: 112px; }
.contact-intro { padding-top: 32px; }
.contact-intro > p { margin: 24px 0 28px; font-size: 18px; line-height: 1.6; color: var(--profile-500); }
.contact-options { display: flex; align-items: center; flex-wrap: wrap; gap: 12px; }
.contact-icon { display: inline-grid; position: relative; place-items: center; width: 48px; height: 48px; color: var(--profile-ink); border-radius: 50%; transition: color 180ms ease, background-color 180ms ease; }
.contact-icon:hover { background: var(--surface-elevated); }
.contact-icon:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 3px; }
.contact-icon svg, .contact-icon span, .contact-icon img { width: 24px; height: 24px; }
.contact-icon span { background: currentColor; mask-position: center; mask-size: contain; mask-repeat: no-repeat; }
.contact-gmail span { mask-image: url(https://api.iconify.design/simple-icons/gmail.svg); }
.contact-gmail img { position: absolute; opacity: 0; }
.contact-gmail:hover img, .contact-gmail:focus-visible img { opacity: 1; }
.contact-linkedin span { mask-image: url(https://api.iconify.design/simple-icons/linkedin.svg); }
.contact-linkedin:hover, .contact-linkedin:focus-visible { color: #0a66c2; }
.contact-email { min-width: 0; font-size: 15px; overflow-wrap: anywhere; color: var(--profile-500); }
.guestbook-card { padding: 32px; background: var(--surface-elevated); border-radius: 24px; min-width: 0; }
.guestbook-card > h3 { margin-bottom: 12px; }
.guestbook-card > p { color: var(--profile-500); font-size: 14px; line-height: 1.6; }
.guestbook-form { display: grid; gap: 20px; margin-top: 24px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
label { display: grid; gap: 8px; font-size: 14px; font-weight: 500; min-width: 0; }
input, textarea, select { display: block; width: 100%; min-width: 0; border: 1px solid var(--profile-400); border-radius: 8px; background: var(--profile-bg); color: var(--profile-ink); padding: 12px 14px; font: inherit; font-size: 16px; font-weight: 400; line-height: 1.5; }
select { cursor: pointer; }
select:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 2px; }
textarea { resize: vertical; min-height: 130px; }
input[aria-invalid='true'], textarea[aria-invalid='true'] { border-color: var(--profile-ink); border-width: 2px; }
.optional-label { display: contents; color: var(--profile-500); font-weight: 400; }
.form-hint { font-size: 12px; font-weight: 400; color: var(--profile-500); }
.field-error { font-weight: 500; color: var(--profile-ink); font-size: 13px; }
.form-errors { padding: 16px; border: 1px solid var(--profile-ink); border-radius: 8px; font-size: 14px; }
.form-errors ul { margin: 8px 0 0 18px; }
.form-errors a { text-decoration: underline; text-underline-offset: 3px; }
.honeypot { display: none; }
.guestbook-form > button { justify-self: start; }
.success-message { color: var(--profile-ink); font-size: 15px; }
.guestbook-entries { margin-top: 36px; }
.guestbook-entries article { padding: 22px 0 0; border-top: 1px solid var(--border-hairline-strong); margin-top: 22px; }
.guestbook-entries h4 { font-size: 16px; margin-bottom: 4px; }
.guestbook-entries article > p { font-size: 15px; color: var(--profile-500); line-height: 1.6; margin-top: 10px; white-space: pre-wrap; overflow-wrap: anywhere; }
.empty-state { padding: 24px 0; color: var(--profile-500); font-size: 16px; }
.modal-overlay { position: fixed; inset: 0; z-index: 100; display: flex; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 6vh 24px; background: rgba(15, 27, 36, .6); }
.modal-overlay.magazine-overlay { padding: 8px; background: rgba(0, 0, 0, .9); }
.modal-overlay.gallery-overlay { background: rgba(0, 0, 0, .9); }
.modal-overlay.video-overlay { padding: 0; background: #000; }
.modal-card.video-modal { display: flex; align-items: center; justify-content: center; width: 100%; max-width: none; min-height: 100dvh; padding: 64px 20px 20px; border-radius: 0; background: #000; color: #fff; }
.video-modal .modal-design-image { width: 100%; height: calc(100dvh - 100px); max-height: none; margin: 0; background: #000; }
.video-modal .close-btn { z-index: 2; color: #fff; background: #333; }
.video-modal .close-btn:hover, .video-modal .close-btn:focus-visible { background: #555; }
.modal-card { position: relative; width: 100%; max-width: 760px; border-radius: 26px; background: var(--profile-bg); color: var(--profile-ink); padding: 48px; margin: auto; overflow-wrap: anywhere; }
.modal-card.magazine-modal { width: 100%; max-width: none; padding: 48px 0 20px; background: transparent; border: 0; box-shadow: none; color: #fff; }
.magazine-modal .close-btn { color: #fff; background: #333; }
.magazine-modal .close-btn:hover, .magazine-modal .close-btn:focus-visible { background: #555; }
.modal-card.gallery-modal { max-width: 1600px; padding: 54px 0 12px; background: transparent; border: 0; box-shadow: none; color: #fff; }
.gallery-modal .close-btn { color: #fff; background: #333; }
.gallery-modal .close-btn:hover, .gallery-modal .close-btn:focus-visible { background: #555; }
.gallery-stage { display: flex; justify-content: center; align-items: center; gap: 4px; overflow: hidden; perspective: 1400px; }
.gallery-viewer { position: relative; }
.gallery-arrow { position: absolute; top: 50%; transform: translateY(-50%); display: grid; place-items: center; width: 44px; height: 44px; border: 0; border-radius: 50%; background: #222d; color: #fff; font: inherit; font-size: 32px; line-height: 1; cursor: pointer; }
.gallery-arrow-previous { left: 8px; }
.gallery-arrow-next { right: 8px; }
.gallery-arrow:focus-visible { outline: 2px solid #fff; outline-offset: 3px; }
@keyframes gallery-flip-next { from { opacity: 0; transform: translateX(-18%) rotateY(-55deg); } to { opacity: 1; transform: translateX(0) rotateY(0); } }
@keyframes gallery-flip-previous { from { opacity: 0; transform: translateX(18%) rotateY(55deg); } to { opacity: 1; transform: translateX(0) rotateY(0); } }
.gallery-stage .gallery-current { display: block; flex: 0 1 auto; width: auto; height: auto; max-width: calc(100% - 2 * min(8vw, 76px) - 8px); max-height: min(82dvh, 1000px); backface-visibility: hidden; }
.gallery-current.gallery-flip-next { animation: gallery-flip-next 800ms cubic-bezier(.2, 0, 0, 1); }
.gallery-current.gallery-flip-previous { animation: gallery-flip-previous 800ms cubic-bezier(.2, 0, 0, 1); }
.gallery-side { position: relative; flex: 0 0 min(8vw, 76px); align-self: stretch; overflow: hidden; opacity: .5; }
.gallery-side img { position: absolute; top: 0; width: auto; max-width: none; height: 100%; }
.gallery-side-previous img { right: 0; }
.gallery-side-next img { left: 0; }
@media (prefers-reduced-motion: reduce) { .gallery-current.gallery-flip-next, .gallery-current.gallery-flip-previous { animation: none; } }
.gallery-count { margin: 12px 0 0; text-align: center; }
.gallery-modal .creative-controls { justify-content: center; }
.close-btn { position: absolute; right: 16px; top: 16px; }
.modal-card h2 { font-size: 34px; margin: 12px 24px 20px 0; }
.modal-card h3 { font-size: 21px; margin-top: 24px; }
.modal-card > p:not(.content-category) { font-size: 16px; line-height: 1.7; color: var(--profile-500); margin: 20px 0; }
.modal-badge-image { display: block; width: 120px; height: 120px; object-fit: contain; margin-bottom: 24px; }
.recognition-modal { max-width: 1120px; }
.experience-modal { max-width: 860px; max-height: 88vh; max-height: calc(100dvh - 12vh); overflow-y: auto; }
.experience-modal-logo { display: block; width: auto; height: auto; max-width: min(100%, 200px); max-height: 90px; margin-bottom: 20px; object-fit: contain; object-position: left; }
:global([data-theme='dark'] .education-entry .organization-logo),
:global([data-theme='dark'] img.experience-modal-logo) { box-sizing: border-box; padding: 8px; border-radius: 8px; background: #fff; }
.experience-modal-freelance { width: 64px; height: 64px; }
.job-description-summary, .job-description-details li { text-align: justify; text-align-last: left; }
.job-description-details { font-size: 16px; line-height: 1.7; color: var(--profile-500); }
.job-description-details h3 { color: var(--profile-ink); }
.job-description-details ol { display: grid; gap: 12px; margin-top: 16px; padding-left: 24px; list-style: decimal; }
.job-description-details li { padding-left: 4px; }
.freelance-responsibility-group { margin-top: 20px; }
.freelance-responsibility-group h4 { color: var(--profile-ink); font-size: 16px; font-weight: 600; line-height: 1.5; }
.freelance-responsibility-group ul { display: grid; gap: 12px; margin-top: 10px; padding-left: 24px; list-style: disc; }
.freelance-responsibility-group p { text-align: justify; text-align-last: left; }
.recognition-certificate-image { display: block; width: 100%; height: auto; margin-top: 24px; border-radius: 12px; }
.project-modal { max-width: 1120px; padding: 28px; }
.project-modal .close-btn { z-index: 1; }
.project-modal-layout { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr); gap: 40px; align-items: center; }
.project-gallery { min-width: 0; }
.project-gallery-frame { display: grid; place-items: center; min-height: 300px; aspect-ratio: 1.45; overflow: hidden; border-radius: 18px; background: var(--surface-elevated); }
.project-gallery-frame img { display: block; width: 100%; height: 100%; object-fit: contain; }
.project-gallery-frame.project-gallery-logo-frame { background: #fff; padding: 32px; }
.project-gallery-frame .project-gallery-logo { width: min(100%, 380px); max-height: 220px; object-fit: contain; }
.project-image-fallback { padding: 24px; color: var(--profile-500); text-align: center; }
.project-pagination { display: flex; justify-content: center; flex-wrap: wrap; margin-top: 8px; }
.project-pagination button { display: grid; place-items: center; width: 44px; height: 44px; border: 0; background: transparent; cursor: pointer; }
.project-pagination button span { width: 9px; height: 9px; border-radius: 50%; background: var(--profile-500); opacity: .45; }
.project-pagination button[aria-pressed="true"] span { background: var(--profile-ink); opacity: 1; }
.project-modal-copy { padding: 32px 24px 32px 0; min-width: 0; }
.project-modal-copy h2 { margin-top: 12px; }
.project-year, .project-description { color: var(--profile-500); line-height: 1.7; }
.project-description { margin: 20px 0; }
.project-tech-logos { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; padding: 0; margin: 22px 0; list-style: none; }
.project-tech-logos li { position: relative; }
.project-tech-icon { display: grid; place-items: center; width: 42px; height: 42px; border-radius: 9px; background: #fff; color: #344854; }
.project-tech-logos img { display: block; width: 28px; height: 28px; object-fit: contain; }
.project-tech-tooltip { position: absolute; z-index: 2; left: 50%; bottom: calc(100% + 8px); width: max-content; max-width: 150px; padding: 6px 9px; border-radius: 6px; background: var(--profile-ink); color: var(--profile-bg); font-size: 12px; line-height: 1.35; text-align: center; opacity: 0; visibility: hidden; pointer-events: none; transform: translateX(-50%); }
.project-tech-logos li:hover .project-tech-tooltip, .project-tech-icon:focus-visible .project-tech-tooltip { opacity: 1; visibility: visible; }
.project-tech-logos li:last-child .project-tech-tooltip { right: 0; left: auto; transform: none; }
.project-modal-copy .modal-actions { margin-top: 28px; }
@media (max-width: 760px) {
    .project-modal { padding: 20px; }
    .project-modal-layout { grid-template-columns: 1fr; gap: 8px; }
    .project-gallery { padding-top: 40px; }
    .project-gallery-frame { min-height: 180px; }
    .project-modal-copy { padding: 16px 0 8px; }
}
@media (max-width: 760px) {
    .project-index-entry { grid-template-columns: minmax(0, 1fr); gap: 14px; padding: 26px 0; }
}
.modal-design-image { display: block; width: 100%; max-height: 60vh; object-fit: contain; margin: 24px 0; background: var(--surface-elevated); border-radius: 14px; }
.modal-design-document { height: 60vh; border: 0; }
.media-preview-fallback { display: grid; place-items: center; width: 100%; height: 100%; color: var(--profile-500); }
.modal-card > .primary-btn { margin-top: 24px; }
.dialog-enter-active, .dialog-leave-active { transition: opacity var(--motion-fast) var(--ease-out-cubic); }
.dialog-enter-active .modal-card { transition: transform var(--motion-base) var(--ease-out-cubic); }
.dialog-enter-from, .dialog-leave-to { opacity: 0; }
.dialog-enter-from .modal-card { transform: translateY(12px); }
.folio-footer { background: var(--surface-elevated); padding: 48px; }
.folio-footer > div { max-width: 1144px; margin: 0 auto; display: flex; align-items: center; flex-wrap: wrap; gap: 24px; }
.folio-footer > div > a { font-size: 20px; letter-spacing: -.5px; }
.folio-footer nav { display: flex; gap: 22px; flex-wrap: wrap; margin-left: auto; }
.folio-footer nav a { color: var(--profile-500); font-size: 14px; padding: 8px 0; }
.folio-footer p { width: 100%; color: var(--profile-500); font-size: 12px; margin-top: 24px; }
:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 5px; }
@media (min-width: 1500px) { .hero-section { padding-top: 104px; padding-bottom: 112px; } }
@media (max-width: 1100px) {
    .header-content { gap: 20px; padding-inline: 32px; }
    .header-nav { gap: 18px; }
    .folio-main { padding-inline: 36px; }
    .hero-section { gap: 6%; padding: 72px 0; }
    .hero-bio { font-size: 16px; }
    .featured-project-copy { padding: 32px; }
    .contact-section { gap: 44px; }
}
@media (max-width: 850px) {
    .header-content { min-height: 76px; flex-wrap: wrap; gap: 12px; padding: 12px 24px; }
    .menu-toggle { display: inline-flex; font-size: 14px; }
    .theme-controls { order: 0; margin-left: auto; }
    .header-nav { display: none; order: 3; flex-basis: 100%; padding: 12px 0 8px; flex-wrap: wrap; gap: 8px 28px; }
    .header-nav.is-open { display: flex; }
    .header-nav a { min-height: 44px; }
    .hero-section { padding: 56px 0 64px; grid-template-columns: 1.12fr 1fr; gap: 36px; }
    h1 { font-size: 54px; }
    .hero-portrait > img { aspect-ratio: .8; border-radius: 24px; }
    .portrait-video { border-radius: 24px; }
    .hero-actions { gap: 10px; }
    .primary-btn, .secondary-btn { font-size: 14px; padding: 12px 20px; }
    .section-container { padding: 60px 0; }
    .section-header { margin-bottom: 32px; }
    h2 { font-size: 38px; }
    .featured-project { grid-template-columns: 1fr; }
    .featured-project-image img { min-height: 0; max-height: 450px; height: auto; aspect-ratio: 1.6; }
    .featured-project-copy { padding: 36px; }
    .services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
    .service-item { grid-column: auto; }
    .service-item h3 { font-size: 21px; }
    .service-item p { font-size: 15px; }
    .experience-grid { gap: 36px; }
    .design-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .design-video-card:last-child:nth-child(odd) { grid-column: 1 / -1; width: calc((100% - 28px) / 2); justify-self: center; }
    .tools-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .contact-section { grid-template-columns: 1fr; gap: 44px; }
    .contact-intro { max-width: 600px; padding-top: 0; }
    .folio-footer { padding: 36px; }
    .folio-footer nav { margin-left: 0; }
}
@media (max-width: 600px) {
    .header-content { padding-inline: 20px; gap: 8px; }
    .menu-toggle { gap: 4px; }
    .menu-toggle span { font-size: 0; }
    .folio-main { padding-inline: 24px; }
    .hero-section { grid-template-columns: 1fr; gap: 36px; padding: 48px 0 40px; }
    .hero-portrait { order: -1; }
    h1 { font-size: clamp(40px, 11.5vw, 48px); margin-bottom: 22px; }
    .hero-role { margin-bottom: 20px; }
    .hero-bio { font-size: 16px; }
    .hero-actions { margin-top: 26px; }
    .hero-external-links { margin-top: 24px; gap: 22px; }
    .hero-portrait > img { aspect-ratio: 1.1; object-position: center 25%; border-radius: 22px; }
    .portrait-video { border-radius: 22px; }
    .section-container { padding: 48px 0; scroll-margin-top: 96px; }
    h2 { font-size: 34px; }
    .section-header > p { margin-top: 16px; font-size: 16px; }
    .filter-tabs { margin-bottom: 24px; gap: 8px; }
    .filter-tabs button { padding: 10px 15px; font-size: 13px; }
    .featured-project { border-radius: 22px; margin-bottom: 36px; }
    .featured-project-image img { aspect-ratio: 1.25; }
    .featured-project-copy { padding: 28px 24px; }
    .featured-project-copy h3 { font-size: 34px; }
    .project-actions { gap: 16px; }
    .projects-grid, .services-grid, .works-list, .experience-grid { grid-template-columns: 1fr; }
    .projects-grid { gap: 36px; }
    .project-copy { padding-right: 0; }
    .project-image { margin-bottom: 20px; }
    .services-grid { gap: 28px; }
    .service-item > svg { margin-bottom: 20px; }
    .service-item h3 { font-size: 24px; max-width: 100%; }
    .service-item p { font-size: 16px; }
    .credential-card { gap: 8px; padding: 12px 6px; }
    .credential-card > img { width: 54px; height: 54px; }
    .credential-copy strong { font-size: 12px; }
    .archive-actions { gap: 12px; }
    .publication-controls { display: grid; gap: 20px; }
    .publication-controls label { max-width: none; }
    .works-list { gap: 16px; }
    .work-item h3 { font-size: 24px; }
    .experience-grid { gap: 40px; }
    .column-heading { padding-bottom: 16px; }
    .employer-group, .education-entry { padding-block: 24px; }
    .employer-group--progression .career-role { margin-left: 0; padding-left: 18px; }
    .design-grid { grid-template-columns: 1fr; gap: 28px 16px; }
    .design-video-card:last-child:nth-child(odd) { grid-column: auto; width: 100%; }
    .design-image { border-radius: 14px; margin-bottom: 14px; }
    .design-image img { aspect-ratio: .9; }
    .design-card h3 { font-size: 18px; }
    .design-card .text-link { font-size: 13px; }
    .tools-grid { gap: 28px 20px; }
    .tool-category h3 { font-size: 18px; }
    .tool-category li { font-size: 13px; gap: 8px; }
    .tool-category img { width: 20px; height: 20px; }
    .contact-intro > p { font-size: 16px; }
    .guestbook-card { padding: 24px 20px; border-radius: 20px; }
    .form-row { grid-template-columns: 1fr; gap: 20px; }
    .modal-overlay { padding: 20px 12px; }
    .modal-card { padding: 64px 24px 32px; border-radius: 20px; }
    .modal-card.project-modal { padding: 20px; }
    .modal-card.experience-modal { max-height: calc(100dvh - 40px); }
    .modal-card h2 { font-size: 29px; margin-right: 0; }
    .folio-footer { padding: 36px 24px; }
    .folio-footer > div { gap: 20px; }
    .folio-footer nav { gap: 8px 20px; }
    .folio-footer p { margin-top: 12px; }
}
@media (max-width: 700px) {
    .hero-section { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { transition: none !important; animation: none !important; scroll-behavior: auto !important; }
    .project-card:hover .project-image img, .design-card:hover .design-image img { transform: none; }
}
</style>
