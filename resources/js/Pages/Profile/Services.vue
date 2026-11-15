<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, ArrowUpRight, Check, Clock3, Mail, MapPin, Moon, Sun, Wrench } from '@lucide/vue';
import SeoHead from '../../Components/SeoHead.vue';
import { gmailComposeUrl } from '../../Support/gmail.js';
import { getIconifyLogoUrl } from '../../Support/iconify.js';
import { usePublicTheme } from './publicPage.js';
import './publicPage.css';

const props = defineProps({
    seo: { type: Object, required: true },
    profile: { type: Object, required: true },
    services: { type: Array, default: () => [] },
});
const { theme, isDark } = usePublicTheme();
const failedToolIcons = ref(new Set());
function markToolIconFailed(tool) {
    failedToolIcons.value = new Set([...failedToolIcons.value, tool]);
}

function inquiryUrl(serviceTitle = 'a new project') {
    const email = props.profile.email ?? 'inquiries@brittmontalvo.dev';
    const subject = `Service inquiry: ${serviceTitle}`;
    const body = ['Hello Britt,', '', `I am interested in ${serviceTitle}.`, '', 'Project goal:', 'Preferred completion date:', 'Helpful links or context:', '', 'Thank you.'].join('\n');
    return gmailComposeUrl(email, { subject, body });
}
</script>

