<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, Award, ListFilter, Moon, Search, Sun, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import SeoHead from '../../Components/SeoHead.vue';
import { useDetailsDialog, usePublicTheme } from './publicPage.js';
import './publicPage.css';

const props = defineProps({
    seo: { type: Object, required: true },
    archiveType: { type: String, required: true },
    title: { type: String, required: true },
    intro: { type: String, required: true },
    items: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    alternate: { type: Object, required: true },
    profile: { type: Object, required: true },
});
const activeCategory = ref('all');
const query = ref('');
const failedImages = ref(new Set());
const { theme, isDark } = usePublicTheme();
const { selectedItem, dialogRef, openDetails, closeDetails } = useDetailsDialog();
const isCertificates = computed(() => props.archiveType === 'certificates');
const filteredItems = computed(() => {
    const term = query.value.trim().toLowerCase();
    return props.items.filter((item) => {
        const matchesCategory = activeCategory.value === 'all' || item.category === activeCategory.value;
        const searchable = [item.title, item.issuer, item.description, item.verification_code, ...(item.tags ?? [])].filter(Boolean).join(' ').toLowerCase();
        return matchesCategory && (!term || searchable.includes(term));
    });
});
const groupedItems = computed(() => props.categories.map((category) => ({
    ...category,
    items: filteredItems.value.filter((item) => item.category === category.value),
})).filter((category) => category.items.length));
function resetFilters() { query.value = ''; activeCategory.value = 'all'; }
function imageFailed(item) { failedImages.value.add(item.id); }
</script>

