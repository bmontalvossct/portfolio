<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';

const firstPage = ref(1);
const direction = ref('next');
const pageCount = 14;
const imageError = ref(false);
const turning = ref(false);
const turningPage = ref(1);
const zoom = ref(1);
const leftPage = computed(() => turning.value && direction.value === 'previous' ? firstPage.value - 1 : firstPage.value);
const rightPage = computed(() => turning.value && direction.value === 'next' ? firstPage.value + 2 : firstPage.value + 1);
let turnTimer;

function turn(step) {
    const next = firstPage.value + step;
    if (turning.value || next < 1 || next >= pageCount) return;
    direction.value = step > 0 ? 'next' : 'previous';
    imageError.value = false;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        firstPage.value = next;
        return;
    }
    turningPage.value = step > 0 ? firstPage.value + 1 : firstPage.value;
    turning.value = true;
    turnTimer = window.setTimeout(() => { firstPage.value = next; turning.value = false; }, 1200);
}

function pageUrl(number) {
    return `/creative-assets/magazine/page-${String(number).padStart(4, '0')}.jpg`;
}
function onBookKeydown(event) {
    if (zoom.value > 1 && event.target.classList.contains('book-viewport')) return;
    if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
    event.preventDefault();
    turn(event.key === 'ArrowLeft' ? 1 : -1);
}
onBeforeUnmount(() => window.clearTimeout(turnTimer));
</script>

<template>
    <div class="creative-book" @keydown="onBookKeydown">
        <button type="button" class="book-arrow book-arrow-previous" aria-label="Next magazine page" :aria-disabled="firstPage + 1 >= pageCount || turning" @click="turn(1)">‹</button>
        <div class="book-viewport" tabindex="0" aria-label="Magazine pages, scroll to view when zoomed">
        <div class="creative-book-spread" :style="{ '--book-zoom': zoom }">
            <img :src="pageUrl(leftPage)" :alt="`Magazine page ${leftPage} of ${pageCount}`" @error="imageError = true">
            <img :src="pageUrl(rightPage)" :alt="`Magazine page ${rightPage} of ${pageCount}`" @error="imageError = true">
            <div v-if="turning" class="turning-leaf" :class="`turn-${direction}`" aria-hidden="true">
                <img :src="pageUrl(turningPage)" alt="" class="leaf-face leaf-front">
                <img :src="pageUrl(turningPage)" alt="" class="leaf-face leaf-back">
            </div>
        </div>
        </div>
        <button type="button" class="page-hit-area page-hit-left" aria-label="Turn to next magazine page" :aria-disabled="firstPage + 1 >= pageCount || turning" @click="turn(1)"></button>
        <button type="button" class="page-hit-area page-hit-right" aria-label="Turn to previous magazine page" :aria-disabled="firstPage <= 1 || turning" @click="turn(-1)"></button>
        <button type="button" class="book-arrow book-arrow-next" aria-label="Previous magazine page" :aria-disabled="firstPage <= 1 || turning" @click="turn(-1)">›</button>
        <span class="sr-only" aria-live="polite">Pages {{ firstPage }} and {{ firstPage + 1 }} of {{ pageCount }}</span>
        <p v-if="imageError" class="book-error" role="status">A page could not be loaded. Please try another page.</p>
        <div class="book-zoom" aria-label="Magazine zoom controls">
            <button type="button" aria-label="Zoom out" :disabled="zoom <= 1" @click="zoom = Math.max(1, zoom - .25)">−</button>
            <button type="button" aria-label="Reset zoom" @click="zoom = 1">{{ Math.round(zoom * 100) }}%</button>
            <button type="button" aria-label="Zoom in" :disabled="zoom >= 2" @click="zoom = Math.min(2, zoom + .25)">+</button>
        </div>
    </div>
</template>

<style scoped>
.creative-book { position: relative; display: flex; align-items: center; justify-content: center; padding-bottom: 50px; }
.book-viewport { width: 100%; max-height: calc(100dvh - 170px); overflow: auto; overscroll-behavior: contain; }
.creative-book-spread { position: relative; display: flex; width: calc(min(100vw - 16px, (100dvh - 170px) * 1.414) * var(--book-zoom)); margin: 0 auto; min-width: 0; perspective: 1800px; }
.creative-book-spread > img { display: block; width: 50%; min-width: 0; height: auto; object-fit: contain; }
.turning-leaf { position: absolute; top: 0; width: 50%; height: 100%; transform-style: preserve-3d; pointer-events: none; }
.page-hit-area { position: absolute; z-index: 1; top: 0; bottom: 50px; width: 12%; padding: 0; border: 0; background: transparent; cursor: pointer; }
.page-hit-left { left: 0; }
.page-hit-right { right: 0; }
.page-hit-area[aria-disabled='true'] { cursor: default; }
.page-hit-area:focus-visible { outline: 3px solid #fff; outline-offset: -4px; }
.turning-leaf .leaf-face { position: absolute; inset: 0; display: block; width: 100%; height: 100%; object-fit: fill; backface-visibility: hidden; }
.turning-leaf .leaf-back { transform: rotateY(180deg); }
.turning-leaf.turn-next { left: 50%; transform-origin: left center; animation: turn-next 1200ms ease-in-out forwards; }
.turning-leaf.turn-previous { right: 50%; transform-origin: right center; animation: turn-previous 1200ms ease-in-out forwards; }
.book-arrow { position: absolute; z-index: 2; top: 50%; transform: translateY(-50%); display: grid; place-items: center; width: 44px; height: 44px; border: 0; border-radius: 50%; background: #222d; color: #fff; font: inherit; font-size: 32px; line-height: 1; cursor: pointer; }
.book-arrow-previous { left: 0; }
.book-arrow-next { right: 0; }
.book-arrow[aria-disabled='true'] { opacity: .4; cursor: default; }
.book-arrow:focus-visible { outline: 2px solid #fff; outline-offset: 3px; }
.book-error { position: absolute; bottom: -32px; left: 0; right: 0; text-align: center; }
.book-zoom { position: absolute; bottom: 0; display: flex; align-items: center; gap: 4px; }
.book-zoom button { min-width: 44px; height: 44px; border: 0; border-radius: 6px; background: #333; color: #fff; font: inherit; cursor: pointer; }
.book-zoom button:disabled { opacity: .45; cursor: default; }
.book-zoom button:focus-visible, .book-viewport:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }
@keyframes turn-next { to { transform: rotateY(-180deg); } }
@keyframes turn-previous { to { transform: rotateY(180deg); } }
@media (prefers-reduced-motion: reduce) { .turning-leaf { animation: none !important; } }
</style>