<template>
    <SeoHead :seo="seo" />
    <div class="public-page services-page">
        <a class="skip-link" href="#main-content">Skip to content</a>
        <header class="archive-topbar">
            <Link class="archive-brand" href="/" :aria-label="`${profile.display_name} portfolio`">
                <span class="archive-brand-mark"><img :src="profile.avatar_url || '/images/profile-avatar.jpg'" alt=""></span>
            </Link>
            <nav aria-label="Portfolio navigation">
                <Link href="/">Portfolio</Link>
                <Link href="/services" class="active" aria-current="page">Services</Link>
                <Link href="/badges">Badges</Link>
                <Link href="/certifications">Certificates</Link>
            </nav>
            <div class="theme-switch" role="group" aria-label="Color theme">
                <button type="button" aria-label="Light theme" :aria-pressed="!isDark" :class="{ active: !isDark }" @click="theme = 'light'"><Sun :size="18" aria-hidden="true" /></button>
                <button type="button" aria-label="Dark theme" :aria-pressed="isDark" :class="{ active: isDark }" @click="theme = 'dark'"><Moon :size="18" aria-hidden="true" /></button>
            </div>
        </header>

        <main id="main-content" class="archive-main">
            <section class="services-hero page-intro">
                <div class="archive-intro">
                    <h1>Practical solutions.<br>Built around your work.</h1>
                    <p>Web platforms, cybersecurity assessments, data analysis, and digital workflows for teams and institutions.</p>
                    <div class="hero-actions">
                        <a :href="inquiryUrl()" target="_blank" rel="noopener noreferrer" class="primary-btn">Discuss a project<ArrowUpRight :size="18" aria-hidden="true" /></a>
                        <Link href="/#work" class="text-link">Explore my work<ArrowUpRight :size="17" aria-hidden="true" /></Link>
                    </div>
                </div>
                <aside class="availability-note" aria-label="Availability and location">
                    <h2>Let's work together</h2>
                    <p>{{ profile.availability || 'Get in touch to discuss your project and availability.' }}</p>
                    <span v-if="profile.location"><MapPin :size="17" aria-hidden="true" />{{ profile.location }}</span>
                    <a :href="inquiryUrl()" target="_blank" rel="noopener noreferrer"><Mail :size="17" aria-hidden="true" />Start a conversation</a>
                </aside>
            </section>

            <section class="services-catalog" aria-labelledby="services-heading">
                <div class="catalog-heading"><h2 id="services-heading">How I can help</h2><p>Find a starting point. We can shape the scope together.</p></div>
                <div class="services-list">
                    <article v-for="service in services" :id="service.key" :key="service.key" class="service-section">
                        <div class="service-description">
                            <h3>{{ service.title }}</h3>
                            <p v-if="service.tagline" class="service-tagline">{{ service.tagline }}</p>
                            <p>{{ service.summary }}</p>
                            <p v-if="service.best_for" class="service-audience"><strong>Ideal for:</strong> {{ service.best_for }}</p>
                            <p v-if="service.expertise" class="service-audience"><strong>{{ service.expertise_label || 'Technologies' }}:</strong> {{ service.expertise }}</p>
                            <div v-if="service.tools?.length" class="tools-row" aria-label="Tools and technologies">
                                <span v-for="tool in service.tools" :key="tool" class="tool-label">
                                    <img v-if="getIconifyLogoUrl(tool) && !failedToolIcons.has(tool)" :src="getIconifyLogoUrl(tool)" alt="" width="18" height="18" loading="lazy" @error="markToolIconFailed(tool)">
                                    <Wrench v-else :size="18" aria-hidden="true" />
                                    {{ tool }}
                                </span>
                            </div>
                        </div>
                        <div class="service-deliverables">
                            <h4>What you can expect</h4>
                            <ul><li v-for="item in service.deliverables" :key="item"><Check :size="18" aria-hidden="true" /><span>{{ item }}</span></li></ul>
                            <p v-if="service.timeline" class="service-timeline"><Clock3 :size="17" aria-hidden="true" />{{ service.timeline }}</p>
                            <div class="service-price-row">
                                <p v-if="service.pricing" class="service-price">{{ service.pricing }}</p>
                                <span v-if="service.proof_url" class="service-proof-group"><span v-if="service.pricing" class="service-price-separator" aria-hidden="true">|</span><Link :href="service.proof_url" class="proof-link">{{ service.proof_label }}<ArrowUpRight :size="16" aria-hidden="true" /></Link></span>
                            </div>
                            <p v-if="service.pricing_note" class="service-price-note">{{ service.pricing_note }}</p>
                            <div class="service-actions">
                                <a :href="inquiryUrl(service.title)" target="_blank" rel="noopener noreferrer" class="primary-btn">Ask about this service<ArrowUpRight :size="17" aria-hidden="true" /></a>
                            </div>
                        </div>
                    </article>
                    <p v-if="!services.length" class="services-empty">Have a project in mind? Get in touch to discuss how I can help.</p>
                </div>
            </section>

            <section class="services-contact">
                <h2>Something else in mind?</h2>
                <p>Let's find the right solution for your project. Need a service that isn't listed? Whether it's technical assistance, research collaboration, troubleshooting, system improvements, or ongoing IT support, let's discuss your requirements. Custom pricing based on project scope.</p>
                <p>All prices are indicative hourly rates in USD, based on competitive freelance pricing for the Philippine market. Final rates depend on project complexity, scope, timeline, and deliverables. Fixed-price projects, milestone-based payments, and monthly support arrangements are also available. Hosting, subscriptions, equipment, travel, and third-party costs are quoted separately when applicable.</p>
                <a :href="inquiryUrl()" target="_blank" rel="noopener noreferrer" class="primary-btn">Get in touch<ArrowUpRight :size="18" aria-hidden="true" /></a>
            </section>
        </main>
        <footer class="archive-footer"><span>&copy; {{ new Date().getFullYear() }} {{ profile.display_name }}</span><Link href="/"><ArrowLeft :size="17" aria-hidden="true" />Return to portfolio</Link></footer>
    </div>
</template>

