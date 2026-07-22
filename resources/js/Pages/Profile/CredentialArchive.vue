<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ArrowUpRight, BadgeCheck, FileBadge, ListFilter, Moon, Search, Sun, X } from '@lucide/vue';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    archiveType: { type: String, required: true },
    title: { type: String, required: true },
    intro: { type: String, required: true },
    items: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    alternate: { type: Object, required: true },
    profile: { type: Object, required: true },
});

const activeCategory = ref('all');
const isDark = ref(false);
const query = ref('');
const selectedItem = ref(null);
const theme = ref('system');

const filteredItems = computed(() => {
    const term = query.value.trim().toLowerCase();

    return props.items.filter((item) => {
        const matchesCategory = activeCategory.value === 'all' || item.category === activeCategory.value;
        const searchable = [item.title, item.issuer, item.description, item.verification_code, ...(item.tags ?? [])]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return matchesCategory && (!term || searchable.includes(term));
    });
});

const groupedItems = computed(() => props.categories
    .map((category) => ({
        ...category,
        items: filteredItems.value.filter((item) => item.category === category.value),
    }))
    .filter((category) => category.items.length));

function initials(value) {
    return String(value ?? 'BM')
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('')
        .toUpperCase();
}

function cardStyle(index) {
    const rotations = ['-0.75deg', '0.55deg', '-0.35deg', '0.8deg', '-0.5deg'];

    return { '--card-rotation': rotations[index % rotations.length] };
}

function applyTheme() {
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    isDark.value = theme.value === 'dark' || (theme.value === 'system' && systemDark);
    document.documentElement.dataset.theme = isDark.value ? 'dark' : 'light';
}