<template>
    <SeoHead :seo="seo" />
    <div class="public-page credential-page">
        <a class="skip-link" href="#main-content">Skip to content</a>
        <header class="archive-topbar">
            <Link class="archive-brand" href="/" :aria-label="`${profile.display_name} portfolio`">
                <span class="archive-brand-mark"><img :src="profile.avatar_url || '/images/profile-avatar.jpg'" alt=""></span>
            </Link>
            <nav aria-label="Portfolio navigation">
                <Link href="/">Portfolio</Link>
                <Link href="/services">Services</Link>
                <Link href="/badges" :class="{ active: !isCertificates }" :aria-current="!isCertificates ? 'page' : undefined">Badges</Link>
                <Link href="/certifications" :class="{ active: isCertificates }" :aria-current="isCertificates ? 'page' : undefined">Certificates</Link>
            </nav>
            <div class="theme-switch" role="group" aria-label="Color theme">
                <button type="button" aria-label="Light theme" :aria-pressed="!isDark" :class="{ active: !isDark }" @click="theme = 'light'"><Sun :size="18" aria-hidden="true" /></button>
                <button type="button" aria-label="Dark theme" :aria-pressed="isDark" :class="{ active: isDark }" @click="theme = 'dark'"><Moon :size="18" aria-hidden="true" /></button>
            </div>
        </header>

        <main id="main-content" class="archive-main">
            <section class="archive-intro page-intro">
                <h1>{{ isCertificates ? 'Certificates & training' : 'Digital badges' }}</h1>
                <p v-if="isCertificates">A record of training, professional development, and continuing education. Public issuer records are linked where available; original documents remain private.</p>
                <p v-else>Learning and credentials across software, data, cybersecurity, and professional practice. Explore each badge for issuer details and available evidence.</p>
            </section>
            <section class="archive-controls" aria-label="Credential filters">
                <label class="archive-search">
                    <span class="field-label">Search {{ isCertificates ? 'certificates' : 'badges' }}</span>
                    <span class="field-control"><Search :size="19" aria-hidden="true" /><input v-model="query" type="search" placeholder="Name, issuer, or subject"></span>
                </label>
                <label class="category-filter">
                    <span class="field-label">Category</span>
                    <span class="field-control"><ListFilter :size="19" aria-hidden="true" /><select v-model="activeCategory"><option value="all">All categories</option><option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option></select></span>
                </label>
                <Link class="text-link" :href="alternate.url">{{ alternate.label }}<ArrowUpRight :size="17" aria-hidden="true" /></Link>
            </section>
            <p class="archive-count" role="status">{{ filteredItems.length }} {{ isCertificates ? 'certificates' : 'badges' }} shown</p>

            <section v-for="group in groupedItems" :key="group.value" class="credential-group" :aria-labelledby="`category-${group.value}`">
                <h2 :id="`category-${group.value}`">{{ group.label }}<span>{{ group.items.length }}</span></h2>
                <div class="credential-grid">
                    <article v-for="item in group.items" :key="item.id" class="credential-card">
                        <button class="credential-main" type="button" :aria-label="`View details: ${item.title}`" @click="openDetails(item, $event)">
                            <span class="credential-mark">
                                <img v-if="item.image_url && !failedImages.has(item.id)" :src="item.image_url" :alt="isCertificates ? (item.provider_name || item.issuer || 'Provider') : item.title" loading="lazy" decoding="async" @error="imageFailed(item)">
                                <span v-else class="credential-fallback"><Award v-if="!isCertificates" :size="38" aria-hidden="true" /><strong v-else>{{ item.provider_initials || 'Certificate' }}</strong></span>
                            </span>
                            <span class="credential-name">{{ item.title }}</span>
                            <span class="credential-issuer">{{ (isCertificates ? item.provider_name : item.issuer) || item.issuer || (isCertificates ? 'Certificate' : 'Digital badge') }}</span>
                            <span v-if="item.date" class="credential-date">{{ item.date }}</span>
                        </button>
                        <div class="credential-actions">
                            <button type="button" @click="openDetails(item, $event)">View details</button>
                            <a v-if="item.verify_url" :href="item.verify_url" target="_blank" rel="noopener noreferrer">Issuer record<ArrowUpRight :size="15" aria-hidden="true" /></a>
                        </div>
                    </article>
                </div>
            </section>
            <div v-if="!groupedItems.length" class="archive-empty">
                <h2>No matching credentials</h2><p>Try another name, issuer, or category.</p><button type="button" class="reset-filter" @click="resetFilters">Clear filters</button>
            </div>
        </main>
        <footer class="archive-footer"><span>{{ profile.display_name }}</span><Link href="/"><ArrowLeft :size="17" aria-hidden="true" />Return to portfolio</Link></footer>

        <dialog ref="dialogRef" class="details-dialog credential-dialog" :aria-labelledby="selectedItem ? `credential-title-${selectedItem.id}` : undefined" @cancel.prevent="closeDetails" @close="closeDetails" @click="($event.target === $event.currentTarget) && closeDetails()">
            <article v-if="selectedItem" class="credential-details">
                <button class="modal-close" type="button" aria-label="Close credential details" autofocus @click="closeDetails"><X :size="20" aria-hidden="true" /></button>
                <div class="modal-mark">
                    <img v-if="selectedItem.image_url && !failedImages.has(selectedItem.id)" :src="selectedItem.image_url" :alt="isCertificates ? (selectedItem.provider_name || selectedItem.issuer || 'Provider') : selectedItem.title" @error="imageFailed(selectedItem)">
                    <span v-else class="credential-fallback"><Award v-if="!isCertificates" :size="38" aria-hidden="true" /><strong v-else>{{ selectedItem.provider_initials || 'Certificate' }}</strong></span>
                </div>
                <p class="modal-category">{{ selectedItem.category_label }}</p>
                <h2 :id="`credential-title-${selectedItem.id}`">{{ selectedItem.title }}</h2>
                <p class="modal-issuer">{{ isCertificates ? (selectedItem.provider_name || selectedItem.issuer) : selectedItem.issuer }}</p>
                <p v-if="selectedItem.description">{{ selectedItem.description }}</p>
                <dl v-if="selectedItem.date || selectedItem.expires_at || selectedItem.verification_code">
                    <div v-if="selectedItem.date"><dt>Issued</dt><dd>{{ selectedItem.date }}</dd></div>
                    <div v-if="selectedItem.expires_at"><dt>Expires</dt><dd>{{ selectedItem.expires_at }}</dd></div>
                    <div v-if="selectedItem.verification_code"><dt>Verification code</dt><dd>{{ selectedItem.verification_code }}</dd></div>
                </dl>
                <div v-if="selectedItem.tags?.length" class="tag-row"><span v-for="tag in selectedItem.tags" :key="tag">{{ tag }}</span></div>
                <div class="modal-actions">
                    <a v-if="selectedItem.verify_url" :href="selectedItem.verify_url" target="_blank" rel="noopener noreferrer" class="text-link">View issuer record<ArrowUpRight :size="17" aria-hidden="true" /></a>
                    <a v-if="selectedItem.criteria_url" :href="selectedItem.criteria_url" target="_blank" rel="noopener noreferrer" class="text-link">View criteria<ArrowUpRight :size="17" aria-hidden="true" /></a>
                    <a v-if="selectedItem.evidence_url" :href="selectedItem.evidence_url" target="_blank" rel="noopener noreferrer" class="text-link">View evidence<ArrowUpRight :size="17" aria-hidden="true" /></a>
                </div>
                <p v-if="isCertificates && !selectedItem.verify_url" class="privacy-note">The original document is kept private.</p>
            </article>
        </dialog>
    </div>