<style scoped>
.services-hero { display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(15rem, 0.8fr); gap: clamp(2.5rem, 7vw, 7rem); align-items: end; }
.services-hero h1 { max-width: 16ch; }
.hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem 1.5rem; margin-top: 2rem; }
.primary-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.65rem; min-height: 50px; padding: 0.85rem 1.5rem; border-radius: 999px; background: var(--accent-primary); color: var(--on-accent, #fff); font-size: 0.95rem; font-weight: 600; transition: background var(--motion-fast, 160ms) ease; }
.public-page a.primary-btn { color: var(--on-accent, #fff); }
.primary-btn:hover { background: var(--accent-primary-hover); }
.availability-note { padding: 2rem; border-radius: 20px; background: var(--surface-muted); }
.availability-note h2 { margin: 0; font-size: 1.45rem; font-weight: 400; letter-spacing: -0.02em; }
.availability-note p { margin: 1rem 0 1.5rem; color: var(--profile-500); line-height: 1.7; }
.availability-note > span, .availability-note > a { display: flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; line-height: 1.5; }
.availability-note > a { min-height: 44px; margin-top: 0.5rem; color: var(--accent-primary); }
.services-catalog { margin-top: clamp(5rem, 10vw, 9rem); }
.catalog-heading { margin-bottom: 2.5rem; }
.catalog-heading h2, .services-contact h2 { margin: 0; font-size: clamp(2rem, 3.5vw, 3rem); font-weight: 400; letter-spacing: -0.03em; line-height: 1.15; }
.catalog-heading p { margin: 1rem 0 0; color: var(--profile-500); line-height: 1.7; }
.service-section { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: clamp(2rem, 6vw, 6rem); padding: 3rem 0; border-top: 1px solid var(--profile-200); scroll-margin-top: 7rem; }
.service-description h3 { margin: 0 0 1rem; font-size: clamp(1.65rem, 2.8vw, 2.1rem); font-weight: 400; line-height: 1.2; letter-spacing: -0.025em; }
.service-description p { margin: 0 0 1.25rem; color: var(--profile-500); font-size: 1rem; line-height: 1.7; }
.service-description .service-tagline { color: var(--profile-ink); font-weight: 600; }
.service-description .service-audience { font-size: 0.9rem; }
.service-audience strong { color: var(--profile-ink); font-weight: 600; }
.tools-row { display: flex; flex-wrap: wrap; gap: 0.6rem 1rem; }
.tool-label { display: inline-flex; align-items: center; gap: 0.4rem; color: var(--profile-500); font-size: 0.85rem; }
.tool-label img { object-fit: contain; }
.service-deliverables h4 { margin: 0 0 1rem; font-size: 1rem; font-weight: 600; }
.service-deliverables ul { display: grid; gap: 0.9rem; padding: 0; margin: 0; list-style: none; }
.service-deliverables li { display: flex; align-items: flex-start; gap: 0.7rem; color: var(--profile-500); line-height: 1.6; }
.service-deliverables li svg { flex-shrink: 0; margin-top: 0.2rem; color: var(--profile-ink); }
.service-timeline { display: flex; align-items: flex-start; gap: 0.55rem; margin: 1.5rem 0 0; color: var(--profile-500); font-size: 0.9rem; line-height: 1.6; }
.service-timeline svg { flex-shrink: 0; margin-top: 0.15rem; }
.service-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem 1.5rem; margin-top: 1.25rem; }
.service-price-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem 0.75rem; margin-top: 0.75rem; }
.service-price { margin: 0; color: var(--profile-ink); font-weight: 600; }
.service-proof-group { display: inline-flex; align-items: center; gap: 0.75rem; min-width: 0; }
.service-price-separator { color: var(--profile-500); }
.service-price-note { margin: 0.3rem 0 0; color: var(--profile-500); font-size: 0.9rem; }
.proof-link { display: inline-flex; align-items: center; gap: 0.4rem; min-height: 44px; font-size: 0.9rem; }
.proof-link:hover { text-decoration: underline; text-underline-offset: 4px; }
.services-contact { padding: clamp(2rem, 5vw, 4rem); margin-top: 3rem; border-radius: 24px; background: var(--surface-muted); }
.services-contact p { max-width: 39rem; margin: 1.25rem 0 2rem; color: var(--profile-500); line-height: 1.7; }
@media (max-width: 760px) { .services-hero, .service-section { grid-template-columns: 1fr; gap: 2rem; } .availability-note { max-width: 36rem; } .service-section { padding: 2.5rem 0; } }
</style>
