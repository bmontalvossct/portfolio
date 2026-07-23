<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, Check, Clock3, Mail, MapPin, Moon, Send, Sun } from '@lucide/vue';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import SeoHead from '../../Components/SeoHead.vue';
import { gmailComposeUrl } from '../../Support/gmail.js';

const props = defineProps({
    seo: { type: Object, required: true },
    profile: { type: Object, required: true },
    services: { type: Array, default: () => [] },
});

const isDark = ref(false);
const theme = ref('system');
let systemThemeQuery = null;

function initials(value) {
    return String(value ?? 'BM')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
}

function applyTheme() {
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = theme.value === 'dark' || (theme.value === 'system' && systemDark);
    document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
}

function setTheme(value) {
    theme.value = value;
}

function inquiryUrl(serviceTitle = 'a new project') {
    const email = props.profile.email ?? 'inquiries@brittmontalvo.dev';
    const subject = `Service inquiry: ${serviceTitle}`;
    const body = [
        'Hello Britt,',
        '',
        `I am interested in ${serviceTitle}.`,
        '',
        'Project goal:',
        'Preferred completion date:',
        'Helpful links or context:',
        '',
        'Thank you.',
    ].join('\n');

    return gmailComposeUrl(email, { subject, body });
}

onMounted(() => {
    theme.value = window.localStorage.getItem('profile-theme') ?? 'system';
    systemThemeQuery = window.matchMedia('(prefers-color-scheme: dark)');
    systemThemeQuery.addEventListener('change', applyTheme);
    applyTheme();
});

onBeforeUnmount(() => {
    systemThemeQuery?.removeEventListener('change', applyTheme);
});

watch(theme, (value) => {
    window.localStorage.setItem('profile-theme', value);
    applyTheme();
});
</script>

