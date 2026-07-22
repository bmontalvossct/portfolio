<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, Film, Image as ImageIcon, Moon, Palette, Search, Sun, X } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    profile: { type: Object, required: true },
});

const activeType = ref('all');
const isDark = ref(false);
const query = ref('');
const selectedItem = ref(null);
const theme = ref('system');

const types = computed(() => [
    { key: 'all', label: 'All media' },
    ...[...new Set(props.items.map((item) => item.media_type).filter(Boolean))]
        .map((type) => ({ key: type, label: type === 'video' ? 'Videos' : (type === 'pdf' ? 'PDFs' : 'Images') })),
]);

const filteredItems = computed(() => {
    const term = query.value.trim().toLowerCase();

    return props.items.filter((item) => (
        (activeType.value === 'all' || item.media_type === activeType.value)
        && (!term || [item.title, item.description, item.year].filter(Boolean).join(' ').toLowerCase().includes(term))
    ));
});

function initials(value) {
    return String(value ?? 'BM').split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join('').toUpperCase();
}

function applyTheme() {
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = theme.value === 'dark' || (theme.value === 'system' && systemDark);
    document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
}

function closeDetails() {
    selectedItem.value = null;
}

function onKeydown(event) {
    if (event.key === 'Escape') closeDetails();
}

onMounted(() => {
    theme.value = window.localStorage.getItem('profile-theme') ?? 'system';
    applyTheme();
    window.addEventListener('keydown', onKeydown);
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onKeydown);
    window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', applyTheme);
    document.body.style.overflow = '';
});

watch(theme, (value) => {
    window.localStorage.setItem('profile-theme', value);
    applyTheme();
});

watch(selectedItem, (item) => {
    document.body.style.overflow = item ? 'hidden' : '';
});
</script>

