<script setup>
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, watch } from 'vue';

const props = defineProps({
    seo: { type: Object, required: true },
});

const schemaElementId = 'portfolio-structured-data';

function syncStructuredData() {
    let element = document.getElementById(schemaElementId);

    if (!props.seo.schema) {
        element?.remove();
        return;
    }

    if (!element) {
        element = document.createElement('script');
        element.id = schemaElementId;
        element.type = 'application/ld+json';
        document.head.appendChild(element);
    }

    element.textContent = JSON.stringify(props.seo.schema);
}

onMounted(syncStructuredData);
onBeforeUnmount(() => document.getElementById(schemaElementId)?.remove());
watch(() => props.seo.schema, syncStructuredData, { deep: true });
</script>

<template>
    <Head :title="seo.title">
        <meta head-key="description" name="description" :content="seo.description">
        <meta head-key="robots" name="robots" :content="seo.robots">
        <link v-if="seo.canonical" head-key="canonical" rel="canonical" :href="seo.canonical">
        <meta head-key="og-type" property="og:type" :content="seo.type">
        <meta head-key="og-site-name" property="og:site_name" :content="seo.site_name">
        <meta head-key="og-locale" property="og:locale" :content="seo.locale">
        <meta head-key="og-title" property="og:title" :content="seo.title">
        <meta head-key="og-description" property="og:description" :content="seo.description">
        <meta v-if="seo.canonical" head-key="og-url" property="og:url" :content="seo.canonical">
        <meta v-if="seo.image" head-key="og-image" property="og:image" :content="seo.image">
        <meta v-if="seo.image_alt" head-key="og-image-alt" property="og:image:alt" :content="seo.image_alt">
        <meta head-key="twitter-card" name="twitter:card" :content="seo.image ? 'summary_large_image' : 'summary'">
        <meta head-key="twitter-title" name="twitter:title" :content="seo.title">
        <meta head-key="twitter-description" name="twitter:description" :content="seo.description">
        <meta v-if="seo.image" head-key="twitter-image" name="twitter:image" :content="seo.image">
    </Head>
</template>
