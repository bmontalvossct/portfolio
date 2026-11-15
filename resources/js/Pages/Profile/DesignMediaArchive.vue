<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, ArrowUpRight, FileText, Film, Moon, Palette, Search, Sun, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import SeoHead from '../../Components/SeoHead.vue';
import { useDetailsDialog, usePublicTheme } from './publicPage.js';
import './publicPage.css';

const props = defineProps({
    seo: { type: Object, required: true },
    items: { type: Array, default: () => [] },
    profile: { type: Object, required: true },
});
const activeType = ref('all');
const query = ref('');
const { theme, isDark } = usePublicTheme();
const { selectedItem, dialogRef, openDetails, closeDetails } = useDetailsDialog();
const types = computed(() => [
    { key: 'all', label: 'All media' },
    ...[...new Set(props.items.map((item) => item.media_type).filter(Boolean))].map((type) => ({ key: type, label: type === 'video' ? 'Videos' : (type === 'pdf' ? 'PDFs' : 'Images') })),
]);
const filteredItems = computed(() => {
    const term = query.value.trim().toLowerCase();
    return props.items.filter((item) => (activeType.value === 'all' || item.media_type === activeType.value)
        && (!term || [item.title, item.description, item.year].filter(Boolean).join(' ').toLowerCase().includes(term)));
});
const selectedIndex = computed(() => filteredItems.value.findIndex((item) => item.id === selectedItem.value?.id));
function resetFilters() { query.value = ''; activeType.value = 'all'; }
function moveSelection(offset) {
    const item = filteredItems.value[selectedIndex.value + offset];
    if (item) selectedItem.value = item;
}
</script>

<template>
    <SeoHead :seo="seo" />
    <div class="public-page media-page">
        <a class="skip-link" href="#main-content">Skip to content</a>
        <header class="archive-topbar">
            <Link class="archive-brand" href="/" :aria-label="`${profile.display_name} portfolio`">
                <span class="archive-brand-mark"><img :src="profile.avatar_url || '/images/profile-avatar.jpg'" alt=""></span>
            </Link>
            <nav aria-label="Portfolio navigation">
                <Link href="/">Portfolio</Link>
                <Link href="/services">Services</Link>
                <Link href="/badges">Badges</Link>
                <Link href="/certifications">Certificates</Link>
            </nav>
            <div class="theme-switch" role="group" aria-label="Color theme">
                <button type="button" aria-label="Light theme" :aria-pressed="!isDark" :class="{ active: !isDark }" @click="theme = 'light'"><Sun :size="18" aria-hidden="true" /></button>
                <button type="button" aria-label="Dark theme" :aria-pressed="isDark" :class="{ active: isDark }" @click="theme = 'dark'"><Moon :size="18" aria-hidden="true" /></button>
            </div>
        </header>

        <main id="main-content" class="archive-main">
            <section class="archive-intro page-intro">
                <h1>Design &amp; media</h1>
                <p>Visual identities, campaign graphics, event materials, publication layouts, and motion work. A closer look at the ideas behind the projects.</p>
            </section>
            <section class="archive-controls" aria-label="Design filters">
                <label class="archive-search"><span class="field-label">Search designs</span><span class="field-control"><Search :size="19" aria-hidden="true" /><input v-model="query" type="search" placeholder="Name, description, or year"></span></label>
                <div class="type-filter" role="group" aria-label="Media type">
                    <button v-for="type in types" :key="type.key" type="button" :aria-pressed="activeType === type.key" :class="{ active: activeType === type.key }" @click="activeType = type.key">{{ type.label }}</button>
                </div>
                <a v-if="profile.canva_url" class="text-link" :href="profile.canva_url" target="_blank" rel="noopener noreferrer"><Palette :size="17" aria-hidden="true" />Canva showcase<ArrowUpRight :size="17" aria-hidden="true" /></a>
            </section>
            <p class="archive-count" role="status">{{ filteredItems.length }} works shown</p>

            <section v-if="filteredItems.length" class="media-grid" aria-label="Design and media works">
                <button v-for="item in filteredItems" :key="item.id" class="media-card" type="button" :aria-label="`View ${item.title}`" @click="openDetails(item, $event)">
                    <span class="media-frame">
                        <template v-if="item.media_type === 'video'">
                            <img v-if="item.thumbnail_url" :src="item.thumbnail_url" :alt="item.title" loading="lazy" decoding="async">
                            <span v-else class="media-placeholder"><Film :size="36" aria-hidden="true" /><span>Video</span></span>
                            <span class="video-mark"><Film :size="17" aria-hidden="true" />Watch video</span>
                        </template>
                        <template v-else-if="item.media_type === 'pdf'">
                            <img v-if="item.thumbnail_url" :src="item.thumbnail_url" :alt="`${item.title} first-page preview`" loading="lazy" decoding="async">
                            <span v-else class="media-placeholder"><FileText :size="36" aria-hidden="true" /><span>PDF document</span></span>
                        </template>
                        <img v-else :src="item.thumbnail_url || item.preview_url || item.media_url" :alt="item.title" loading="lazy" decoding="async">
                    </span>
                    <span class="media-copy"><span class="media-meta">{{ item.type_label }}<template v-if="item.year"> &middot; {{ item.year }}</template></span><span class="media-title">{{ item.title }}</span></span>
                </button>
            </section>
            <div v-else class="archive-empty"><h2>No matching work</h2><p>Try another search or choose a different media type.</p><button type="button" class="reset-filter" @click="resetFilters">Clear filters</button></div>
        </main>
        <footer class="archive-footer"><span>Visual work by {{ profile.display_name }}</span><Link href="/"><ArrowLeft :size="17" aria-hidden="true" />Return to portfolio</Link></footer>

        <dialog ref="dialogRef" class="details-dialog media-dialog" :aria-labelledby="selectedItem ? `media-title-${selectedItem.id}` : undefined" @cancel.prevent="closeDetails" @close="closeDetails" @click="($event.target === $event.currentTarget) && closeDetails()">
            <article v-if="selectedItem" class="media-details">
                <button class="modal-close" type="button" aria-label="Close media viewer" autofocus @click="closeDetails"><X :size="20" aria-hidden="true" /></button>
                <div :key="selectedItem.id" class="modal-media">
                    <video v-if="selectedItem.media_type === 'video'" controls playsinline preload="metadata" :poster="selectedItem.thumbnail_url || undefined"><source :src="selectedItem.media_url"></video>
                    <iframe v-else-if="selectedItem.media_type === 'pdf'" :src="`${selectedItem.media_url}#page=1&toolbar=0&navpanes=0`" :title="selectedItem.title"></iframe>
                    <img v-else :src="selectedItem.media_url" :alt="selectedItem.title">
                </div>
                <div class="modal-copy">
                    <p class="media-meta">{{ selectedItem.type_label }}<template v-if="selectedItem.year"> &middot; {{ selectedItem.year }}</template></p>
                    <h2 :id="`media-title-${selectedItem.id}`">{{ selectedItem.title }}</h2>
                    <p v-if="selectedItem.description">{{ selectedItem.description }}</p>
                    <a v-if="selectedItem.external_url" :href="selectedItem.external_url" target="_blank" rel="noopener noreferrer" class="text-link">View source<ArrowUpRight :size="17" aria-hidden="true" /></a>
                    <nav v-if="filteredItems.length > 1" class="gallery-navigation" aria-label="Media viewer navigation">
                        <button type="button" :disabled="selectedIndex <= 0" aria-label="Previous work" @click="moveSelection(-1)"><ArrowLeft :size="19" aria-hidden="true" /></button>
                        <span role="status">{{ selectedIndex + 1 }} of {{ filteredItems.length }}</span>
                        <button type="button" :disabled="selectedIndex >= filteredItems.length - 1" aria-label="Next work" @click="moveSelection(1)"><ArrowRight :size="19" aria-hidden="true" /></button>
                    </nav>
                </div>
            </article>
        </dialog>
    </div>