<template>
    <SeoHead :seo="seo" />

    <a class="skip-link" href="#main-content">Skip to services</a>

    <div class="services-shell">
        <div class="signal-wash" aria-hidden="true"></div>

        <header class="services-topbar">
            <Link class="services-brand" href="/" :aria-label="`${profile.display_name} portfolio`">
                <span class="brand-mark">
                    <span>{{ initials(profile.display_name) }}</span>
                    <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="" @error="$event.currentTarget.remove()">
                </span>
                <strong>{{ profile.display_name }}</strong>
            </Link>

            <nav aria-label="Public portfolio pages">
                <Link href="/"><ArrowLeft :size="14" />Portfolio</Link>
                <Link href="/services" class="active" aria-current="page">Services</Link>
                <Link href="/designs">Designs</Link>
                <Link href="/badges">Badges</Link>
                <Link href="/certifications">Certificates</Link>
            </nav>

            <div class="theme-switch" role="group" aria-label="Color theme">
                <button type="button" title="Light theme" aria-label="Light theme" :aria-pressed="!isDark" :class="{ active: !isDark }" @click="setTheme('light')"><Sun :size="15" /></button>
                <button type="button" title="Dark theme" aria-label="Dark theme" :aria-pressed="isDark" :class="{ active: isDark }" @click="setTheme('dark')"><Moon :size="15" /></button>
            </div>
        </header>

        <main id="main-content">
            <section class="services-hero">
                <div class="hero-copy">
                    <span class="folio-label">Services / practical digital work</span>
                    <h1>From complex information to working systems.</h1>
                    <p>I combine systems analysis, programming, research, infrastructure, and visual communication to help organizations move from a difficult problem to a usable result.</p>

                    <div class="hero-actions">
                        <a class="primary-action" :href="inquiryUrl()" target="_blank" rel="noopener noreferrer"><Mail :size="16" />Request a quote</a>
                        <a class="text-action" href="/#work">Review selected work<ArrowUpRight :size="15" /></a>
                    </div>
                </div>

                <aside class="availability-card" aria-label="Engagement availability">
                    <div>
                        <span class="status-line"><span></span>Open for selected engagements</span>
                        <p>{{ profile.availability || 'Available for scoped systems, research, infrastructure, and publication work.' }}</p>
                    </div>
                    <div class="availability-meta">
                        <span v-if="profile.location"><MapPin :size="14" />{{ profile.location }}</span>
                        <span><Clock3 :size="14" />Remote-first / on-site by scope</span>
                    </div>
                </aside>
            </section>

            <section class="ledger-section" aria-label="Available services">
                <div class="ledger-heading">
                    <span>Engagement ledger</span>
                    <p>Services grounded in demonstrated practice, each shaped to the scope and outcome you need.</p>
                </div>

                <ol class="service-ledger">
                    <li v-for="(service, index) in services" :key="service.key" :style="{ '--service-delay': `${index * 70}ms` }">
                        <article :id="service.key" class="service-sheet">
                            <div class="service-index" aria-hidden="true">{{ service.number }}</div>

                            <div class="service-overview">
                                <span class="service-time">Typical timeline / {{ service.timeline }}</span>
                                <h2>{{ service.title }}</h2>
                                <p class="service-summary">{{ service.summary }}</p>
                                <p class="best-for"><strong>Best for</strong>{{ service.best_for }}</p>
                                <div class="tool-row" aria-label="Related tools and skills">
                                    <span v-for="tool in service.tools" :key="tool">{{ tool }}</span>
                                </div>
                            </div>

                            <div class="service-scope">
                                <h3>What the engagement can include</h3>
                                <ul>
                                    <li v-for="deliverable in service.deliverables" :key="deliverable"><Check :size="15" />{{ deliverable }}</li>
                                </ul>
                                <Link class="proof-link" :href="service.proof_url">{{ service.proof_label }}<ArrowUpRight :size="14" /></Link>
                            </div>

                            <div class="service-action">
                                <span>Have a project in mind?</span>
                                <a
                                    :href="inquiryUrl(service.title)"
                                    :aria-label="`Request a quote for ${service.title}`"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >Request a quote<Send :size="14" /></a>
                            </div>
                        </article>
                    </li>
                </ol>
            </section>

            <section class="process-section" aria-labelledby="process-title">
                <div>
                    <span class="folio-label">A simple working rhythm</span>
                    <h2 id="process-title">Clear scope before execution.</h2>
                </div>
                <ol>
                    <li><span>01</span><strong>Discover</strong><p>Goals, users, constraints, source material, and success measures.</p></li>
                    <li><span>02</span><strong>Define</strong><p>Deliverables, milestones, responsibilities, timeline, and final quote.</p></li>
                    <li><span>03</span><strong>Build</strong><p>Visible progress, focused reviews, testing, and documented decisions.</p></li>
                    <li><span>04</span><strong>Handover</strong><p>Deployment or delivery, documentation, training, and next-step options.</p></li>
                </ol>
            </section>


            <section class="contact-panel">
                <div>
                    <span class="folio-label">Have a defined need—or just a difficult problem?</span>
                    <h2>Let’s shape the right engagement.</h2>
                </div>
                <div class="contact-actions">
                    <a class="primary-action" :href="inquiryUrl()" target="_blank" rel="noopener noreferrer"><Mail :size="16" />Request a quote</a>
                    <a
                        class="email-link"
                        :href="gmailComposeUrl(profile.email, { subject: 'Portfolio inquiry' })"
                        target="_blank"
                        rel="noopener noreferrer"
                    >{{ profile.email }}<ArrowUpRight :size="14" /></a>
                </div>
            </section>
        </main>

        <footer class="services-footer">
            <span>{{ profile.display_name }}</span>
            <Link href="/"><ArrowLeft :size="14" />Return to portfolio</Link>
        </footer>
    </div>
</template>