<template>
    <Head title="Design & media archive">
        <link v-if="profile.avatar_url" rel="icon" :href="profile.avatar_url">
    </Head>

    <div class="media-archive-shell">
        <header class="archive-topbar">
            <Link class="archive-brand" href="/" :aria-label="`${profile.display_name} portfolio`">
                <span class="archive-brand-mark">
                    <span>{{ initials(profile.display_name) }}</span>
                    <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="" @error="$event.currentTarget.remove()">
                </span>
                <strong>{{ profile.display_name }}</strong>
            </Link>

            <nav aria-label="Portfolio archives">
                <Link href="/"><ArrowLeft :size="14" />Portfolio</Link>
                <Link href="/designs" class="active">Designs</Link>
                <Link href="/badges">Badges</Link>
                <Link href="/certifications">Certificates</Link>
            </nav>

            <div class="theme-switch" aria-label="Color theme">
                <button type="button" title="Light theme" aria-label="Light theme" :class="{ active: !isDark }" @click="theme = 'light'"><Sun :size="15" /></button>
                <button type="button" title="Dark theme" aria-label="Dark theme" :class="{ active: isDark }" @click="theme = 'dark'"><Moon :size="15" /></button>
            </div>
        </header>

        <main class="archive-main">
            <section class="archive-intro">
                <span class="folio-label">Complete visual archive / {{ items.length }} works</span>
                <h1>Design &amp; media</h1>
                <p>A working collection of visual identities, campaign graphics, event materials, publication layouts, and motion work.</p>
            </section>

            <section class="archive-controls" aria-label="Archive filters">
                <label class="archive-search">
                    <Search :size="16" />
                    <span class="sr-only">Search designs</span>
                    <input v-model="query" type="search" placeholder="Search the archive">
                </label>
                <div class="type-filter" aria-label="Media type">
                    <button v-for="type in types" :key="type.key" type="button" :class="{ active: activeType === type.key }" @click="activeType = type.key">{{ type.label }}</button>
                </div>
                <a v-if="profile.canva_url" class="canva-link" :href="profile.canva_url" target="_blank" rel="noreferrer"><Palette :size="15" />Canva showcase<ArrowUpRight :size="14" /></a>
            </section>

            <div class="archive-count"><span>{{ filteredItems.length }} works shown</span><span>Click any work to enlarge</span></div>

            <section v-if="filteredItems.length" class="media-grid" aria-label="Design and media works">
                <button v-for="(item, index) in filteredItems" :key="item.id" class="media-card" type="button" @click="selectedItem = item">
                    <span class="media-frame">
                        <video v-if="item.media_type === 'video'" muted playsinline preload="metadata" :poster="item.thumbnail_url || undefined">
                            <source :src="item.media_url">
                        </video>
                        <iframe v-else-if="item.media_type === 'pdf'" :src="`${item.media_url}#page=1&toolbar=0&navpanes=0&scrollbar=0`" :title="`${item.title} preview`" tabindex="-1"></iframe>
                        <img v-else :src="item.media_url" :alt="item.title" loading="lazy">
                        <span v-if="item.media_type === 'video'" class="video-mark"><Film :size="17" />Video</span>
                        <span class="work-number">{{ String(index + 1).padStart(2, '0') }}</span>
                    </span>
                    <span class="media-copy"><small>{{ item.type_label }}<template v-if="item.year"> / {{ item.year }}</template></small><strong>{{ item.title }}</strong></span>
                </button>
            </section>

            <div v-else class="archive-empty"><ImageIcon :size="30" /><span>No media matches this filter.</span></div>
        </main>

        <footer class="archive-footer"><span>Selected visual work by {{ profile.display_name }}</span><Link href="/"><ArrowLeft :size="14" />Return to portfolio</Link></footer>

        <Transition name="modal">
            <div v-if="selectedItem" class="modal-backdrop" role="presentation" @click.self="closeDetails">
                <article class="media-modal" role="dialog" aria-modal="true" :aria-labelledby="`media-title-${selectedItem.id}`">
                    <button class="modal-close" type="button" title="Close" aria-label="Close" @click="closeDetails"><X :size="18" /></button>
                    <div class="modal-media">
                        <video v-if="selectedItem.media_type === 'video'" controls autoplay playsinline :poster="selectedItem.thumbnail_url || undefined"><source :src="selectedItem.media_url"></video>
                        <iframe v-else-if="selectedItem.media_type === 'pdf'" :src="`${selectedItem.media_url}#page=1&toolbar=0&navpanes=0`" :title="selectedItem.title"></iframe>
                        <img v-else :src="selectedItem.media_url" :alt="selectedItem.title">
                    </div>
                    <div class="modal-copy">
                        <span class="folio-label">{{ selectedItem.type_label }}<template v-if="selectedItem.year"> / {{ selectedItem.year }}</template></span>
                        <h2 :id="`media-title-${selectedItem.id}`">{{ selectedItem.title }}</h2>
                        <p v-if="selectedItem.description">{{ selectedItem.description }}</p>
                        <a v-if="selectedItem.external_url" :href="selectedItem.external_url" target="_blank" rel="noreferrer">View source<ArrowUpRight :size="14" /></a>
                    </div>
                </article>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.media-archive-shell { min-height: 100vh; background: var(--profile-bg); color: var(--profile-ink); }