</template>

<style scoped>
.credential-group { margin-top: 3.5rem; }
.credential-group h2 { display: flex; align-items: baseline; gap: 0.75rem; margin: 0 0 1.5rem; font-size: clamp(1.5rem, 2.4vw, 2rem); font-weight: 400; letter-spacing: -0.025em; }
.credential-group h2 > span { color: var(--profile-500); font-size: 0.9rem; letter-spacing: 0; }
.credential-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 2rem 1.75rem; }
.credential-card { min-width: 0; display: flex; flex-direction: column; border-bottom: 1px solid var(--profile-200); }
.credential-main { display: flex; flex-direction: column; align-items: flex-start; width: 100%; flex: 1; min-width: 0; border: 0; padding: 0; background: transparent; color: var(--profile-ink); text-align: left; }
.credential-mark { display: grid; place-items: center; width: 100%; height: 10rem; margin-bottom: 1.25rem; border-radius: 16px; background: var(--surface-muted); transition: background var(--motion-fast, 160ms) ease; }
.credential-main:hover .credential-mark { background: var(--profile-100); }
.credential-mark img { width: 88px; height: 88px; object-fit: contain; }
.credential-fallback { display: grid; place-items: center; max-width: 90%; color: var(--profile-500); font-size: 1.25rem; }
.credential-fallback strong { font-weight: 400; }
.credential-name { font-size: 1.1rem; line-height: 1.5; overflow-wrap: anywhere; }
.credential-issuer { margin-top: 0.5rem; color: var(--profile-500); font-size: 0.95rem; line-height: 1.5; }
.credential-date { margin-top: 0.4rem; color: var(--profile-500); font-size: 0.85rem; }
.credential-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem 1.25rem; margin: 1rem 0 0.5rem; }
.credential-actions button, .credential-actions a { display: inline-flex; align-items: center; gap: 0.35rem; min-height: 44px; padding: 0; border: 0; background: transparent; color: var(--accent-primary); font-size: 0.9rem; }
.credential-actions :is(button, a):hover { text-decoration: underline; text-underline-offset: 4px; }
.credential-dialog { width: 40rem; }
.credential-details { position: relative; padding: clamp(1.5rem, 5vw, 3rem); }
.modal-mark { display: grid; place-items: center; width: 6rem; height: 6rem; margin-bottom: 1.5rem; }
.modal-mark img { max-width: 100%; max-height: 100%; object-fit: contain; }
.modal-category, .modal-issuer { margin: 0.75rem 0; font-size: 0.95rem; }
.credential-details dl { display: grid; gap: 1rem; margin: 1.5rem 0; }
.credential-details dl > div { display: grid; grid-template-columns: 9rem minmax(0, 1fr); gap: 1rem; }
.credential-details dt { color: var(--profile-500); }
.credential-details dd { margin: 0; overflow-wrap: anywhere; }
.tag-row { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.tag-row span { padding: 0.4rem 0.75rem; border-radius: 999px; background: var(--profile-100); color: var(--profile-500); font-size: 0.85rem; }
.modal-actions { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-top: 1.5rem; }
.privacy-note { margin-bottom: 0; font-size: 0.9rem; }
@media (max-width: 900px) { .credential-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 560px) { .credential-grid { grid-template-columns: 1fr; gap: 2rem; } .credential-details dl > div { grid-template-columns: 1fr; gap: 0.3rem; } }
</style>