</template>

<style scoped>
.type-filter { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.type-filter button { min-height: 48px; padding: 0.75rem 1rem; border: 1px solid var(--profile-300); border-radius: 999px; background: var(--profile-bg); color: var(--profile-500); font-size: 0.9rem; transition: background var(--motion-fast, 160ms) ease, color var(--motion-fast, 160ms) ease; }
.type-filter button.active { border-color: var(--profile-ink); background: var(--profile-ink); color: var(--profile-bg); }
.media-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: 3rem 2rem; margin-top: 2rem; }
.media-card { min-width: 0; padding: 0; border: 0; background: transparent; color: var(--profile-ink); text-align: left; }
.media-frame { position: relative; display: grid; place-items: center; width: 100%; aspect-ratio: 4 / 3; overflow: hidden; border-radius: 20px; background: var(--surface-muted); }
.media-frame img { width: 100%; height: 100%; object-fit: contain; }
.media-placeholder { display: grid; justify-items: center; gap: 0.75rem; color: var(--profile-500); }
.video-mark { position: absolute; bottom: 1rem; left: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.55rem 0.8rem; border-radius: 999px; background: var(--profile-bg); color: var(--profile-ink); font-size: 0.85rem; }
.media-copy { display: grid; gap: 0.55rem; padding: 1.25rem 0; }
.media-meta { color: var(--profile-500); font-size: 0.9rem; }
.media-title { font-size: clamp(1.25rem, 2vw, 1.6rem); font-weight: 400; line-height: 1.35; letter-spacing: -0.02em; }
.media-card:hover .media-title { text-decoration: underline; text-underline-offset: 5px; text-decoration-thickness: 1px; }
.media-dialog { width: 76rem; }
.media-details { position: relative; display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(17rem, 0.9fr); }
.modal-media { display: grid; place-items: center; min-height: 30rem; overflow: hidden; background: var(--surface-muted); }
.modal-media img, .modal-media video { display: block; width: 100%; max-height: calc(100dvh - 2rem); object-fit: contain; }
.modal-media iframe { width: 100%; height: min(80dvh, 55rem); border: 0; }
.modal-copy { min-width: 0; padding: 5rem 2rem 2rem; align-self: center; overflow-wrap: anywhere; }
.modal-copy > .media-meta { margin: 0; }
.gallery-navigation { display: flex; gap: 1rem; align-items: center; justify-content: space-between; padding-top: 1.5rem; margin-top: 2rem; border-top: 1px solid var(--profile-200); }
.gallery-navigation button { display: grid; place-items: center; width: 44px; height: 44px; border: 1px solid var(--profile-300); border-radius: 50%; color: var(--profile-ink); background: var(--profile-bg); }
.gallery-navigation button:disabled { opacity: 0.35; cursor: default; }
.gallery-navigation span { color: var(--profile-500); font-size: 0.9rem; }
@media (max-width: 900px) { .media-details { grid-template-columns: 1fr; } .modal-copy { padding: 2rem; } .modal-media { min-height: 0; padding-top: 4.5rem; } .modal-media img, .modal-media video { max-height: 55dvh; } }
@media (max-width: 640px) { .media-grid { grid-template-columns: 1fr; gap: 1.5rem; } .media-frame { border-radius: 16px; } }
</style>