function setTheme(value) {
    theme.value = value;
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
    <Head :title="title">
        <link v-if="profile.avatar_url" rel="icon" :href="profile.avatar_url">
    </Head>

    <div class="archive-shell" :class="{ 'is-certifications': archiveType === 'certificates' }">
        <header class="archive-topbar">
            <Link class="archive-brand" href="/" :aria-label="`${profile.display_name} portfolio`">
                <span class="archive-brand-mark">
                    <span>{{ initials(profile.display_name) }}</span>
                    <img v-if="profile.avatar_url" :src="profile.avatar_url" alt="" @error="$event.currentTarget.remove()">
                </span>
                <strong>{{ profile.display_name }}</strong>
            </Link>

            <nav aria-label="Credential archives">
                <Link href="/"><ArrowLeft :size="14" />Portfolio</Link>
                <Link href="/badges" :class="{ active: archiveType === 'badges' }">Badges</Link>
                <Link href="/certifications" :class="{ active: archiveType === 'certificates' }">Certificates</Link>
            </nav>

            <div class="theme-switch" aria-label="Color theme">
                <button type="button" title="Light theme" aria-label="Light theme" :class="{ active: !isDark }" @click="setTheme('light')"><Sun :size="15" /></button>
                <button type="button" title="Dark theme" aria-label="Dark theme" :class="{ active: isDark }" @click="setTheme('dark')"><Moon :size="15" /></button>
            </div>
        </header>

        <main class="archive-main">
            <section class="archive-intro">
                <span class="folio-label">Evidence archive / {{ items.length }}</span>
                <h1>{{ title }}</h1>
                <p>{{ intro }}</p>

                <div class="archive-controls">
                    <label class="archive-search"><Search :size="16" /><input v-model="query" type="search" :placeholder="`Search ${archiveType}`" :aria-label="`Search ${archiveType}`"></label>
                    <label class="category-filter"><ListFilter :size="16" /><span class="sr-only">Category</span><select v-model="activeCategory" aria-label="Category"><option value="all">All categories</option><option v-for="category in categories" :key="category.value" :value="category.value">{{ category.label }}</option></select></label>
                    <Link class="alternate-link" :href="alternate.url">{{ alternate.label }}<ArrowUpRight :size="14" /></Link>
                </div>
            </section>

            <section v-for="group in groupedItems" :key="group.value" class="credential-group">
                <header><span>{{ group.label }}</span><small>{{ group.items.length }}</small></header>
                <div class="credential-grid">
                    <article v-for="(item, index) in group.items" :key="item.id" class="credential-card" :style="cardStyle(index)">
                        <button class="credential-main" type="button" :title="`View ${item.title} details`" @click="selectedItem = item">
                            <span class="credential-mark">
                                <img v-if="item.image_url" :src="item.image_url" :alt="archiveType === 'certificates' ? item.provider_name + ' logo' : item.title" loading="lazy">
                                <span v-else><BadgeCheck v-if="archiveType === 'badges'" :size="25" /><strong v-else>{{ item.provider_initials }}</strong></span>
                            </span>
                            <strong>{{ item.title }}</strong>
                            <small>{{ archiveType === 'badges' ? (item.issuer || 'Verified badge') : (item.provider_name || item.issuer || 'Certificate') }}</small>
                            <span v-if="item.date" class="credential-date">{{ item.date }}</span>
                        </button>
                        <div class="credential-actions">
                            <button type="button" @click="selectedItem = item">Details</button>
                            <a v-if="item.verify_url" :href="item.verify_url" target="_blank" rel="noreferrer">Verify<ArrowUpRight :size="13" /></a>
                            <span v-else class="protected-document">Protected</span>
                        </div>
                    </article>
                </div>
            </section>

            <div v-if="!groupedItems.length" class="archive-empty">
                <Search :size="23" />
                <p>No credentials match this search.</p>
            </div>
        </main>

        <footer class="archive-footer">
            <span>{{ profile.display_name }}</span>
            <Link href="/">Return to portfolio<ArrowUpRight :size="14" /></Link>
        </footer>

        <Transition name="modal">
            <div v-if="selectedItem" class="modal-backdrop" role="presentation" @click.self="closeDetails">
                <article class="credential-modal" role="dialog" aria-modal="true" :aria-labelledby="`credential-title-${selectedItem.id}`">
                    <button class="modal-close" type="button" title="Close" aria-label="Close" @click="closeDetails"><X :size="17" /></button>
                    <div class="modal-mark">
                        <img v-if="selectedItem.image_url" :src="selectedItem.image_url" :alt="archiveType === 'certificates' ? selectedItem.provider_name + ' logo' : selectedItem.title">
                        <BadgeCheck v-else-if="archiveType === 'badges'" :size="38" />
                        <strong v-else>{{ selectedItem.provider_initials }}</strong>
                    </div>
                    <span class="folio-label">{{ selectedItem.category_label }}</span>
                    <h2 :id="`credential-title-${selectedItem.id}`">{{ selectedItem.title }}</h2>
                    <p class="modal-issuer">{{ archiveType === 'certificates' ? (selectedItem.provider_name || selectedItem.issuer) : selectedItem.issuer }}</p>
                    <p v-if="selectedItem.description" class="modal-description">{{ selectedItem.description }}</p>
                    <dl v-if="selectedItem.date || selectedItem.expires_at || selectedItem.verification_code"><div v-if="selectedItem.date"><dt>Issued</dt><dd>{{ selectedItem.date }}</dd></div><div v-if="selectedItem.expires_at"><dt>Expires</dt><dd>{{ selectedItem.expires_at }}</dd></div><div v-if="selectedItem.verification_code"><dt>Verification code</dt><dd>{{ selectedItem.verification_code }}</dd></div></dl>
                    <div v-if="selectedItem.tags?.length" class="tag-row"><span v-for="tag in selectedItem.tags" :key="tag">{{ tag }}</span></div>
                    <div class="modal-actions">
                        <a v-if="selectedItem.verify_url" :href="selectedItem.verify_url" target="_blank" rel="noreferrer">Verify credential<ArrowUpRight :size="14" /></a>
                        <a v-if="selectedItem.criteria_url" :href="selectedItem.criteria_url" target="_blank" rel="noreferrer">View criteria<ArrowUpRight :size="14" /></a>
                        <a v-if="selectedItem.evidence_url" :href="selectedItem.evidence_url" target="_blank" rel="noreferrer">View evidence<ArrowUpRight :size="14" /></a>
                        <span v-if="archiveType === 'certificates' && !selectedItem.verify_url" class="protected-document">Original document withheld for privacy</span>
                    </div>
                </article>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.archive-shell { min-height: 100vh; background: var(--profile-bg); color: var(--profile-ink); }
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
.archive-intro { max-width: 64rem; }
.archive-intro h1 { margin: 0.7rem 0 0; font-size: 4rem; font-weight: 500; letter-spacing: 0; line-height: 1; }
.archive-intro > p { max-width: 42rem; margin: 2rem 0 0; color: var(--profile-700); font-family: var(--font-serif); font-size: 1.15rem; line-height: 1.7; }
.archive-controls { display: grid; grid-template-columns: repeat(2, minmax(12rem, 1fr)) auto; gap: 0.65rem; align-items: end; margin-top: 2.2rem; }
.archive-search, .category-filter { display: flex; height: 2.9rem; align-items: center; gap: 0.55rem; border: 1px solid var(--profile-300); border-radius: 4px; padding: 0 0.7rem; }
.archive-search input, .category-filter select { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; padding: 0.7rem 0; color: var(--profile-ink); }
.archive-search > svg, .category-filter > svg { flex: 0 0 auto; color: var(--profile-500); }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; clip-path: inset(50%); }
.alternate-link { display: inline-flex; align-items: center; gap: 0.35rem; border-bottom: 1px solid var(--profile-ink); padding: 0.7rem 0 0.2rem; font-family: var(--font-mono); font-size: 0.64rem; text-decoration: none; text-transform: uppercase; }
.credential-group { margin-top: 4.5rem; }
.credential-group > header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.2rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.65rem; text-transform: uppercase; }
.credential-group > header small { display: grid; width: 1.6rem; aspect-ratio: 1; place-items: center; border: 1px solid var(--profile-300); border-radius: 50%; }
.credential-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.45rem; align-items: start; }
.archive-shell.is-certifications .credential-grid { display: flex; flex-wrap: wrap; justify-content: center; }
.archive-shell.is-certifications .credential-card { flex: 0 1 calc((100% - 1.35rem) / 4); max-width: 19rem; }
.credential-card { position: relative; min-width: 0; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 5px; background: var(--profile-bg); box-shadow: 4px 8px 18px -14px rgb(0 0 0 / 45%); transform: rotate(var(--card-rotation)); transition: border-color 160ms ease, transform 160ms ease; }
.credential-card:hover, .credential-card:focus-within { z-index: 2; border-color: var(--profile-ink); transform: rotate(0deg) translateY(-2px); }
.credential-main { display: grid; width: 100%; min-height: 10.5rem; justify-items: center; align-content: start; border: 0; background: transparent; padding: 1rem 0.8rem 0.8rem; color: var(--profile-ink); text-align: center; cursor: pointer; }
.credential-mark { display: grid; width: 3rem; height: 3rem; place-items: center; margin-bottom: 0.8rem; overflow: hidden; border: 1px solid var(--profile-200); border-radius: 4px; background: #fff; color: #17251e; }
.credential-mark img { width: 100%; height: 100%; object-fit: contain; padding: 0.2rem; }
.credential-main strong { display: -webkit-box; overflow: hidden; font-size: 0.82rem; line-height: 1.35; -webkit-box-orient: vertical; -webkit-line-clamp: 3; }
.credential-main small, .credential-date { margin-top: 0.3rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.56rem; text-transform: uppercase; }
.credential-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); border-top: 1px solid var(--profile-200); }
.credential-actions button, .credential-actions a { display: inline-flex; min-width: 0; align-items: center; justify-content: center; gap: 0.25rem; border: 0; border-right: 1px solid var(--profile-200); background: transparent; padding: 0.55rem 0.4rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.56rem; text-decoration: none; text-transform: uppercase; cursor: pointer; }
.credential-actions > :last-child { border-right: 0; }
.credential-actions a:hover, .credential-actions button:hover { color: var(--profile-ink); }
.protected-document { display: inline-flex; align-items: center; justify-content: center; padding: 0.55rem 0.4rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.56rem; text-transform: uppercase; }
.archive-empty { display: flex; min-height: 20rem; align-items: center; justify-content: center; gap: 0.7rem; color: var(--profile-500); }
.archive-footer { display: flex; width: min(100% - 2rem, 78rem); align-items: center; justify-content: space-between; gap: 1rem; border-top: 1px solid var(--profile-200); margin: 0 auto; padding: 1.5rem 0; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.62rem; text-transform: uppercase; }
.archive-footer a { display: inline-flex; align-items: center; gap: 0.3rem; color: var(--profile-ink); text-decoration: none; }
.modal-backdrop { position: fixed; inset: 0; z-index: 60; display: grid; place-items: center; background: color-mix(in srgb, #000 55%, transparent); padding: 1rem; backdrop-filter: blur(10px); }
.credential-modal { position: relative; width: min(100%, 35rem); max-height: calc(100vh - 2rem); overflow-y: auto; border: 1px solid var(--profile-ink); background: var(--profile-bg); padding: clamp(1.1rem, 4vw, 2rem); box-shadow: 10px 10px 0 var(--profile-ink); }
.modal-close { position: absolute; top: 1rem; right: 1rem; display: grid; width: 2.2rem; height: 2.2rem; place-items: center; border: 1px solid var(--profile-300); border-radius: 4px; background: var(--profile-bg); color: var(--profile-ink); cursor: pointer; }
.modal-mark { display: grid; width: 7rem; height: 7rem; place-items: center; margin-bottom: 1.5rem; overflow: hidden; }
.modal-mark img { width: 100%; height: 100%; object-fit: contain; }
.credential-modal h2 { max-width: 27rem; margin: 0.6rem 0 0; font-size: 2.4rem; letter-spacing: 0; line-height: 1.05; }
.modal-issuer { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.65rem; text-transform: uppercase; }
.modal-description { color: var(--profile-700); font-family: var(--font-serif); line-height: 1.7; }
.credential-modal dl { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.8rem; margin: 1.2rem 0; }
.credential-modal dl div { border-top: 1px solid var(--profile-200); padding-top: 0.6rem; }
.credential-modal dt { color: var(--profile-500); font-family: var(--font-mono); font-size: 0.58rem; text-transform: uppercase; }
.credential-modal dd { margin: 0.25rem 0 0; }
.tag-row, .modal-actions { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.tag-row span { border: 1px solid var(--profile-300); border-radius: 999px; padding: 0.3rem 0.55rem; color: var(--profile-500); font-family: var(--font-mono); font-size: 0.58rem; text-transform: uppercase; }
.modal-actions { margin-top: 1.2rem; gap: 0.8rem; }
.modal-actions a { display: inline-flex; align-items: center; gap: 0.3rem; border-bottom: 1px solid var(--profile-ink); padding-bottom: 0.2rem; font-family: var(--font-mono); font-size: 0.62rem; text-decoration: none; text-transform: uppercase; }
.modal-enter-active, .modal-leave-active { transition: opacity 180ms ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

@media (max-width: 980px) {
    .credential-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .archive-shell.is-certifications .credential-card { flex-basis: calc((100% - 0.9rem) / 3); }
}

@media (max-width: 760px) {
    .archive-topbar { grid-template-columns: minmax(0, 1fr) auto; }
    .archive-topbar nav { grid-column: 1 / -1; grid-row: 2; overflow-x: auto; }
    .archive-main { width: min(100% - 1.25rem, 78rem); padding-top: 3.5rem; }
    .archive-intro h1 { font-size: 2.6rem; }
    .archive-controls { grid-template-columns: 1fr; align-items: stretch; }
    .alternate-link { width: fit-content; }
    .credential-group { margin-top: 3.5rem; }
    .credential-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.25rem; }
    .archive-shell.is-certifications .credential-card { flex-basis: calc((100% - 0.25rem) / 2); max-width: 11rem; }
    .credential-main { min-height: 11.5rem; padding-inline: 0.55rem; }
    .credential-actions { grid-template-columns: 1fr; }
    .credential-actions button { display: none; }
    .archive-footer { width: min(100% - 1.25rem, 78rem); align-items: flex-start; flex-direction: column; }
    .credential-modal h2 { font-size: 1.8rem; }
}
</style>