<style scoped>
.services-shell { --service-accent: #c43f22; position: relative; min-height: 100vh; isolation: isolate; overflow: clip; background: var(--profile-bg); color: var(--profile-ink); }
.signal-wash { position: fixed; inset: 0; z-index: -1; pointer-events: none; background: radial-gradient(circle at 86% 12%, color-mix(in srgb, var(--service-accent) 12%, transparent) 0, transparent 24rem), linear-gradient(90deg, transparent 0 49.85%, color-mix(in srgb, var(--profile-200) 40%, transparent) 50%, transparent 50.15%); background-size: auto, 7rem 100%; }
:global(:root[data-theme='dark']) .services-shell { --service-accent: #ff7857; }
:global(:root[data-theme='dark']) .signal-wash { background: radial-gradient(circle at 86% 12%, rgb(28 105 212 / 25%) 0, transparent 28rem), radial-gradient(circle at 15% 70%, rgb(226 39 24 / 12%) 0, transparent 24rem), linear-gradient(90deg, transparent 0 49.85%, rgb(255 255 255 / 3%) 50%, transparent 50.15%); background-size: auto, auto, 7rem 100%; }
.skip-link { position: fixed; top: 0.6rem; left: 0.6rem; z-index: 100; transform: translateY(-150%); border: 1px solid var(--profile-ink); background: var(--profile-bg); padding: 0.65rem 0.8rem; font-family: var(--font-mono); font-size: 0.8rem; text-transform: uppercase; }
.skip-link:focus { transform: translateY(0); }
.services-topbar { position: sticky; top: 0; z-index: 30; display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: center; gap: 1.2rem; border-bottom: 1px solid var(--profile-200); background: color-mix(in srgb, var(--profile-bg) 94%, transparent); padding: 0.75rem clamp(1rem, 3vw, 2rem); backdrop-filter: blur(16px); }
.services-brand { display: flex; min-width: 0; align-items: center; gap: 0.65rem; color: var(--profile-ink); font-family: var(--font-mono); font-size: 0.8rem; text-decoration: none; text-transform: uppercase; }
.services-brand strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.brand-mark { position: relative; display: inline-grid; width: 2rem; height: 2rem; flex: 0 0 auto; place-items: center; overflow: hidden; border-radius: 50%; background: var(--profile-ink); color: var(--profile-bg); font-size: 0.75rem; }
.brand-mark img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center top; }
.services-topbar nav { display: flex; align-items: center; gap: 0.9rem; font-family: var(--font-mono); font-size: 0.8rem; text-transform: uppercase; }
.services-topbar nav a { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 0.3rem; color: var(--profile-500); text-decoration: none; }
.services-topbar nav a:hover, .services-topbar nav a.active { color: var(--profile-ink); }
.services-topbar nav a.active { border-bottom: 1px solid var(--service-accent); }
.theme-switch { display: grid; grid-template-columns: repeat(2, 2rem); overflow: hidden; border: 1px solid var(--profile-200); border-radius: 4px; }
.theme-switch button { display: inline-grid; width: 2rem; height: 2rem; place-items: center; border: 0; border-right: 1px solid var(--profile-200); background: var(--profile-bg); color: var(--profile-500); cursor: pointer; }
.theme-switch button:last-child { border-right: 0; }
.theme-switch button.active { background: var(--profile-ink); color: var(--profile-bg); }
.services-shell main, .services-footer { position: relative; z-index: 1; width: min(100% - 2rem, 78rem); margin: 0 auto; }
.services-hero { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(17rem, 0.55fr); gap: clamp(2rem, 7vw, 7rem); align-items: end; padding: clamp(3.5rem, 8vw, 7.5rem) 0 clamp(3rem, 7vw, 6rem); }
.folio-label, .ledger-heading span, .service-time, .best-for strong, .tool-row, .service-scope h3, .service-action, .process-section li span, .services-footer { font-family: var(--font-mono); text-transform: uppercase; }
.folio-label { color: var(--profile-500); font-size: 0.78rem; letter-spacing: 0.03em; }
.hero-copy h1 { max-width: 12ch; margin: 1rem 0 0; font-family: var(--font-mono); font-size: clamp(3.2rem, 8vw, 7rem); font-weight: 650; letter-spacing: -0.025em; line-height: 0.9; }
.hero-copy > p { max-width: 49rem; margin: 1.7rem 0 0; color: var(--profile-700); font-size: clamp(1.05rem, 2vw, 1.28rem); line-height: 1.7; }
.hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-top: 2rem; }
.primary-action, .service-action a { display: inline-flex; min-height: 2.8rem; align-items: center; justify-content: center; gap: 0.45rem; border: 1px solid var(--profile-ink); background: var(--profile-ink); padding: 0.7rem 0.9rem; color: var(--profile-bg); font-family: var(--font-mono); font-size: 0.8rem; text-decoration: none; text-transform: uppercase; transition: transform 160ms ease, box-shadow 160ms ease; }
.primary-action:hover, .service-action a:hover { box-shadow: 4px 4px 0 var(--service-accent); transform: translate(-2px, -2px); }
.text-action, .proof-link, .email-link, .services-footer a { display: inline-flex; align-items: center; gap: 0.35rem; font-family: var(--font-mono); font-size: 0.8rem; text-transform: uppercase; }
.availability-card { position: relative; display: grid; gap: 1.4rem; border: 1px solid var(--profile-ink); background: color-mix(in srgb, var(--profile-bg) 88%, transparent); padding: 1.25rem; box-shadow: 10px 10px 0 color-mix(in srgb, var(--service-accent) 72%, var(--profile-ink)); }
.availability-card > div { position: relative; z-index: 1; }
.status-line { display: flex; align-items: center; gap: 0.5rem; font-family: var(--font-mono); font-size: 0.78rem; text-transform: uppercase; }
.status-line > span { width: 0.55rem; height: 0.55rem; border-radius: 50%; background: var(--service-accent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--service-accent) 15%, transparent); }
.availability-card p { margin: 0.8rem 0 0; color: var(--profile-700); line-height: 1.6; }
.availability-meta { display: grid; gap: 0.45rem; border-top: 1px solid var(--profile-200); padding-top: 1rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.75rem; text-transform: uppercase; }
.availability-meta span { display: flex; align-items: center; gap: 0.35rem; }
.ledger-section { border-top: 1px solid var(--profile-ink); }
.ledger-heading { display: grid; grid-template-columns: minmax(10rem, 0.5fr) minmax(0, 1fr); gap: 2rem; padding: 1.1rem 0; }
.ledger-heading span { color: var(--service-accent); font-size: 0.78rem; }
.ledger-heading p { max-width: 42rem; margin: 0; color: var(--profile-700); line-height: 1.6; }
.service-ledger { padding: 0; margin: 0; list-style: none; }
.service-ledger > li { border-top: 1px solid var(--profile-200); animation: service-enter 520ms cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: var(--service-delay); }
.service-ledger > li:last-child { border-bottom: 1px solid var(--profile-ink); }
.service-ledger > li:nth-child(even) { background: color-mix(in srgb, var(--profile-50) 68%, transparent); }
.service-sheet { display: grid; grid-template-columns: 7.25rem minmax(0, 1.2fr) minmax(16rem, 0.85fr) minmax(13rem, 0.55fr); min-width: 0; scroll-margin-top: 8rem; }
.service-sheet > div { min-width: 0; padding: clamp(1.2rem, 2.5vw, 2rem); }
.service-sheet > div + div { border-left: 1px solid var(--profile-200); }
.service-sheet > .service-index { display: flex; justify-content: center; overflow: hidden; padding-inline: 0.75rem; }
.service-index { color: var(--service-accent); font-family: var(--font-mono); font-size: clamp(2.4rem, 5vw, 4.8rem); font-variant-numeric: tabular-nums; font-weight: 700; line-height: 0.85; }
.service-time { color: var(--profile-500); font-size: 0.75rem; letter-spacing: 0.03em; }
.service-overview h2 { max-width: 17ch; margin: 0.7rem 0 0; font-size: clamp(1.7rem, 3.2vw, 3rem); line-height: 1; }
.service-summary { margin: 1rem 0 0; color: var(--profile-700); font-size: 1rem; line-height: 1.65; }
.best-for { display: grid; gap: 0.3rem; margin: 1.1rem 0 0; color: var(--profile-700); line-height: 1.55; }
.best-for strong { color: var(--profile-500); font-size: 0.75rem; letter-spacing: 0.03em; }
.tool-row { display: flex; flex-wrap: wrap; gap: 0.35rem; margin-top: 1.15rem; }
.tool-row span { border: 1px solid var(--profile-300); border-radius: 999px; padding: 0.28rem 0.5rem; color: var(--profile-500); font-size: 0.75rem; }
.service-scope { display: flex; flex-direction: column; align-items: flex-start; }
.service-scope h3 { margin: 0; color: var(--profile-500); font-size: 0.75rem; letter-spacing: 0.03em; }
.service-scope ul { display: grid; gap: 0.75rem; padding: 0; margin: 1.1rem 0 1.4rem; list-style: none; }
.service-scope li { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.5rem; color: var(--profile-700); line-height: 1.45; }
.service-scope li svg { margin-top: 0.15rem; color: var(--service-accent); }
.proof-link { margin-top: auto; font-size: 0.75rem; }
.service-action { position: relative; display: grid; min-height: 10rem; place-items: center; }
.service-action > span { position: absolute; top: clamp(1.2rem, 2.5vw, 2rem); left: clamp(1.2rem, 2.5vw, 2rem); color: var(--profile-500); font-size: 0.75rem; letter-spacing: 0.03em; }
.service-action a { width: min(100%, 15rem); margin: 0; font-size: 0.8rem; }
.process-section { display: grid; grid-template-columns: minmax(16rem, 0.55fr) minmax(0, 1.45fr); gap: clamp(2rem, 6vw, 6rem); padding: clamp(4rem, 8vw, 7rem) 0; }
.process-section h2 { max-width: 12ch; margin: 0.7rem 0 0; font-size: clamp(2.3rem, 5vw, 4.5rem); line-height: 0.98; }
.process-section ol { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); padding: 0; margin: 0; border-top: 1px solid var(--profile-ink); list-style: none; }
.process-section li { display: grid; min-height: 12rem; align-content: start; border-right: 1px solid var(--profile-200); border-bottom: 1px solid var(--profile-200); padding: 1.2rem; }
.process-section li:nth-child(even) { border-right: 0; }
.process-section li span { color: var(--service-accent); font-size: 0.75rem; }
.process-section li strong { margin-top: 1.5rem; font-size: 1.25rem; }
.process-section li p { margin: 0.55rem 0 0; color: var(--profile-700); line-height: 1.55; }
.contact-panel { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 2rem; align-items: end; padding: clamp(4rem, 9vw, 8rem) 0; }
.contact-panel h2 { max-width: 15ch; margin: 0.7rem 0 0; font-size: clamp(2.5rem, 6vw, 5.5rem); line-height: 0.95; }
.contact-actions { display: grid; gap: 0.8rem; justify-items: end; }
.email-link { max-width: 100%; overflow-wrap: anywhere; text-transform: none; }
.services-footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; border-top: 1px solid var(--profile-200); padding: 1.2rem 0 2rem; color: var(--profile-500); font-size: 0.75rem; }
@keyframes service-enter { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
a:focus-visible, button:focus-visible { outline: 2px solid var(--service-accent); outline-offset: 3px; }

@media (max-width: 980px) {
    .service-sheet { grid-template-columns: 6rem minmax(0, 1fr) minmax(15rem, 0.7fr); }
    .service-index { font-size: 3.6rem; }
    .service-action { grid-column: 2 / -1; border-top: 1px solid var(--profile-200); border-left: 1px solid var(--profile-200); }
}

@media (max-width: 800px) {
    .services-topbar { grid-template-columns: minmax(0, 1fr) auto; }
    .services-topbar nav { grid-column: 1 / -1; grid-row: 2; overflow-x: auto; padding-top: 0.25rem; }
    .services-hero, .process-section, .contact-panel { grid-template-columns: 1fr; }
    .availability-card { width: min(100%, 30rem); }
    .process-section { gap: 2rem; }
    .contact-actions { justify-items: start; }
}

@media (max-width: 680px) {
    .services-shell main, .services-footer { width: min(100% - 1.25rem, 78rem); }
    .ledger-heading { grid-template-columns: 1fr; gap: 0.65rem; }
    .service-sheet { grid-template-columns: 1fr; }
    .service-sheet > div + div, .service-action { border-left: 0; }
    .service-sheet > div { padding: 1.1rem 0.9rem; }
    .service-sheet > .service-index { justify-content: flex-start; overflow: visible; border-bottom: 1px solid var(--profile-200); padding: 1.1rem 0.9rem 0.7rem; }
    .service-index { font-size: 2.5rem; }
    .service-action { grid-column: auto; border-top: 1px solid var(--profile-200); }
    .service-action > span { top: 1.1rem; left: 0.9rem; }
    .service-scope { border-top: 1px solid var(--profile-200); }
    .service-action a { width: min(100%, 20rem); min-height: 3rem; }
    .process-section ol { grid-template-columns: 1fr; }
    .process-section li { min-height: 0; border-right: 0; }
    .contact-panel { align-items: start; }
}

@media (max-width: 460px) {
    .services-brand strong { display: none; }
    .hero-copy h1 { font-size: clamp(3rem, 16vw, 4.4rem); }
    .hero-actions, .hero-actions a { width: 100%; }
    .hero-actions a { justify-content: center; }
    .services-footer { align-items: flex-start; flex-direction: column; }
}
</style>
