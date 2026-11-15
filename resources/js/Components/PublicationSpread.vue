<script setup>
import { ArrowUpRight } from '@lucide/vue';

const props = defineProps({ work: { type: Object, required: true } });
const emit = defineEmits(['media-load']);
function isDohPublication() {
    return String(props.work.title ?? '').toLowerCase().includes('sure-health');
}
</script>

<template>
    <article class="research-spread" :aria-label="work.title">
        <div class="research-cover">
            <div class="research-cover-content">
                <img v-if="isDohPublication()" :src="'/storage/portfolio/organizations/department-of-health.png'" alt="Department of Health" class="research-publisher-logo" width="64" height="64" @load="emit('media-load')" @error="emit('media-load')">
                <img v-else-if="work.publication?.toLowerCase().includes('idap')" :src="'/images/publications/idap.png'" alt="International Data &amp; Analytics Proceedings (IDAP)" class="research-publisher-logo research-publisher-logo--idap" width="104" height="64" @load="emit('media-load')" @error="emit('media-load')">
                <span v-else class="research-identity">{{ work.type_label }}</span>
                <h3>{{ work.title }}</h3>
                <p v-if="isDohPublication()">Published by the Provincial DOH Office, Surigao del Norte</p>
                <p v-if="work.publication">{{ work.publication }}</p>
            </div>
        </div>
        <div class="research-page">
            <div class="research-page-content">
                <span class="research-page-label">Summary</span>
                <p>{{ work.summary }}</p>
                <p v-if="work.role" class="research-role">{{ work.role }}</p>
                <a v-if="work.external_url" :href="work.external_url" target="_blank" rel="noopener noreferrer" class="text-link">Read publication <ArrowUpRight :size="16" aria-hidden="true" /></a>
            </div>
        </div>
    </article>
</template>

<style scoped>
.research-spread { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); min-width: 0; perspective: 1600px; }
.research-cover, .research-page { display: flex; flex-direction: column; justify-content: center; min-width: 0; padding: clamp(24px, 4vw, 52px); overflow: visible; overflow-wrap: anywhere; backface-visibility: hidden; }
.research-cover { border-right: 1px solid var(--border-hairline); background: color-mix(in srgb, var(--accent-primary) 8%, var(--profile-bg)); }
.research-cover-content, .research-page-content { display: flex; flex-direction: column; align-items: flex-start; }
.research-cover-content > *, .research-page-content > * { flex-shrink: 0; }
.research-cover h3 { margin: 24px 0 16px; font-size: clamp(21px, 2.5vw, 32px); font-weight: 400; letter-spacing: -.02em; line-height: 1.25; }
.research-cover p, .research-role { color: var(--profile-500); font-size: 16px; line-height: 1.6; }
.research-publisher-logo { width: 64px; height: 64px; object-fit: contain; }
.research-publisher-logo--idap { width: 104px; padding: 8px; border-radius: 8px; background: #fff; }
.research-identity, .research-page-label { color: var(--profile-500); font-size: 13px; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
.research-page { background: var(--profile-bg); }
.research-page-content { gap: 18px; }
.research-page p { margin: 0; font-size: 16px; line-height: 1.7; }
.text-link { display: inline-flex; gap: 7px; align-items: center; color: var(--accent-primary); font-size: 15px; font-weight: 500; min-height: 36px; }
.text-link:hover { text-decoration: underline; text-underline-offset: 4px; }
.text-link:focus-visible { outline: 2px solid var(--accent-primary); outline-offset: 3px; }
@media (max-width: 700px) {
    .research-spread { grid-template-columns: minmax(0, 1fr); grid-template-rows: auto auto; }
    .research-cover { border-right: 0; border-bottom: 1px solid var(--border-hairline); }
    .research-cover, .research-page { padding: 24px; }
    .research-cover h3 { margin-top: 18px; font-size: 23px; }
}
</style>