.archive-topbar { position: sticky; top: 0; z-index: 40; display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 1.2rem; align-items: center; border-bottom: 1px solid var(--profile-200); background: color-mix(in srgb, var(--profile-bg) 94%, transparent); padding: 0.75rem clamp(1rem, 4vw, 2.5rem); backdrop-filter: blur(16px); }
.archive-brand { display: flex; min-width: 0; align-items: center; gap: 0.7rem; text-decoration: none; }
.archive-brand-mark { position: relative; display: grid; width: 2.25rem; aspect-ratio: 1; flex: 0 0 auto; place-items: center; overflow: hidden; border-radius: 50%; background: var(--profile-ink); color: var(--profile-bg); font-family: var(--font-mono); font-size: 0.62rem; }
.archive-brand-mark img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center top; }
.archive-brand strong { overflow: hidden; font-family: var(--font-mono); font-size: 0.72rem; text-overflow: ellipsis; text-transform: uppercase; white-space: nowrap; }
.archive-topbar nav { display: flex; align-items: center; gap: 0.3rem; }
.archive-topbar nav a { display: inline-flex; align-items: center; gap: 0.35rem; border: 1px solid transparent; border-radius: 4px; padding: 0.45rem 0.55rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.64rem; text-decoration: none; text-transform: uppercase; }
.archive-topbar nav a.active { border-color: var(--profile-ink); color: var(--profile-ink); }
.theme-switch { display: grid; grid-template-columns: repeat(2, 2rem); border: 1px solid var(--profile-200); border-radius: 4px; overflow: hidden; }
.theme-switch button { display: grid; width: 2rem; height: 2rem; place-items: center; border: 0; border-right: 1px solid var(--profile-200); background: var(--profile-bg); color: var(--profile-500); cursor: pointer; }
.theme-switch button:last-child { border-right: 0; }
.theme-switch button.active { background: var(--profile-ink); color: var(--profile-bg); }
.archive-main { width: min(100% - 2rem, 78rem); margin: 0 auto; padding: 5rem 0 7rem; }
.folio-label { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.64rem; text-transform: uppercase; }
.archive-intro h1 { margin: 0.7rem 0 0; font-size: clamp(3rem, 7vw, 6.4rem); font-weight: 500; letter-spacing: -0.045em; line-height: 0.9; }
.archive-intro > p { max-width: 43rem; margin: 2rem 0 0; color: var(--profile-700); font-family: var(--font-serif); font-size: 1.15rem; line-height: 1.7; }
.archive-controls { display: grid; grid-template-columns: minmax(14rem, 1fr) auto auto; gap: 0.65rem; align-items: center; margin-top: 2.5rem; }
.archive-search { display: flex; height: 2.9rem; align-items: center; gap: 0.55rem; border: 1px solid var(--profile-300); border-radius: 4px; padding: 0 0.7rem; }
.archive-search input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; padding: 0.7rem 0; color: var(--profile-ink); }
.type-filter { display: flex; height: 2.9rem; border: 1px solid var(--profile-300); border-radius: 4px; overflow: hidden; }
.type-filter button { border: 0; border-right: 1px solid var(--profile-300); background: transparent; padding: 0 0.75rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.6rem; text-transform: uppercase; cursor: pointer; }
.type-filter button:last-child { border-right: 0; }
.type-filter button.active { background: var(--profile-ink); color: var(--profile-bg); }
.canva-link { display: inline-flex; align-items: center; gap: 0.35rem; border-bottom: 1px solid var(--profile-ink); padding: 0.55rem 0 0.2rem; font-family: var(--font-mono); font-size: 0.61rem; text-decoration: none; text-transform: uppercase; }
.archive-count { display: flex; justify-content: space-between; border-bottom: 1px solid var(--profile-200); margin: 4rem 0 1rem; padding-bottom: 0.6rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.59rem; text-transform: uppercase; }
.media-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.7rem; align-items: start; }
.media-card { min-width: 0; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 4px; background: var(--profile-50); padding: 0; color: var(--profile-ink); text-align: left; cursor: zoom-in; transition: border-color 160ms ease, transform 160ms ease; }
.media-card:hover, .media-card:focus-visible { border-color: var(--profile-ink); transform: translateY(-3px); }
.media-frame { position: relative; display: grid; aspect-ratio: 4 / 3; place-items: center; overflow: hidden; background: #171b19; }
.media-frame img, .media-frame video, .media-frame iframe { width: 100%; height: 100%; object-fit: contain; }
.media-frame iframe { border: 0; pointer-events: none; }
.work-number { position: absolute; top: 0.55rem; right: 0.55rem; border-radius: 999px; background: rgb(0 0 0 / 68%); padding: 0.3rem 0.45rem; color: #fff; font-family: var(--font-mono); font-size: 0.55rem; }
.video-mark { position: absolute; bottom: 0.55rem; left: 0.55rem; display: inline-flex; align-items: center; gap: 0.3rem; border-radius: 3px; background: rgb(0 0 0 / 72%); padding: 0.35rem 0.5rem; color: #fff; font-family: var(--font-mono); font-size: 0.56rem; text-transform: uppercase; }
.media-copy { display: grid; min-height: 5.6rem; align-content: start; gap: 0.35rem; padding: 0.85rem; }
.media-copy small { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.56rem; text-transform: uppercase; }
.media-copy strong { font-size: 0.92rem; line-height: 1.3; }
.archive-empty { display: flex; min-height: 20rem; align-items: center; justify-content: center; gap: 0.7rem; color: var(--profile-500); }
.archive-footer { display: flex; width: min(100% - 2rem, 78rem); align-items: center; justify-content: space-between; gap: 1rem; border-top: 1px solid var(--profile-200); margin: 0 auto; padding: 1.5rem 0; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; }
.archive-footer a { display: inline-flex; align-items: center; gap: 0.3rem; color: var(--profile-ink); text-decoration: none; }
.modal-backdrop { position: fixed; inset: 0; z-index: 60; display: grid; place-items: center; background: color-mix(in srgb, #000 68%, transparent); padding: 1rem; backdrop-filter: blur(10px); }
.media-modal { position: relative; display: grid; width: min(100%, 74rem); max-height: calc(100vh - 2rem); grid-template-columns: minmax(0, 2fr) minmax(16rem, 0.7fr); overflow: auto; border: 1px solid var(--profile-ink); background: var(--profile-bg); box-shadow: 10px 10px 0 var(--profile-ink); }
.modal-close { position: absolute; top: 0.8rem; right: 0.8rem; z-index: 2; display: grid; width: 2.2rem; height: 2.2rem; place-items: center; border: 1px solid var(--profile-300); border-radius: 4px; background: var(--profile-bg); color: var(--profile-ink); cursor: pointer; }
.modal-media { display: grid; min-height: 24rem; place-items: center; overflow: hidden; background: #111513; }
.modal-media img, .modal-media video, .modal-media iframe { width: 100%; max-height: calc(100vh - 2.1rem); object-fit: contain; }
.modal-media iframe { height: min(80vh, 54rem); border: 0; }
.modal-copy { align-self: end; padding: clamp(1.2rem, 4vw, 2.5rem); }
.modal-copy h2 { margin: 0.6rem 0 0; font-size: clamp(1.8rem, 3vw, 3rem); line-height: 1; }
.modal-copy p { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.7; }
.modal-copy a { display: inline-flex; align-items: center; gap: 0.3rem; border-bottom: 1px solid var(--profile-ink); padding-bottom: 0.2rem; font-family: var(--font-mono); font-size: 0.62rem; text-decoration: none; text-transform: uppercase; }
.modal-enter-active, .modal-leave-active { transition: opacity 180ms ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; clip-path: inset(50%); }

@media (max-width: 900px) {
    .archive-topbar { grid-template-columns: minmax(0, 1fr) auto; }
    .archive-topbar nav { grid-column: 1 / -1; grid-row: 2; overflow-x: auto; }
    .archive-controls { grid-template-columns: 1fr; align-items: stretch; }
    .type-filter { width: fit-content; }
    .canva-link { width: fit-content; }
    .media-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .media-modal { grid-template-columns: 1fr; }
    .modal-media { min-height: 0; }
}

@media (max-width: 560px) {
    .archive-main { width: min(100% - 1.25rem, 78rem); padding-top: 3.5rem; }
    .archive-brand strong { display: none; }
    .media-grid { grid-template-columns: 1fr; }
    .archive-count span:last-child { display: none; }
    .archive-footer { width: min(100% - 1.25rem, 78rem); align-items: flex-start; flex-direction: column; }
}
</style>